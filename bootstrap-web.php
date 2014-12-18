<?php

/**
 * session
 */
	$Session = new Php\Session();
	$Session->sessionStart();


/**
 * current url
 * @link http://davidwalsh.name/iis-php-server-request_uri
 */
	$url = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on'  ? 'https://' : 'http://' .
		$_SERVER['HTTP_HOST'] .
		( isset( $_SERVER['REQUEST_URI'] )
			? $_SERVER['REQUEST_URI']
			: substr( $_SERVER['PHP_SELF'], 1 ) . (
					isset( $_SERVER['QUERY_STRING'] )
					? '?' . $_SERVER['QUERY_STRING']
					: ''
				)
  );

  $url = parse_url( $url );


/**
 * page
 */
	$Page = new Html\Page();


/**
 *  routing
 *
 *  checks initially for a control page in the path provided
 *  e.g. /user/log-in looks for user/log-in_ctrl.php
 *
 *  if that’s not present, looks for an index control in the given path
 *  e.g. /user/log-in looks for user/index_ctrl.php
 *
 *  if still no control page is found, defaults to pg = 404
 */
	$Page->page = '404';
	$default_route = 'my-europeana/tag-list-search';
	$route = $url['path'] == '/' ? $default_route : trim( $url['path'], '/' );

	if ( file_exists( 'controls/' . $route . '_ctrl.php' ) ) {
		$Page->page = $route;
	} else if ( file_exists( 'controls/' . $route . '/index_ctrl.php' ) ) {
		$Page->page = $route . '/index';
	}


/**
 * page control
 */
	include $Page->page . '_ctrl.php';
