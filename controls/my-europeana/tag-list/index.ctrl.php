<?php

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $Config->content_type . '; charset=' . $Config->charset );

	$WebPage->page = 'my-europeana/tag-list/';
	$WebPage->title = 'Tag List, My Europeana: ' . $Config->site_name;
	$WebPage->heading = 'Tag List, My Europeana: ' . $Config->site_name;
	$WebPage->view = 'html-layout.tpl.php';


	/**
	 * set-up csrf
	 */
	$Csrf = new Penn\Owasp\Csrf( array( 'Session' => $Session, 'token-key-obfuscate' => true ) );


	/**
	 * set-up page view
	 */
	$WebPage->html = include 'my-europeana/tag-list/search.form.php';
	include $WebPage->view;
