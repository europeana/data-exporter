<?php

namespace Europeana\Api\Response\Objects;
use Europeana\Api\Response\ResponseObjectAbstract;


/**
 * is a list of facet object (available in case of facets and portal profile applications). Each facet object contains the following fields
 */
class FacetField extends ResponseObjectAbstract {

	/**
	 * @var int
	 * the number of records this value is available given the query and filter parameters
	 */
	public $count;

	/**
	 * @var string
	 * the actual value of the facet instance
	 */
	public $label;

	public function __construct( array $properties ) {
		$this->init();
		$this->populate( $properties );
	}

	public function init() {
		parent::init();

		$this->count = 0;
		$this->label = null;
	}

}