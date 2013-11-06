<?php

// Example site-specific configuration file

return [
	'debug' => true,
	'mysql' => [
		'auth' => [
			'ro' => [	// read-only credentials
				'user' => 'ro_user',
				'pass' => 'ro_password'
			],
			'rw' => [	// read/write credentials
				'user' => 'rw_user',
				'pass' => 'rw_password'
			]
		]
	]
];
