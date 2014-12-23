<?php
namespace	W3c\Http;
use Exception;


abstract class Request implements RequestInterface {

	/**
	 * @var {W3C\Http\RequestInterface}
	 */
	protected $RequestService;


	/**
	 * @param {array} $options
	 */
	public function __construct( array $options = array() ) {
		$this->init();
		$this->populate( $options );
	}

	/**
	 * @param {object|array|string} $data
	 * data to send in the get
	 *
	 * @param {string} $type
	 *
	 * @return {array} $result
	 * @return {bool|string} $result['message_body']
	 * @return {array} $result['http_info']
	 */
	//public function call( $data = array(), $type = 'get' ) {
	//	$result = null;
	//
	//	switch ( strtolower( $type ) ) {
	//		case 'get':
	//			$result = new Response(
	//				$this->HttpRequest->get( $this->endpoint, $data ),
	//				$this->HttpRequest->getCurlInfo()
	//			);
	//			break;
	//
	//		case 'post':
	//			$result = new Response(
	//				$this->HttpRequest->post( $this->endpoint, $data ),
	//				$this->HttpRequest->getCurlInfo()
	//			);
	//			break;
	//	}
	//
	//	return $result;
	//}

	/**
	 * @param {string} $url
	 * the uri to get
	 *
	 * @param {object|array|string} $data
	 * data to send in the get
	 *
	 * @returns {bool|W3c\Http\Response}
	 **/
	public function get( $url, $data = array() ) {
		return new Response(
			$this->RequestService->get( $url, $data ),
			$this->RequestService->getRequestInfo()
		);
	}

	protected function init() {
		$this->RequestService = null;
	}

	/**
	 * @param {array} $options
	 */
	protected function populate( $options = array() ) {
		if ( isset( $options['RequestService'] ) && $options['RequestService'] instanceof RequestInterface ) {
			$this->RequestService = $options['RequestService'];
		} else {
			throw new Exception( __METHOD__ . ' no RequestService provided', 1 );
		}
	}


	/**
	 * @param {string} $url
	 * the uri to get
	 *
	 * @param {object|array|string} $data
	 * data to send in the get
	 *
	 * @returns {bool|W3c\Http\Response}
	 **/
	public function post( $url = '', $data = array() ) {
		return new Response(
			$this->RequestService->post( $url, $data ),
			$this->RequestService->getRequestInfo()
		);
	}
}
