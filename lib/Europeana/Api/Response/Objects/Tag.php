<?php

namespace Europeana\Api\Response\Objects;
use Europeana\Api\Response\ObjectAbstract;


/**
 * if the search has results, the hits take place in the "items" array. Each item is an object, and represents a summary of metadata record. The actual content is depending of the profile parameter. The mandatory field are:
 */
class Tag extends ObjectAbstract {

	/**
	 * @var {int}
	 */
	public $id;

	/**
	 * @var {string}
	 */
	public $europeanaId;

	/**
	 * @var {string}
	 */
	public $guid;

	/**
	 * @var {string}
	 */
	public $link;

	/**
	 * @var {string}
	 */
	public $title;

	/**
	 * @var {string}
	 */
	public $edmPreview;

	/**
	 * @var {string}
	 */
	public $type;

	/**
	 * @var {timestamp}
	 */
	public $dateSaved;

	/**
	 * @var {string}
	 */
	public $tag;


	public function __construct( array $properties ) {
		$this->init();
		$this->populate( $properties );
	}

	public function init() {
		parent::init();

		$this->id = 0;
		$this->europeanaId = '';
		$this->guid = '';
		$this->link = '';
		$this->title = '';
		$this->edmPreview = '';
		$this->type = '';
		$this->dateSaved = 0;
		$this->tag = '';
	}

}