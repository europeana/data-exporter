<?php
namespace App\BatchJobs;
use Php\Exception;


abstract class JobAbstract {

	/**
	 * @var {string}
	 */
	public $endpoint;

	/**
	 * @var {string}
	 * the job_group_id this job belongs to
	 */
	public $job_group_id;

	/**
	 * @var {string}
	 */
	public $output_filename;

	/**
	 * @var {string}
	 */
	public $params;

	/**
	 * @var {string}
	 * ese or edm
	 */
	public $schema;

	/**
	 * @var {int}
	 */
	public $timestamp;

	/**
	 * @var {int}
	 */
	public $total_records_found;

	/**
	 * @var {string}
	 */
	public $username;


	/**
	 * @param {array} $options
	 * @param {bool} $lazy_populate
	 */
	public function __construct( array $options = array(), $lazy_populate = false ) {
		$this->init();

		if ( $lazy_populate ) {
			return;
		}

		$this->populate( $options );
	}

	protected function init() {
		$this->endpoint = '';
		$this->job_group_id = '';
		$this->output_filename = '';
		$this->params = '';
		$this->schema = '';
		$this->timestamp = 0;
		$this->total_records_found = 0;
		$this->username = '';
	}

	/**
	 * @param {array} $options
	 */
	public function populate( array $options = array() ) {
		if ( isset( $options['endpoint'] ) && is_string( $options['endpoint'] ) ) {
			$this->endpoint = filter_var( $options['endpoint'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['job_group_id'] ) && is_string( $options['job_group_id'] ) ) {
			$this->job_group_id = filter_var( $options['job_group_id'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['output_filename'] ) && is_string( $options['output_filename'] ) ) {
			$this->output_filename = filter_var( $options['output_filename'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['params'] ) && is_string( $options['params'] ) ) {
			$this->params = filter_var( $options['params'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['schema'] ) && is_string( $options['schema'] ) ) {
			$this->schema = filter_var( $options['schema'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['timestamp'] ) && is_int( $options['timestamp'] ) ) {
			$this->timestamp = (int) $options['timestamp'];
		}

		if ( isset( $options['total_records_found'] ) && is_int( $options['total_records_found'] ) ) {
			$this->total_records_found = (int) $options['total_records_found'];
		}

		if ( isset( $options['username'] ) && is_string( $options['username'] ) ) {
			$this->username = filter_var( $options['username'], FILTER_SANITIZE_STRING );
		}
	}

	public function reset() {
		$this->init();
	}

	/**
	 * @throws {Exception}
	 */
	public function validate() {
		if ( empty( $this->endpoint ) || !is_string( $this->endpoint ) ) {
			error_log( __METHOD__ . '() no endpoint provided' );
			throw new Exception( 'no endpoint provided', 2 );
		}

		if ( empty( $this->job_group_id ) || !is_string( $this->job_group_id ) ) {
			error_log( __METHOD__ . '() no job_group_id provided' );
			throw new Exception( 'no job_group_id provided', 2 );
		}

		if ( empty( $this->output_filename ) || !is_string( $this->output_filename ) ) {
			error_log( __METHOD__ . '() no output_filename provided' );
			throw new Exception( 'no output_filename provided', 2 );
		}

		if ( empty( $this->params ) || !is_string( $this->params ) ) {
			error_log( __METHOD__ . '() no params provided' );
			throw new Exception( 'no params provided', 2 );
		}

		if ( empty( $this->schema ) || !is_string( $this->schema ) ) {
			error_log( __METHOD__ . '() no schema provided' );
			throw new Exception( 'no schema provided', 2 );
		}

		if ( empty( $this->timestamp ) || !is_int( $this->timestamp ) ) {
			error_log( __METHOD__ . '() no timestamp provided' );
			throw new Exception( 'no timestamp provided', 2 );
		}

		if ( empty( $this->total_records_found ) || !is_int( $this->total_records_found ) ) {
			error_log( __METHOD__ . '() no total_records_found provided' );
			throw new Exception( 'no total_records_found provided', 2 );
		}
	}

}