<?php
namespace App\BatchJobs;
use Europeana\Api\Helpers\Response as Response_Helper;
use Php\Exception;
use Php\FileAdapterInterface;


class JobHandler {

	/**
	 * @var {string}
	 */
	public $control_job_filename;

	/**
	 * @var {\Php\FileAdapterInterface}
	 */
	public $FileAdapter;

	/**
	 * @var {string}
	 */
	public $job_archive_path;

	/**
	 * @var {string}
	 */
	public $job_completed_groups_path;

	/**
	 * @var {string}
	 */
	public $job_failed_path;

	/**
	 * @var {string}
	 */
	public $job_filename_prefix;

	/**
	 * @var {string}
	 */
	public $job_path;

	/**
	 * @var {string}
	 */
	public $job_processing_path;

	/**
	 * @var {string}
	 */
	public $job_output_path;

	/**
	 * @var {string}
	 */
	public $job_succeeded_path;

	/**
	 * @var {string}
	 */
	public $job_to_process_path;

	/**
	 * @var {string}
	 */
	public $storage_path;


	/**
	 * @var {string}
	 */
	protected $job_group_id;

	/**
	 * @var {string}
	 */
	protected $job_group_path;



	/**
	 * @param {array} $options
	 */
	public function __construct( array $options = array() ) {
		$this->init();
		$this->populate( $options );
	}

	/**
	 * @param {string} $job_sub_path
	 * @return {int}
	 */
	protected function countFiles( $job_sub_path = '' ) {
		$result = 0;
		$directory = new \DirectoryIterator ( $this->storage_path . '/' . $this->job_path . '/' . $job_sub_path );

		foreach ( $directory as $fileinfo ) {
			if ( !$fileinfo->isDot() && !$fileinfo->isDir() ) {
				$result += 1;
			}
		}

		return $result;
	}

	/**
	 * @todo adapt this method for this handler
	 *
	 * @param {string} $output_filename
	 * @param {string} $schema
	 */
	protected function closeXMLFile( $output_filename = '', $schema = 'ese' ) {
		if ( empty( $output_filename ) ) {
			throw new Exception( __METHOD__ . ': no filename provided' );
		}

		$output_filename = filter_var( $output_filename, FILTER_SANITIZE_STRING );

		if ( !file_exists( $output_filename ) ) {
			return;
		}

		switch ( $schema ) {
			case 'edm':
				$xml_close = '</records>' . PHP_EOL;
				break;

			default:
				$xml_close =
					'</records>' . PHP_EOL .
					'</searchRetrieveResponse>' . PHP_EOL;
				break;
		}

		$fp = fopen( $output_filename, 'a' );
		fwrite( $fp, $xml_close );
		fclose( $fp );
	}

	/**
	 * @param {\App\BatchJobs\ControlJob} $ControlJob
	 * @return {bool}
	 */
	public function createControlJob( ControlJob $ControlJob ) {
		$this->ensureDirectories();
		$content = '<?php '. PHP_EOL . 'return ' . var_export( get_object_vars( $ControlJob ), true ) . ';' . PHP_EOL;

		$this->FileAdapter->create(
			array(
				'content' => $content,
				'filename' => $this->getControlJobFilename( $ControlJob ),
				'storage_path' => $this->getJobPath( $ControlJob )
			)
		);
	}

	/**
	 * @param {string} $dir
	 */
	protected function createDirectory( $dir = '' ) {
		$dir = $this->sanitizeFilenamePath( $dir );

		if ( empty( $dir ) ) {
			throw new Exception( __METHOD__ . ' no dir provided' );
		}

		if ( !mkdir( $dir, 0755 ) ) {
			throw new Exception( __METHOD__ . 'could not create directory [' . $dir . ']' );
		}
	}

	/**
	 * @param {\App\BatchJobs\Job} $Job
	 * @throws {Exception}
	 * @return {bool}
	 */
	public function createJob( Job $Job ) {
		$Job->validate();
		$this->ensureDirectories();
		$content = '<?php '. PHP_EOL . 'return ' . var_export( get_object_vars( $Job ), true ) . ';' . PHP_EOL;

		$this->FileAdapter->create(
			array(
				'content' => $content,
				'filename' => $this->getJobFilename( $Job ),
				'storage_path' => $this->getToProcessPath( $Job )
			)
		);
	}

	/**
	 * @param {array} $options
	 */
	public function destroy( array $options = array() ) {}

