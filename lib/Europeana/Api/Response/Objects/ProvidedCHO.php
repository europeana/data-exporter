<?php

namespace Europeana\Api\Response\Objects;
use Europeana\Api\Response\ResponseObjectAbstract;


class ProvidedCHO extends ResponseObjectAbstract {

	/**
	 * @var string
	 */
	public $about;

	public function __construct( array $properties ) {
		$this->init();
		$this->populate( $properties );
	}

	public function init() {
		parent::init();
		$this->about = null;
	}

}