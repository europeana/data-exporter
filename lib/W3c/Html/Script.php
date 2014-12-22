<?php
namespace W3c\Html;


class Script {

	/**
	 * @var {string}
	 */
	protected $content;

	/**
	 * @var {string}
	 */
	protected $src;

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

		if ( !empty( $this->src ) ) {
			$result = '<script src="' . $this->src . '"></script>' . PHP_EOL;
		} elseif ( !empty( $this->content ) ) {
			$result = '<script>' . $this->content . '</script>' . PHP_EOL;
		}

		return $result;
	}

	protected function init() {
		$this->src = '';
	}

	/**
	 * @param {array} $options
	 */
	protected function populate( array $options ) {
		if ( isset( $options['src'] ) ) {
			$this->src = filter_var( $options['src'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $options['content'] ) ) {
			$this->content = $options['content'];
		}
	}
}