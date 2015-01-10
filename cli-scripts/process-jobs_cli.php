<?php

	chdir( dirname( __DIR__ ) );
	include 'bootstrap.php';

	use \App\BatchJobs\Job as Job;
	use App\BatchJobs\JobHandler as JobHandler;

	$job = array();

	try {

		do {

			$BatchJobHandler = new JobHandler(
				array(
					'FileAdapter' => \Php\File::getInstance(),
					'storage_path' => APPLICATION_PATH
				)
			);

			$count = 0;

			do {

				// get the first job to process
				$Job = $BatchJobHandler->getJobFromQueue();

				if ( !( $Job instanceof Job ) ) {
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

				unset( $Job, $result );
				$count += 1;

			} while ( $count < $config['process_jobs_limit'] );

		} while ( false );

	} catch ( Exception $e ) {

		error_log( $e->getMessage() );

	}