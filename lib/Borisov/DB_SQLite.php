<?php

// SQLite driver
//
// Author: George Borisov <george@gir.me.uk>

final class DB_SQLite extends DB_Base
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
