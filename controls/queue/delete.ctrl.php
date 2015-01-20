<?php

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $Config->content_type . '; charset=' . $Config->charset );

	$WebPage->page = 'queue/delete';
	$WebPage->title = 'Delete, Queue: ' . $Config->site_name;
	$WebPage->heading = 'Delete, Queue: ' . $Config->site_name;
	$WebPage->view = 'html-layout.tpl.php';


	/**
	 * set-up variables
	 */
	$html = '';


	try {


	} catch( Exception $e ) {

		$html .= '<p class="error">' . $e->getMessage() . '</p>';

	}

	/**
	 * set-up page view
	 */
	$WebPage->html = $html;
	include $WebPage->view;