<?php
return array(
	'charset' => 'utf-8',
	'content_type' => 'text/html',
	'lang' => 'en',
	'site_name' => 'Europeana Data Exporter',

	'europeana_api' => array (
		'wskey' => 'api2demo'
	),

	'jobs' => array(
		'job_max' => 10000,
		'process_jobs_limit' => 100,
		'process_completed_jobs_limit' => 10
	)
);