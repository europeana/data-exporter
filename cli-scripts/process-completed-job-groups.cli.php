<?php

	chdir( dirname( __DIR__ ) );
	include 'bootstrap.php';

	use App\BatchJobs\ControlJob as ControlJob;
	use App\BatchJobs\Job as Job;
	use App\BatchJobs\JobHandler as JobHandler;
	use Zend\Mail;

	$job = array();
	$mail_template = include 'completed-email.tpl.php';

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

			// mail the user
			$Mail = new Mail\Message();

			$Mail->setBody(
				sprintf(
					filter_var( $mail_template['message'], FILTER_SANITIZE_STRING ),
					str_replace( array( '.', '_' ), ' ', $ControlJob->username ),
					$Config->host_name . '/queue/?job-group-id=' . $ControlJob->job_group_id
				)
			);

			$Mail->setSubject(
				sprintf(
					filter_var( $mail_template['subject'], FILTER_SANITIZE_STRING ),
					$ControlJob->job_group_id
				)
			);

			$Mail->setFrom(
				filter_var( $mail_template['from']['email'], FILTER_SANITIZE_STRING ),
				filter_var( $mail_template['from']['label'], FILTER_SANITIZE_STRING )
			);

			$Mail->addTo( $ControlJob->email );

			$transport = new Mail\Transport\Sendmail();
			$transport->send( $Mail );

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