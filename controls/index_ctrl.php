<?php

if ( APPLICATION_ENV === 'developments' ) {
	header( "Location: /search" );
} else {
	header( "Location: /my-europeana/tag-list-search" );
}