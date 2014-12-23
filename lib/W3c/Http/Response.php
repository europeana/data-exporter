<?php
namespace	W3c\Http;
use Exception;


class Response implements ResponseInterface {

	/**
	 * @var {array}
	 */
	protected $http_info;

	/**
	 * @var {string}
	 */
	protected $message_body;

	/**
	 * @param {string} $message_body
	 * @param {array} $http_info
	 */
	public function __construct( $message_body = '', array $http_info = array() ) {
		$this->http_info = $http_info;
		$this->message_body = $message_body;
	}

	public function getHttpInfo() {
		return $this->http_info;
	}

	public function getMessageBody() {
		return $this->message_body;
	}

}