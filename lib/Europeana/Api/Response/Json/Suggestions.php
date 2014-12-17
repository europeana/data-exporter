<?php

namespace Europeana\Api\Response\Json;
use Europeana\Api\Response\JsonAbstract;


class Suggestions extends JsonAbstract {

	/**
	 * @var array
	 * a list of suggestion objects. Each suggestion has the following fields:
	 */
	public $items;

	/**
	 * @return void
	 */
	public function init() {
		parent::init();

		$this->items = array();
		$this->_property_to_class['items'] = 'Europeana\Api\Response\Objects\Suggestion';
	}

}