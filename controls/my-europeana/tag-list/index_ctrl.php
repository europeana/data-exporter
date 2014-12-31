<?php

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $config['content-type'] . '; charset=' . $config['charset'] );

	$Page->page = 'my-europeana/tag-list/';
	$Page->title = 'Tag List, My Europeana: ' . $config['site-name'];
	$Page->heading = 'Tag List, My Europeana: ' . $config['site-name'];
	$Page->view = 'html-layout_tpl.php';


	/**
	 * set-up csrf
	 */
	$Csrf = new \OWASP\Csrf( array( 'Session' => $Session, 'token-key-obfuscate' => true ) );


	/**
	 * set-up page view
	 */
	$Page->html = include 'my-europeana/tag-list/search_form.php';
	include $Page->view;
