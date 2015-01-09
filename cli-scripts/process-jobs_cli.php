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

			$count = 0;

			do {

				// get the first job to process
				$Job = $BatchJobHandler->getJobFromQueue();

				if ( !( $Job instanceof \App\BatchJobs\Job ) ) {
					break;
				}

				// move the job to the processing state
				$result = $BatchJobHandler->moveJob( 'job_processing_path', $Job );

				if ( !$result ) {
					break;
				}

				// process the job
				$result = $BatchJobHandler->processJob(
					array(
						'Job' => $Job,
						'wskey' => $config['wskey']
					)
				);

				// move the job to the next appropriate state
				if ( !$result ) {
					$result = $BatchJobHandler->moveJob( 'job_failed_path', $Job );
				} else {
					$result = $BatchJobHandler->moveJob( 'job_succeeded_path', $Job );
				}

				$count += 1;

			} while ( $count < $config['job_run_limit'] );

		} while ( false );

	} catch ( Exception $e ) {

		error_log( $e->getMessage() );

	}
