<?php
namespace W3c\Html;


class Style {

	/**
	 * @var {string}
	 */
	protected $content;


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

		if ( !empty( $this->content ) ) {
			$result = '<style>' . $this->content . '</style>' . PHP_EOL;
		}

		return $result;
	}

	protected function init() {
		$this->content = '';
	}

	/**
	 * @param {array} $options
	 */
	protected function populate( array $options ) {
		if ( isset( $options['content'] ) ) {
			$this->content = filter_var( $options['content'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
		}
	}
}