<?php
/**
 * intended for processing single batch jobs
 *
 * - it retireves the first batch job that needs to be processed
 * - moves the job thru the appropriate states as it processes it
 * - adds the processed output to its output file
 */
chdir( dirname( __DIR__ ) );
include 'bootstrap.php';

use App\BatchJobs\Job as Job;
use App\BatchJobs\JobHandler as JobHandler;

try {

	$BatchJobHandler = new JobHandler(
		array(
			'FileAdapter' => Penn\Php\File::getInstance(),
			'storage_path' => APPLICATION_PATH
		)
	);

	$Job = null;
	$count = 0;

	do {

		// get the first job to process
		$job_queue_result = $BatchJobHandler->getJobFromQueue();
		$Job = $job_queue_result['Job'];

		if ( !( $Job instanceof Job ) ) {
			break;
		}

		// move the job to the processing state
		if ( $job_queue_result['job-state-path'] === 'job_to_process_path' ) {
			$result = $BatchJobHandler->moveJob( 'job_processing_path', $Job );

			if ( !$result ) {
				break;
			}
		}

		// fail the Job?
		if ( $Job->attempts_to_process >= $Config->jobs->max_attempts_to_process ) {
			$BatchJobHandler->moveJob( 'job_failed_path', $Job );
			break;
		}

		// process the job
		$Job->attempts_to_process += 1;
		$BatchJobHandler->updateJob( $Job, 'job_processing_path' );

		// catch process errors so that script can continue to process additional jobs
		try {
			$result = $BatchJobHandler->processJob(
				array(
					'Job' => $Job,
					'wskey' => $Config->europeana_api->wskey
				)
			);

			// move the job to the next appropriate state
			if ( !$result ) {
				$result = $BatchJobHandler->moveJob( 'job_failed_path', $Job );
			} else {
				$result = $BatchJobHandler->moveJob( 'job_succeeded_path', $Job );
			}
		} catch ( Exception $e ) {
			echo date( 'r' ) . $e->getMessage() . PHP_EOL;
		}

		$count += 1;

	} while ( $count < $Config->jobs->max_to_process_per_run );

	if ( $count > 0 ) {
		echo date('r') . ' processed ' . $count . ( $count > 1 ? ' jobs' : ' job' ) . PHP_EOL;
	} else {
		echo date('r') . ' no jobs to process' . PHP_EOL;
	}

} catch ( Exception $e ) {

	error_log( $e->getMessage() );

}