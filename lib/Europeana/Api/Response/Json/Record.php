<?php

namespace Europeana\Api\Response\Json;
use Europeana\Api\Response\JsonAbstract;


class Record extends JsonAbstract {

	/**
	 * @var Europeana\Api\Response\Objects\ApiResponse
	 */
	public $api_response;

	/**
	 * @var Europeana\Api\Response\Objects\Object
	 * an object represents the EDM metadata record. The object has the following parts:
	 */
	public $object;

	/**
	 * @var array
	 * an array of metadata records similar to the current one. Available only if profile parameter's value is similar. The structure of the elements of the list is the same as the search call's items array, see there.
	 */
	public $similarItems;


	/**
	 * @return void
	 */
	public function init() {
		parent::init();

		$this->object = array();
		$this->similarItems = array();
		$this->statsDuration = 0;

		$this->_property_to_class['api_response'] = 'Europeana\Api\Response\Objects\ApiResponse';
		$this->_property_to_class['object'] = 'Europeana\Api\Response\Objects\Object';
		$this->_property_to_class['similarItems'] = 'Europeana\Api\Response\Objects\Item';
	}

}