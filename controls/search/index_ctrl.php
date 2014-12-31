<?php

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $config['content-type'] . '; charset=' . $config['charset'] );

	$Page->page = 'search/';
	$Page->title = 'Search: ' . $config['site-name'];
	$Page->heading = 'Search: ' . $config['site-name'];
	$Page->view = 'html-layout_tpl.php';


	/**
	 * set-up csrf
	 */
	$Csrf = new \OWASP\Csrf( array( 'Session' => $Session, 'token-key-obfuscate' => true ) );


	/**
	 * set-up page view
	 */
	$Page->html = include 'search/search_form.php';
	include $Page->view;
