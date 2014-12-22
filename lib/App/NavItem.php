<?php
namespace App;

class NavItem {

	public $href;
	public $page;
	public $title;


	public function __construct( array $item = array() ) {
		$this->init( $item );
	}

	public function init( array $item = array() ) {
		if ( isset( $item['href'] ) ) {
			$this->href = filter_var( $item['href'], FILTER_SANITIZE_STRING );
		}

		if ( isset( $item['page'] ) ) {
			$this->page = filter_var( $item['page'], FILTER_SANITIZE_STRING );
		}

		if ( isset ( $item['title'] ) ) {
			$this->title = filter_var( $item['title'], FILTER_SANITIZE_STRING );
		}
	}

}