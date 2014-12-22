<?php
namespace W3c\Html;


class Link {

	/**
	 * @var {string}
	 */
	protected $href;

	/**
	 * @var {string}
	 */
	protected $rel;


	/**
	 * @param {array} $options
	 */
	public function __construct( array $options = array() ) {
		$this->init();
		$this->populate( $options );
	}

	/**
	 * @return {string}
	 */
	public function __toString() {
		$result = '';

		if ( !empty( $this->href ) ) {
			$result = '<link rel="' . $this->rel . '" href="' . $this->href . '" />' . PHP_EOL;
		}

		return $result;
	}

	protected function init() {
		$this->href = '';
		$this->rel = 'stylesheet';
	}

	/**
	 * @param {array} $options
	 */
	protected function populate( array $options ) {
		if ( isset( $options['href'] ) ) {
			$this->href = filter_var( $options['href'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['rel'] ) ) {
			$this->rel = filter_var( $options['rel'], FILTER_SANITIZE_STRING );
		}
	}
}