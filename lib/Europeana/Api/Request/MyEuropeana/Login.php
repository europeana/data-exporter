<?php

namespace	Europeana\Api\Request\MyEuropeana;
use Europeana\Api\Request\RequestAbstract;


/**
 * @link http://labs.europeana.eu/api/authentication
 */
class Login extends RequestAbstract {

	/**
	 * @var {string}
	 * a public apikey
	 */
	public $j_username;

	/**
	 * @var {string}
	 * a private apikey
	 */
	public $j_password;


	/**
	 * @return {array}
	 */
	public function call() {
		$data = array(
			'j_username' => $this->j_username,
			'j_password' => $this->j_password
		);

		$result = array(
			'response' => $this->_HttpRequest->post( $this->_endpoint, $data, true ),
			'info' => $this->_HttpRequest->getCurlInfo()
		);

		return $result;
	}

	public function init() {
		parent::init();
		$this->_endpoint = 'http://europeana.eu/api/login.do';
	}

}