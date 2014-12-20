<?php

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $config['content-type'] . '; charset=' . $config['charset'] );

	$Page->page = 'my-europeana/tag-list-search-results';
	$Page->title = 'my europeana - tag list search results, ' . $config['site-name'];
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
	$data = array();
	$debug = false;
	$empty_result = '<pre class="prettyprint">[{}]</pre>';
	$europeanaid = '';
	$form_feedback = '';
	$html_result = '<h2 class="page-header">my europeana - tag list: search results</h2>';
	$j_username = '';
	$j_password = '';
	$login_result = '';
	$tag = '';
	$tag_result = '';


	/**
	 * set-up csrf
	 */
	$Csrf = new \App\Csrf( array( 'Session' => $Session, 'token-key-obfuscate' => true ) );


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
				$html_result .= '<ul><li><span class="error">In order to use this form, your browser must accept cookies for this site.</span></li><li><a href="https://support.google.com/websearch/answer/35851?hl=en" target="_external">Enable cookies</a> for this site and then return to <a href="/my-europeana/tag-list-search">the tag list search form</a>.</li></ul>';
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


			// get regular form params
			if ( isset( $_POST['debug'] ) && $_POST['debug'] === 'true' ) {
				$debug = true;
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
				'HttpRequest' => $Curl,
				'j_username' => $j_username,
				'j_password' => $j_password
			);

			$LoginRequest = new Europeana\Api\Request\MyEuropeana\Login( $data );
			$LoginResponse = new Europeana\Api\Response\Json\Login( $LoginRequest->call() );


			// output curl info & response
			if ( $debug ) {
				$login_result .= '<h3>login cURL info</h3>';
				$login_result .= '<pre class="prettyprint">' . print_r( $LoginResponse->_response_info, true ) . '</pre>';

				$login_result .= '<h3>login response body</h3>';
				$login_result .= '<pre class="prettyprint">' . $LoginResponse->getResponseAsJson() . '</pre>';
			}


			// setup tag
			$data = array(
				'europeanaid' => $europeanaid,
				'HttpRequest' => $Curl,
				'tag' => $tag
			);


			// make the tag call
			$TagRequest = new Europeana\Api\Request\MyEuropeana\Tag( $data );
			$TagResponse = new Europeana\Api\Response\Json\Tag( $TagRequest->call(), $j_username );


			// process the response
			if ( $TagResponse->totalResults > 0 ) {

				// add batch job form
				$html_result .= include 'my-europeana/tag-list-create-batch-job_form.php';

				// add results example set
				$html_result .= Europeana\Api\Helpers\Response::getResponseImagesWithLinks( $TagResponse );

			// set no results output
			} else {
				$html_result .= '<h3>sample result set</h3>';
				$html_result .= '<p>no tags found</p>';
			}


			// output curl info & response
			if ( $debug ) {
				$tag_result .= '<h3>tag cURL info</h3>';
				$tag_result .= '<pre class="prettyprint">' . print_r( $TagResponse->_response_info, true ) . '</pre>';

				$tag_result .= '<h3>tag response body</h3>';
				$tag_result .= '<pre class="prettyprint">' . $TagResponse->getResponseAsJson() . '</pre>';
			}


			// finalize html output
			$html_result .= $login_result . $tag_result;

		} while( false );

	} catch( Exception $e ) {

		$msg = '<p class="error">%s</p>';
		$parts = explode( 'Array', $e->getMessage(), 2 );

		if ( count( $parts ) === 2 ) {
			$html_result .= sprintf( $msg, nl2br( $parts[0] ) );
			$html_result .= '<pre class="prettyprint">' . $parts[1] . '</pre>';
		} else {
			$html_result .= sprintf( $msg, $e->getMessage() );
		}

	}


	/**
	 * set-up page view
	 */
	$Page->html = $html_result;
	include $Page->view;
