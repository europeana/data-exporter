<?php
/**
 * php environment variables
 */
	error_reporting( E_ALL | E_STRICT );
	ini_set( 'display_errors', 0 );
	ini_set( 'log_errors', 1 );
	ini_set( 'track_errors', 1 );
	ini_set( 'ignore_repeated_errors', 1 );
	ini_set( 'ignore_repeated_soruce', 1 );
	ini_set( 'magic_quotes_gpc', 0 );
	ini_set( 'magic_quotes_runtime', 0 );
	date_default_timezone_set( 'Europe/Amsterdam' );


/**
 * display errors
 * application environment
 */
	if ( ( isset( $_SERVER['PHP_ENV'] ) && $_SERVER['PHP_ENV'] === 'development' ) || php_sapi_name() === 'cli' ) {
		define( 'APPLICATION_ENV', 'development' );
		ini_set( 'display_errors', 1 );
	} else {
		define( 'APPLICATION_ENV', 'production' );
	}

	define( 'APPLICATION_PATH', realpath( __DIR__ ) );
	define( 'APPLICATION_EOL', php_sapi_name() === 'cli' ? PHP_EOL : '<br />' );


/**
 * include paths
 */
	set_include_path(
		'controls' . PATH_SEPARATOR .
		'forms' . PATH_SEPARATOR .
		'lib' . PATH_SEPARATOR .
		'views' . PATH_SEPARATOR .
		'templates' . PATH_SEPARATOR .
		get_include_path()
	);


/**
 * autoloader
 */
	function __autoload( $class_name ) {
		$include_path = str_replace( array( '\\', '_', '..', '.' ), array( '/' ), $class_name );
		include $include_path . '.php';
	}


/**
 * config
 */
	$config = parse_ini_file( 'config.ini' );



/**
 * interface between web server and PHP
 */
	if ( php_sapi_name() !== 'cli' ) {
		include 'bootstrap-web.php';
	}
