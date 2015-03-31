<?php
namespace App\BatchJobs;

use Pennline\Php\Exception;

class Job extends JobAbstract {

	/**
	 * @var {int}
	 */
	public $attempts_to_process;

	/**
	 * @var {int}
	 */
	public $job_id;

	/**
	 * @var {string}
	 */
	public $record_id;


	protected function init() {
		parent::init();
		$this->attempts_to_process = 0;
		$this->job_id = 0;
		$this->record_id = '';
	}

	/**
	 * @param {array} $properties
	 */
	public function populate( $properties = array() ) {
		parent::populate( $properties );

		if ( isset( $properties['attempts_to_process'] ) && is_int( $properties['attempts_to_process'] ) ) {
			$this->attempts_to_process = (int) $properties['attempts_to_process'];
		}

		if ( isset( $properties['job_id'] ) && is_int( $properties['job_id'] ) ) {
			$this->job_id = (int) $properties['job_id'];
		}

		if ( isset( $properties['record_id'] ) && is_string( $properties['record_id'] ) ) {
			$this->record_id = filter_var( $properties['record_id'], FILTER_SANITIZE_STRING );
		}

		$this->validate();
	}

	/**
	 * @throws {Exception}
	 */
	public function validate() {
		parent::validate();

		if ( !is_int( $this->job_id ) ) {
			error_log( __METHOD__ . '() no job_id provided' );
			throw new Exception( 'no job_id provided', 2 );
		}

		if ( empty( $this->record_id ) || !is_string( $this->record_id ) ) {
			error_log( __METHOD__ . '() no record_id provided' );
			throw new Exception( 'no record_id provided', 2 );
		}
	}

}