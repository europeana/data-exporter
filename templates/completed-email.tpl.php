<?php

	return array(
		'message' =>
			'Dear %s,' . PHP_EOL . PHP_EOL .
			'Your export is ready. Please click this link to download it:' . PHP_EOL . PHP_EOL .
			'%s' . PHP_EOL . PHP_EOL .
			'Note that while there is currently no limit on how long the completed job will stay on the server, this policy may change without notice, so please make sure you download your own copy as soon as possible.' . PHP_EOL . PHP_EOL .
			'If you have questions or would like to contact us, please feel free to write to us at: api@europeana.eu' . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL .
			'Best regards,' . PHP_EOL .
			'The Europeana API Team' . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL .
			'For more information about the Europeana API visit Europeana Labs - http://labs.europeana.eu/api',
		'subject' => 'Europeana Data Exporter: Batch Job %s completed',
		'from' => array(
			'email' => 'noreply@europeana.eu',
			'label' => 'Europeana Data Exporter'
		)
	);