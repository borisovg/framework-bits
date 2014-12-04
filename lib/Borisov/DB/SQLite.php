<?php

// SQLite driver
//
// Author: George Borisov <george@gir.me.uk>

namespace Borisov\DB;
use Borisov\Config;

class SQLite extends Base
{
	public function __construct ($file = '', $opt = []) {
		if (!$file) {
			if (!$file = Config::get('sqlite_file')) {
				$this->error('Missing configuration');	
			}
		}
		try {
			parent::__construct('sqlite:' . ROOT_PATH . $file, false, false, $opt);
		} catch (PDOException $e) {
			$this->error($e->getMessage());
		}	
	}
}
