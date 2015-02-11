<?php

// Generic wrapper class for database access using PDO layer, with some useful methods added
//
// Author: George Borisov <george@gir.me.uk>

namespace Borisov\DB;
use PDO;

class Base extends PDO
{
	public function __construct ($target, $user, $pass, $opt = []) {
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
			self::printException($e);
		}	
	}

	// Sugar :-)

	public function get ($s, $mode = PDO::FETCH_ASSOC) {
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

	public function getRow ($s) {
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

	public function getColumn ($s) {
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

	public function getOne ($s) {
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
	
	public function getTuple ($s) {
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

	public function transaction ($a) {
		try {
			$this->beginTransaction();
			
			foreach ($a as $sql) {
				$s = $this->prepare($sql);
				$s->execute();
			}
			
			$this->commit();

		} catch (Exception $e) {
			$this->rollBack();
			throw $e;
		}
	}

	public function insert ($table, $a, $suffix = '') {
		$a = array_filter($a, function ($v) { return ($v || is_numeric($v)); });
		$data = self::prepare_data($a);

		$sql = "INSERT INTO $table (" . implode(",", array_keys($a)) . ") VALUES (" . implode (',', array_keys($data)) . ") $suffix"; 

		$s = $this->prepare($sql);
		$s->execute($data);
		$s->closeCursor();

		return $this->lastInsertId();
	}

	public function update ($table, $data, $conditions) {
		$p_data = self::prepare_data($data);
		$p_where = self::prepare_data($conditions);

		$cols = [];
		foreach ($data as $k => $v) {
			$cols[] = ($v === NULL) ? "$k=NULL" : "$k=:$k";
		}

		$where = [];
		foreach ($conditions as $k => $v) {
			$where[] = "$k=:$k";
		}

		$sql = "UPDATE $table SET " . implode(',', $cols) . ' WHERE ' . implode(' AND ', $where); 
		
		$s = $this->prepare($sql);
		$s->execute(array_merge($p_data, $p_where));
		$s->closeCursor();
	}

	// Internal functions

	protected static function error ($message, $code = 500) {
		http_response_code($code);
		
		echo "<p>$code: $message</p>";
		
		echo "<pre>";
		debug_print_backtrace();
		echo "</pre>";

		exit;
	}

	protected static function printException ($e) {
		http_response_code(500);

		echo ("<p>500: {$e->getMessage()}</p>");
		echo ("<pre>{$e->getTraceAsString()}</pre>");

		exit;
	}

	private static function prepare_data ($data) {
		$a = [];

		foreach ($data as $k => $v) {
			if (preg_match('/[^\w]/', $k)) {
				self::error('Invalid characters in column name', 400);
			}

			if (!$v && !is_numeric($v)) {
				$v = NULL;
			}

			$a[":$k"] = $v;
		}

		return $a;
	}
}
