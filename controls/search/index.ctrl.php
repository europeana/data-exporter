<?php

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $Config->content_type . '; charset=' . $Config->charset );

	$WebPage->page = 'search/';
	$WebPage->title = 'Search: ' . $Config->site_name;
	$WebPage->heading = 'Search: ' . $Config->site_name;
	$WebPage->view = 'html-layout.tpl.php';


	/**
	 * set-up csrf
	 */
	$Csrf = new \OWASP\Csrf( array( 'Session' => $Session, 'token-key-obfuscate' => true ) );


	/**
	 * set-up page view
	 */
	$WebPage->html = include 'search/search.form.php';
	include $WebPage->view;
