<?php

	header( 'Content-Type: ' . $config['content-type'] . '; charset=' . $config['charset'] );

	$Page->page = 'queue';
	$Page->title = 'queue, ' . $config['site-name'];
	$Page->heading = $config['site-name'];
	$Page->view = 'html-layout_tpl.php';

	try {

		$html = '<h2 class="page-header">queue</h2>';

		$job_path = realpath( __DIR__ . '/../cli-jobs/' ) . '/';

		$html .= App\Helpers\Jobs::retrieveJobsAsHtmlTable(
			array(
				'filename' => $config['dataset-jobs'],
				'path' => $job_path
			)
		);

	} catch( Exception $e ) {

		$html .= '<p class="error">' . $e->getMessage() . '</p>';

	}

	$Page->html = $html;

	include $Page->view;