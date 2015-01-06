<?php
namespace App\BatchJobs;
use Php\Exception;


class Job {

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
	 * @var {int}
	 */
	public $job_id;

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
	 */
	public $record_id;

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
		$this->populate( $options );
	}

	protected function init() {
		$this->endpoint = '';
		$this->job_group_id = '';
		$this->job_id = 0;
		$this->output_filename = '';
		$this->params = '';
		$this->record_id = '';
		$this->schema = '';
		$this->start = 0;
		$this->timestamp = 0;
		$this->total_records_found = 0;
	}

	/**
	 * @throws {Exception}
	 */
	public function validate() {
		if ( empty( $this->job_group_id ) || !is_string( $this->job_group_id ) ) {
			throw new Exception( __METHOD__ . '() no job_group_id provided', 2 );
		}

		if ( empty( $this->output_filename ) || !is_string( $this->output_filename ) ) {
			throw new Exception( __METHOD__ . '() no output_filename provided', 2 );
		}

		if ( empty( $this->record_id ) || !is_string( $this->record_id ) ) {
			throw new Exception( __METHOD__ . '() no record_id provided', 2 );
		}
	}

	/**
	 * @param {array} $options
	 */
	public function populate( array $options = array() ) {
		$this->init();

		if ( isset( $options['endpoint'] ) && is_string( $options['endpoint'] ) ) {
			$this->endpoint = filter_var( $options['endpoint'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['job_group_id'] ) && is_string( $options['job_group_id'] ) ) {
			$this->job_group_id = filter_var( $options['job_group_id'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['job_id'] ) && is_int( $options['job_id'] ) ) {
			$this->job_id = (int) $options['job_id'];
		}

		if ( isset( $options['output_filename'] ) && is_string( $options['output_filename'] ) ) {
			$this->output_filename = filter_var( $options['output_filename'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['params'] ) && is_string( $options['params'] ) ) {
			$this->params = filter_var( $options['params'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['record_id'] ) && is_string( $options['record_id'] ) ) {
			$this->record_id = filter_var( $options['record_id'], FILTER_SANITIZE_STRING );
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
	}

}