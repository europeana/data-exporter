<?php

	/**
	 * set-up page
	 */
	use \Europeana\Api\Helpers\Response as Response_Helper;
	use \Europeana\Api\Helpers\Request as Request_Helper;
	header( 'Content-Type: ' . $config['content-type'] . '; charset=' . $config['charset'] );

	$Page->page = 'search/create-batch-job';
	$Page->title = 'Create Batch Job - Search: ' . $config['site-name'];
	$Page->heading = 'Create Batch Job - Search: ' . $config['site-name'];
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
	$debug = false;
	$empty_result = '<pre class="prettyprint">[{}]</pre>';
	$form_feedback = '';
	$html_result = '';
	$query = '';
	$rows = 12;
	$SearchResponse = null;
	$schema = 'ese';
	$search_request_options = array();
	$search_result = '';
	$start = 1;
	$total_records_found = 0;
	$wskey = '';


	/**
	 * set-up csrf
	 */
	$Csrf = new \OWASP\Csrf( array( 'Session' => $Session, 'token-key-obfuscate' => true ) );


	try {

		do {

			// check for a post
			if ( empty( $_POST ) ) {
				$html_result .= $empty_result;
				break;
			}


			// check for cookie
			if ( !$Session->cookiePresent() ) {
				$html_result .= '<ul><li><span class="error">In order to use this form, your browser must accept cookies for this site.</span></li><li><a href="https://support.google.com/websearch/answer/35851?hl=en" target="_external">Enable cookies</a> for this site and then return to <a href="/search">the search form</a>.</li></ul>';
				$html_result .= $empty_result;
				break;
			}


			// check for token
			if ( !$Csrf->isTokenValid( $_POST ) ) {
				$html_result .= $empty_result;
				break;
			}


			// get regular form params
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

			if ( isset( $_POST['total-records-found'] ) ) {
				$total_records_found = (int) $_POST['total-records-found'];
			}

			if ( isset( $_POST['query'] ) ) {
				$query = filter_var( $_POST['query'], FILTER_SANITIZE_STRING );
			}

			if ( empty( $query ) ) {
				$html_result .= '<pre class="prettyprint">{ success: false, message: "no query provided" }</pre>';
				break;
			}


			// get start param from query - allow user to override coded default
			$start = (int) Request_Helper::getQueryParam( $query, 'start', $start );


			// clean-up query
			$query_string = Request_Helper::normalizeQueryString( $query );


			// remove rows param from query - we're going to ignore user provided value
			$query_string = Request_Helper::removeQueryParam( $query_string, 'rows' );


			// remove start param from the query
			$query_string = Request_Helper::removeQueryParam( $query_string, 'start' );


			// set api key
			if ( isset( $config['wskey'] ) ) {
				$wskey = filter_var( $config['wskey'], FILTER_SANITIZE_STRING );
			}

			// set search options
			$search_request_options = array(
				'query' => $query_string,
				'rows' => $rows,
				'start' => $start,
				'wskey' => $wskey
			);


			// set-up the search
			$Curl = new Libcurl\Curl();
			$Curl->setHttpHeader( array( 'Accept: application/json' ) );
			$search_request_options['RequestService'] = $Curl;
			$SearchRequest = new Europeana\Api\Request\Search( $search_request_options );


			// make the call
			$SearchResponse = new Europeana\Api\Response\Search( $SearchRequest->call(), $wskey );


			// output curl info & response
			if ( $debug ) {
				$search_result .= '<h3>cURL info</h3>';
				$search_result .= '<pre class="prettyprint">' . print_r( $SearchResponse->http_info, true ) . '</pre>';

				$search_result .= '<h3>response body</h3>';
				$search_result .= '<pre class="prettyprint">' . $SearchResponse->getResponseAsJson() . '</pre>';
			}


			// process the response
			if ( $SearchResponse->totalResults > 0 ) {
				$job_path = realpath( APPLICATION_PATH . '/cli-jobs/' ) . '/';

				if ( !empty( $SearchResponse->items ) && !empty( $SearchResponse->items[0]->europeanaCollectionName ) ) {
					$job_identifier = $SearchResponse->items[0]->europeanaCollectionName;
				} else {
					$job_identifier = uniqid();
				}

				if ( is_array( $job_identifier ) && isset( $job_identifier[0] ) ) {
					$job_identifier = $job_identifier[0];
				}

				$items = array();

				foreach( $SearchResponse->items as $item ) {
					$items[] = $item->id;
				}

				App\Helpers\Jobs::addJobToFile(
					array(
						'endpoint' => $SearchRequest->endpoint,
						'items' => $items,
						'job-identifier' => $job_identifier,
						'output-filename' => App\Helpers\Jobs::createOutputFilename( $job_identifier ),
						'params' => $query_string,
						'schema' => $schema,
						'timestamp' => time(),
						'total-records-found' => $total_records_found
					),
					array(
						'filename' => $config['dataset-jobs'],
						'path' => $job_path
					)
				);

				$html_result = '<pre class="prettyprint">{ success: true, message: "batch job created" }</pre>';
			} else {
				$html_result = '<pre class="prettyprint">{ success: false, message: "no results found" }</pre>';
			}

			// finalize html output
			$html_result .= $search_result;

		} while ( false );

	} catch( Exception $e ) {

		$msg = '<p class="error">%s</p>';
		$parts = explode( 'Array', $e->getMessage(), 2 );

		if ( count( $parts ) === 2 ) {
			$html_result .= sprintf( $msg, nl2br( $parts[0] ) );
			$html_result .= '<pre class="prettyprint">' . Response_Helper::obfuscateApiKey( $parts[1], $wskey ) . '</pre>';
		} else {
			$html_result .= sprintf( $msg, Response_Helper::obfuscateApiKey( $e->getMessage(), $wskey ) );
		}

	}


	/**
	 * set-up page view
	 */
	$Page->html = $html_result;
	include $Page->view;
