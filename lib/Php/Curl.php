<?php
namespace	Php;
use	Exception;
use W3c\Http\HttpRequestInterface;


class Curl implements HttpRequestInterface {

	protected $curl;
	protected $curl_connecttimeout;
	protected $curl_followlocation;
	protected $curl_header;
	protected $curl_max_redirects;
	protected $curl_returntransfer;
	protected $curl_timeout;
	protected $curlinfo_header_out;

	protected $cookiejar;
	protected $cookie_directory;
	protected $cookie_extension;
	protected $cookie_name;

	protected $curl_info;
	protected $curl_error;
	protected $curl_errno;

	protected $http_headers;
	protected $debug_on;

	public $response_header;
	public $useragent;


	/**
	 * @param array $options
	 * useragent, cookie_directory, cookie_extension, cookie_name
	 */
	public function __construct( array $options = array() ) {
		$this->init();
		$this->setClassProperties( $options );
		$this->curl = curl_init();

		if ( !$this->curl ) {
			throw new Exception( 'could not init curl' );
		}

		$this->createCookie();
	}

	public function __destruct () {
		if ( is_resource( $this->curl ) ) {
			curl_close( $this->curl );
		}

		if ( file_exists( $this->cookiejar ) ) {
			unlink( $this->cookiejar );
		}
	}

	protected function createCookie() {
		if ( !file_exists( $this->cookie_directory ) ) {
			throw new Exception( 'cookie directory does not exist' );
		}

		$this->cookiejar = $this->cookie_directory . '/' . $this->cookie_name . '.' . dechex( rand( 0,99999999 ) ) . $this->cookie_extension;

		if ( !touch( $this->cookiejar ) ) {
			throw new Exception( 'could not create a cookie' );
		}

		chmod( $this->cookiejar, 0600 );
		curl_setopt( $this->curl, CURLOPT_COOKIEJAR, $this->cookiejar );
		curl_setopt( $this->curl, CURLOPT_COOKIEFILE, $this->cookiejar );
	}

	/**
	 * @return {bool|string}
	 * Returns TRUE on success or FALSE on failure. However, if the
	 * CURLOPT_RETURNTRANSFER option is set, it will return the result on success,
	 * FALSE on failure.
	 */
	protected function executeCurl() {
		$this->response_header = '';
		$result = curl_exec( $this->curl );

		$this->curl_info = curl_getinfo( $this->curl );
		$this->curl_error = curl_error( $this->curl );
		$this->curl_errno = curl_errno( $this->curl );

		if ( $this->curl_errno != 0 ) {
			$msg = 'cURL Error: ' . $this->curl_error . ' (' . $this->curl_errno . ')';

			if ( $this->debug_on ) {
				$msg .= '<pre>' . print_r( $this->curl_info, true ) . '</pre>';
			}

			throw new Exception( $msg );
		}

		$this->curl_info['response_header'] = $this->response_header;

		return $result;
	}

	/**
	 * Sends a GET request
	 *
	 * @param {string} $url
	 * the uri to get
	 *
	 * @param {object|array|string} $data
	 * data to send in the get
	 *
	 * @param {bool} $form_encoded
	 * whether or not to use http_build_query to url encode the array of data provided
	 *
	 * @returns {bool|string}
	 * false or the text response
	 **/
	public function get( $url, $data = array(), $form_encoded = true ) {
		$this->isUrlValid( $url );

		if ( $form_encoded && ( is_array( $data ) || is_object( $data ) ) ) {
			$data = http_build_query( $data );
		}

		if ( !empty( $data ) && is_string( $data ) ) {
			$url .= '?' . $data;
		}

		$this->setCurlOption( CURLOPT_URL, $url );
		$this->setCurlOption( CURLOPT_FOLLOWLOCATION, $this->curl_followlocation );
		$this->setCurlOption( CURLOPT_MAXREDIRS, $this->curl_max_redirects );
		$this->setCurlOption( CURLOPT_HEADER, $this->curl_header );
		$this->setCurlOption( CURLOPT_HEADERFUNCTION, array( $this, 'storeResponseHeader' ) );
		$this->setCurlOption( CURLOPT_HTTPGET, true );
		$this->setCurlOption( CURLOPT_RETURNTRANSFER, $this->curl_returntransfer );
		$this->setCurlOption( CURLOPT_CONNECTTIMEOUT, $this->curl_connecttimeout );
		$this->setCurlOption( CURLOPT_USERAGENT, $this->useragent );
		$this->setCurlOption( CURLOPT_TIMEOUT, $this->curl_timeout );
		$this->setCurlOption( CURLINFO_HEADER_OUT, $this->curlinfo_header_out );

		return $this->executeCurl();
	}

	public function getCurlInfo() {
		return $this->curl_info;
	}

	/**
	 * Sends a GET request
	 *
	 * @param {string} $url
	 * is the address of the page you are looking for
	 *
	 * @returns {bool|string}
	 * false or the text response
	 **/
	public function getHeadersOnly( $url ) {
		$this->isUrlValid( $url );

		$this->setCurlOption( CURLOPT_URL, $url );
		$this->setCurlOption( CURLOPT_FOLLOWLOCATION, $this->curl_followlocation );
		$this->setCurlOption( CURLOPT_MAXREDIRS, $this->curl_max_redirects );
		$this->setCurlOption( CURLOPT_HEADER, $this->curl_header );
		$this->setCurlOption( CURLOPT_HEADERFUNCTION, array( $this, 'storeResponseHeader' ) );
		$this->setCurlOption( CURLOPT_NOBODY, true );
		$this->setCurlOption( CURLOPT_RETURNTRANSFER, $this->curl_returntransfer );
		$this->setCurlOption( CURLOPT_CONNECTTIMEOUT, $this->curl_connecttimeout );
		$this->setCurlOption( CURLOPT_USERAGENT, $this->useragent );
		$this->setCurlOption( CURLOPT_TIMEOUT, $this->curl_timeout );
		$this->setCurlOption( CURLINFO_HEADER_OUT, $this->curlinfo_header_out );

		return $this->executeCurl();
	}

