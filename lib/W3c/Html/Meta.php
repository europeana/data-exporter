<?php
namespace W3c\Html;


class Meta {

	/**
	 * @var {string}
	 */
	protected $charset;

	/**
	 * @var {string}
	 */
	protected $content;

	/**
	 * @var {string}
	 */
	protected $name;


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
		$result = '<meta ';

		if ( !empty( $this->name ) ) {
			$result .= 'name="' . $this->name . '"';
		}

		if ( !empty( $this->content ) ) {
			$result .= 'content="' . $this->content . '"';
		}

		$result .= ' />' . PHP_EOL;
		return $result;
	}

	protected function init() {
		$this->name = '';
		$this->content = '';
	}

	/**
	 * @param {array} $options
	 */
	protected function populate( array $options ) {
		if ( isset( $options['content'] ) ) {
			$this->content = filter_var( $options['content'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['name'] ) ) {
			$this->name = filter_var( $options['name'], FILTER_SANITIZE_STRING );
		}
	}
}