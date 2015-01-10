<?php

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $config['content-type'] . '; charset=' . $config['charset'] );

	$WebPage->page = 'download/';
	$WebPage->title = 'Download: ' . $config['site-name'];
	$WebPage->heading = 'Download: ' . $config['site-name'];
	$WebPage->view = 'html-layout_tpl.php';


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

		} while ( false );

	} catch( Exception $e ) {

		$html .= '<p class="error">' . $e->getMessage() . '</p>';

	}


	/**
	 * set-up page view
	 */
	$WebPage->html = $html;
	include $WebPage->view;