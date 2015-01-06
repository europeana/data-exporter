<?php

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $config['content-type'] . '; charset=' . $config['charset'] );

	$Page->page = 'queue/';
	$Page->title = 'Queue: ' . $config['site-name'];
	$Page->heading = 'Queue: ' . $config['site-name'];
	$Page->view = 'html-layout_tpl.php';


	/**
	 * set-up variables
	 */
	$BatchJobHandler = new App\BatchJobs\JobHandler(
		array(
			'FileAdapter' => \Php\File::getInstance(),
			'storage_path' => APPLICATION_PATH
		)
	);

	$html = '';
	$job_group_id = '';


	try {

		do {

			// check for a get
			if ( empty( $_GET ) ) {
				break;
			}

			// check for a job group id
			if ( isset( $_GET['job-group-id'] ) ) {
				$job_group_id = filter_var( $_GET['job-group-id'], FILTER_SANITIZE_STRING );
			}

			if ( empty( $job_group_id ) ) {
				break;
			}

			$html .= $BatchJobHandler->getControlJob( $job_group_id );

		} while ( false );

		if ( empty( $html ) ) {
			$html = $BatchJobHandler->retrieveJobsAsHtmlTable();
		}

	} catch( Exception $e ) {

		$html .= '<p class="error">' . $e->getMessage() . '</p>';

	}


	/**
	 * set-up page view
	 */
	$Page->html = $html;
	include $Page->view;