	protected function ensureDirectories() {
		if ( !is_dir( $this->getJobGroupPath() ) ) {
			$this->createDirectory( $this->storage_path . '/' . $this->getJobGroupPath() );
			$this->createDirectory( $this->storage_path . '/' . $this->getJobGroupPath() . '/' . $this->job_failed_path );
			$this->createDirectory( $this->storage_path . '/' . $this->getJobGroupPath() . '/' . $this->job_output_path );
			$this->createDirectory( $this->storage_path . '/' . $this->getJobGroupPath() . '/' . $this->job_processing_path );
			$this->createDirectory( $this->storage_path . '/' . $this->getJobGroupPath() . '/' . $this->job_succeeded_path );
			$this->createDirectory( $this->storage_path . '/' . $this->getJobGroupPath() . '/' . $this->job_to_process_path );
		}
	}

	/**
	 * @param {array} $options
	 */
	public function fetch( array $options = array() ) {}

	protected function init() {
		$this->control_job_filename = 'control-job';
		$this->FileAdapter = null;
		$this->job_archive_path = 'archive';
		$this->job_completed_groups_path = 'completed';
		$this->job_group_id = '';
		$this->job_group_path = '';
		$this->job_failed_path = 'failed';
		$this->job_filename_prefix = 'job-';
		$this->job_output_path = 'output';
		$this->job_path = 'cli-jobs';
		$this->job_processing_path = 'processing';
		$this->job_succeeded_path = 'succeeded';
		$this->job_to_process_path = 'to-process';
		$this->storage_path = '';
	}

	public function getJobGroupId() {
		if ( empty( $this->job_group_id ) ) {
			$this->job_group_id = date( 'Y-m-d_H.i.s' ) . '_' . uniqid();
		}

		return $this->job_group_id;
	}

	public function getJobGroupPath() {
		if ( empty( $this->job_group_path ) ) {
			$this->job_group_path = $this->job_path . '/' . $this->getJobGroupId();
		}

		return $this->job_group_path;
	}

	/**
	 * @todo implement this method
	 * @return {string}
	 */
	public function getControlJob( $job_group_id = '' ) {
		return 'your batch job was created successfully. note this job group id for future reference: <code>' . filter_var( $job_group_id, FILTER_SANITIZE_STRING ) . '</code>';
	}

	/**
	 * @param {\App\BatchJobs\ControlJob}
	 * @return {string}
	 */
	protected function getControlJobFilename( ControlJob $ControlJob ) {
		return $this->control_job_filename . '.php';
	}

	/**
	 * @param {\App\BatchJobs\Job} $Job
	 * @return {string}
	 */
	protected function getJobFilename( Job $Job ) {
		return $this->job_filename_prefix .
			str_pad(
				$Job->job_id,
				(string) strlen( $Job->total_records_found ),
				'0',
				STR_PAD_LEFT
			) .
			'.php';
	}

	/**
	 * @todo move the job group to complete folder
	 */
	protected function getJobFromQueue() {
		$result = '';
		$job_directory = new \DirectoryIterator ( $this->storage_path . '/' . $this->job_path );

		foreach ( $job_directory as $job_directory_fileinfo ) {
			if ( !$job_directory_fileinfo->isDot() ) {
				if ( $job_directory_fileinfo->isDir() ) {
					$job_group_directory = new \DirectoryIterator ( $this->storage_path . '/' . $this->job_path  . '/' . $job_directory_fileinfo->getFilename() . '/' . $this->job_to_process_path );

					foreach ( $job_group_directory as $job_group_directory_fileinfo ) {
						if ( !$job_group_directory_fileinfo->isDot() && !$job_group_directory_fileinfo->isDir() ) {
							$result = $this->storage_path . '/' . $this->job_path  . '/' . $job_directory_fileinfo->getFilename() . '/' . $this->job_to_process_path . '/' . $job_group_directory_fileinfo->getFilename();
							break;
						}
					}

					// nothing was in the directory so this group must be done. do we move it now? is there a chance another job run may be busy with it?
					// maybe should check the directory again after a write to the
					if ( !empty( $result ) ) {
						break;
					}


					//$jobs[$count]['job_to_process'] = $job_group_fileinfo->getFilename() . '/' . $this->job_to_process_path;
				}
			}
		}

		return $result;
	}