	public function init() {
		$this->curl = null;
		$this->curl_timeout = 60;
		$this->curl_connecttimeout = 30;
		$this->curl_followlocation = false;
		$this->curl_header = false;
		$this->curl_returntransfer = true;
		$this->curl_max_redirects = 10;
		$this->curlinfo_header_out = true;

		$this->cookiejar = '';
		$this->cookie_directory = '/tmp';
		$this->cookie_extension = '.dat';
		$this->cookie_name = 'http.cookie';

		$this->curl_info = array();
		$this->curl_error = null;
		$this->curl_errno = 0;

		$this->debug_on = false;
		$this->useragent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36';
	}

	protected function isUrlValid( $url ) {
		if ( !filter_var( $url, FILTER_VALIDATE_URL ) ) {
			throw new Exception('invalid url : [' . filter_var( $url, FILTER_SANITIZE_STRING ) . ']');
		}

		return true;
	}

	/**
	 * Sends a POST request
	 *
	 * @param {string} $url
	 * the uri to post to
	 *
	 * @param {object|array} $data
	 * data to send in the post
	 *
	 * @param {bool} $form_encoded
	 * whether or not to use http_build_query to url encode the array of data provided
	 *
	 * @returns {bool|string}
	 * false or the text response
	 **/
	public function post( $url, array $data = array(), $form_encoded = true ) {
		$this->isUrlValid( $url );

		if ( $form_encoded && ( is_array( $data ) || is_object( $data ) ) ) {
			$data = http_build_query( $data );
		}

		if ( !empty( $data ) && is_string( $data ) ) {
			$this->setCurlOption( CURLOPT_POSTFIELDS, $data );
		}

		$this->setCurlOption( CURLOPT_URL, $url );
		$this->setCurlOption( CURLOPT_FOLLOWLOCATION, $this->curl_followlocation );
		$this->setCurlOption( CURLOPT_MAXREDIRS, $this->curl_max_redirects );
		$this->setCurlOption( CURLOPT_HEADER, $this->curl_header );
		$this->setCurlOption( CURLOPT_HEADERFUNCTION, array( $this, 'storeResponseHeader' ) );
		$this->setCurlOption( CURLOPT_POST, true );
		$this->setCurlOption( CURLOPT_RETURNTRANSFER, $this->curl_returntransfer );
		$this->setCurlOption( CURLOPT_CONNECTTIMEOUT, $this->curl_connecttimeout );
		$this->setCurlOption( CURLOPT_USERAGENT, $this->useragent );
		$this->setCurlOption( CURLOPT_TIMEOUT, $this->curl_timeout );
		$this->setCurlOption( CURLINFO_HEADER_OUT, $this->curlinfo_header_out );

		return $this->executeCurl();
	}

	/**
	 * @param {resource} $ch
	 * the curl resource
	 *
	 * @param {string} $header_line
	 * a single response header line
	 */
	public function storeResponseHeader( $ch, $header_line ) {
		$this->response_header .= $header_line;
		return strlen( $header_line );
	}

	/**
	 * @todo: validate options array
	 */
	protected function setClassProperties( array &$options ) {
		if ( empty( $options ) ) {
			return;
		}

		if ( isset( $options['cookie-directory'] ) ) {
			$this->cookie_directory = $options['cookie-directory'];
		}

		if ( isset( $options['cookie-extension'] ) ) {
			$this->cookie_extension = $options['cookie-extension'];
		}

		if ( isset( $options['cookie-name'] ) ) {
			$this->cookie_name = $options['cookie-name'];
		}

		if ( isset( $options['curl-connecttimeout'] ) ) {
			$this->curl_connecttimeout = (int) $options['curl-connecttimeout'];
		}

		if ( isset( $options['curl-followlocation'] ) ) {
			$this->curl_followlocation = (bool) $options['curl-followlocation'];
		}

		if ( isset( $options['curl-header'] ) ) {
			$this->curl_header = (bool) $options['curl-header'];
		}

		if ( isset( $options['curl-max-redirects'] ) ) {
			$this->curl_max_redirects = (int) $options['curl-max-redirects'];
		}

		if ( isset( $options['curl-returntransfer'] ) ) {
			$this->curl_returntransfer = (bool) $options['curl-returntransfer'];
		}

		if ( isset( $options['curl-timeout'] ) ) {
			$this->curl_timeout = (int) $options['curl-timeout'];
		}

		if ( isset( $options['curlinfo-header-out'] ) ) {
			$this->curlinfo_header_out = (bool) $options['curlinfo-header-out'];
		}

		if ( isset( $options['debug-on'] ) ) {
			$this->debug_on = $options['debug-on'];
		}

		if ( isset( $options['useragent'] ) ) {
			$this->useragent = $options['useragent'];
		}
	}

	protected function setCurlOption( $option, $value ) {
		if ( !curl_setopt( $this->curl, $option, $value ) ) {
			throw new Exception('could not set cURL option [' . filter_var( $option, FILTER_SANITIZE_STRING ) . '] to value [' . filter_var( $value, FILTER_SANITIZE_STRING ) . ']');
		}
	}

	public function setHttpHeader( array $headers ) {
		foreach ( $headers as $header ) {
			$this->http_headers[] = $header;
		}

		$this->setCurlOption( CURLOPT_HTTPHEADER, $this->http_headers );
	}

}