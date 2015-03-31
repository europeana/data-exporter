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
		'config' . PATH_SEPARATOR .
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
	include 'vendor/autoload.php';

	function psr0Autoloader( $class_name ) {
		$include_path = str_replace( array( '\\', '_', '..', '.' ), array( '/' ), $class_name );
		include $include_path . '.php';
	}

	spl_autoload_register( 'psr0Autoloader' );

/**
 * config
 */
	try {
		$Config = new Pennline\Config\Config( include 'application.config.php' );
	} catch ( Exception $e ) {
		echo 'we apologize, the application configuration is not available at this time.';
		exit();
	}


/**
 * interface between web server and PHP
 */
	if ( php_sapi_name() !== 'cli' ) {
		if ( !include 'bootstrap-web.php' ) {
			echo 'we apologize, the web application could not start at this time.';
			exit();
		}
	}
