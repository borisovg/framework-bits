<?php

// MySQL driver
//
// Author: George Borisov <george@gir.me.uk>

namespace Borisov\DB;

class MySQL extends Base
{
	public function __construct($type = 'ro', $opt = [], $db_name = false) {
		if (!$a = Config::get('mysql')) {
			$this->error('Missing configuration');
		}

		// allow overriding of DB name
		if ($db_name) {
			$a['db'] = $db_name;
		}

		if (isset ($a['server']) && isset ($a['auth']) && isset ($a['auth'][$type])) {
			// WARNING: charset option ignored in PHP < 5.3.6
			parent::__construct("mysql:host={$a['server']};dbname={$a['db']};charset=utf8", $a['auth'][$type]['user'], $a['auth'][$type]['pass'], $opt);
		} else {
			$this->error('Bad configuration');
		}
	}
}
