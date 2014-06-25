<?php 

// This class extends the standard "mysqli" class with some useful methods
//
// Author: George Borisov <george@gir.me.uk>

class DB extends mysqli
{	
	public $sql_errors;

	public function __construct($type = 'ro', $db_name = false) {
		$a_config = Config::get('mysql');
		if (!$db_name) {
			$db_name = $a_config['db'];
		}

		if (isset ($a_config['auth']) && isset ($a_config['auth'][$type])) {
			parent::__construct($a_config['server'], $a_config['auth'][$type]['user'], $a_config['auth'][$type]['pass'], $db_name);
			if (mysqli_connect_errno()) {
				printf("Connect failed: %s\n", mysqli_connect_error());
				exit;
			}
			$this->set_charset('utf8');
		} else {
			exit ("500: Bad configuration\n");
		}
	}

	function q($sql) {
		$result = $this->doQuery($sql);
	}

	function insert($sql) {
		$this->doQuery($sql);
		return $this->get_one('SELECT LAST_INSERT_ID()');
	}
	
	function get($sql) {
		$result = $this->doQuery($sql);
		$a_result = [];
		while($row = $result->fetch_assoc()) {
			$a_result[] = $row;
		}
		if(!empty($a_result)) {
			return $a_result;
		}
		return FALSE;
	}
	
	function get_indexed($sql) {
		$result = $this->doQuery($sql);
		$a_tmp = [];
		while ($row = $result->fetch_assoc()) {
			$a_tmp[] = $row;
		}
		if (!empty($a_tmp)) {
			$a_result = [];
			foreach ($a_tmp as &$r) {
				$a_keys = array_keys($r);
				$index = $r[$a_keys[0]];
				unset ($r[$a_keys[0]]);
				$a_result[$index] = $r;
			}
			return $a_result;
		}
		return FALSE;
	}

	function get_tuple($sql) {
		$result = $this->doQuery($sql);
		$a_tmp = [];
		while($row = $result->fetch_assoc()) {
			$a_tmp[] = $row;
		}
		if(!empty($a_tmp)) {
			$a_result = [];
			foreach ($a_tmp as &$r) {
				$a_keys = array_keys($r);
				$a_result[ $r[ $a_keys[0] ] ] = $r[ $a_keys[1] ];
			}
			return $a_result;
		}
		return FALSE;
	}

	function get_row($sql) {
		$result = $this->doQuery($sql);
		$a_result = [];
		if($result) {
			while($a_row = $result->fetch_assoc()) {
				$a_result = $a_row;
				break;
			}
			if(!empty($a_result)) {
				return $a_result;
			}
		}
		return FALSE;
	}
	
	function get_column($sql) {
		$result = $this->doQuery($sql);
		$a_result = [];
		while($a_row = $result->fetch_row()) {
			$a_result[] = $a_row[0];
		}
		if(!empty($a_result)) {
			return $a_result;
		}
		return FALSE;
	}
	
	function get_one($sql) {
		$o_result = $this->doQuery($sql);
		$result = FALSE;
		while($a_row = $o_result->fetch_row()) {
			$result = $a_row[0];
			break;
		}
		return $result;
	}

	function build_insert($table, $a_data) {
		/*
		 * array a_data
		 * | column name -> value
		 */
		foreach($a_data as $key => $value) {
			if((!$value && !is_numeric($value)) || ($value === 'NULL')) {
				$a_data[$key] = 'NULL';
			} else {
				$a_data[$key] = "'" . $this->real_escape_string($value) . "'";
			}
		}
		return "INSERT INTO $table (`". implode("`,`", array_keys($a_data)) ."`) VALUES (". implode(",", $a_data) .")";
	}
	
	function build_update($table, $a_data, $where) {
		/*
		 * array a_data
		* | column name -> value
		*/
		$a_string = [];
		foreach($a_data as $key => $value) {
			if((!$value && !is_numeric($value)) || ($value === 'NULL')) {
				$a_string[$key] = "`$key`=NULL";
			} else {
				$a_string[] = "`$key`='" . $this->real_escape_string($value) . "'";
			}
		}
		
		return "UPDATE $table SET ". implode(", ", $a_string) ." WHERE $where";
	}
	
	function transaction($a_sql) {
		if(!empty($a_sql)) {
			$a_thisSql = [];
			$a_thisSql[] = 'BEGIN';
			foreach($a_sql as &$sql ) {
				$a_thisSql[] = $sql;
			}
			$a_thisSql[] = 'COMMIT';
			foreach($a_thisSql as &$sql) {
				$result = $this->query($sql);
				if(!$result) {
					$sql_record = implode("\n",$a_thisSql);
					$sql_record .= "\n\nFailed Query:\n\n{$sql}";
					$this->errorHandler($sql_record);
					$this->query('ROLLBACK');
					break;
				}
				if($this->warning_count) {
					$a_warnings = $this->get('SHOW WARNINGS');
					$this->warningHandler($sql, $a_warnings);
				}
			}
			if($this->sql_errors) {
	 			print_r($this->format_error());
	 			exit;
	 		}
		}
	}
	
	private function doQuery($sql) {
		$result = $this->query($sql);
		if(!$result) {
			$this->errorHandler($sql);
		}
		if($this->warning_count) {
			$a_warnings = $this->get('SHOW WARNINGS');
			$this->warningHandler($sql, $a_warnings);
		}
 		if($this->sql_errors) {
 			print_r($this->format_error());
 			exit;
 		}
		return $result;
	}
	
	private function errorHandler($sql) {
		if (Config::get('debug')) {
			$this->sql_errors .= "### SQL ###\n";
			$this->sql_errors .= "{$sql}\n\n";
		}
		$this->sql_errors .= "### MYSQL ERROR ###\n";
		$this->sql_errors .= $this->error."\n\n";
		http_response_code(500);
	}
	
	private function warningHandler($sql, $a_warnings) {
		if (Config::get('debug')) {
			$this->sql_errors .= "### SQL ###\n";
			$this->sql_errors .= "{$sql}\n\n";
		}
		$this->sql_errors .= "### MYSQL WARNINGS ###\n\n";
		foreach ($a_warnings as &$a_row) {
			$this->sql_errors .= "{$a_row['Message']} ({$a_row['Code']})\n";
		}
	}
	
	function format_error() {
		$html = '';
		foreach(explode("\n\n", $this->sql_errors) as $par) {
			if(trim($par)) {	
				$html .= "<p>";
				foreach(explode("\n", trim($par)) as $line) {
					$html .= $line."<br />";
				}
				$html .= "</p>";
			}
		}
		return $html;
	}
}
