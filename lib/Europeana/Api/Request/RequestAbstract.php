<?php
namespace	Europeana\Api\Request;
use	W3c\Http\HttpRequestInterface;


abstract class RequestAbstract implements RequestInterface {

	/**
	 * @var {string}
	 */
	public $endpoint;

	/**
	 * @var {W3C\Http\HttpRequestInterface}
	 */
	public $HttpRequest;


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
	 * @return {bool|string} $result['response']
	 * @return {array} $result['info']
	 */
	public function call( $data = array(), $type = 'get' ) {
		$result = array( 'response' => '', 'info' => array() );

		switch ( strtolower( $type ) ) {
			case 'get':
				$result = array(
					'response' => $this->HttpRequest->get( $this->endpoint, $data ),
					'info' => $this->HttpRequest->getCurlInfo()
				);
				break;

			case 'post':
				$result = array(
					'response' => $this->HttpRequest->post( $this->endpoint, $data ),
					'info' => $this->HttpRequest->getCurlInfo()
				);
				break;
		}

		return $result;
	}

	protected function init() {
		$this->endpoint = '';
		$this->HttpRequest = null;
	}

	/**
	 * @param {array} $options
	 */
	protected function populate( $options = array() ) {
		if ( isset( $options['HttpRequest'] ) && $options['HttpRequest'] instanceof HttpRequestInterface ) {
			$this->HttpRequest = $options['HttpRequest'];
		} else {
			throw new Exception( __METHOD__ . ' no HttpRequestInterface provided', 1 );
		}
	}

}
