<?php

	return
	'<h3>batch job</h3>' .

	'<ul id="form-feedback">' . $form_feedback. '</ul>' .

	'<p>verify that the sample result set below matches the results you’re expecting. if they do, you can create a background server process that will create an XML file based on this query. the resulting XML file will contain a total of ' . number_format( $SearchResponse->totalResults ) . ' items and can be used to upload those items to <a href="https://commons.wikimedia.org">Wikimedia Commons</a> with the <a href="http://www.mediawiki.org/wiki/Extension:GWToolset">Mediawiki GWToolset Extension</a>.</p>' .

	'<form action="/search/create-batch-job/" method="post">' .
		'<input type="hidden" name="create-batch-job" value="true" />' .
		'<input type="hidden" name="' . $Csrf->getTokenKey() . '" value="' . $Csrf->getTokenValue() . '" />' .
		'<input type="hidden" name="debug" value="' . ( $debug ? 'true' : 'false' ) . '" />' .
		'<input type="hidden" name="query" value="' . $query . '" />' .
		'<input type="hidden" name="total-records-found" value="' . $SearchResponse->totalResults . '" />' .
		'<input type="submit" class="btn btn-default" value="create a batch job" />' .
	'</form>';