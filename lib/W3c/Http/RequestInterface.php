<?php
namespace	W3c\Http;


interface RequestInterface {

	/**
	 * @param {array} $options
	 */
	public function __construct( array $options = array() );

	/**
	 * @param {string} $url
	 * the uri to get
	 *
	 * @param {object|array|string} $data
	 * data to send in the get
	 *
	 * @returns {bool|W3c\Http\Response}
	 **/
	public function get( $url, $data = array() );

	/**
	 * @param {string} $url
	 * the uri to post to
	 *
	 * @param {object|array} $data
	 * data to send in the post
	 *
	 * @returns {bool|W3c\Http\Response}
	 **/
	public function post( $url, $data = array() );

}
