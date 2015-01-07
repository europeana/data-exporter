<?php

	chdir( dirname( __DIR__ ) );
	include 'bootstrap.php';

	$job = array();

	try {

		do {} while( false );

	} catch ( Exception $e ) {

		error_log( $e->getMessage() );

	}