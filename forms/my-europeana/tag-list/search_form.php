<?php

	return
	'<p>' .
		'<a href="#form-help" title="form help">form help</a>' .
	'</p>' .

	'<form action="/my-europeana/tag-list/results/" method="post" role="form">' .
		'<input type="hidden" name="' . $Csrf->getTokenKey() . '" value="' . $Csrf->getTokenValue() . '" />' .

		'<p>' .
			'<label>' .
				'my europeana user’s public api key' .
				'<input type="text" name="public-api-key" placeholder="&lt;public-api-key>" autofocus class="form-control" />' .
			'</label>' .
		'</p>' .

		'<p>' .
			'<label>' .
				'my europeana user’s private api key' .
				'<input type="text" name="private-api-key" placeholder="&lt;private-api-key>" class="form-control" />' .
			'</label>' .
		'</p>' .

		'<p>' .
			'<label>' .
				'tag' .
				'<input type="text" name="tag" placeholder="&lt;tag>" class="form-control" />' .
			'</label>' .
		'</p>' .

		'<p>' .
			'<label>' .
				'europeanaid' .
				'<input type="text" name="europeanaid" placeholder="&lt;/2020601/attachments_55533_3404_55533_original_55533_jpg>" class="form-control" />' .
			'</label>' .
		'</p>' .

		'<p>' .
			'<label>' .
				'<input type="checkbox" name="debug" value="true" /> display request and response details' .
			'</label>' .
		'</p>' .

		'<p>' .
			'<input type="submit" value="get tag list" class="btn btn-default" />' .
		'</p>' .
	'</form>' .

	'<h2 id="form-help" class="page-header">form help</h2>' .
	'<p>this form uses the my europeana api method, <code>http://europeana.eu/api/v2/mydata/tag.json</code>, to retrieve a list of europeana objects tagged by a specific my europeana account. the account is identified by the public and private api keys you enter in the form.</p><p>some things to note:</p>' .

	'<h4>tag</h4>' .
	'<ul>' .
		'<li>when you provide no tag, the entire tag list for the user is retrieved.</li>' .
		'<li>when you provide a tag, the tag list is filtered by that tag and only europeana objects tagged with that specific tag, by that my europeana user, are returned.' .
			'<ul>' .
				'<li>the api method does not search other my europeana users’ tag lists.</li>' .
				'<li>it’s not possible to filter on multiple tags.</li>' .
			'</ul>' .
		'</li>' .
	'</ul>' .

	'<h4>europeanaid</h4>' .
	'<ul>' .
		'<li>when you provide a europeanaid, the tag list is filtered by that specific europeana object.</li>' .
		'<li>when you provide a europeanaid <i>and</i> a tag, the europeanaid is ignored and the tag list is filtered only by the tag.</li>' .
	'</ul>';