<?php

namespace	Europeana\Api\Request\MyEuropeana;
use Europeana\Api\Request\RequestAbstract;


/**
 * @link http://labs.europeana.eu/api/myeuropeana/
 */
class Tag extends RequestAbstract {

	/**
	 * @var {string}
	 * a single tag
	 */
	public $tag;

	/**
	 * @var {string}
	 * the europeanaId of the object you wish to retrieve
	 */
	public $europeanaid;


	/**
	 * @link http://labs.europeana.eu/api/myeuropeana/#tags
	 */
	public function call() {
		$data = array(
			'tag' => $this->tag,
			'europeanaid' => $this->europeanaid
		);

		$result = array(
			'response' => $this->_HttpRequest->post( $this->_endpoint, $data, true ),
			'info' => $this->_HttpRequest->getCurlInfo()
		);

		return $result;
	}

	public function init() {
		parent::init();
		$this->europeanaid = '';
		$this->tag = '';
		$this->_endpoint = 'http://europeana.eu/api/v2/mydata/tag.json';
	}

}
