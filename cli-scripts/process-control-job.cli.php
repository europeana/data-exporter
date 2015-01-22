<?php
/**
 * intended for creating batch jobs for search.json
 *
 * - it creates batch jobs based on control jobs that have not yet created
 *   all of the individual batch jobs they are meant to create
 * - it creates a limited nr of batch jobs during each run. the limit is set by
 *   the number of records retrieved by the call to search.json, which uses
 *   the rows parameter, e.g. 12, 24, 48, 96
 * - it relies on the control job to store its place in the iteration
 */
chdir( dirname( __DIR__ ) );
include 'bootstrap.php';

use App\BatchJobs\ControlJob as ControlJob;
use App\BatchJobs\JobHandler as JobHandler;
use Europeana\Api\Helpers\Request as Request_Helper;

try {

	$BatchJobHandler = new JobHandler(
		array(
			'FileAdapter' => Penn\Php\File::getInstance(),
			'storage_path' => APPLICATION_PATH
		)
	);

	do {

		// get the first control job to process
		$ControlJob = $BatchJobHandler->getControlJobFromQueue();

		if ( !( $ControlJob instanceof ControlJob ) ) {
			echo date( 'r' ) . ' no control jobs to process' . PHP_EOL;
			break;
		}

		// retrieve the results of the query using the start parameter in the control job
		// set-up the search
		$Curl = new Penn\Php\Curl();
		$Curl->setHttpHeader( array( 'Accept: application/json' ) );
		$wskey = filter_var( $Config->europeana_api->wskey, FILTER_SANITIZE_STRING );
		$SearchRequest = new Europeana\Api\Request\Search(
			array(
				'query' => Request_Helper::normalizeQueryString( $ControlJob->params ),
				'RequestService' => $Curl,
				'rows' => $Config->jobs->max_to_create_per_run,
				'start' => $ControlJob->start,
				'wskey' => $wskey
			)
		);

		// make the call
		$SearchResponse = new Europeana\Api\Response\Search( $SearchRequest->call(), $wskey );

		// create the individual batch jobs based on the result set returned
		if ( $SearchResponse->totalResults > 0 ) {
			$BatchJob = new App\BatchJobs\Job( array(), true );

			$ControlJob->creating_jobs = true;
			$BatchJobHandler->updateControlJob( $ControlJob );

			foreach( $SearchResponse->items as $item ) {
				$BatchJob->reset();

				$BatchJob->populate(
					array(
						'endpoint' => $SearchRequest->getEndpoint(),
						'job_group_id' => $ControlJob->job_group_id,
						'job_id' => $ControlJob->start,
						'output_filename' => $ControlJob->output_filename,
						'params' => $ControlJob->params,
						'record_id' => $item->id,
						'schema' => $ControlJob->schema,
						'timestamp' => time(),
						'total_records_found' => $SearchResponse->totalResults,
						'username' => $ControlJob->username
					)
				);

				$BatchJobHandler->createJob( $BatchJob );
				$ControlJob->start += 1;
			}

			if ( $ControlJob->start > $ControlJob->total_records_found ) {
				$ControlJob->all_jobs_created = true;
			}

			$ControlJob->creating_jobs = false;
			$BatchJobHandler->updateControlJob( $ControlJob );

			echo date( 'r' ) . ' control group created ' . count( $SearchResponse->items ) . ' jobs' . PHP_EOL;
		}

	} while ( false );

} catch ( Exception $e ) {

	error_log( $e->getMessage() );

}
