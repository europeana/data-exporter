<?php

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $config['content-type'] . '; charset=' . $config['charset'] );

	$WebPage->page = 'search/';
	$WebPage->title = 'Search: ' . $config['site-name'];
	$WebPage->heading = 'Search: ' . $config['site-name'];
	$WebPage->view = 'html-layout_tpl.php';


	/**
	 * set-up csrf
	 */
	$Csrf = new \OWASP\Csrf( array( 'Session' => $Session, 'token-key-obfuscate' => true ) );


	/**
	 * set-up page view
	 */
	$WebPage->html = include 'search/search_form.php';
	include $WebPage->view;
