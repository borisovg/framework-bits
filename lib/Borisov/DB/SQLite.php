<?php

// SQLite driver
//
// Author: George Borisov <george@gir.me.uk>

namespace Borisov\DB;
use Borisov\Config;

class SQLite extends Base
{
	public function __construct ($type = 'ro', $opt = [], $file = false) {
		if (!$file) {
			if (!$file = Config::get('sqlite_file')) {
				parent::error('Missing configuration');	
			}
		}

        //FIXME: R/O mode is not supported by PDO :-(

		parent::__construct('sqlite:' . ROOT_PATH . $file, false, false, $opt);
	}
}
