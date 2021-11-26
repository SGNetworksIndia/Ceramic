<?php
defined('CORE_PATH') or exit('No direct script access allowed');

/**
 *================================================================
 *system/database/mysql/Driver.php
 *Database operation class
 *================================================================
 * @property QueryBuilder queryBuilder
 */
class Driver extends DBDriver {

	/**
	 * Constructor, to connect to database, select database and set charset
	 *
	 * @param $config array configuration array
	 */
	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function __destruct() {
		if($this->conn_id instanceof mysqli)
			mysqli_close($this->conn_id);
	}

	public function db_connect($persistent = FALSE): mysqli {
		if(!$this->conn_id instanceof mysqli)
			$this->conn_id = mysqli_connect($this->hostname, $this->username, $this->password, null, $this->port) or die('Database connection error');
		mysqli_select_db($this->conn_id, $this->database) or die('Database selection error');
		$this->setChar($this->char_set);
		return $this->conn_id;
	}

	/**
	 * Set charset
	 *
	 * @access private
	 *
	 * @param $charset string charset
	 */
	private function setChar($charest) {
		$sql = 'set names ' . $charest;
		$this->query($sql);
	}

	/**
	 * Execute SQL statement
	 *
	 * Accepts an SQL string as input and returns a result object upon
	 * successful execution of a "read" type query. Returns boolean TRUE
	 * upon successful execution of a "write" type query. Returns boolean
	 * FALSE upon failure, and if the $db_debug variable is set to TRUE
	 * will raise an error.
	 *
	 * @access public
	 *
	 * @param string $sql SQL query statement
	 * @param array $binds An array of binding data
	 * @param bool $return_object Return the object (TRUE / FALSE)
	 *
	 * @return Driver if succeed, return resources; if fail return error message and exit
	 */
	public function query(string $sql, array $binds = array(), bool $return_object = false): DBDriver {
		$this->sql = $sql;
		// Write SQL statement into log
		$str = $sql . "  [" . date("Y-m-d H:i:s") . "]" . PHP_EOL;
		file_put_contents("log.txt", $str, FILE_APPEND);
		$result = mysqli_query($this->conn_id, $this->sql);
		if(!$result) {
			die($this->error_no() . ':' . $this->error() . '<br />Error SQL statement is ' . $this->sql . '<br />');
		} else
			$this->queryStatus = true;
		$this->result = $result;
		return $this;
	}

	/**
	 * Get error number
	 *
	 * @access private
	 * @return int error number
	 */
	public function error_no(): int {
		return mysqli_errno($this->conn_id);
	}

	/**
	 * Get error message
	 *
	 * @access private
	 * @return string error message
	 */
	public function error(): string {
		return mysqli_error($this->conn_id);
	}

	public function reconnect() {
		if($this->conn_id !== FALSE && $this->conn_id->ping() === FALSE) {
			$this->conn_id = FALSE;
		}
	}

