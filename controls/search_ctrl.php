<?php

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $config['content-type'] . '; charset=' . $config['charset'] );

	$Page->page = 'search';
	$Page->title = 'search, ' . $config['site-name'];
	$Page->heading = $config['site-name'];
	$Page->view = 'html-layout_tpl.php';


	/**
	 * set-up csrf
	 */
	$Csrf = new \App\Csrf( array( 'Session' => $Session, 'token-key-obfuscate' => true ) );


	/**
	 * set-up page view
	 */
	$Page->html = include 'search_form.php';
	include $Page->view;
