<?php
return array(
	'charset' => 'utf-8',
	'content_type' => 'text/html',
	'lang' => 'en',
	'site_name' => '<your-site-name>',

	'europeana_api' => array (
		'wskey' => '<your-api-key>'
	),

	'jobs' => array(
		'max_allowed' => 10000,
		'max_attempts_to_process' => 3,
		'max_to_create_per_run' => 96, // using 96 because api has row limit of 96
		'max_to_process_per_run' => 96
	),

	'job_groups' => array(
		'max_to_process_per_run' => 10
	)
);