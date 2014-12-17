<?php

if ( APPLICATION_ENV === 'development' ) {
	header( "Location: /search" );
} else {
	header( "Location: /my-europeana/tag" );
}