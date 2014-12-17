<?php

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $config['content-type'] . '; charset=' . $config['charset'] );

	$Page->page = 'my-europeana/tag-list-search';
	$Page->title = 'my europeana - tag list, ' . $config['site-name'];
	$Page->heading = $config['site-name'];
	$Page->view = 'html-layout_tpl.php';


	/**
	 * set-up variables
	 */
	$form_feedback = '';


	/**
	 * set-up csrf
	 */
	$Csrf = new \App\Csrf( array( 'Session' => $Session, 'token-key-obfuscate' => true ) );


	/**
	 * set-up page view
	 */
	$Page->html = include 'my-europeana/tag-list-search_form.php';
	include $Page->view;
