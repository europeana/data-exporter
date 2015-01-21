<?php

	chdir( dirname( __DIR__ ) );
	include 'bootstrap.php';

	use App\BatchJobs\ControlJob as ControlJob;
	use App\BatchJobs\JobHandler as JobHandler;

	$job = array();

	try {

		do {

			$BatchJobHandler = new JobHandler(
				array(
					'FileAdapter' => Penn\Php\File::getInstance(),
					'storage_path' => APPLICATION_PATH
				)
			);

			$count = 0;

			do {

				// get a ControlJob if no items exisit in to process or processing for a job group
				$JobControl = $BatchJobHandler->getCompletedJobGroup();

				if ( !( $JobControl instanceof ControlJob ) ) {
					break;
				}

				// close output file
				$BatchJobHandler->closeXmlFile( $JobControl );

				// move the job group to cli-jobs-completed
				$BatchJobHandler->moveJobGroup( 'job_completed_path', $JobControl );

				// @todo create a job where at least one job gets stuck in processing
				// how to deal with that scenario?

				unset( $JobControl );
				$count += 1;

			} while ( $count < $Config->jobs->process_completed_jobs_limit );

		} while ( false );

	} catch ( Exception $e ) {

		error_log( $e->getMessage() );

	}