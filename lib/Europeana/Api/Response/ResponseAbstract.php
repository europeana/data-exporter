<?php
namespace	Europeana\Api\Response;
use Exception;


abstract class ResponseAbstract extends ResponseObjectAbstract implements ResponseInterface {

	/**
	 * @var array
	 */
	protected $_http_status_code_to_error;

	/**
	 * @var {array}
	 */
	protected $_response_array;

	/**
	 * @var {array}
	 */
	protected $_response_info;

	/**
	 * @var {string}
	 */
	protected $_response_raw;


	public function __get( $property ) {
		return $this->$property;
	}

	public function getRequestUrl() {
		$result = null;

		if ( isset( $this->_response_info['url'] ) ) {
			$result = $this->_response_info['url'];
		}

		return $result;
	}

	public function init() {
		parent::init();

		$this->_response_array = array();
		$this->_response_info = array();
		$this->_response_raw = '';

		$this->_http_status_code_to_error = array(
			200 => 'The request was executed successfully',
			400 => 'The request sent by the client was syntactically incorrect',
			401 => 'Service was called with invalid argument(s); check the call URL',
			404 => 'The requested resource is not available',
			429 => 'The request could be served because the application has reached its usage limit',
			500 => 'Internal Server Error. Something has gone wrong, please report to us'
		);
	}

	/**
	 * @throws Exception
	 */
	protected function throwRequestError() {
		$msg = 'API call error : ';

		if ( array_key_exists( $this->_response_info['http_code'], $this->_http_status_code_to_error ) ) {
			$msg .= $this->_http_status_code_to_error[ $this->_response_info['http_code'] ];
		} else {
			$msg .= 'the API returned an http status code that’s not officially handled by the API - ' . $this->_response_info['http_code'];
		}

		$msg .= PHP_EOL . 'API call info  : ' . PHP_EOL;
		$msg .= print_r( $this->_response_info, true );
		throw new Exception( $msg );
	}

}