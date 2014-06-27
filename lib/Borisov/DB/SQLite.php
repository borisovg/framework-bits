<?php

// SQLite driver
//
// Author: George Borisov <george@gir.me.uk>

namespace Borisov\DB;
use Borisov\Config;

final class SQLite extends Base
{
	public function __construct ($file = '', $opt = []) {
		if (!$file) {
			if (!$file = Config::get('sqlite_file')) {
				$this->error('Missing configuration');	
			}
		}
		parent::__construct('sqlite:' . ROOT_PATH . $file, false, false, $opt);
	}
}
