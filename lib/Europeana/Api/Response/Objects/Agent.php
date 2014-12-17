<?php

namespace Europeana\Api\Response\Objects;
use Europeana\Api\Response\ResponseObjectAbstract;


class Agent extends ResponseObjectAbstract {

	/**
	 * @var string
	 * A collection of definitions for the referring object
	 */
	public $agent;

	public function __construct( array $properties ) {
		$this->init();
		$this->populate( $properties );
	}

	public function init() {
		parent::init();
		$this->agent = null;
	}

}