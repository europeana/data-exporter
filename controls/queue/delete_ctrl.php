<?php

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $config['content-type'] . '; charset=' . $config['charset'] );

	$Page->page = 'queue/delete';
	$Page->title = 'Delete, Queue: ' . $config['site-name'];
	$Page->heading = 'Delete, Queue: ' . $config['site-name'];
	$Page->view = 'html-layout_tpl.php';


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
	$Page->html = $html;
	include $Page->view;