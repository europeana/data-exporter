<?php
namespace App\BatchJobs;

use Penn\Php\Exception;

class Job extends JobAbstract {

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
		$this->job_id = 0;
		$this->record_id = '';
	}

	/**
	 * @param {array} $options
	 */
	public function populate( array $options = array() ) {
		parent::populate( $options );

		if ( isset( $options['job_id'] ) && is_int( $options['job_id'] ) ) {
			$this->job_id = (int) $options['job_id'];
		}

		if ( isset( $options['record_id'] ) && is_string( $options['record_id'] ) ) {
			$this->record_id = filter_var( $options['record_id'], FILTER_SANITIZE_STRING );
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