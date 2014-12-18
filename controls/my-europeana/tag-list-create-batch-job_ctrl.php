<?php

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $config['content-type'] . '; charset=' . $config['charset'] );

	$Page->page = 'my-europeana/tag-list-create-batch-job';
	$Page->title = 'my europeana - tag list create batch job, ' . $config['site-name'];
	$Page->heading = $config['site-name'];
	$Page->view = 'html-layout_tpl.php';

	if ( !isset( $_SERVER['PHP_ENV'] ) || $_SERVER['PHP_ENV'] !== 'developments'  ) {
		$Page->script_body .= '<script src="/js/prettify.min.js"></script>' . PHP_EOL;
	} else {
		$Page->script_body .= '<script src="/js/prettify.js"></script>' . PHP_EOL;
	}

	$Page->script_body .= '<script>prettyPrint();</script>' . PHP_EOL;


	/**
	 * set-up variables
	 */
	$create_batch_job = false;
	$data = array();
	$europeanaid = '';
	$html_result = '<h2 class="page-header">my europeana - tag list: create batch job</h2>';
	$j_username = '';
	$j_password = '';
	$schema = 'ese';
	$tag = '';
	$total_records_found = 0;


	/**
	 * set-up csrf
	 */
	$Csrf = new \App\Csrf( array( 'Session' => $Session, 'token-key-obfuscate' => true ) );


	/**
	 * check for a posted form
	 */
	try {

		do {

			// validate post
			include 'post_ctrl.php';


			// get login params
			if ( isset( $_POST['public-api-key'] ) ) {
				$j_username = filter_var( $_POST['public-api-key'], FILTER_SANITIZE_STRING );
			}

			if ( isset( $_POST['public-api-key'] ) ) {
				$j_password = filter_var( $_POST['private-api-key'], FILTER_SANITIZE_STRING );
			}

			if ( empty( $j_username ) || empty( $j_password ) ) {
				$html_result .= '<pre class="prettyprint">{ success: false, message: "missing credentials" }</pre>';
				break;
			}


			// check for batch job params
			if ( isset( $_POST['create-batch-job'] ) ) {
				$create_batch_job = filter_var( $_POST['create-batch-job'], FILTER_SANITIZE_STRING );
			}

			if ( isset( $_POST['total-records-found'] ) ) {
				$total_records_found = (int) $_POST['total-records-found'];
			}

			if ( isset( $_POST['europeanaid'] ) ) {
				$europeanaid = filter_var( $_POST['europeanaid'], FILTER_SANITIZE_STRING );
			}

			if ( isset( $_POST['tag'] ) ) {
				$tag = filter_var( $_POST['tag'], FILTER_SANITIZE_STRING );
			}


			// setup curl
			$Curl = new Php\Curl( array( 'curl-followlocation' => true ) ); // because of 302 Moved Temporarily response from login.do
			$Curl->setHttpHeader( array( 'Accept: application/json' ) );


			// make the login call
			$data = array(
				'j_username' => $j_username,
				'j_password' => $j_password
			);

			$LoginRequest = new Europeana\Api\Request\MyEuropeana\Login( $Curl, $data );
			$LoginResponse = new Europeana\Api\Response\Json\Login( $LoginRequest->call() );


			// setup tag
			$data = array(
				'europeanaid' => $europeanaid,
				'tag' => $tag
			);

			// make the tag call
			$TagRequest = new Europeana\Api\Request\MyEuropeana\Tag( $Curl, $data );
			$TagResponse = new Europeana\Api\Response\Json\Tag( $TagRequest->call(), $j_username );


			// create a batch job?
			if ( $create_batch_job === 'true' )  {
				$items = array();
				$job_path = realpath( __DIR__ . '/../../cli-jobs/' ) . '/';

				foreach( $TagResponse->items as $item ) {
					$items[] = $item->europeanaId;
				}

				App\Helpers\Jobs::addJobToFile(
					array(
						'endpoint' => $TagRequest->_endpoint,
						'items' => $items,
						'job-identifier' => $TagResponse->username,
						'output-filename' => App\Helpers\Jobs::createOutputFilename( $TagResponse->username ),
						'params' => 'tag=' . $tag . '&europeanaid=' . $europeanaid,
						'schema' => $schema,
						'timestamp' => time(),
						'total-records-found' => $total_records_found
					),
					array(
						'filename' => $config['dataset-jobs'],
						'path' => $job_path
					)
				);

				$html_result .= '<pre class="prettyprint">{ success: true, message: "batch job created" }</pre>';
				break;
			}

		} while( false );

	} catch( Exception $e ) {

		$msg = '<p class="error">%s</p>';
		$parts = explode( 'Array', $e->getMessage(), 2 );

		if ( count( $parts ) === 2 ) {
			$html_result .= sprintf( $msg, nl2br( $parts[0] ) );
			$json = $parts[1];
		} else {
			$html_result .= sprintf( $msg, $e->getMessage() );
		}

	}


	/**
	 * set-up page view
	 */
	$Page->html = $html_result;
	include $Page->view;
