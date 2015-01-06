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

		// get the first job to process
		$job = $BatchJobHandler->retrieve();

		// @todo implement moveJob()
		// moveJob( $Job, job_processing_path );
		// make sure the move was successful, if not try one more time only or fail? maybe another job picked it up before this one could move it

    // want to change this so retrieve jsut retrieves the job and the file path is built in the processJob method
		$result = $BatchJobHandler->processJob(
			new \App\BatchJobs\Job( $job['job'] ),
			array(
				'file_path_and_name' => $job['file_path_and_name'],
				'wskey' => $config['wskey']
			)
		);

		if ( !$result ) {
			// @todo implement moveJob()
			// moveJob( $Job, job_failed_path );
		} else {
			// @todo implement moveJob()
			// moveJob( $Job, job_succeeded_path );
		}

		} while( false );

	} catch ( Exception $e ) {
		error_log( $e->getMessage() );
	}
