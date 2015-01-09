<?php

	chdir( dirname( __DIR__ ) );
	include 'bootstrap.php';

	$job = array();

	try {

		do {

			$BatchJobHandler = new App\BatchJobs\JobHandler(
				array(
					'FileAdapter' => \Php\File::getInstance(),
					'storage_path' => APPLICATION_PATH
				)
			);

			// check for no items in to_process or processing
			$JobControl = $BatchJobHandler->hasJobGroupCompleted();

			if ( !( $JobControl instanceof \App\BatchJobs\Job ) ) {
				break;
			}


		// should we check processing file dates and move them back into to_process?
		// if no files in to_process or processing:
		//   * close output file
		//   * cp output file to cli-output
		//   * move job group to cli-archive

		} while ( false );

	} catch ( Exception $e ) {

		error_log( $e->getMessage() );

	}