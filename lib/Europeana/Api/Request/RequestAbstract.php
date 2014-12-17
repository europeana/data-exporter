<?php
namespace	Europeana\Api\Request;
use	W3c\Http\HttpRequestInterface;
use ReflectionClass;
use ReflectionProperty;


abstract class RequestAbstract implements RequestInterface {

	/**
	 * @var {W3C\Http\HttpRequestInterface}
	 */
	protected $_HttpRequest;

	/**
	 * @var {string}
	 */
	protected $_endpoint;

	/**
	 * @access protected
	 */
	protected $_reflection;

	/**
	 * @access protected
	 */
	protected $_public_properties;


	/**
	 * @param {W3c\Http\HttpRequestInterface $HttpRequest}
	 * @param {array} $properties
	 * @param {string} $wskey
	 */
	public function __construct( HttpRequestInterface $HttpRequest, array $properties = array() ) {
		$this->init();
		$this->_HttpRequest = $HttpRequest;
		$this->populate( $properties );
	}

	public function __get( $property ) {
		return $this->$property;
	}

	/**
	 * @return string
	 * the url to the api
	 */
	protected function buildUrl() {
		$result = urldecode( $this->query );

		// add rows
		$result .= '&rows=' . (int) $this->rows;

		// add start
		$result .= '&start=' . (int) $this->start;

		// add the api key
		$result .= '&wskey=' . $this->wskey;

		// url encode the query string
		$result = \Europeana\Api\Helpers\Request::urlencodeQueryParams( $result );

		// add the endpoint
		$result = $this->_endpoint . $result;

		return $result;
	}

	/**
	 * @return {array}
	 */
	public function call() {
		$result = array(
			'response' => $this->_HttpRequest->get( $this->buildUrl() ),
			'info' => $this->_HttpRequest->getCurlInfo()
		);

		return $result;
	}

	public function init() {
		$this->_reflection = new ReflectionClass( $this );
		$this->_public_properties = $this->_reflection->getProperties( ReflectionProperty::IS_PUBLIC );
	}

	protected function parseQuote( $property ) {
		$result = null;
		$pieces = explode( '"', $this->{$property->name} );

		foreach( $pieces as $piece ) {
			$result .= urlencode( $piece ) . '"';
		}

		return $result;
	}

	protected function parseSlash( $property ) {
		$result = null;
		$pieces = explode( '/', $this->{$property->name} );

		foreach( $pieces as $piece ) {
			$result .= urlencode( $piece ) . '/';
		}

		$result = substr( $result, 0, strlen( $result ) - 1 );
		return $result;
	}

	/**
	 * @return void
	 */
	protected function populate( array $properties ) {
		if ( empty( $properties ) ) {
			return;
		}

		foreach( $this->_public_properties as $property ) {
			if ( isset( $properties[ $property->name ] ) ) {
				$this->{$property->name} = $properties[ $property->name ];
			}
		}
	}

}
