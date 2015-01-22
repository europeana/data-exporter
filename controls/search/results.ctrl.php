<?php

	use Europeana\Api\Helpers\Response as Response_Helper;
	use Europeana\Api\Helpers\Request as Request_Helper;
	use Penn\Html\Script;

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $Config->content_type . '; charset=' . $Config->charset );

	$WebPage->page = 'search/results';
	$WebPage->title = 'Results - Search: ' . $Config->site_name;
	$WebPage->heading = 'Results - Search: ' . $Config->site_name;
	$WebPage->view = 'html-layout.tpl.php';

	if ( isset( $_SERVER['PHP_ENV'] ) && $_SERVER['PHP_ENV'] === 'development'  ) {
		$WebPage->addScript( new Script( array( 'src' => '/js/prettify.js' ) ) );
	} else {
		$WebPage->addScript( new Script( array( 'content' => file_get_contents( 'public/js/prettify.min.js' ) ) ) );
	}

	$WebPage->addScript( new Script( array( 'content' => 'prettyPrint();' ) ) );


	/**
	 * set-up variables
	 */
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
	$username = '';
	$wskey = '';


	/**
	 * set-up csrf
	 */
	$Csrf = new Penn\Owasp\Csrf( array( 'Session' => $Session, 'token-key-obfuscate' => true ) );


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
			if ( isset( $_POST['debug'] ) && $_POST['debug'] === 'true' ) {
				$debug = true;
			}

			if ( isset( $_POST['query'] ) ) {
				$query = filter_var( $_POST['query'], FILTER_SANITIZE_STRING );
			}

			if ( isset( $_POST['username'] ) ) {
				$username = filter_var( $_POST['username'], FILTER_SANITIZE_STRING );
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


			// add profile to the query string
			$query_string .= '&profile=minimal';


			// set api key
			$wskey = filter_var( $Config->europeana_api->wskey, FILTER_SANITIZE_STRING );

			// set search options
			$search_request_options = array(
				'query' => $query_string,
				'rows' => $rows,
				'start' => $start,
				'wskey' => $wskey
			);


			// set-up the search
			$Curl = new Penn\Php\Curl();
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
			if ( $SearchResponse->totalResults > $Config->jobs->max_allowed ) {

				$html_result .=
					sprintf(
						'<h2 class="page-header">batch job</h2><p>the total result set of <b>%s</b> items exceeds the maximum job limit of <b>%s</b> items. you need to narrow down the result set in order to create a batch job.</p>',
						number_format( $SearchResponse->totalResults ),
						number_format( $Config->jobs->max_allowed )
					);

				$html_result .= Response_Helper::getResponseImagesWithLinks( $SearchResponse );

			} elseif ( $SearchResponse->totalResults > 0 ) {

				// add batch job form
				$html_result .= include 'search/create-batch-job.form.php';
				$html_result .= Response_Helper::getResponseImagesWithLinks( $SearchResponse );

			} else {

				$html_result .= '<h3>sample result set</h3>';
				$html_result .= '<p>no search results found</p>';

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
	$WebPage->html = $html_result;
	include $WebPage->view;
