<?php

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $config['content-type'] . '; charset=' . $config['charset'] );

	$WebPage->page = 'my-europeana/tag-list/';
	$WebPage->title = 'Tag List, My Europeana: ' . $config['site-name'];
	$WebPage->heading = 'Tag List, My Europeana: ' . $config['site-name'];
	$WebPage->view = 'html-layout_tpl.php';


	/**
	 * set-up csrf
	 */
	$Csrf = new \OWASP\Csrf( array( 'Session' => $Session, 'token-key-obfuscate' => true ) );


	/**
	 * set-up page view
	 */
	$WebPage->html = include 'my-europeana/tag-list/search_form.php';
	include $WebPage->view;
