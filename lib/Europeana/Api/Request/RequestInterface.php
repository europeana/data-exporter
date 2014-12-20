<?php
namespace	Europeana\Api\Request;


interface RequestInterface {

	/**
	 * @param {array} $options
	 */
	public function __construct( array $options = array() );

	/**
	 * @param {object|array|string} $data
	 * data to send in the get
	 *
	 * @return {array} $result
	 * @return {bool|string} $result['response']
	 * @return {array} $result['info']
	 */
	public function call( $data = array() );

}
