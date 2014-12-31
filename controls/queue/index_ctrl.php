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
	$html = '';


	try {

		$job_path = realpath( APPLICATION_PATH . '/cli-jobs/' ) . '/';

		$html = App\Helpers\Jobs::retrieveJobsAsHtmlTable(
			array(
				'filename' => $config['dataset-jobs'],
				'path' => $job_path
			)
		);

	} catch( Exception $e ) {

		$html .= '<p class="error">' . $e->getMessage() . '</p>';

	}


	/**
	 * set-up page view
	 */
	$Page->html = $html;
	include $Page->view;