<?php

namespace Europeana\Api\Response\Objects;
use Europeana\Api\Response\ResponseObjectAbstract;


/**
 * is object representing spellcheck suggestions (available in case of spellcheck and portal profile applications). The object contains the following fields:
 */
class Spellcheck extends ResponseObjectAbstract {

	/**
	 * @var boolean
	 * boolean value notifies whether the actual query is an existing term in the database
	 */
	public $correctlySpelled;

	/**
	 * @var array
	 * a list of alternative terms available in the database. Each suggestion contains
	 */
	public $suggestions;

	public function __construct( array $properties ) {
		$this->init();
		$this->populate( $properties );
	}

	public function init() {
		parent::init();

		$this->correctlySpelled = false;
		$this->suggestions = array();
	}

}