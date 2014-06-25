<?php

// Generic wrapper class for database access using PDO layer, with some useful methods added
//
// Author: George Borisov <george@gir.me.uk>

class DB_Base extends PDO
{
	public function __construct($target, $user, $pass, $opt = []) {
		if (!isset ($opt[PDO::ATTR_ERRMODE])) {
			//if (Config::get('debug')) {
			$opt[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;	// throw exceptions for all errors
			//}
		}
		if (!isset ($opt[PDO::ATTR_DEFAULT_FETCH_MODE])) {
			$opt[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_ASSOC;	// fetch as assoc array by default
		}
		try {
			parent::__construct($target, $user, $pass, $opt);
		} catch (PDOException $e) {
			$this->error($e->getMessage());
		}	
	}

	// Sugar :-)

	public function get($s, $mode = PDO::FETCH_ASSOC) {
		$r = false;
		// accept a PDOStatement object or an SQL query string
		if (!$s instanceof PDOStatement) {
			$s = $this->prepare($s);
		}
		if ($s->execute()) {
			$r = $s->fetchAll($mode);
		}
		$s->closeCursor();
		return $r;
	}

	public function getRow($s) {
		$r = false;
		// accept a PDOStatement object or an SQL query string
		if (!$s instanceof PDOStatement) {
			$s = $this->prepare($s);
		}
		if ($s->execute()) {
			$r = $s->fetch(PDO::FETCH_ASSOC);
		}
		$s->closeCursor();
		return $r;
	}

	public function getColumn($s) {
		$r = false;
		// accept a PDOStatement object or an SQL query string
		if (!$s instanceof PDOStatement) {
			$s = $this->prepare($s);
		}
		if ($s->execute()) {
			$r = [];
			while ($row = $s->fetch(PDO::FETCH_NUM)) {
				$r[] = $row[0];
			}	
		}
		$s->closeCursor();
		return $r;
	}

	public function getOne($s) {
		$r = false;
		// accept a PDOStatement object or an SQL query string
		if (!$s instanceof PDOStatement) {
			$s = $this->prepare($s);
		}
		if ($s->execute()) {
			if ($row = $s->fetch(PDO::FETCH_NUM)) {
				$r = $row[0];
			}	
		}
		$s->closeCursor();
		return $r;
	}
	
	function getTuple($s) {
		$r = false;
		// accept a PDOStatement object or an SQL query string
		if (!$s instanceof PDOStatement) {
			$s = $this->prepare($s);
		}
		if ($s->execute()) {
			$r = [];
			while ($row = $s->fetch(PDO::FETCH_NUM)) {
				$r[$row[0]] = $row[1];
			}
		}
		$s->closeCursor();
		return $r;
	}

	// Internal functions

	protected function error($message, $code = 500) {
		http_response_code(500);
		exit ("$code: $message\n");
	}
}
