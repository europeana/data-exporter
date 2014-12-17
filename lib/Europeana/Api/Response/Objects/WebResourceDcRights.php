<?php

namespace Europeana\Api\Response\Objects;
use Europeana\Api\Response\ResponseObjectAbstract;


class WebResourceDcRights extends ResponseObjectAbstract {

	/**
	 * @var array
	 * A collection of definitions for the referring object
	 */
	public $sv;

	public function init() {
		parent::init();
		$this->sv = array();
	}

	public function __construct( array $properties ) {
		$this->init();
		$this->populate( $properties );
	}

}