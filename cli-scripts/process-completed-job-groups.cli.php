<?php

	chdir( dirname( __DIR__ ) );
	include 'bootstrap.php';

	use App\BatchJobs\ControlJob as ControlJob;
	use App\BatchJobs\Job as Job;
	use App\BatchJobs\JobHandler as JobHandler;

	$job = array();

	try {

		$BatchJobHandler = new JobHandler(
			array(
				'FileAdapter' => Penn\Php\File::getInstance(),
				'storage_path' => APPLICATION_PATH
			)
		);

		$count = 0;

		do {

			// get a ControlJob if no items exisit in to-process or processing for a job group
			$job_queue_result = $BatchJobHandler->getJobFromQueue();
			$ControlJob = $job_queue_result['ControlJob'];
			$Job = $job_queue_result['Job'];

			if (
				$Job instanceof Job ||
				!( $ControlJob instanceof ControlJob ) ||
				!$ControlJob->all_jobs_created ||
				$ControlJob->creating_jobs
			) {
				break;
			}

			// close output file
			$BatchJobHandler->closeXmlFile( $ControlJob );

			// move the job group to cli-jobs-completed
			$BatchJobHandler->moveJobGroup( 'job_completed_path', $ControlJob );
			$count += 1;

		} while ( $count < $Config->job_groups->max_to_process_per_run );

		if ( $count > 0 ) {
			echo date( 'r' ) . ' processed ' . $count . ( $count > 1 ? ' job groups' : ' job group' ) . PHP_EOL;
		} else {
			echo date( 'r' ) . ' no job groups to process' . PHP_EOL;
		}

	} catch ( Exception $e ) {

		error_log( $e->getMessage() );

	}