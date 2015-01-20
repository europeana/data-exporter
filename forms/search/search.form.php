<?php

	return
	'<p><a href="#form-help" title="form help">form help</a></p>' .

	'<form action="/search/results/" method="post" rolse="form">' .
		'<input type="hidden" name="' . $Csrf->getTokenKey() . '" value="' . $Csrf->getTokenValue() . '" />' .

		'<p>' .
			'<input type="text" name="query" class="form-control" placeholder="&lt;europeana query>" autofocus/>' .
		'</p>' .

		'<p>' .
			'<input type="text" name="username" class="form-control" placeholder="&lt;username>" />' .
		'</p>' .

		'<p>' .
			'<label>' .
				'<input type="checkbox" name="debug" value="true" /> display request and response details' .
			'</label>' .
		'</p>' .

		'<p>' .
			'<input type="submit" class="btn btn-default" value="query europeana" />' .
		'</p>' .
	'</form>' .

	'<h2 id="form-help" class="page-header">form help</h2>' .
	'<p>this form uses the europeana api method, <code>http://europeana.eu/api/v2/search.json</code>, to retrieve a list of europeana objects based on the query provided. the username, while not required, helps distinguish your query from other queries that may be in the queue. the easiest way to provide the query is to:</p>' .

	'<ol>' .
		'<li>go to the <a href="http://europeana.eu/portal/">europeana</a> website and create a search that produces a dataset you want to export.</li>' .
		'<li>copy the resulting URL from the browser’s address bar and paste it in the query form field above.' .
			'<ul>' .
				'<li>e.g., <code>http://europeana.eu/portal/search.html?query="Luftbildaufnahmen+Nord-Frankreich+1918"&qf=TYPE:IMAGE</code></li>' .
				'<li>note: the API call will be carried out with rows=12 no matter the number given in the URL.</li>' .
			'</ul>' .
		'</li>' .
	'</ol>';