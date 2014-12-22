<?php
namespace App;

class Nav {

	public $items;

	public function __construct( array $items = array() ) {
		$this->init( $items );
	}

	/**
	 * @param {string} $class
	 * @param {string} $page
	 * @return {string}
	 */
	public function getNavAsUl( $class = '', $page = '' ) {
		$result = '';

		if ( empty( $this->items ) ) {
			return $result;
		}

		$result = sprintf(
			'<ul class="%s">',
			filter_var( $class, FILTER_SANITIZE_STRING )
		);

		foreach ( $this->items as $item ) {
			$class = '';

			if ( $item->page === $page ) {
				$class = ' class="active"';
			}

			$result .= '<li>';

			$result .= sprintf(
				'<a href="%s" title="%s"%s>%s</a>',
				$item->href,
				$item->title,
				$class,
				$item->title
			);

			$result .= '</li>';
		}

		$result .= '</ul>';

		return $result;
	}

	public function init( array $items = array() ) {
		$this->items = array();

		foreach ( $items as $item ) {
			$this->items[] = new NavItem( $item );
		}
	}

}