<?php

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $Config->content_type . '; charset=' . $Config->charset );

	$WebPage->page = 'queue/';
	$WebPage->title = 'Queue: ' . $Config->site_name;
	$WebPage->heading = 'Queue: ' . $Config->site_name;
	$WebPage->view = 'html-layout.tpl.php';


	/**
	 * set-up variables
	 */
	$BatchJobHandler = new App\BatchJobs\JobHandler(
		array(
			'FileAdapter' => Pennline\Php\File::getInstance(),
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

			$html = $BatchJobHandler->getJobGroupHtml( $job_group_id );

		} while ( false );

		if ( empty( $html ) ) {
			$html = $BatchJobHandler->getJobsAsHtmlTable();
		}

	} catch( Exception $e ) {

		$html .= '<p class="error">' . $e->getMessage() . '</p>';

	}


	/**
	 * set-up page view
	 */
	$WebPage->html = $html;
	include $WebPage->view;