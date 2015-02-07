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
	 * @param {array} $properties
	 * @param {bool} $lazy_populate
	 */
	public function __construct( $properties = array(), $lazy_populate = false ) {
		$this->init();

		if ( $lazy_populate ) {
			return;
		}

		$this->populate( $properties );
	}

	protected function init() {
		$this->endpoint = '';
		$this->job_group_id = '';
		$this->output_filename = '';
		$this->params = '';
		$this->schema = '';
		$this->timestamp = 0;
		$this->total_records_found = 0;
	}

	/**
	 * @param {array} $properties
	 */
	public function populate( $properties = array() ) {
		if ( !is_array( $properties ) ) {
			error_log( __METHOD__ . '() $properties provided are not an array' );
			throw new Exception( 'parameter type error', 1 );
		}

		if ( isset( $properties['endpoint'] ) && is_string( $properties['endpoint'] ) ) {
			$this->endpoint = filter_var( $properties['endpoint'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $properties['job_group_id'] ) && is_string( $properties['job_group_id'] ) ) {
			$this->job_group_id = filter_var( $properties['job_group_id'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $properties['output_filename'] ) && is_string( $properties['output_filename'] ) ) {
			$this->output_filename = filter_var( $properties['output_filename'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $properties['params'] ) && is_string( $properties['params'] ) ) {
			$this->params = filter_var( $properties['params'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $properties['schema'] ) && is_string( $properties['schema'] ) ) {
			$this->schema = filter_var( $properties['schema'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $properties['timestamp'] ) && is_int( $properties['timestamp'] ) ) {
			$this->timestamp = (int) $properties['timestamp'];
		}

		if ( isset( $properties['total_records_found'] ) && is_int( $properties['total_records_found'] ) ) {
			$this->total_records_found = (int) $properties['total_records_found'];
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