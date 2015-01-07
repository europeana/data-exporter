<?php
namespace App\BatchJobs;
use Php\Exception;


class ControlJob {

	/**
	 * @var {bool}
	 */
	public $all_jobs_created;

	/**
	 * @var {bool}
	 */
	public $creating_jobs;

	/**
	 * @var {string}
	 */
	public $endpoint;

	/**
	 * @var {string}
	 * the job_group_id the control job belongs to
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
	public $start;

	/**
	 * @var {int}
	 */
	public $timestamp;

	/**
	 * @var {int}
	 */
	public $total_records_found;


	/**
	 * @param {array} $options
	 */
	public function __construct( array $options = array() ) {
		$this->init();
		$this->populate( $options );
	}

	protected function init() {
		$this->all_jobs_created = false;
		$this->creating_jobs = false;
		$this->endpoint = '';
		$this->job_group_id = '';
		$this->output_filename = '';
		$this->params = '';
		$this->schema = '';
		$this->start = 0;
		$this->timestamp = 0;
		$this->total_records_found = 0;
	}

	/**
	 * @param {array} $options
	 */
	public function populate( array $options = array() ) {
		if ( isset( $options['all_jobs_created'] ) && is_bool( $options['all_jobs_created'] ) ) {
			$this->all_jobs_created = (bool) $options['all_jobs_created'];
		}

		if ( isset( $options['creating_jobs'] ) && is_bool( $options['creating_jobs'] ) ) {
			$this->creating_jobs = (bool) $options['creating_jobs'];
		}

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

		if ( isset( $options['start'] ) && is_int( $options['start'] ) ) {
			$this->start = (int) $options['start'];
		}

		if ( isset( $options['timestamp'] ) && is_int( $options['timestamp'] ) ) {
			$this->timestamp = (int) $options['timestamp'];
		}

		if ( isset( $options['total_records_found'] ) && is_int( $options['total_records_found'] ) ) {
			$this->total_records_found = (int) $options['total_records_found'];
		}

		$this->validate();
	}

	/**
	 * @throws {Exception}
	 */
	public function validate() {
		if ( empty( $this->endpoint ) || !is_string( $this->endpoint ) ) {
			throw new Exception( __METHOD__ . '() no endpoint provided', 2 );
		}

		if ( empty( $this->job_group_id ) || !is_string( $this->job_group_id ) ) {
			throw new Exception( __METHOD__ . '() no job_group_id provided', 2 );
		}

		if ( empty( $this->output_filename ) || !is_string( $this->output_filename ) ) {
			throw new Exception( __METHOD__ . '() no output_filename provided', 2 );
		}

		if ( empty( $this->params ) || !is_string( $this->params ) ) {
			throw new Exception( __METHOD__ . '() no params provided', 2 );
		}

		if ( empty( $this->schema ) || !is_string( $this->schema ) ) {
			throw new Exception( __METHOD__ . '() no schema provided', 2 );
		}

		if ( empty( $this->timestamp ) || !is_int( $this->timestamp ) ) {
			throw new Exception( __METHOD__ . '() no timestamp provided', 2 );
		}

		if ( empty( $this->total_records_found ) || !is_int( $this->total_records_found ) ) {
			throw new Exception( __METHOD__ . '() no total_records_found provided', 2 );
		}
	}

}