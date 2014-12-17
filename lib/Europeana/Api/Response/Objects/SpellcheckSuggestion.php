<?php

namespace Europeana\Api\Response\Objects;
use Europeana\Api\Response\ResponseObjectAbstract;


/**
 * a list of alternative terms available in the database. Each suggestion contains
 */
class SpellcheckSuggestion extends ResponseObjectAbstract {

	/**
	 * @var int
	 * the number of records the term exists in
	 */
	public $count;

	/**
	 * @var array
	 * the suggested term
	 */
	public $label;

	public function __construct( array $properties ) {
		$this->init();
		$this->populate( $properties );
	}

	public function init() {
		parent::init();

		$this->count = 0;
		$this->label = array();
	}

}