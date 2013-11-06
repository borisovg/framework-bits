<?php

// Example global configuration file

return [
	'auth_session_timeout' => 1800, // in seconds
	'mysql' => [
		'auth' => 'use_local_config', // defined in local config
		'db' => 'my_database',
		'server' => 'localhost',
	]
];
