<?php

	chdir( dirname( __DIR__ ) );

	if ( !include 'bootstrap.php' ) {
		echo 'we apologize, the application could not start at this time.';
		exit();
	}
