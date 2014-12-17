<?php

namespace App\Helpers;
use Europeana\Api\Response\Json\Search as SearchResponse;
use Exception;


class Jobs {

	/**
	 * retrieve the job list array
	 * add a job to that list
	 * save the updated job list
	 *
	 * @param {array} $job
	 *
	 * @param {array} $options
	 * @param {string} $options['filename']
	 * @param {array} $options['path']
	 *
	 * @throws {Exception}
	 * @return {bool}
	 */
	public static function addJobToFile( $job, $options ) {
		// verify paramters
		if ( !is_array( $job ) ) {
			throw new Exception( __METHOD__ . ': job not provided as an array' );
		}

		if ( empty( $job ) ) {
			throw new Exception( __METHOD__ . ': no job provided' );
		}

		$filename = self::getFilepath( $options );

		if ( file_exists( $filename ) ) {
			$jobs = include $filename;
			$jobs[] = $job;
		} else {
			$jobs = array();
			$jobs[] = $job;
		}

		return self::saveJobs( $filename, $jobs );
	}

	/**
	 * @param {string} $output_filename
	 * @param {string} $schema
	 */
	protected static function closeXMLFile( $output_filename = '', $schema = 'ese' ) {
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
	 * @param {string} $job_identifier
	 */
	public static function createOutputFilename( $job_identifier ) {
		return date( 'Y-m-d_H:i:s_' ) . $job_identifier . '.xml';
	}

	/**
	 * @param {array} $options
	 * @param {string} $options['filename']
	 * @param {array} $options['path']
	 *
	 * @return {string}
	 */
	protected static function getFilepath( $options ) {
		if ( !is_array( $options ) ) {
			throw new Exception( __METHOD__ . ': options not provided as an array' );
		}

		if ( empty( $options['filename'] ) ) {
			throw new Exception( __METHOD__ . ': no filename provided' );
		}

		if ( empty( $options['path'] ) ) {
			throw new Exception( __METHOD__ . ': no path provided' );
		}

		return (  $options['path'] . $options['filename'] );
	}

	/**
	 * @param {string} $url
	 * @param {string} $schema
	 *
	 * @return {array}
	 */
	protected static function loadRecordFromXml( $url = '', $schema = 'ese' ) {
		$result = array( 'success' => false, 'xml-snippet' => '', 'msg' => '' );

		if ( empty( $url ) ) {
			throw new Exception( __METHOD__ . ': no URL provided' );
		}

		libxml_use_internal_errors( true );

		// supress errors so that they can be handled programatically
		$xml = @simplexml_load_file( $url );

		if ( $xml === false ) {
			$result['msg'] = self::logLibXMLError();
			return $result;
		}

		$old_value = libxml_disable_entity_loader( true );

		switch( $schema ) {
			case 'edm':
				$result = self::processRdfXmlRecord( $xml, $result );
				break;

			default:
				$result = self::processSrwXmlRecord( $xml, $result );
		}

		libxml_disable_entity_loader( $old_value );
		return $result;
	}

	/**
	 * @return {string}
	 */
	protected static function logLibXMLError() {
		$msg = '';

		foreach( libxml_get_errors() as $error ) {
			switch ( $error->level ) {
				case LIBXML_ERR_WARNING:
					$msg .= 'LibXML Warning';
					break;

				case LIBXML_ERR_ERROR:
					$msg .= 'LibXML Error';
					break;

				case LIBXML_ERR_FATAL:
					$msg .= 'LibXML Fatal Error';
					break;
			}

			$msg .= ' ' . $error->code;
			$msg .= ' : ' . $error->message;
			$msg .= ' - file ' . $error->file;
			$msg .= ', line ' . $error->line;
		}

		libxml_clear_errors();

		// http://stackoverflow.com/questions/3760816/remove-new-lines-from-string#answer-3760830
		return trim( preg_replace( '/\s\s+/', ' ', $msg ) );
	}

	/**
	 * @param {string} $schema
	 */
	protected static function openXMLFile( $schema = 'ese' ) {
		switch ( $schema ) {
			case 'edm':
				return
					'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . PHP_EOL .
					'<records>' . PHP_EOL;
				break;

			default:
				return
					'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . PHP_EOL .
					'<searchRetrieveResponse xmlns:tel="http://krait.kb.nl/coop/tel/handbook/telterms.html" xmlns:mods="http://www.loc.gov/mods/v3" xmlns:enrichment="http://www.europeana.eu/schemas/ese/enrichment/" xmlns:srw="http://www.loc.gov/zing/srw/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:europeana="http://www.europeana.eu" xmlns:xcql="http://www.loc.gov/zing/cql/xcql/" xmlns:diag="http://www.loc.gov/zing/srw/diagnostic/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . PHP_EOL .
					'<records>' . PHP_EOL;
				break;
		}
	}

	/**
	 * control method that will process the response and add XML snippets to an output file.
	 *
	 * @param {array} $job
	 * @param {array} $job['items']
	 * @param {string} $job['output-filename']
	 * @param {string} $job['schema']
	 *
	 * @param {array} $options
	 * @param {string} $options['output-path']
	 * @param {string} $options['wskey']
	 *
	 * @throws {Exception}
	 * @return {array} $job
	 */
	public static function processJob( $job, $options ) {
		// verify job parameters
		if ( !is_array( $job ) ) {
			throw new Exception( __METHOD__ . ': job not provided as an array' );
		}

		if ( empty( $job['items'] ) ) {
			throw new Exception( __METHOD__ . ': no items provided' );
		}

		if ( empty( $job['output-filename'] ) ) {
			throw new Exception( __METHOD__ . ': no output filename provided' );
		}

		if ( empty( $job['schema'] ) ) {
			throw new Exception( __METHOD__ . ': no schema provided' );
		}

		if ( !isset( $job['errors'] ) ) {
			$job['errors'] = array();
		}

		if ( !is_array( $options ) ) {
			throw new Exception( __METHOD__ . ': options not provided as an array' );
		}

		if ( empty( $options['job-run-limit'] ) ) {
			throw new Exception( __METHOD__ . ': no job run limit provided' );
		}

		if ( empty( $options['output-path'] ) ) {
			throw new Exception( __METHOD__ . ': no output path provided' );
		}

		if ( empty( $options['wskey'] ) ) {
			throw new Exception( __METHOD__ . ': no API key provided' );
		}

		$output_filename = $options['output-path'] . $job['output-filename'];

		switch ( $job['schema'] ) {
			case 'edm':
				$endpoint = 'http://europeana.eu/api/v2/record%s.rdf';

			default:
				$endpoint = 'http://europeana.eu/api/v1/record%s.srw';
		}

		for ( $i = 0; $i < $options['job-run-limit']; $i += 1 ) {
			$item = array_shift( $job['items'] );
			$url = sprintf( $endpoint, $item ) . '?wskey=' . $options['wskey'];
			$xml_record = self::loadRecordFromXml( $url, $job['schema'] );

			if ( !empty( $xml_record['xml-snippet'] ) ) {
				self::saveXMLSnippet( $output_filename, $xml_record['xml-snippet'], $job['schema'] );
			} else {
				$msg = \Europeana\Api\Helpers\Response::obfuscateApiKey( $xml_record['msg'], $options['wskey'] );
				$job['errors'][] = $msg;
				error_log( $msg );
			}

			if ( count( $job['items'] ) === 0 ) {
				break;
			}
		}

		if ( count( $job['items'] ) === 0 ) {
			self::closeXMLFile( $output_filename, $job['schema'] );
		}

		return $job;
	}


	/**
	 * @param {SimpleXMLElement} $xml
	 *
	 * @param {array} $result
	 * @param {bool} $result['success']
	 * @param {string} $result['msg']
	 * @param {string} $result['xml-snippet']
	 *
	 * @return {array}
	 */
	protected static function processRdfXmlRecord( $xml, $result ) {
		$result['msg'] = 'not yet processing rdf xml records';
		return $result;
	}

	/**
	 * @param {SimpleXMLElement} $xml
	 *
	 * @param {array} $result
	 * @param {bool} $result['success']
	 * @param {string} $result['msg']
	 * @param {string} $result['xml-snippet']
	 *
	 * @return {array}
	 */
	protected static function processSrwXmlRecord( $xml, $result ) {
		$namespaces = $xml->getNamespaces( true );

		if ( empty( $namespaces ) ) {
			$result['msg'] = 'no namespaces found in the root node of the XML document';
			return $result;
		}

		$xml_snippet = $xml->records[0]->children( $namespaces['srw'] );

		if ( empty( $xml_snippet ) ) {
			$result['msg'] = 'could not find an <srw:record> in the <records> element';
		} else {
			$result['xml-snippet'] = $xml_snippet->asXml();
			$result['success'] = true;
		}

		return $result;
	}

	/**
	 * retrieves the entire job list,
	 * removes the first job in the array,
	 * saves the job list with the modification,
	 * returns the first job retrieved from the jobs array
	 *
	 * @param {array} $options
	 * @param {string} $options['filename']
	 * @param {array} $options['path']
	 *
	 * @return {null|array}
	 */
	public static function retrieveJob( $options ) {
		$job_filename = self::getFilepath( $options );

		if ( !file_exists( $job_filename ) ) {
			self::saveJobs( $job_filename, array() );
		}

		$jobs = include $job_filename;
		$result = array_shift( $jobs );

		self::saveJobs( $job_filename, $jobs );
		return $result;
	}

	/**
	 * @param {array} $options
	 * @param {string} $options['filename']
	 * @param {array} $options['path']
	 *
	 * @return {string}
	 */
	public static function retrieveJobsAsHtmlTable( $options ) {
		$result = 'there are currently no jobs in the queue.';
		$job_filename = self::getFilepath( $options );

		if ( !file_exists( $job_filename ) ) {
			return $result;
		}

		$jobs = include $job_filename;

		if ( empty( $jobs ) ) {
			return $result;
		}

		$result = '<table class="table table-striped">';
			$result .= '<thead>';
				$result .= '<tr>';
				$result .= '<th>identifier</th>';
				$result .= '<th>job created</th>';
				$result .= '<th>endpoint</th>';
				$result .= '<th>params</th>';
				$result .= '<th>remaining records</th>';
				$result .= '<th>errors</th>';
				$result .= '<th>records found</th>';
			$result .= '</tr>';
			$result .= '</thead>';
			$result .= '<tbody>';

		$rows = '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>';

		foreach( $jobs as $job ) {
			$identifier = '';
			$timestamp = '';
			$endpoint = '';
			$params = '';
			$remaining_records = 0;
			$errors = 0;
			$total_records_found = 0;

			if ( isset( $job['job-identifier'] ) ) {
				$identifier = filter_var( $job['job-identifier'], FILTER_SANITIZE_STRING );
			}

			if ( isset( $job['timestamp'] ) ) {
				$timestamp = filter_var( $job['timestamp'], FILTER_SANITIZE_STRING );
				$timestamp = date( 'c', $timestamp );
				$timestamp = str_replace( array( 'T', '+' ), array( ' ', ' +' ), $timestamp );
			}

			if ( isset( $job['endpoint'] ) ) {
				$endpoint = filter_var( $job['endpoint'], FILTER_SANITIZE_STRING );
				$endpoint = str_replace( 'http://europeana.eu', '', $endpoint );
			}

			if ( isset( $job['params'] ) ) {
				$params = filter_var( $job['params'], FILTER_SANITIZE_STRING );
			}

			if ( isset( $job['items'] ) ) {
				$remaining_records = count( $job['items'] );
			}

			if ( isset( $job['errors'] ) ) {
				$errors = count( $job['errors'] );
			}

			if ( isset( $job['total-records-found'] ) ) {
				$total_records_found = filter_var( $job['total-records-found'], FILTER_SANITIZE_NUMBER_INT );
			}

			$result .= sprintf(
				$rows,
				$identifier,
				$timestamp,
				$endpoint,
				$params,
				$remaining_records,
				$errors,
				$total_records_found
			);
		}

		$result .= '</tbody>';
		$result .= '</table>';

		return $result;
	}

	/**
	 * @param {string} $filename
	 * @param {array} $jobs
	 *
	 * @throws {Exception}
	 * @return {bool}
	 */
	protected static function saveJobs( $filename = '', $jobs ) {
		// verify parameters
		if ( empty( $filename ) ) {
			throw new Exception( __METHOD__ . ': no filename provided' );
		}

		if ( !is_array( $jobs ) ) {
			throw new Exception( __METHOD__ . ': jobs not provided as an array' );
		}

		$filename = filter_var( $filename, FILTER_SANITIZE_STRING );

		$contents =
			'<?php '. PHP_EOL .
			'return ' . var_export( $jobs, true ) . ';' . PHP_EOL;

		$fp = @fopen( $filename, 'w' );

		if ( $fp ) {
			$bytes = fwrite( $fp, $contents );
		} else {
			throw new Exception( __METHOD__ . ': could not open [' . $filename . '] for writing' );
		}

		return fclose( $fp );
	}

	/**
	 * @param {string} $output_filename
	 * @param {string} $xml_snippet
	 * @param {string} $schema
	 *
	 * @throws {Exception}
	 */
	protected static function saveXMLSnippet( $output_filename = '', $xml_snippet = '', $schema = 'ese' ) {
		if ( empty( $output_filename ) ) {
			throw new Exception( __METHOD__ . ': no output filename provided' );
		}

		if ( empty( $xml_snippet ) ) {
			throw new Exception( __METHOD__ . ': no XML snippet provided' );
		}

		$output_filename = filter_var( $output_filename, FILTER_SANITIZE_STRING );
		$xml_snippet = $xml_snippet . PHP_EOL;

		if ( !file_exists( $output_filename ) ) {
			$xml_snippet = self::openXMLFile( $schema ) . $xml_snippet;
		}

		$fp = fopen( $output_filename, 'a' );

		if ( $fp ) {
			$bytes = fwrite( $fp, $xml_snippet );
		}

		fclose( $fp );
	}

}
