<?php

	$links = '';
	$links .= '<link rel="stylesheet" href="/css/prettify.css" />';
	$links .= '<link rel="stylesheet" href="/css/css.css" />';

	if ( !isset( $_SERVER['PHP_ENV'] ) || $_SERVER['PHP_ENV'] !== 'development'  ) {
		$links = '<style>';
		$links .= file_get_contents( 'public/css/prettify.min.css' );
		$links .= file_get_contents( 'public/css/css.min.css' );
		$links .= '</style>';
	}

	echo $links . PHP_EOL;