	/**
	 * @param {\App\BatchJobs\ControlJob} $ControlJob
	 * @return {string}
	 */
	protected function getJobPath( ControlJob $ControlJob ) {
		if ( !empty( $ControlJob ) ) {
			$job_group_id = $ControlJob->job_group_id;
		} else {
			$job_group_id = $this->getJobGroupId();
		}

		return $this->storage_path . '/' . $this->job_path . '/' . $job_group_id;
	}

	/**
	 * @param {\App\BatchJobs\Job} $Job
	 * @return {string}
	 */
	public function getOutputFilename( Job $Job = null ) {
		if ( !empty( $Job ) ) {
			$job_group_id = $Job->job_group_id;
		} else {
			$job_group_id = $this->getJobGroupId();
		}

		return $job_group_id . '.xml';
	}

	/**
	 * @param {\App\BatchJobs\Job} $Job
	 * @return {string}
	 */
	protected function getOutputPath( Job $Job = null ) {
		if ( !empty( $Job ) ) {
			$job_group_id = $Job->job_group_id;
		} else {
			$job_group_id = $this->getJobGroupId();
		}

		return $this->storage_path . '/' . $this->job_path . '/' . $job_group_id . '/'  . $this->job_output_path;
	}

	/**
	 * @param {\App\BatchJobs\Job} $Job
	 * @return {string}
	 */
	protected function getToProcessPath( Job $Job = null ) {
		if ( !empty( $Job ) ) {
			$job_group_id = $Job->job_group_id;
		} else {
			$job_group_id = $this->getJobGroupId();
		}

		return $this->storage_path . '/' . $this->job_path . '/' . $job_group_id . '/'  . $this->job_to_process_path;
	}

	/**
	 * @param {\App\BatchJobs\Job} $Job
	 * @return {string}
	 */
	protected static function openXMLFile( Job $Job ) {
		switch ( $Job->schema ) {
			case 'edm':
				return
					'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . PHP_EOL .
					'<records>' . PHP_EOL;
				break;

			case 'ese':
				return
					'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . PHP_EOL .
					'<searchRetrieveResponse xmlns:tel="http://krait.kb.nl/coop/tel/handbook/telterms.html" xmlns:mods="http://www.loc.gov/mods/v3" xmlns:enrichment="http://www.europeana.eu/schemas/ese/enrichment/" xmlns:srw="http://www.loc.gov/zing/srw/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:europeana="http://www.europeana.eu" xmlns:xcql="http://www.loc.gov/zing/cql/xcql/" xmlns:diag="http://www.loc.gov/zing/srw/diagnostic/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . PHP_EOL .
					'<records>' . PHP_EOL;
				break;
		}
	}

