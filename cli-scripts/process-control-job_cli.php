<?php

	chdir( dirname( __DIR__ ) );
	include 'bootstrap.php';

	$job = array();

	try {

		do {

			// @todo implement this method
			// intended for regular search
			//
			// this method differs from process-jobs_cli.php:
			// * it only creates a ceratin nr of jobs each run - process-jobs creates
			//   all jobs as long as the process job limit is not reached. this is the
			//   case because the taglist call does not allow for iteration and only
			//   returns the entire list no matter how large
			// * it relies on the control job to store its place in the iteration

		} while ( false );

	} catch ( Exception $e ) {

		error_log( $e->getMessage() );

	}
