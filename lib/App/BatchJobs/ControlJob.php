<?php
namespace App\BatchJobs;

use Pennline\Php\Exception;

class ControlJob extends JobAbstract {

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
	public $email;

	/**
	 * @var {int}
	 */
	public $start;

	/**
	 * @var {string}
	 */
	public $username;


	protected function init() {
		parent::init();
		$this->all_jobs_created = false;
		$this->creating_jobs = false;
		$this->email = '';
		$this->start = 0;
		$this->username = '';
	}

	/**
	 * @param {array} $properties
	 */
	public function populate( $properties = array() ) {
		parent::populate( $properties );

		if ( isset( $properties['all_jobs_created'] ) && is_bool( $properties['all_jobs_created'] ) ) {
			$this->all_jobs_created = (bool) $properties['all_jobs_created'];
		}

		if ( isset( $properties['creating_jobs'] ) && is_bool( $properties['creating_jobs'] ) ) {
			$this->creating_jobs = (bool) $properties['creating_jobs'];
		}

		if ( isset( $properties['email'] ) && is_string( $properties['email'] ) ) {
			$this->email = filter_var( $properties['email'], FILTER_VALIDATE_EMAIL );
		}

		if ( isset( $properties['start'] ) && is_int( $properties['start'] ) ) {
			$this->start = (int) $properties['start'];
		}

		$this->setUsername();
		$this->validate();
	}

	protected function setUsername() {
		if ( empty( $this->username ) && !empty( $this->email ) ) {
			$this->username = strstr( $this->email, '@', true );
		}
	}

	/**
	 * @throws {Exception}
	 */
	public function validate() {
		parent::validate();

		if ( empty( $this->email ) || !is_string( $this->email ) ) {
			error_log( __METHOD__ . '() no email provided' );
			throw new Exception( 'no email provided', 2 );
		}
	}

}