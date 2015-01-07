<?php

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $config['content-type'] . '; charset=' . $config['charset'] );

	$WebPage->page = 'queue/delete';
	$WebPage->title = 'Delete, Queue: ' . $config['site-name'];
	$WebPage->heading = 'Delete, Queue: ' . $config['site-name'];
	$WebPage->view = 'html-layout_tpl.php';


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