	public function db_select(string $database): bool {
		if($database === '') {
			$database = $this->database;
		}

		if($this->conn_id->select_db($database)) {
			$this->database = $database;
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Set client character set
	 *
	 * @param string $charset
	 * @return    bool
	 */
	public function db_set_client_charset($charset): bool {
		return $this->conn_id->set_charset($charset);
	}

	public function result_call(array $callable): array|false {
		$callables = array();
		if(!empty($callable)) {
			foreach($callable as $var => $item) {
				$call = $item['call'];
				$callables[] = array('var' => $var, 'call' => $call);
			}
		}
		if($this->result instanceof mysqli_result) {
			if($this->num_rows() > 0) {
				$rows = array();
				$result = $this->result;
				while($r = mysqli_fetch_assoc($result)) {
					for($i = 0; $i < count($callables); $i++) {
						$callable = $callables[$i];
						$var = $callable['var'];
						$call = $callable['call'];
						$class = $call['class'];
						$method = $call['method'];
						$args = $call['arguments'];
						$args = preg_replace_callback('/%(.*)%/', function($m) use ($r) {
							return $r[$m[1]];
						}, $args);
						if(!empty($method)) {
							if(!empty($class)) {
								if(method_exists($class, $method)) {
									$r[$var] = call_user_func_array(array($class, $method), $args);
								}
							} else {
								$r[$var] = call_user_func_array($method, $args);
							}
						}
					}
					array_push($rows, $r);
				}
				return $rows;
			}
		}
		return false;
	}

	public function num_rows(): int {
		if($this->result instanceof mysqli_result) {
			return mysqli_num_rows($this->result);
		}
		return 0;
	}

	public function exists(string $table, string $field, string $value): bool {
		$sql = "SELECT {$field} FROM {$table} WHERE {$field} = '{$value}'";
		return ($this->query($sql)->num_rows() > 0);
	}

	public function affected_rows(): int {
		if($this->result instanceof mysqli_result) {
			return mysqli_affected_rows($this->result);
		}
		return 0;
	}

	/**
	 * Get last insert id
	 */
	public function insert_id(): int {
		return mysqli_insert_id($this->conn_id);
	}

	public function close() {
		if($this->conn_id instanceof mysqli) {
			$this->conn_id->close();
		}
	}

	/**
	 * Get the value of a column
	 *
	 * @access public
	 *
	 * @param $sql string SQL query statement
	 *
	 * @return array an array of the value of this column
	 */
	public function getColumn(string $get = '*'): string|array|null {
		if($this->is_success() && $this->num_rows() > 0) {
			$row = mysqli_fetch_assoc($this->result);
			$f = "'".$get."'";
			if(!empty($row))
				return ($get == '*') ? $row : $row[$get];
		}
		return null;
	}

	public function getRow(int $row = -1): array|null {
		if($this->is_success() && $this->num_rows() > 0) {
			if($row >= 0) {
				$i = 0;
				while($rows = mysqli_fetch_assoc($this->result)) {
					if($i == $row)
						return $rows;
					$i++;
				}
			} else {
				return $this->result();
			}
		}
		return null;
	}

	public function result(): array|false {
		if($this->result instanceof mysqli_result) {
			if($this->num_rows() > 0) {
				$rows = array();
				$result = $this->result;
				while($r = mysqli_fetch_assoc($result))
					array_push($rows, $r);
				return $rows;
			}
		}
		return false;
	}

	/**
	 * @method mixed filter_binary_data(string $data) Filters a binary data for use in an SQL statement.
	 * @param mixed $data The binary data to be filtered.
	 *
	 * @return int|string|null The filtered base64 encoded binary data.
	 * @access public
	 * @static
	 * @see https://docs.libraries.sgnetworks.ga/sgnsseal/function.filter_binary_data Documentations of filter_binary_data()
	 * @since Method available since Release 1.0.1
	 */
	public function filter_binary_data(mixed $data): int|string|null {
		if(!empty($data)) {
			$data = base64_encode($data);
			return $this->filter_db_data($data);
		}
		return null;
	}

	/**
	 * @method string|null filter_db_data(string $data = null, bool $optional = false) Filters a string for use in an SQL statement.
	 * @param string|null $data The string to be filtered.
	 * @param bool $optional
	 *
	 * @return int|string|null The filtered string on success, otherwise <b>null</b>.
	 * @access public
	 * @static
	 * @see https://docs.libraries.sgnetworks.ga/sgnsseal/function.filter_db_data Documentations of filter_db_data()
	 * @since Method available since Release 1.2.1
	 */
	public function filter_db_data(string $data = null, bool $optional = false): int|string|null {
		if(!empty($data)&&!is_numeric($data)) {
			if(version_compare(PHP_VERSION, '7.0.0', '<')) {
				if(get_magic_quotes_gpc()) {
					$return = stripslashes($data);
				}
			}
			$return = htmlspecialchars($data);
			$return = db_real_escape_string($return, $this->conn_id);
			return ($optional)?"'{$return}'":$return;
		} elseif(is_numeric($data))
			return $data;
		else {
			return NULL;
		}
	}

	/**
	 * @method string|null defilter_data(string $data) Defilters a string filtered by filter_data().
	 * @param string $data The data to be defiltered.
	 * @return string|null <b>The defiltered string</b> on success, <b>null</b> otherwise.
	 * @access public
	 * @static
	 * @see https://docs.libraries.sgnetworks.ga/sgnsseal/function.defilter_data Documentations of defilter_data()
	 * @since Method available since Release 1.0.1
	 */
	function defilter_data(string $data): ?string {
		if(!empty($data)||is_numeric($data)) {
			if(version_compare(PHP_VERSION, '7.0.0', '<')) {
				if(get_magic_quotes_gpc()) {
					$return = stripslashes($data);
				}
			}
			return htmlspecialchars_decode($data);
		} else {
			return "NULL";
		}
	}


}