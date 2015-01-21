<?php
namespace App\BatchJobs;

use App\BatchJobs\ControlJob as ControlJob;
use App\BatchJobs\Job as Job;
use Europeana\Api\Helpers\Response as Response_Helper;
use Penn\Http\RequestInterface;
use Penn\Php\Exception;
use Penn\Php\FileAdapterInterface;

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
	public $job_completed_path;

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
	 * @var {array}
	 */
	protected $allowed_job_paths;

	/**
	 * @var {array}
	 */
	protected $allowed_state_paths;

	/**
	 * @var {string}
	 */
	protected $job_group_id;


	/**
	 * @param {array} $options
	 */
	public function __construct( array $options = array() ) {
		$this->init();
		$this->populate( $options );
	}

	/**
	 * @param {string} $path
	 * @return {int}
	 */
	protected function countFiles( $path = '' ) {
		$result = 0;
		$directory = new \DirectoryIterator ( $path );

		foreach ( $directory as $fileinfo ) {
			if ( !$fileinfo->isDot() && !$fileinfo->isDir() ) {
				$result += 1;
			}
		}

		return $result;
	}

	/**
	 * @param {ControlJob} $ControlJob
	 * @throws {Exception}
	 * @return {bool}
	 */
	public function closeXmlFile( $ControlJob = null ) {
		if ( !( $ControlJob instanceof ControlJob ) ) {
			error_log( __METHOD__ . '() ControlJob provided is not a valid ControlJob.' );
			throw new Exception( 'ControlJob provided is not a valid ControlJob.', 1 );
		}

		switch ( $ControlJob->schema ) {
			case 'edm':
				$xml_close = '</records>' . PHP_EOL;
				break;

			case 'ese':
				$xml_close =
					'</records>' . PHP_EOL .
					'</searchRetrieveResponse>' . PHP_EOL;
				break;
		}

		return $this->FileAdapter->update(
			array(
				'content' => $xml_close,
				'filename' => $this->getOutputFilename( $ControlJob ),
				'storage_path' => $this->getJobPath( 'job_output_path', $ControlJob )
			)
		);
	}

	/**
	 * @param {ControlJob} $ControlJob
	 * @throws {Exception}
	 * @return {bool}
	 */
	public function createControlJob( $ControlJob = null ) {
		if ( !( $ControlJob instanceof ControlJob ) ) {
			error_log( __METHOD__ . '() ControlJob provided is not a valid ControlJob.' );
			throw new Exception( 'ControlJob provided is not a valid ControlJob.', 1 );
		}

		$this->ensureDirectories();
		$content = '<?php '. PHP_EOL . 'return ' . var_export( get_object_vars( $ControlJob ), true ) . ';' . PHP_EOL;

		return $this->FileAdapter->create(
			array(
				'content' => $content,
				'filename' => $this->getControlJobFilename( $ControlJob ),
				'storage_path' => $this->getJobPath( 'job_group_path', $ControlJob )
			)
		);
	}

	/**
	 * @param {string} $dir
	 * @throws {Exception}
	 */
	protected function createDirectory( $dir = '' ) {
		$dir = $this->sanitizeFilenamePath( $dir );

		if ( empty( $dir ) ) {
			error_log( __METHOD__ . '() no dir provided' );
			throw new Exception( 'no dir provided', 25 );
		}

		$this->FileAdapter->mkDir( $dir, 0755 );
	}

	/**
	 * @param {Job} $Job
	 * @throws {Exception}
	 * @return {bool}
	 */
	public function createJob( $Job = null ) {
		if ( !( $Job instanceof Job ) ) {
			error_log( __METHOD__ . '() Job provided is not a valid Job.' );
			throw new Exception( 'Job provided is not a valid Job.', 1 );
		}

		$Job->validate();
		$this->ensureDirectories();
		$content = '<?php '. PHP_EOL . 'return ' . var_export( get_object_vars( $Job ), true ) . ';' . PHP_EOL;

		$this->FileAdapter->create(
			array(
				'content' => $content,
				'filename' => $this->getJobFilename( $Job ),
				'storage_path' => $this->getJobPath( 'job_to_process_path', $Job )
			)
		);
	}

	/**
	 * @param {array} $options
	 */
	public function destroy( array $options = array() ) {}

	protected function ensureDirectories() {
		if ( !is_dir( $this->getJobPath( 'job_group_path' ) ) ) {
			$this->createDirectory( $this->getJobPath( 'job_group_path' ) );
			$this->createDirectory( $this->getJobPath( 'job_failed_path' ) );
			$this->createDirectory( $this->getJobPath( 'job_output_path' )  );
			$this->createDirectory( $this->getJobPath( 'job_processing_path' )  );
			$this->createDirectory( $this->getJobPath( 'job_succeeded_path' )  );
			$this->createDirectory( $this->getJobPath( 'job_to_process_path' )  );
		}
	}

	/**
	 * tries to find a job and return it.
	 *
	 * the $options determine which type of job state to look for
	 * and which job type to return
	 *
	 * @param {array} $options
	 *
	 * @param {bool} $options['job-required']
	 * whether or not to continue looking until a Job is found
	 * defaults to true
	 *
	 * @param {array} $options['job-state-paths']
	 *
	 * @param {string} $options['job-state-paths'][]
	 * one of $this->allowed_state_paths
	 *
	 * @return {null|ControlJob} $result['ControlJob']
	 * @return {null|Job} $result['Job']
	 */
	protected function findJob( array $options = array() ) {
		$result = array(
			'ControlJob' => null,
			'Job' => null
		);

		if ( !isset( $options['job-required'] ) || !is_bool( $options['job-required'] ) ) {
			$options['job-required'] = true;
		}

		$job_directory = new \DirectoryIterator ( $this->storage_path . '/' . $this->job_path );

		// find a job group directory
		foreach ( $job_directory as $job_directory_fileinfo ) {
			if ( !$job_directory_fileinfo->isDot() ) {

				// found a job group directory
				if ( $job_directory_fileinfo->isDir() ) {

					// look for a job in the $options['job-state-paths'][]
					foreach( $options['job-state-paths'] as $job_state_path ) {
						$result = $this->findJobInState(
							array(
								'job-group' => $job_directory_fileinfo->getFilename(),
								'job-required' => $options['job-required'],
								'job-state-path' => $job_state_path
							)
						);

						// if a Job is not required and a ControlJob was returned, stop
						if ( !$options['job-required'] && $result['ControlJob'] instanceof ControlJob ) {
							break;

						// if a Job  is required and a Job was returned, stop
						} elseif ( $result['Job'] instanceof Job ) {
							break;
						}

						// otherwise, continue to look for a Job
					}
				}
			}

			// otherwise, continue to look for a job group directory with a Job
			// in one of the $options['job-state-paths'][]
		}

		return $result;
	}

	/**
	 * find a job in a given group, in a given state and return it
	 * or the control job for the group.
	 *
	 * @param {array} $options
	 * @param {string} $options['job-group']
	 *
	 * @param {string} $options['job-path']
	 * one of $this->allowed_job_paths
	 *
	 * @param {bool} $options['job-required']
	 * whether or not to continue looking until a Job is found
	 * defaults to true
	 *
	 * @param {string} $options['job-state-path']
	 * one of $this->allowed_state_paths
	 *
	 * @throws {Exception}
	 *
	 * @return {null|ControlJob} $result['ControlJob']
	 * @return {null|Job} $result['Job']
	 */
	protected function findJobInState( array $options = array() ) {
		$default_options = array(
			'job-path' => 'job_path',
			'job-required' => true
		);

		$result = array(
			'ControlJob' => null,
			'Job' => null
		);

		$options = array_merge( $default_options, $options );

		if ( !isset( $options['job-group'] ) || !is_string( $options['job-group'] ) ) {
			error_log( __METHOD__ . '() job-group not provided.' );
			throw new Exception( 'job-group not provided.', 25 );
		}

		if ( !in_array( $options['job-path'], $this->allowed_job_paths ) ) {
			error_log( __METHOD__ . '() job-path [' . filter_var( $options['job-path'], FILTER_SANITIZE_STRING ) . '] not yet handled by the application' );
			throw new Exception( 'job-path given not yet handled by the application', 26 );
		}

		// find the ControlJob
		$control_job_path_and_filename = $this->storage_path . '/' . $this->{$options['job-path']}  . '/' . $options['job-group'] . '/' . $this->control_job_filename . '.php';

		if ( file_exists( $control_job_path_and_filename ) ) {
			$result['ControlJob'] = new ControlJob( include $control_job_path_and_filename );
		}

		if ( !$options['job-required'] ) {
			return $result;
		}

		// find a Job in the state given
		if ( !in_array( $options['job-state-path'], $this->allowed_state_paths ) ) {
			error_log( __METHOD__ . '() job-state-path [' . filter_var( $options['job-state-path'], FILTER_SANITIZE_STRING ) . '] not yet handled by the application.' );
			throw new Exception( 'job-state-path given not yet handled by the application', 26 );
		}

		$job_filepath_and_name = '';
		$job_state_directory = new \DirectoryIterator ( $this->storage_path . '/' . $this->{$options['job-path']}  . '/' . $options['job-group'] . '/' . $this->{$options['job-state-path']} );

		foreach ( $job_state_directory as $job_fileinfo ) {

			// found a Job, stop searching
			if ( !$job_fileinfo->isDot() && !$job_fileinfo->isDir() ) {
				$job_filepath_and_name = $this->storage_path . '/' . $this->job_path  . '/' . $options['job-group'] . '/' . $this->{$options['job-state-path']} . '/' . $job_fileinfo->getFilename();
				break;
			}

		}

		// if a Job exists, set $result['Job'] to that Job
		if ( file_exists( $job_filepath_and_name ) ) {
			$result['Job'] = new Job( include $job_filepath_and_name );
		}

		return $result;
	}

	protected function init() {
		$this->control_job_filename = 'control-job';
		$this->FileAdapter = null;
		$this->job_archive_path = 'cli-job-archive';
		$this->job_completed_path = 'cli-jobs-completed';
		$this->job_group_id = '';
		$this->job_failed_path = 'failed';
		$this->job_filename_prefix = 'job-';
		$this->job_output_path = 'output';
		$this->job_path = 'cli-jobs';
		$this->job_processing_path = 'processing';
		$this->job_succeeded_path = 'succeeded';
		$this->job_to_process_path = 'to-process';
		$this->storage_path = '';

		$this->allowed_job_paths = array(
			'job_path',
			'job_completed_path'
		);

		$this->allowed_state_paths = array(
			'job_failed_path',
			'job_output_path',
			'job_processing_path',
			'job_succeeded_path',
			'job_to_process_path'
		);
	}

	/**
	 * retrieves the first job group that has no more to process or processing jobs
	 *
	 * @return {null|ControlJob}
	 */
	public function getCompletedJobGroup() {
		$result = $this->findJob(
			array(
				'job-state-paths' => array( 'job_to_process_path', 'job_processing_path' ),
				'job-required' => false
			)
		);

		if ( empty( $result['Job'] ) ) {
			return $result['ControlJob'];
		}

		return null;
	}

	/**
	 * @param {string} $job_group_id
	 * @param {string} $job_path
	 *
	 * @return {null|JobControl}
	 */
	public function getControlJob( $job_group_id = '', $job_path = 'job_path' ) {
		$job_group_id = filter_var( $job_group_id, FILTER_SANITIZE_STRING );
		$job_path = filter_var( $job_path, FILTER_SANITIZE_STRING );

		$result = $this->findJobInState(
			array(
				'job-group' => $job_group_id,
				'job-path' => $job_path,
				'job-required' => false
			)
		);

		return $result['ControlJob'];
	}

	/**
	 * @param {ControlJob|Job}
	 * @return {string}
	 */
	public function getJobGroupId( $Job = null ) {
		if ( $Job instanceof ControlJob || $Job instanceof Job ) {
			$this->job_group_id = $Job->job_group_id;
		} elseif ( empty( $this->job_group_id ) ) {
			$this->job_group_id = date( 'Y-m-d_H.i.s' ) . '_' . uniqid();
		}

		return $this->job_group_id;
	}

	/**
	 * @param {string} $job_group_id
	 * @return {string}
	 */
	public function getJobGroupHtml( $job_group_id = '' ) {
		$result = '';
		$job_group_id = filter_var( $job_group_id, FILTER_SANITIZE_STRING ) ;

		// search for completed output
		$completed_output_directory = new \DirectoryIterator ( $this->storage_path . '/' . $this->job_completed_path );

		foreach ( $completed_output_directory as $output_fileinfo ) {
			if (
				!$output_fileinfo->isDot() &&
				$output_fileinfo->isDir() &&
				$output_fileinfo->getFilename() === $job_group_id
			) {
				$result = '<h2 class="page-header">batch job ready</h2><p>the batch job completed. you can now <a href="/downloads/?job-group-id=' . urlencode( $job_group_id ) . '">download the output</a>.</p>';
				$result .= $this->getJobGroupAsHtmlTable( $job_group_id, 'job_completed_path' );
			}
		}

		// no completed output file, search for the job group in the active jobs directory
		if ( empty( $result ) ) {
			$result = $this->getJobGroupAsHtmlTable( $job_group_id );

			if ( !empty( $result ) ) {
				$result = '<h2 class="page-header">batch job status</h2><p>note this <a href="/queue/?job-group-id=' . urlencode( $job_group_id ) . '">page url</a> in order to keep track of the job status. when the batch job completes, a link to the download page will appear here.</p>' . $result;
			}
		}

		if ( empty( $result ) ) {
			$result = '<h2 class="page-header">no batch job</h2><p>the batch job group <code>' . $job_group_id . '</code> does not exist.</p>';
		}

		return $result;
	}

	/**
	 * @todo try to integrate this method with getJobsAsHtmlTable
	 *
	 * @param {string} $job_group_id
	 * @param {string} $path
	 *
	 * @return {string}
	 */
	protected function getJobGroupAsHtmlTable( $job_group_id = '', $job_path = 'job_path' ) {
		$result = '';
		$job_info = array();

		switch ( $job_path ) {
			case 'job_completed_path':
				$job_group_path = $this->storage_path . '/' . $this->job_completed_path . '/' . $job_group_id;
				break;

			case 'job_path':
				$job_group_path = $this->storage_path . '/' . $this->job_path . '/' . $job_group_id;
				break;

			default:
				return $result;
		}

		if ( !realpath( $job_group_path ) ) {
			return $result;
		}

		$job_group_directory = new \DirectoryIterator ( $job_group_path );

		foreach ( $job_group_directory as $job_group_fileinfo ) {
			if ( !$job_group_fileinfo->isDot() ) {
				if ( $job_group_fileinfo->isDir() ) {
					$job_info['job_group'] = $job_group_id;
					$job_info['job_to_process'] = $this->countFiles( $job_group_path . '/' . $this->job_to_process_path );
					$job_info['job_processing'] = $this->countFiles( $job_group_path . '/' . $this->job_processing_path );
					$job_info['job_succeeded'] = $this->countFiles( $job_group_path . '/' . $this->job_succeeded_path );
					$job_info['job_errors'] = $this->countFiles( $job_group_path . '/' . $this->job_failed_path );
				}
			}
		}

		if ( empty( $job_info ) ) {
			return $result;
		}

		$result = '<table class="table table-striped">';
			$result .= '<thead>';
				$result .= '<tr>';
				$result .= '<th></th>';
				$result .= '<th>job group</th>';
				$result .= '<th>username</th>';
				$result .= '<th>params</th>';
				$result .= '<th>total</th>';
				$result .= '<th>to process</th>';
				$result .= '<th>succeeded</th>';
				$result .= '<th>errors</th>';
			$result .= '</tr>';
			$result .= '</thead>';
			$result .= '<tbody>';

		$rows = '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>';

		//@todo implement delete
		//$delete = '<form action="/queue/delete" method="post"><button name="delete" value="' . $job['job_group']. '" class="btn icon-delete" title="delete"></button></form>';
		$delete = '';
		$username = '';
		$params = '';
		$total = $job_info['job_to_process'] + $job_info['job_processing'] + $job_info['job_succeeded'] + $job_info['job_errors'];
		$ControlJob = $this->getControlJob( $job_info['job_group'], $job_path );

		if ( $ControlJob instanceof ControlJob ) {
			$username = $ControlJob->username;
			$params = $ControlJob->params;

			// this happens when not all jobs were created with the control job; e.g., search.json
			if ( $total < $ControlJob->total_records_found ) {
				$total = $ControlJob->total_records_found;
			}
		}

		$result .= sprintf(
			$rows,
			$delete,
			$job_info['job_group'],
			$username,
			$params,
			$total,
			$job_info['job_to_process'] + $job_info['job_processing'],
			$job_info['job_succeeded'],
			$job_info['job_errors']
		);

		$result .= '</tbody>';
		$result .= '</table>';

		return $result;
	}

	/**
	 * @todo try to integrate this method with getJobGroupAsHtmlTable
	 * @return {string}
	 */
	public function getJobsAsHtmlTable() {
		$result = '<h2 class="page-header">batch jobs</h2><p>there are currently no jobs in the queue.</p>';
		$jobs = array();
		$job_path = $this->storage_path . '/' . $this->job_path;
		$job_directory = new \DirectoryIterator ( $job_path );
		$count = 0;

		foreach ( $job_directory as $job_group_fileinfo ) {
			if ( !$job_group_fileinfo->isDot() ) {
				if ( $job_group_fileinfo->isDir() ) {
					$jobs[$count]['job_group'] = $job_group_fileinfo->getFilename();
					$jobs[$count]['job_to_process'] = $this->countFiles( $job_path . '/' . $job_group_fileinfo->getFilename() . '/' . $this->job_to_process_path );
					$jobs[$count]['job_processing'] = $this->countFiles( $job_path . '/' . $job_group_fileinfo->getFilename() . '/' . $this->job_processing_path );
					$jobs[$count]['job_succeeded'] = $this->countFiles( $job_path . '/' . $job_group_fileinfo->getFilename() . '/' . $this->job_succeeded_path );
					$jobs[$count]['job_errors'] = $this->countFiles( $job_path . '/' . $job_group_fileinfo->getFilename() . '/' . $this->job_failed_path );
				}

				$count += 1;
			}
		}

		if ( empty( $jobs ) ) {
			return $result;
		}

		$result = '<h2 class="page-header">batch job queue</h2><p>below is a status table listing all currently active batch jobs.</p><p>note that the total columns, to process, succeeded, and errors, for jobs run against search.json, may not add up to the nr in the total column until all batch jobs have been put into the queue. whereas, jobs runs against tag.json are put all at once into the queue and should always equal the total column.</p>';
		$result .= '<table class="table table-striped">';
			$result .= '<thead>';
				$result .= '<tr>';
				$result .= '<th></th>';
				$result .= '<th>job group</th>';
				$result .= '<th>username</th>';
				$result .= '<th>params</th>';
				$result .= '<th>total</th>';
				$result .= '<th>to process</th>';
				$result .= '<th>succeeded</th>';
				$result .= '<th>errors</th>';
			$result .= '</tr>';
			$result .= '</thead>';
			$result .= '<tbody>';

		$rows = '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>';
		$count = 0;

		foreach( $jobs as $job ) {
			// @todo implement delete
			//$delete = '<form action="/queue/delete" method="post"><button name="delete" value="' . $job['job_group']. '" class="btn icon-delete" title="delete"></button></form>';
			$delete = '';
			$username = '';
			$params = '';
			$total = $job['job_to_process'] + $job['job_processing'] + $job['job_succeeded'] + $job['job_errors'];
			$ControlJob = $this->getControlJob( $job['job_group'] );

			if ( $ControlJob instanceof ControlJob ) {
				$username = $ControlJob->username;
				$params = $ControlJob->params;

				// this happens when not all jobs were created with the control job; e.g., search.json
				if ( $total < $ControlJob->total_records_found ) {
					$total = $ControlJob->total_records_found;
				}
			}

			$result .= sprintf(
				$rows,
				$delete,
				'<a href="/queue/?job-group-id=' . $job['job_group'] . '">' . $job['job_group'] . '</a>',
				$username,
				$params,
				$total,
				$job['job_to_process'] + $job['job_processing'],
				$job['job_succeeded'],
				$job['job_errors']
			);

			$count += 1;
		}

		$result .= '</tbody>';
		$result .= '</table>';

		return $result;
	}

	/**
	 * @return {string}
	 * only the job filename, does not include the absolute or relative path
	 */
	protected function getControlJobFilename() {
		return $this->control_job_filename . '.php';
	}

	/**
	 * @param {Job}
	 * @throws {Exception}
	 * @return {string}
	 */
	protected function getJobAsXml( $Job = null ) {
		if ( !( $Job instanceof Job ) ) {
			error_log( __METHOD__ . '() Job provided is not a valid Job' );
			throw new Exception( 'Job provided is not a valid Job', 1 );
		}

		$properties = get_object_vars( $Job );
		$result = '<batch_job_metadata>' . PHP_EOL;

		foreach( $properties as $key => $value ) {
			$result .= chr(9) . '<' . $key . '>' . htmlspecialchars( $value ) . '</' . $key . '>' . PHP_EOL;
		}

		$result .= '</batch_job_metadata>';
		return $result;
	}

	/**
	 * @param {Job} $Job
	 * @throws {Exception}
	 *
	 * @return {string}
	 * only the job filename, does not include the absolute or relative path
	 */
	protected function getJobFilename( $Job = null ) {
		if ( !( $Job instanceof Job ) ) {
			error_log( __METHOD__ . '() Job provided is not a valid Job' );
			throw new Exception( 'Job provided is not a valid Job', 1 );
		}

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
	 * retrieves the first job to be processed.
	 *
	 * @return {null|Job}
	 */
	public function getJobFromQueue() {
		$result = null;

		$result = $this->findJob(
			array(
				'job-state-paths' => array( 'job_to_process_path' )
			)
		);

		return $result['Job'];
	}

	/**
	 * @param {string} $type
	 * job_group_path|job_failed_path|job_output_path|
	 * job_processing_path|job_succeeded_path|job_to_process_path
	 *
	 * @param {ControlJob|Job} $Job
	 *
	 * @return {string}
	 * an absolute storage path
	 */
	protected function getJobPath( $type = '', $Job = null ) {
		$result = '';
		$job_group_id = $this->getJobGroupId( $Job );

		$storage_group_path = $this->storage_path . '/' . $this->job_path . '/' . $job_group_id;

		switch( $type ) {
			case 'job_completed_path':
				$result = $this->storage_path . '/' . $this->job_completed_path . '/' . $job_group_id;
				break;

			case 'job_group_path':
				$result = $storage_group_path;
				break;

			case 'job_failed_path':
				$result = $storage_group_path . '/'  . $this->job_failed_path;
				break;

			case 'job_output_path':
				$result = $storage_group_path . '/'  . $this->job_output_path;
				break;

			case 'job_processing_path':
				$result = $storage_group_path . '/'  . $this->job_processing_path;
				break;

			case 'job_succeeded_path':
				$result = $storage_group_path . '/'  . $this->job_succeeded_path;
				break;

			case 'job_to_process_path':
				$result = $storage_group_path . '/'  . $this->job_to_process_path;
				break;
		}

		return $result;
	}

	/**
	 * @param {Job|ControlJob} $Job
	 *
	 * @return {string}
	 * only the job filename, does not include the absolute or relative path
	 */
	public function getOutputFilename( $Job = null ) {
		return $this->getJobGroupId( $Job ) . '.xml';
	}

	/**
	 * @param {string} $dest
	 * @param {Job} $Job
	 *
	 * @throws {Exception}
	 *
	 * @return {bool}
	 */
	public function moveJob( $dest = '', $Job = null ) {
		if ( !( $Job instanceof Job ) ) {
			error_log( __METHOD__ . '() Job provided is not a valid Job' );
			throw new Exception( 'Job provided is not a valid Job', 1 );
		}

		$dest_path = '';

		switch( $dest ) {
			case 'job_processing_path':
				$source_path = $this->getJobPath( 'job_to_process_path', $Job );
				$dest_path = $this->getJobPath( $dest, $Job );
				break;

			case 'job_failed_path':
				$source_path = $this->getJobPath( 'job_processing_path', $Job );
				$dest_path = $this->getJobPath( $dest, $Job );
				break;

			case 'job_succeeded_path':
				$source_path = $this->getJobPath( 'job_processing_path', $Job );
				$dest_path = $this->getJobPath( $dest, $Job );
				break;
		}

		if ( empty( $dest_path ) ) {
			error_log( __METHOD__ . '() destination [' . $dest . '] is not yet handled by the application' );
			throw new Exception( 'destination given is not yet handled by the application', 26 );
		}

		return $this->FileAdapter->move(
			array(
				'source_path' => $source_path,
				'source_filename' => $this->getJobFilename( $Job ),
				'dest_path' => $dest_path,
				'dest_filename' => $this->getJobFilename( $Job ),
			)
		);
	}

	/**
	 * @param {string} $dest
	 * @param {ControlJob} $ControlJob
	 *
	 * @throws {Exception}
	 *
	 * @return {bool}
	 */
	public function moveJobGroup( $dest = '', $ControlJob = null ) {
		if ( !( $ControlJob instanceof ControlJob ) ) {
			error_log( __METHOD__ . '() ControlJob provided is not a valid ControlJob' );
			throw new Exception( 'ControlJob provided is not a valid ControlJob', 1 );
		}

		$dest = filter_var( $dest, FILTER_SANITIZE_STRING );
		$dest_path = '';

		switch( $dest ) {
			case 'job_completed_path':
				$source_path = $this->getJobPath( 'job_group_path', $ControlJob );
				$dest_path = $this->getJobPath( $dest, $ControlJob );
				break;
		}

		if ( empty( $dest_path ) ) {
			error_log( __METHOD__ . '() destination [' . $dest . '] is not yet handled by the application' );
			throw new Exception( 'destination given is not yet handled by the application', 26 );
		}

		return $this->FileAdapter->move(
			array(
				'source_path' => $source_path,
				'dest_path' => $dest_path
			)
		);
	}

	/**
	 * @param {Job} $Job
	 * @throws {Exception}
	 * @return {string}
	 */
	protected function openXmlFile( $Job = null ) {
		if ( !( $Job instanceof Job ) ) {
			error_log( __METHOD__ . '() Job provided is not a valid Job' );
			throw new Exception( 'Job provided is not a valid Job', 1 );
		}

		switch ( $Job->schema ) {
			case 'edm':
				return
					'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . PHP_EOL .
					$this->getJobAsXml( $Job ) . PHP_EOL .
					'<records>' . PHP_EOL;
				break;

			case 'ese':
				return
					'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . PHP_EOL .
					'<searchRetrieveResponse xmlns:tel="http://krait.kb.nl/coop/tel/handbook/telterms.html" xmlns:mods="http://www.loc.gov/mods/v3" xmlns:enrichment="http://www.europeana.eu/schemas/ese/enrichment/" xmlns:srw="http://www.loc.gov/zing/srw/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:europeana="http://www.europeana.eu" xmlns:xcql="http://www.loc.gov/zing/cql/xcql/" xmlns:diag="http://www.loc.gov/zing/srw/diagnostic/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . PHP_EOL .
					$this->getJobAsXml( $Job ) . PHP_EOL .
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

		if ( isset( $options['job_completed_path'] ) && is_string( $options['job_completed_path'] ) ) {
			$this->job_completed_path = filter_var( $options['job_completed_path'], FILTER_SANITIZE_STRING );
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
	 * @param {array} $options
	 * @param {Job} $options['Job']
	 * @param {string} $options['wskey']
	 *
	 * @throws {Exception}
	 * @return {bool}
	 */
	public function processJob( array $options = array() ) {
		if ( !isset( $options['Job'] ) || !( $options['Job'] instanceof Job ) ) {
			error_log( __METHOD__ . '() Job provided is not a valid Job' );
			throw new Exception( 'Job provided is not a valid Job', 1 );
		} else {
			$Job = $options['Job'];
		}

		if ( isset( $options['wskey'] ) && is_string( $options['wskey'] ) ) {
			$wskey = filter_var( $options['wskey'], FILTER_SANITIZE_STRING );
		} else {
			$wskey = '';
		}

		// set-up the record request
		$RecordRequest = null;
		$Curl = new \Penn\Php\Curl();
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

		if ( !( $RecordRequest instanceof RequestInterface ) ) {
			error_log( __METHOD__ . '() job schema, [' . filter_var( $Job->schema, FILTER_SANITIZE_STRING ) . '], is not yet handled by the application' );
			throw new Exception( 'job schema given is not yet handled by the application', 26 );
		}

		if ( !$RecordResponse->loadRecordFromXml() ) {
			error_log( Response_Helper::obfuscateApiKey( $RecordResponse->xml_errors, $wskey ) );
			return false;
		}

		return $this->saveXmlSnippet( $Job, $RecordResponse->xml_snippet_as_string );
	}

	/**
	 * @param {string} $filename_path
	 * a directory, filename or full filename path
	 */
	protected function sanitizeFilenamePath( $filename_path = '' ) {
		return preg_replace( '/[^a-zA-Z0-9\-._\/]/', '', $filename_path );
	}

	/**
	 * @param {Job} $Job
	 * @param {string} $xml_snippet
	 *
	 * @throws {Exception}
	 * @return {bool}
	 */
	protected function saveXmlSnippet( $Job, $xml_snippet = '' ) {
		if ( !( $Job instanceof Job ) ) {
			error_log( __METHOD__ . '() Job provided is not a valid Job' );
			throw new Exception( 'Job provided is not a valid Job', 1 );
		}

		if ( empty( $xml_snippet ) ) {
			error_log( __METHOD__ . '() no XML snippet provided' );
			throw new Exception( 'no XML snippet provided', 25 );
		}

		$xml_snippet = $xml_snippet . PHP_EOL;

		// output file does not yet exist, create it
		if ( !file_exists( $this->getJobPath( 'job_output_path', $Job ) . '/' . $Job->output_filename ) ) {
			$xml_snippet = $this->openXmlFile( $Job ) . $xml_snippet;

			return $this->FileAdapter->create(
				array(
					'content' => $xml_snippet,
					'filename' => $Job->output_filename,
					'storage_path' => $this->getJobPath( 'job_output_path', $Job )
				)
			);

		// output file exists, update it
		} else {
			return $this->FileAdapter->update(
				array(
					'content' => $xml_snippet,
					'filename' => $Job->output_filename,
					'storage_path' => $this->getJobPath( 'job_output_path', $Job )
				)
			);
		}
	}

	/**
	 * @throws {Exception}
	 */
	protected function validate() {
		if ( empty( $this->control_job_filename ) || !is_string( $this->control_job_filename ) ) {
			error_log( __METHOD__ . '() control_job_filename not provided' );
			throw new Exception( 'control_job_filename not provided', 2 );
		}

		if ( !( $this->FileAdapter instanceof FileAdapterInterface ) ) {
			error_log( __METHOD__ . '() FileAdapter provided is not a valid FileAdapter' );
			throw new Exception( 'FileAdapter provided is not a valid FileAdapter', 2 );
		}

		if ( empty( $this->job_archive_path ) || !is_string( $this->job_archive_path ) ) {
			error_log( __METHOD__ . '() job_archive_path not provided' );
			throw new Exception( 'job_archive_path not provided', 2 );
		}

		if ( empty( $this->job_completed_path ) || !is_string( $this->job_completed_path ) ) {
			error_log( __METHOD__ . '() job_completed_path not provided' );
			throw new Exception( 'job_completed_path not provided', 2 );
		}

		if ( empty( $this->job_failed_path ) || !is_string( $this->job_failed_path ) ) {
			error_log( __METHOD__ . '() job_failed_path not provided' );
			throw new Exception( 'job_failed_path not provided', 2 );
		}

		if ( empty( $this->job_filename_prefix ) || !is_string( $this->job_filename_prefix ) ) {
			error_log( __METHOD__ . '() job_filename_prefix not provided' );
			throw new Exception( 'job_filename_prefix not provided', 2 );
		}

		if ( empty( $this->job_output_path ) || !is_string( $this->job_output_path ) ) {
			error_log( __METHOD__ . '() job_output_path not provided' );
			throw new Exception( 'job_output_path not provided', 2 );
		}

		if ( empty( $this->job_path ) || !realpath( $this->job_path ) ) {
			error_log( __METHOD__ . '() job_path provided is not a valid path' );
			throw new Exception( 'job_path provided is not a valid path', 2 );
		}

		if ( empty( $this->job_processing_path ) || !is_string( $this->job_processing_path ) ) {
			error_log( __METHOD__ . '() job_processing_path not provided' );
			throw new Exception( 'job_processing_path not provided', 2 );
		}

		if ( empty( $this->job_succeeded_path ) || !is_string( $this->job_succeeded_path ) ) {
			error_log( __METHOD__ . '() job_succeeded_path not provided' );
			throw new Exception( 'job_succeeded_path not provided', 2 );
		}

		if ( empty( $this->job_to_process_path ) || !is_string( $this->job_to_process_path ) ) {
			error_log( __METHOD__ . '() job_to_process_path not provided' );
			throw new Exception( 'job_to_process_path not provided', 2 );
		}

		if ( empty( $this->storage_path ) || !realpath( $this->storage_path ) ) {
			error_log( __METHOD__ . '() storage_path provided is not a valid path' );
			throw new Exception( 'storage_path provided is not a valid path', 2 );
		}
	}

}