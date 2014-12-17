<?php

	header( 'HTTP/1.1 404 File Not Found' );
	header( 'Content-Type: ' . $config['content-type'] . '; charset=' . $config['charset'] );

	$Page->page = '404';
	$Page->title = '404 File Not Found';
	$Page->heading = $Page->title . ' : ' . $config['site-name'];
	$Page->view = 'html-layout_tpl.php';

	include $Page->view;