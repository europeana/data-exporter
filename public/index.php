<?php

	chdir( dirname( __DIR__ ) );

	if ( !file_exists( 'bootstrap.php' ) ) {
		echo 'we apologize, the application could not start at this time.';
		exit();
	}

	include 'bootstrap.php';
