<?php

	header( 'HTTP/1.1 404 File Not Found' );
	header( 'Content-Type: ' . $config['content-type'] . '; charset=' . $config['charset'] );

	$WebPage->page = '404';
	$WebPage->title = '404 File Not Found';
	$WebPage->heading = $WebPage->title . ' : ' . $config['site-name'];
	$WebPage->view = 'html-layout_tpl.php';

	include $WebPage->view;