<?php
namespace	Europeana\Api\Request\MyEuropeana;
use Exception;
use W3C\Http\Request;


/**
 * @link http://labs.europeana.eu/api/authentication
 */
class Login extends Request {

	/**
	 * @var {string}
	 * a public apikey
	 */
	public $j_username;

	/**
	 * @var {string}
	 * a private apikey
	 */
	public $j_password;


	/**
	 * @param {object|array|string} $data
	 * data to send in the call
	 *
	 * @return {array} $result
	 * @return {bool|string} $result['response']
	 * @return {array} $result['info']
	 */
	public function call( $data = array() ) {
		if ( empty( $data ) ) {
			$data = array(
				'j_username' => $this->j_username,
				'j_password' => $this->j_password
			);
		}

		return parent::post( $this->endpoint, $data );
	}

	public function init() {
		parent::init();

		$this->endpoint = 'http://europeana.eu/api/login.do';
		$this->j_password = '';
		$this->j_username = '';
	}

	/**
	 * @param {array} $options
	 */
	protected function populate( $options = array() ) {
		parent::populate( $options );

		if ( isset( $options['j_username'] ) ) {
			$this->j_username = $options['j_username'];
		} else {
			throw new Exception( __METHOD__ . ' no j_username provided', 2 );
		}

		if ( isset( $options['j_password'] ) ) {
			$this->j_password = $options['j_password'];
		} else {
			throw new Exception( __METHOD__ . ' no j_password provided', 2 );
		}
	}

}