<?php

namespace Html;


class Page {

	/**
	 * @var string
	 */
	public $page;

	/**
	 * @var string
	 */
	public $heading;

	/**
	 * @var string
	 */
	public $html;

	/**
	 * @var string
	 */
	public $script_body;

	/**
	 * @var string
	 */
	public $script_head;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var string
	 */
	public $view;


	public function __construct() {
		$this->init();
	}

	protected function init() {
		$this->page = '';
		$this->heading = '';
		$this->html = '';
		$this->script_body = '';
		$this->script_head = '';
		$this->title = '';
		$this->view = '';
	}

}