	/**
	 * @param {array} $options
	 */
	public function populate( array $options = array() ) {
		if ( isset( $options['control_job_filename'] ) && is_string( $options['control_job_filename'] ) ) {
			$this->control_job_filename = filter_var( $options['control_job_filename'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['FileAdapter'] ) && $options['FileAdapter'] instanceof FileAdapterInterface ) {
			$this->FileAdapter = $options['FileAdapter'];
		}

		if ( isset( $options['job_archive_path'] ) && is_string( $options['job_archive_path'] ) ) {
			$this->job_archive_path = filter_var( $options['job_archive_path'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['job_completed_groups_path'] ) && is_string( $options['job_completed_groups_path'] ) ) {
			$this->job_completed_groups_path = filter_var( $options['job_completed_groups_path'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['job_failed_path'] ) && is_string( $options['job_failed_path'] ) ) {
			$this->job_failed_path = filter_var( $options['job_failed_path'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['job_filename_prefix'] ) && is_string( $options['job_filename_prefix'] ) ) {
			$this->job_filename_prefix = filter_var( $options['job_filename_prefix'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['job_output_path'] ) && is_string( $options['job_output_path'] ) ) {
			$this->job_output_path = filter_var( $options['job_output_path'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['job_path'] ) && is_string( $options['job_path'] ) ) {
			$this->job_path = filter_var( $options['job_path'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['job_processing_path'] ) && is_string( $options['job_processing_path'] ) ) {
			$this->job_processing_path = filter_var( $options['job_processing_path'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['job_succeeded_path'] ) && is_string( $options['job_succeeded_path'] ) ) {
			$this->job_succeeded_path = filter_var( $options['job_succeeded_path'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['job_to_process_path'] ) && is_string( $options['job_to_process_path'] ) ) {
			$this->job_to_process_path = filter_var( $options['job_to_process_path'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['storage_path'] ) && is_string( $options['storage_path'] ) ) {
			$this->storage_path = filter_var( $options['storage_path'], FILTER_SANITIZE_STRING );
		}

		$this->validate();
	}

	/**
	 * control method that will process the response and add XML snippets to an output file.
	 *
	 * @param {\App\BatchJobs\Job} $Job
	 * @param {array} $options
	 * @return {bool}
	 */
	public function processJob( Job $Job, array $options = array() ) {
		$Job->validate();
		$wskey = '';

		if ( isset( $options['wskey'] ) ) {
			$wskey = $options['wskey'];
		}

		// set-up the record request
		$RecordRequest = null;
		$Curl = new \Libcurl\Curl();
		$Curl->setHttpHeader( array( 'Accept: text/xml, application/xml' ) );
		$request_options = array(
			'record_id' => $Job->record_id,
			'RequestService' => $Curl,
			'wskey' => $wskey
		);


		// make the call
		switch ( $Job->schema ) {
			case 'edm':
				//$endpoint = 'http://europeana.eu/api/v2/record%s.rdf';
				//$RecordRequest = new \Europeana\Api\Request\RecordRdf( $request_options );
				//$RecordResponse = new \Europeana\Api\Response\RecordRdf( $SearchRequest->call(), $wskey );
				break;

			case 'ese':
				$RecordRequest = new \Europeana\Api\Request\RecordSrw( $request_options );
				$RecordResponse = new \Europeana\Api\Response\RecordSrw( $RecordRequest->call(), $wskey );
				break;
		}

		if ( !( $RecordRequest instanceof \W3C\Http\RequestInterface ) ) {
			throw new Exception( __METHOD__ . '() job schema, [' . filter_var( $Job->schema, FILTER_SANITIZE_STRING ) . '], is not yet handled by the application' );
		}

		if ( !$RecordResponse->loadRecordFromXml() ) {
			error_log( Response_Helper::obfuscateApiKey( $RecordResponse->xml_errors, $wskey ) );
			return false;
		}

		return $this->saveXMLSnippet( $Job, $RecordResponse->xml_snippet_as_string );
	}

	/**
	 * retrieves the first job to be processed from $this->job_path
	 */
	public function retrieve() {
		$result = array(
			'file_path_and_name' => '',
			'job_details' => ''
		);

		if ( empty( $result ) ) {
			return $result;
		}

		$result['file_path_and_name'] = $this->getJobFromQueue();
		$result['job'] = include $result['file_path_and_name'];

		return $result;
	}

	/**
	 * @return {string}
	 */
	public function retrieveJobsAsHtmlTable() {
		$result = 'there are currently no jobs in the queue.';
		$jobs = array();
		$job_directory = new \DirectoryIterator ( $this->storage_path . '/' . $this->job_path );
		$count = 0;

		foreach ( $job_directory as $job_group_fileinfo ) {
			if ( !$job_group_fileinfo->isDot() ) {
				if ( $job_group_fileinfo->isDir() ) {
					$jobs[$count]['job_group'] = $job_group_fileinfo->getFilename();
					$jobs[$count]['job_to_process'] = $this->countFiles( $job_group_fileinfo->getFilename() . '/' . $this->job_to_process_path );
					$jobs[$count]['job_processing'] = $this->countFiles( $job_group_fileinfo->getFilename() . '/' . $this->job_processing_path );
					$jobs[$count]['job_succeeded'] = $this->countFiles( $job_group_fileinfo->getFilename() . '/' . $this->job_succeeded_path );
					$jobs[$count]['job_errors'] = $this->countFiles( $job_group_fileinfo->getFilename() . '/' . $this->job_failed_path );
				}

				$count += 1;
			}
		}

		if ( empty( $jobs ) ) {
			return $result;
		}

		$result = '<table class="table table-striped">';
			$result .= '<thead>';
				$result .= '<tr>';
				$result .= '<th></th>';
				$result .= '<th>job group</th>';
				$result .= '<th>total</th>';
				$result .= '<th>to process</th>';
				$result .= '<th>errors</th>';
			$result .= '</tr>';
			$result .= '</thead>';
			$result .= '<tbody>';

		$rows = '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>';
		$count = 0;

		foreach( $jobs as $job ) {
			// @todo implement delete
			$delete = '<form action="/queue/delete" method="post"><button name="delete" value="' . $job['job_group']. '" class="btn icon-delete" title="delete"></button></form>';
			$delete = '';

			$result .= sprintf(
				$rows,
				$delete,
				$job['job_group'],
				$job['job_to_process'] + $job['job_processing'] + $job['job_succeeded'] + $job['job_errors'],
				$job['job_to_process'] + $job['job_processing'],
				$job['job_errors']
			);

			$count += 1;
		}

		$result .= '</tbody>';
		$result .= '</table>';

		return $result;
	}

	/**
	 * @param {string} $filename_path
	 * a directory, filename or full filename path
	 */
	protected function sanitizeFilenamePath( $filename_path ) {
		return preg_replace( '/[^a-zA-Z0-9\-._\/]/', '', $filename_path );
	}

	/**
	 * @param {\App\BatchJobs\Job} $Job
	 * @param {string} $xml_snippet
	 *
	 * @throws {\Php\Exception}
	 * @return {bool}
	 */
	protected function saveXMLSnippet( Job $Job, $xml_snippet = '' ) {
		if ( empty( $xml_snippet ) ) {
			throw new Exception( __METHOD__ . '() no XML snippet provided' );
		}

		$xml_snippet = $xml_snippet . PHP_EOL;

		if ( !file_exists( $this->getOutputPath( $Job ) . '/' . $Job->output_filename ) ) {
			$xml_snippet = $this->openXMLFile( $Job ) . $xml_snippet;

			return $this->FileAdapter->create(
				array(
					'content' => $xml_snippet,
					'filename' => $Job->output_filename,
					'storage_path' => $this->getOutputPath( $Job )
				)
			);
		} else {
			return $this->FileAdapter->update(
				array(
					'content' => $xml_snippet,
					'filename' => $Job->output_filename,
					'storage_path' => $this->getOutputPath( $Job )
				)
			);
		}
	}

	/**
	 * @throws {Exception}
	 */
	protected function validate() {
		if ( empty( $this->control_job_filename ) || !is_string( $this->control_job_filename ) ) {
			throw new Exception( __METHOD__ . '() control_job_filename not provided', 2 );
		}

		if ( !( $this->FileAdapter instanceof FileAdapterInterface ) ) {
			throw new Exception( __METHOD__ . '() FileAdapter provided is not a valid FileAdapter', 2 );
		}

		if ( empty( $this->job_archive_path ) || !is_string( $this->job_archive_path ) ) {
			throw new Exception( __METHOD__ . '() job_archive_path not provided', 2 );
		}

		if ( empty( $this->job_completed_groups_path ) || !is_string( $this->job_completed_groups_path ) ) {
			throw new Exception( __METHOD__ . '() job_completed_groups_path not provided', 2 );
		}

		if ( empty( $this->job_failed_path ) || !is_string( $this->job_failed_path ) ) {
			throw new Exception( __METHOD__ . '() job_failed_path not provided', 2 );
		}

		if ( empty( $this->job_filename_prefix ) || !is_string( $this->job_filename_prefix ) ) {
			throw new Exception( __METHOD__ . '() job_filename_prefix not provided', 2 );
		}

		if ( empty( $this->job_output_path ) || !is_string( $this->job_output_path ) ) {
			throw new Exception( __METHOD__ . '() job_output_path not provided', 2 );
		}

		if ( empty( $this->job_path ) || !realpath( $this->job_path ) ) {
			throw new Exception( __METHOD__ . '() job_path provided is not a valid path', 2 );
		}

		if ( empty( $this->job_processing_path ) || !is_string( $this->job_processing_path ) ) {
			throw new Exception( __METHOD__ . '() job_processing_path not provided', 2 );
		}

		if ( empty( $this->job_succeeded_path ) || !is_string( $this->job_succeeded_path ) ) {
			throw new Exception( __METHOD__ . '() job_succeeded_path not provided', 2 );
		}

		if ( empty( $this->job_to_process_path ) || !is_string( $this->job_to_process_path ) ) {
			throw new Exception( __METHOD__ . '() job_to_process_path not provided', 2 );
		}

		if ( empty( $this->storage_path ) || !realpath( $this->storage_path ) ) {
			throw new Exception( __METHOD__ . '() storage_path provided is not a valid path', 2 );
		}
	}

}