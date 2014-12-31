<?php

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $config['content-type'] . '; charset=' . $config['charset'] );

	$Page->page = 'my-europeana/tag-list/create-batch-job';
	$Page->title = 'Create Batch Job - Tag List, My Europeana: ' . $config['site-name'];
	$Page->heading = 'Create Batch Job - Tag List, My Europeana: ' . $config['site-name'];
	$Page->view = 'html-layout_tpl.php';

	if ( isset( $_SERVER['PHP_ENV'] ) && $_SERVER['PHP_ENV'] === 'development'  ) {
		$Page->addScript( new W3C\Html\Script( array( 'src' => '/js/prettify.js' ) ) );
	} else {
		$Page->addScript( new W3C\Html\Script( array( 'content' => file_get_contents( 'public/js/prettify.min.js' ) ) ) );
	}

	$Page->addScript( new W3C\Html\Script( array( 'content' => 'prettyPrint();' ) ) );


	/**
	 * set-up variables
	 */
	$create_batch_job = false;
	$data = array();
	$debug = false;
	$empty_result = '<pre class="prettyprint">[{}]</pre>';
	$europeanaid = '';
	$html_result = '';
	$j_username = '';
	$j_password = '';
	$login_request_options = array();
	$login_result = '';
	$schema = 'ese';
	$tag = '';
	$tag_request_options = array();
	$tag_result = '';
	$total_records_found = 0;


	/**
	 * set-up csrf
	 */
	$Csrf = new \OWASP\Csrf( array( 'Session' => $Session, 'token-key-obfuscate' => true ) );


	/**
	 * check for a posted form
	 */
	try {

		do {

			// check for a post
			if ( empty( $_POST ) ) {
				$html_result .= $empty_result;
				break;
			}


			// check for cookie
			if ( !$Session->cookiePresent() ) {
				$html_result .= '<ul><li><span class="error">In order to use this form, your browser must accept cookies for this site.</span></li><li><a href="https://support.google.com/websearch/answer/35851?hl=en" target="_external">Enable cookies</a> for this site and then come back to <a href="/my-europeana/tag-list-search">the tag list search form</a>.</li></ul>';
				$html_result .= $empty_result;
				break;
			}


			// check for token
			if ( !$Csrf->isTokenValid( $_POST ) ) {
				$html_result .= $empty_result;
				break;
			}


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


			// check for regular form params
			if ( isset( $_POST['create-batch-job'] ) ) {
				$create_batch_job = filter_var( $_POST['create-batch-job'], FILTER_SANITIZE_STRING );
			}

			if ( $create_batch_job !== 'true' )  {
				$html_result .= '<pre class="prettyprint">{ success: false, message: "no batch job requested" }</pre>';
				break;
			}

			if ( isset( $_POST['debug'] ) && $_POST['debug'] === 'true' ) {
				$debug = true;
			}

			if ( isset( $_POST['europeanaid'] ) ) {
				$europeanaid = filter_var( $_POST['europeanaid'], FILTER_SANITIZE_STRING );
			}

			if ( isset( $_POST['tag'] ) ) {
				$tag = filter_var( $_POST['tag'], FILTER_SANITIZE_STRING );
			}

			if ( isset( $_POST['total-records-found'] ) ) {
				$total_records_found = (int) $_POST['total-records-found'];
			}


			// setup curl
			$Curl = new Libcurl\Curl( array( 'curl-followlocation' => true ) ); // because of 302 Moved Temporarily response from login.do
			$Curl->setHttpHeader( array( 'Accept: application/json' ) );


			// make the login call
			$login_request_options = array(
				'j_username' => $j_username,
				'j_password' => $j_password,
				'RequestService' => $Curl
			);

			$LoginRequest = new Europeana\Api\Request\MyEuropeana\Login( $login_request_options );
			$LoginResponse = new Europeana\Api\Response\Login( $LoginRequest->call() );


			// output curl info & response
			if ( $debug ) {
				$login_result .= '<h3>login cURL info</h3>';
				$login_result .= '<pre class="prettyprint">' . print_r( $LoginResponse->http_info, true ) . '</pre>';

				$login_result .= '<h3>login response body</h3>';
				$login_result .= '<pre class="prettyprint">' . $LoginResponse->getResponseAsJson() . '</pre>';
			}


			// setup tag
			$tag_request_options = array(
				'europeanaid' => $europeanaid,
				'RequestService' => $Curl,
				'tag' => $tag
			);


			// make the tag call
			$TagRequest = new Europeana\Api\Request\MyEuropeana\Tag( $tag_request_options );
			$TagResponse = new Europeana\Api\Response\Tag( $TagRequest->call(), $j_username );


			// output curl info & response
			if ( $debug ) {
				$tag_result .= '<h3>tag cURL info</h3>';
				$tag_result .= '<pre class="prettyprint">' . print_r( $TagResponse->http_info, true ) . '</pre>';

				$tag_result .= '<h3>tag response body</h3>';
				$tag_result .= '<pre class="prettyprint">' . $TagResponse->getResponseAsJson() . '</pre>';
			}


			// process the response
			if ( $TagResponse->items > 0 ) {
				$items = array();
				$job_path = realpath( APPLICATION_PATH . '/cli-jobs/' ) . '/';

				foreach( $TagResponse->items as $item ) {
					$items[] = $item->europeanaId;
				}

				App\Helpers\Jobs::addJobToFile(
					array(
						'endpoint' => $TagRequest->endpoint,
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
			} else {
				$html_result = '<pre class="prettyprint">{ success: false, message: "no results found" }</pre>';
			}

			// finalize html output
			$html_result .= $login_result . $tag_result;

		} while ( false );

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
