<?php

	// check for a post
	if ( empty( $_POST ) ) {
		$html_result .= $empty_result;
		break;
	}


	// check for cookie
	if ( !$Session->cookiePresent() ) {
		$html_result .= '<ul><li><span class="error">In order to use this form, your browser must accept cookies for this site.</span></li><li><a href="https://support.google.com/websearch/answer/35851?hl=en" target="_external">Enable cookies</a> for this site and then come back to <a href="/my-europeana/tag-list-search">the tag list search form</a>.</li></ul>';
		$html_result .= $empty_result;
		break;
	}


	// check for token
	if ( !$Csrf->isTokenValid( $_POST ) ) {
		$html_result .= $empty_result;
		break;
	}