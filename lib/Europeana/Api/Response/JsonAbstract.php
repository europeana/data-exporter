<?php

namespace Europeana\Api\Response;
use Europeana\Api\Helpers\Response as Response_Helper;


abstract class JsonAbstract extends ResponseAbstract {

	/**
	 * @param {array} $response
	 * @param {string} $apikey
	 */
	public function __construct( array $response, $apikey = '' ) {
		if ( empty( $response ) ) {
			throw new Exception( 'no response provided' );
		}

		$this->init();

		if ( isset( $response['response'] ) ) {
			$this->_response_raw = $response['response'];
		}

		if ( isset( $response['info'] ) ) {
			$this->_response_info = $response['info'];
		}

		if ( !empty( $apikey ) ) {
			if ( isset( $this->_response_info['url'] ) ) {
				$this->_response_info['url'] = Response_Helper::obfuscateApiKey( $this->_response_info['url'], $apikey );
			}

			if ( isset( $this->_response_info['request_header'] ) ) {
				$this->_response_info['request_header'] = Response_Helper::obfuscateApiKey( $this->_response_info['request_header'], $apikey );
			}

			$this->_response_raw = Response_Helper::obfuscateApiKey( $this->_response_raw, $apikey );
		}

		if ( $this->_response_info['http_code'] !== 200 ) {
			$this->throwRequestError();
		}

		$this->_response_array = json_decode( $this->_response_raw, true );

		// adding the api response as an array so that the application can
		// create the corresponding object for it
		$this->_response_array['api_response'] = array(
			'action' => isset( $this->_response_array['action'] ) ? $this->_response_array['action'] : null,
			'apikey' => isset( $this->_response_array['apikey'] ) ? $this->_response_array['apikey'] : null,
			'error' => isset( $this->_response_array['error'] ) ? $this->_response_array['error'] : false,
			'requestNumber' => isset( $this->_response_array['requestNumber'] ) ? $this->_response_array['requestNumber'] : 0,
			'success' => isset( $this->_response_array['success'] ) ? $this->_response_array['success'] : false
		);

		$this->populate( $this->_response_array );
	}

	public function getResponseAsJson() {
		$result = null;

		if ( defined( 'JSON_PRETTY_PRINT' ) ) {
			$result = json_encode( $this->_response_raw, JSON_PRETTY_PRINT );
		} else {
			$result = $this->indent( $this->_response_raw );
		}

		return $result;
	}

	/**
	 * Indents a flat JSON string to make it more human-readable.
	 *
	 * @param string $json The original JSON string to process.
	 * @return string Indented version of the original JSON string.
	 * @link http://recursive-design.com/blog/2008/03/11/format-json-with-php/
	 */
	protected function indent( $json ) {
		$result      = '';
		$pos         = 0;
		$strLen      = strlen($json);
		$indentStr   = '  ';
		$newLine     = "\n";
		$prevChar    = '';
		$outOfQuotes = true;

		for ( $i = 0; $i <= $strLen; $i++ ) {
			// Grab the next character in the string.
			$char = substr($json, $i, 1);

			// Are we inside a quoted string?
			if ($char == '"' && $prevChar != '\\') {
					$outOfQuotes = !$outOfQuotes;

			// If this character is the end of an element,
			// output a new line and indent the next line.
			} else if(($char == '}' || $char == ']') && $outOfQuotes) {
					$result .= $newLine;
					$pos --;
					for ($j=0; $j<$pos; $j++) {
							$result .= $indentStr;
					}
			}

			// Add the character to the result string.
			$result .= $char;

			// If the last character was the beginning of an element,
			// output a new line and indent the next line.
			if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
				$result .= $newLine;
				if ($char == '{' || $char == '[') {
					$pos ++;
				}

				for ($j = 0; $j < $pos; $j++) {
					$result .= $indentStr;
				}
			}

			$prevChar = $char;
		}

		return $result;
	}

	/**
	 * @return void
	 */
	public function init() {
		parent::init();
	}

}