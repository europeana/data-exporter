<?php
namespace Europeana\Api\Response;
use DOMDocument;
use Europeana\Api\Helpers\Response as Response_Helper;


abstract class XmlAbstract extends ResponseAbstract {

	/**
	 * @param {array} $response
	 * @param {string} $apikey
	 */
	public function __construct( array $response, $apikey = '' ) {
		if ( empty( $response ) ) {
			throw new Exception( 'no response provided' );
		}

		$this->init();
		$this->_response_raw = $response['response'];
		$this->_response_info = $response['info'];

		if ( !empty( $apikey ) ) {
			if ( isset( $this->_response_info['url'] ) ) {
				$this->_response_info['url'] = Response_Helper::obfuscateApiKey( $this->_response_info['url'], $apikey );
			}
		}
		if ( 200 != $this->_response_info['http_code'] ) {
			$this->throwRequestError();
		}
	}

	public function getResponseAsXml() {
		$result = null;

		$dom = new DOMDocument();
		$dom->loadXML( $this->_response_raw );
		$dom->formatOutput = true;
		$result = $dom->saveXML();
		$result = str_replace( array('<'), array('&lt;' ), $result );

		return $result;
	}

	/**
	 * @return void
	 */
	public function init() {
		parent::init();
		$this->_response_raw = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . '<document></document>';
	}

}