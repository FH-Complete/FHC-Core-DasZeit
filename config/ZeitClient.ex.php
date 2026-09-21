<?php

$config['active_connection'] = 'TESTING'; // the used configuration set of the chosen connection

// Example of a configuration set. All parameters are required!
$config['zeit_connections'] = array(
	'PRODUCTION' => array(
		'protocol' => 'https',
		'host' => 'example.com',
		'version' => 'v1',
		'client_access_token_url' => 'https://example.com',
		'client_id' => 'client_id',
		'client_secret' => 'client_secret'
	),
	'TESTING' => array(
		'protocol' => 'https',
		'host' => 'example.com',
		'version' => 'v1',
		'client_access_token_url' => 'https://example.com',
		'client_id' => 'client_id',
		'client_secret' => 'client_secret'
	)
);