<?php

// SQLite driver
//
// Author: George Borisov <george@gir.me.uk>

namespace Borisov\DB;

final class SQLite extends Base
{
	public function __construct ($file = '', $opt = []) {
		if (!$file) {
			if (!$file = Config::get('sqlite_file')) {
				$this->error('Missing configuration');	
			}
		}
		parent::__construct('sqlite:' . $file, false, false, $opt);
	}
}
