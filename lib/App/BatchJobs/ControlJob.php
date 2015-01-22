<?php
namespace App\BatchJobs;

use Penn\Php\Exception;

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
	 * @var {int}
	 */
	public $start;


	protected function init() {
		parent::init();
		$this->all_jobs_created = false;
		$this->creating_jobs = false;
		$this->start = 0;
	}

	/**
	 * @param {array} $options
	 */
	public function populate( $options = array() ) {
		parent::populate( $options );

		if ( isset( $options['all_jobs_created'] ) && is_bool( $options['all_jobs_created'] ) ) {
			$this->all_jobs_created = (bool) $options['all_jobs_created'];
		}

		if ( isset( $options['creating_jobs'] ) && is_bool( $options['creating_jobs'] ) ) {
			$this->creating_jobs = (bool) $options['creating_jobs'];
		}

		if ( isset( $options['start'] ) && is_int( $options['start'] ) ) {
			$this->start = (int) $options['start'];
		}

		$this->validate();
	}

	/**
	 * @throws {Exception}
	 */
	public function validate() {
		parent::validate();
	}

}