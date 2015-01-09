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
					echo 'breaking';
					break;
				}

				echo 'continuing';
				// should we check processing file dates and move them back into to_process?
				// if no files in to_process or processing:
				//   * close output file
				//   * cp output file to cli-output
				//   * move job group to cli-archive

				unset( $JobControl );
				$count += 1;

			} while ( $count < $config['job_run_limit'] );

		} while ( false );

	} catch ( Exception $e ) {

		error_log( $e->getMessage() );

	}