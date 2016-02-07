<?php


defined ('in_bn6') or exit;

class MySQL {
	
	private static $instance;

	public static $query_count = 0;

	private $conn, $dbc, $last_res;
	
	public function __construct($dbc) {
		
		$this->dbc = $dbc;

		self::$instance = $this;

		$this->connect();

	}

	public function __destruct() {
		$this->close();
	}

	private function connect() {
		if (!($this->conn = @mysql_connect($this->dbc['host'], $this->dbc['user'], $this->dbc['pass']))) {
			$this->error('Error connecting: '.mysql_error());
		}

		@mysql_select_db($this->dbc['db'], $this->conn) or
			$this->error('Error selecting: '.mysql_error($this->conn));
	}

	public function close() {
		@mysql_close($this->conn);
	}

	private function error($msg) {
		exit($msg);
	}

	public static function singleton() {
		return self::$instance;
	}

	public function escape($string) {
		return mysql_real_escape_string($string, $this->conn);
	}

	public function query($sql) {
		self::$query_count++;
		if (!($this->last_res = mysql_query($sql, $this->conn)))
			$this->error('Query error: '.mysql_error($this->conn));
		else
			return $this->last_res;
	}

	public function free($res = false) {
		@mysql_free_result($res ? $res : $this->last_res);
	}

	public function fetch_row($res) {
		return mysql_fetch_row($res);
	}

	public function fetch_assoc($res) {
		return mysql_fetch_assoc($res);
	}
	
	public function num($res) {
		return mysql_num_rows($res);
	}
	
	public function lastid() {
		return mysql_insert_id($this->conn);
	}
}
