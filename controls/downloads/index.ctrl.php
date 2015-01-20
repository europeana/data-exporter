<?php

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

			$output_path_and_filename = $BatchJobHandler->storage_path . '/' . $BatchJobHandler->job_completed_path . '/' . $job_group_id . '/' . $BatchJobHandler->job_output_path . '/' . $job_group_id . '.xml';

			if ( !file_exists( $output_path_and_filename ) ) {
				header( 'Content-Type: ' . $Config->content_type . '; charset=' . $Config->charset );
				$WebPage->page = 'downloads/';
				$WebPage->title = 'Downloads: ' . $Config->site_name;
				$WebPage->heading = 'Downloads: ' . $Config->site_name;
				$WebPage->view = 'html-layout.tpl.php';
				$html = '<h2 class="page-header">download batch job</h2><p>the batch job group, <code>' . $job_group_id . '</code>, has not yet completed processing so there’s no output file to download. you can also follow its <a href="/queue/?job-group-id=' . $job_group_id . '">status page</a> for the latest information.</p>';
				$WebPage->html = $html;
				include $WebPage->view;
				break;
			}

			header( 'Content-Type: application/xml' );
			header( 'Content-Transfer-Encoding: Binary' );
			header( 'Content-disposition: attachment; filename="' . basename( $output_path_and_filename ) . '"' );
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize( $output_path_and_filename ) );
			readfile( $output_path_and_filename );

		} while ( false );

	} catch( Exception $e ) {

		header( 'Content-Type: ' . $Config->content_type . '; charset=' . $Config->charset );
		$WebPage->page = 'downloads/';
		$WebPage->title = 'Downloads: ' . $Config->site_name;
		$WebPage->heading = 'Downloads: ' . $Config->site_name;
		$WebPage->view = 'html-layout.tpl.php';
		$html = '<p class="error">' . $e->getMessage() . '</p>';
		$WebPage->html = $html;
		include $WebPage->view;

	}
