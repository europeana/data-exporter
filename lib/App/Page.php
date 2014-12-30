<?php
namespace App;


class Page {

	/**
	 * @var {string}
	 */
	public $page;

	/**
	 * @var {string}
	 */
	public $heading;

	/**
	 * @var {string}
	 */
	public $html;

	/**
	 * @var {array}
	 * an array collection of \W3C\Html\Link’s
	 */
	protected $links;

	/**
	 * @var {array}
	 * an array collection of \W3C\Html\Meta’s
	 */
	protected $metas;

	/**
	 * @var {array}
	 * an array collection of \W3C\Html\Script’s
	 */
	protected $scripts;

	/**
	 * @var {array}
	 * an array collection of \W3C\Html\Style’s
	 */
	protected $styles;

	/**
	 * @var {string}
	 */
	public $title;

	/**
	 * @var {string}
	 */
	public $view;


	public function __construct() {
		$this->init();
	}

	/**
	 * @param {\W3C\Html\Link} $Link
	 */
	public function addLink( \W3C\Html\Link $Link ) {
		$this->links[] = $Link;
	}

	/**
	 * @param {\W3C\Html\Meta} $Meta
	 */
	public function addMeta( \W3C\Html\Meta $Meta ) {
		$this->metas[] = $Meta;
	}

	/**
	 * @param {\W3C\Html\Script} $Script
	 * @param {string} $placement
	 */
	public function addScript( \W3C\Html\Script $Script, $placement = 'body' ) {
		switch ( $placement ) {
			case 'body':
				$this->scripts['body'][] = $Script;
				break;

			case 'head':
				$this->scripts['head'][] = $Script;
		}
	}

	/**
	 * @param {\W3C\Html\Style} $Style
	 */
	public function addStyle( \W3C\Html\Style $Style ) {
		$this->styles[] = $Style;
	}

	public function getHeading() {
		$result = '';

		if ( empty( $this->heading ) ) {
			return $result;
		}

		$result = '<h1>' . $this->heading . '</h1>';
		return $result;
	}

	/**
	 * @return {string}
	 */
	public function getLinks() {
		$result = '';

		foreach ( $this->links as $link ) {
			$result .= $link;
		}

		return $result;
	}

	/**
	 * @return {string}
	 */
	public function getMeta() {
		$result = '';

		foreach ( $this->metas as $meta ) {
			$result .= $meta;
		}

		return $result;
	}

	/**
	 * @param {string} $placement
	 * @return {string}
	 */
	public function getScripts( $placement = 'body' ) {
		$result = '';

		switch ( $placement ) {
			case 'head':
				$scripts = $this->scripts['head'];
				break;

			case 'body':
				$scripts = $this->scripts['body'];
				break;

			default:
				return $result;
		}

		foreach ( $scripts as $script ) {
			$result .= $script;
		}

		return $result;
	}

	public function getStyles() {
		$result = '';

		foreach ( $this->styles as $style ) {
			$result .= $style;
		}

		return $result;
	}

	protected function init() {
		$this->page = '';
		$this->heading = '';
		$this->html = '';
		$this->links = array();
		$this->metas = array();
		$this->scripts = array( 'head' => array(), 'body' => array() );
		$this->styles = array();
		$this->title = '';
		$this->view = '';
	}

}