<?php

	header( 'HTTP/1.1 404 File Not Found' );
	header( 'Content-Type: ' . $Config->content_type . '; charset=' . $Config->charset );

	$WebPage->page = '404';
	$WebPage->title = '404 File Not Found';
	$WebPage->heading = $WebPage->title . ' : ' . $Config->site_name;
	$WebPage->view = 'html-layout.tpl.php';

	include $WebPage->view;