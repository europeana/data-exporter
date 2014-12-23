<?php
namespace	W3c\Http;


interface ResponseInterface {

		/**
	 * @param {string} $message_body
	 * @param {array} $http_info
	 */
	public function __construct( $message_body = '', array $http_info = array() );

}