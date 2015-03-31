<?php
/**
 * session
 */
	$Session = new Pennline\Php\Session();
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
 * web page
 */
	$WebPage = new Pennline\Html\Document();


/**
 * web page meta
 */
	$WebPage->addMeta( new Pennline\Html\Meta( array( 'name' => 'viewport', 'content' => 'width=device-width, initial-scale=1' ) ) );


/**
 * web page link defaults
 */
	if ( isset( $_SERVER['PHP_ENV'] ) && $_SERVER['PHP_ENV'] === 'development'  ) {
		$WebPage->addLink( new Pennline\Html\Link( array( 'href' => '/css/prettify.css' ) ) );
		$WebPage->addLink( new Pennline\Html\Link( array( 'href' => '/css/css.css' ) ) );
	} else {
		$WebPage->addStyle( new Pennline\Html\Style( array( 'content' => file_get_contents( 'public/css/prettify.min.css' ) ) ) );
		$WebPage->addStyle( new Pennline\Html\Style( array( 'content' => file_get_contents( 'public/css/css.min.css' ) ) ) );
	}


/**
 *  routing
 *
 *  checks initially for a control page in the path provided
 *  e.g. /user/log-in looks for user/log-in.ctrl.php
 *
 *  if that’s not present, looks for an index control in the given path
 *  e.g. /user/log-in looks for user/index.ctrl.php
 *
 *  if still no control page is found, defaults to pg = 404
 */
	$WebPage->page = '404';
	$default_route = 'search';
	$route = $url['path'] == '/' ? $default_route : trim( $url['path'], '/' );

	if ( file_exists( 'controls/' . $route . '.ctrl.php' ) ) {
		$WebPage->page = $route;
	} else if ( file_exists( 'controls/' . $route . '/index.ctrl.php' ) ) {
		$WebPage->page = $route . '/index';
	}


/**
 * page control
 */
	include $WebPage->page . '.ctrl.php';
