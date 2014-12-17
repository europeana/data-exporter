<?php

namespace App;
use Exception;
use Php\Session;

/**
 * @link https://www.owasp.org/index.php/PHP_CSRF_Guard
 * @link http://www.wikihow.com/Prevent-Cross-Site-Request-Forgery-(CSRF)-Attacks-in-PHP
 */
class Csrf {

	/**
	 * @var {Php\Session}
	 */
	protected $Session;

	/**
	 * @var {string}
	 * the key name, of the session key, that contains the key name,
	 * of the session key containing the token value
	 */
	protected $session_token_key;

	/**
	 * @var {string}
	 */
	protected $token_key;

	/**
	 * @var {bool}
	 */
	protected $token_key_obfuscate;

	/**
	 * @var {string}
	 */
	protected $token_key_prefix;

	/**
	 * @var {string}
	 */
	protected $token_value;


	/**
	 * @param {array} $options
	 */
	public function __construct( $options = array() ) {
		$this->init();
		$this->populate( $options );
	}

	/**
	 * @return {string}
	 */
	protected function createTokenKey() {
		$this->token_key = $this->token_key_prefix;

		if ( $this->token_key_obfuscate ) {
			$this->token_key .= '-' . mt_rand( 0, mt_getrandmax() );
		}

		$this->Session->setValue( $this->session_token_key, $this->token_key );
		return $this->token_key;
	}

	/**
	 * @return {string}
	 */
	protected function createTokenValue() {
		$this->token_value = '';

		if ( function_exists( 'hash_algos' ) && in_array( 'sha512', hash_algos() ) ) {
			$this->token_value = hash( 'sha512', $this->random( 500 ) );
		} else {
			for ( $i = 0; $i < 128; $i += 1 ) {
				$r = mt_rand( 0, 35 );

				if ( $r < 26 ) {
					$c = chr( ord( 'a' ) + $r );
				} else {
					$c = chr( ord( '0' ) + $r - 26 );
				}

				$this->token_value .= $c;
			}
		}

		$this->Session->setValue( $this->getTokenKey(), $this->token_value );
		return $this->token_value;
	}

	/**
	 * @return {string}
	 */
	public function getTokenValue() {
		$this->token_value = $this->Session->getValue( $this->getTokenKey() );

		if ( empty( $this->token_value ) ) {
			$this->token_value = $this->createTokenValue();
		}

		return $this->token_value;
	}

	/**
	 * @return {string}
	 */
	public function getTokenKey() {
		if ( empty( $this->token_key ) ) {
			$this->token_key = $this->Session->getValue( $this->session_token_key );
		}

		if ( empty( $this->token_key ) ) {
			$this->token_key = $this->createTokenKey();
		}

		return $this->token_key;
	}

	public function init() {
		$this->Session = null;
		$this->session_token_key = 'csrf-key';
		$this->token_key = '';
		$this->token_key_obfuscate = false;
		$this->token_key_prefix = 'csrf-value';
	}

	/**
	 * only allows token to be passed via a $_POST
	 *
	 * @param {array}
	 * @throws {Exception}
	 * @return {bool}
	 */
	public function isTokenValid() {
		$result = false;

		if (
			!empty( $_POST[ $this->getTokenKey() ] ) &&
			$_POST[ $this->getTokenKey() ] === $this->getTokenValue()
		) {
			$result = true;
		}

		return $result;
	}

	/**
	 * @param {array} $options
	 * @param {\Php\Session} $options['Session']
	 * @param {bool} $options['token-param-obfuscate']
	 * @param {string} $options['token-param-prefix']
	 */
	protected function populate( array $options ) {
		if ( isset( $options['Session'] ) && $options['Session'] instanceof Session ) {
			$this->Session = $options['Session'];
		}

		if ( isset( $options['session-token-key'] ) && is_string( $options['session-token-key'] ) ) {
			$this->session_token_key = $options['session-token-key'];
		}

		if ( isset( $options['token-key-obfuscate'] ) && is_bool( $options['token-key-obfuscate'] ) ) {
			$this->token_key_obfuscate = $options['token-key-obfuscate'];
		}

		if ( isset( $options['token-key-prefix'] ) && is_string( $options['token-key-prefix'] ) ) {
			$this->token_key_prefix = filter_var( $options['token-key-prefix'], FILTER_SANITIZE_STRING );
		}
	}

	/**
	 * @param {int} $length
	 * @return {string}
	 */
	protected function random( $length ) {
		$result = '';

		if ( function_exists( 'hash_algos' ) && in_array( 'sha512', hash_algos() ) ) {
			$result = hash( 'sha512', mt_rand( 0, mt_getrandmax() ) );
		} elseif ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
      $byteLen = intval( ( $length / 2 ) + 1 );
      $result = substr( bin2hex( openssl_random_pseudo_bytes( $byteLen ) ), 0, $length );
		} else {
			$result = md5( time() . 'this is a phrase' . rand( 1, 100 ) );
		}

    return $result;
	}

}