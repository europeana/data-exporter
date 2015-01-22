<?php

	use App\BatchJobs\JobHandler as JobHandler;

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $Config->content_type . '; charset=' . $Config->charset );

	$WebPage->page = 'my-europeana/tag-list/create-batch-job';
	$WebPage->title = 'Create Batch Job - Tag List, My Europeana: ' . $Config->site_name;
	$WebPage->heading = 'Create Batch Job - Tag List, My Europeana: ' . $Config->site_name;
	$WebPage->view = 'html-layout.tpl.php';

	if ( isset( $_SERVER['PHP_ENV'] ) && $_SERVER['PHP_ENV'] === 'development'  ) {
		$WebPage->addScript( new Penn\Html\Script( array( 'src' => '/js/prettify.js' ) ) );
	} else {
		$WebPage->addScript( new Penn\Html\Script( array( 'content' => file_get_contents( 'public/js/prettify.min.js' ) ) ) );
	}

	$WebPage->addScript( new Penn\Html\Script( array( 'content' => 'prettyPrint();' ) ) );


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


	/**
	 * set-up csrf
	 */
	$Csrf = new Penn\Owasp\Csrf( array( 'Session' => $Session, 'token-key-obfuscate' => true ) );


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


			// setup curl
			$Curl = new Penn\Php\Curl( array( 'curl-followlocation' => true ) ); // because of 302 Moved Temporarily response from login.do
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
			// exceeded job max
			if ( $TagResponse->totalResults > $Config->jobs->max_allowed ) {

				$html_result = '<pre class="prettyprint">{ success: false, message: "total results exceeded the maximum number of items per job" }</pre>';

			// create the job control job
			} elseif ( $TagResponse->items > 0 ) {

				$BatchJob = new App\BatchJobs\Job( array(), true );

				$BatchJobHandler = new JobHandler(
					array(
						'FileAdapter' => Penn\Php\File::getInstance(),
						'storage_path' => APPLICATION_PATH
					)
				);

				$count = 1;
				$job_group_id = $BatchJobHandler->getJobGroupId();
				$output_filename = $BatchJobHandler->getOutputFilename();

				foreach( $TagResponse->items as $item ) {
					$BatchJob->reset();

					$BatchJob->populate(
						array(
							'endpoint' => $TagRequest->getEndpoint(),
							'job_group_id' => $job_group_id,
							'job_id' => $count,
							'output_filename' => $output_filename,
							'params' => 'tag=' . $tag . '&europeanaid=' . $europeanaid,
							'record_id' => $item->europeanaId,
							'schema' => $schema,
							'timestamp' => time(),
							'total_records_found' => $TagResponse->totalResults,
							'username' => $TagResponse->username
						)
					);

					$BatchJobHandler->createJob( $BatchJob );
					$count += 1;
				}

				// because there is no start/limit for this api method, all jobs should be created during this run
				if ( ( $count - 1 ) !== $TagResponse->totalResults ) {
					error_log( __FILE__ . ' the number of batch jobs created does not match the total results' );
					throw new Exception( 'the number of batch jobs created does not match the total results', 99 );
				}

				$ControlJob = new App\BatchJobs\ControlJob(
					array(
						'all_jobs_created' => true,
						'creating_jobs' => false,
						'endpoint' => $TagRequest->getEndpoint(),
						'job_group_id' => $job_group_id,
						'output_filename' => $output_filename,
						'params' => 'tag=' . $tag . '&europeanaid=' . $europeanaid,
						'schema' => $schema,
						'start' => $count,
						'timestamp' => time(),
						'total_records_found' => $TagResponse->totalResults,
						'username' => $TagResponse->username
					)
				);

				$BatchJobHandler->createControlJob( $ControlJob );
				header( 'Location: /queue/?job-group-id=' . urlencode( $ControlJob->job_group_id ) );

			// no results
			} else {

				$html_result = '<pre class="prettyprint">{ success: false, message: "no results found" }</pre>';

			}

			// finalize html output
			$html_result .= $login_result . $tag_result;

		} while ( false );

	} catch( Exception $e ) {

		$html_result .= '<p class="error">' . $e->getMessage() . '</p>';
		$html_result .= '<pre class="prettyprint">{ success: false, message: "application exception" }</pre>';

	}


	/**
	 * set-up page view
	 */
	$WebPage->html = $html_result;
	include $WebPage->view;
