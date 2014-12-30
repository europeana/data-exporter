<?php
namespace Europeana\Api\Response;
use Europeana\Api\Helpers\Response as Response_Helper;
use Exception;


abstract class JsonAbstract extends ObjectAbstract {

	/**
	 * @var {array}
	 */
	public $http_info;

	/**
	 * @var {string}
	 */
	public $message_body;

	/**
	 * @var {\W3CHttp\Response}
	 */
	public $Response;

	/**
	 * @var {string}
	 */
	public $wskey;

	/**
	 * @param {array} $response
	 * @param {string} $wskey
	 */
	public function __construct( \W3C\Http\Response $Response, $wskey = '' ) {
		$this->init();

		if ( empty( $Response ) ) {
			throw new Exception( 'no Response provided' );
		}

		$this->Response = $Response;
		$this->wskey = $wskey;

		$this->http_info = $Response->getHttpInfo();
		$this->message_body = $Response->getMessageBody();

		if ( !empty( $wskey ) ) {
			if ( isset( $this->http_info['url'] ) ) {
				$this->http_info['url'] = Response_Helper::obfuscateApiKey( $this->http_info['url'], $wskey );
			}

			if ( isset( $this->http_info['request_header'] ) ) {
				$this->http_info['request_header'] = Response_Helper::obfuscateApiKey( $this->http_info['request_header'], $wskey );
			}

			$this->message_body = Response_Helper::obfuscateApiKey( $this->message_body, $wskey );
		}

		if ( $this->http_info['http_code'] !== 200 ) {
			$this->throwRequestError();
		}

		$this->response_array = json_decode( $this->message_body, true );

		// adding the api response as an array so that the application can
		// create the corresponding object for it
		$this->response_array['api_response'] = array(
			'action' => isset( $this->response_array['action'] ) ? $this->response_array['action'] : null,
			'apikey' => isset( $this->response_array['apikey'] ) ? $this->response_array['apikey'] : null,
			'error' => isset( $this->response_array['error'] ) ? $this->response_array['error'] : false,
			'requestNumber' => isset( $this->response_array['requestNumber'] ) ? $this->response_array['requestNumber'] : 0,
			'success' => isset( $this->response_array['success'] ) ? $this->response_array['success'] : false
		);

		$this->populate( $this->response_array );
	}

	public function getResponseAsJson() {
		$result = null;

		if ( defined( 'JSON_PRETTY_PRINT' ) ) {
			$result = json_encode( $this->message_body, JSON_PRETTY_PRINT );
		} else {
			$result = $this->indent( $this->message_body );
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
		$this->Response = null;
		$this->http_info = array();
		$this->message_body = '';
		$this->wskey = '';

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

		if ( array_key_exists( $this->http_info['http_code'], $this->_http_status_code_to_error ) ) {
			$msg .= $this->_http_status_code_to_error[ $this->http_info['http_code'] ];
		} else {
			$msg .= 'the API returned an http status code that’s not officially handled by the API - ' . $this->http_info['http_code'];
		}

		$msg .= PHP_EOL . 'API call info  : ' . PHP_EOL;
		$msg .= print_r( $this->http_info, true );
		throw new Exception( $msg );
	}

}