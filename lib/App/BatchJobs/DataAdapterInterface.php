<?php
namespace Php;


interface DataAdapterInterface {

	/**
	 * @param {array} $options
	 */
	public function __construct( array $options = array() );

	/**
	 * @param {array} $options
	 */
	public function destroy( array $options = array() );

	/**
	 * @param {array} $options
	 */
	public function fetch( array $options = array() );

	/**
	 * @param {array} $options
	 */
	public function populate( array $options = array() );

	/**
	 * @param {array} $options
	 */
	public function save( array $options = array() );

}
