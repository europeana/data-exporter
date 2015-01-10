<?php

	chdir( dirname( __DIR__ ) );
	include 'bootstrap.php';

	use \App\BatchJobs\ControlJob as ControlJob;
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

				// get a ControlJob if no items exisit in to process or processing for a job group
				$JobControl = $BatchJobHandler->getCompletedJobGroup();

				if ( !( $JobControl instanceof ControlJob ) ) {
					break;
				}

				// close output file
				$BatchJobHandler->closeXmlFile( $JobControl );

				// copy output file to cli-output
				$BatchJobHandler->copyOutputFile( $JobControl );

				// move the job group to cli-archive
				$BatchJobHandler->moveJobGroup( $JobControl );

				// @todo create a job where at least one job gets stuck in processing
				// how to deal with that scenario?

				unset( $JobControl );
				$count += 1;

			} while ( $count < $config['job_run_limit'] );

		} while ( false );

	} catch ( Exception $e ) {

		error_log( $e->getMessage() );

	}