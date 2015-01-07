<?php

	chdir( dirname( __DIR__ ) );
	include 'bootstrap.php';

	$job = array();

	try {

		do {

			// @todo implement this method
			return;

			$BatchJob = new App\BatchJobs\Job();

			$BatchJobHandler = new App\BatchJobs\JobHandler(
				array(
					'FileAdapter' => \Php\File::getInstance(),
					'storage_path' => APPLICATION_PATH
				)
			);

			$count = 1;
			$job_group_id = $BatchJobHandler->getJobGroupId();
			$output_filename = $BatchJobHandler->getOutputFilename();

			foreach( $TagResponse->items as $item ) {
				$BatchJob->populate(
					array(
						'endpoint' => $TagRequest->getEndpoint(),
						'record_id' => $item->europeanaId,
						'job_group_id' => $job_group_id,
						'job_id' => $count,
						'output_filename' => $output_filename,
						'params' => 'tag=' . $tag . '&europeanaid=' . $europeanaid,
						'schema' => $schema,
						'timestamp' => time(),
						'total_records_found' => $TagResponse->totalResults
					)
				);

				$BatchJobHandler->createJob( $BatchJob );
				$count += 1;
			}

		} while( false );

	} catch ( Exception $e ) {

		error_log( $e->getMessage() );

	}
