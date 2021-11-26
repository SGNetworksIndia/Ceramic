<?php
abstract class DBDriver {
	protected string $hostname, $username, $password, $database, $port, $socket, $char_set;
	protected mixed $conn_id = false;
	protected string $sql;
	protected mixed $result, $data;
	protected bool $queryStatus = false;

	private bool $pconnect = false;

	/**
	 * Class constructor
	 *
	 * @param array $config
	 * @return    void
	 */
	public function __construct(array $config) {
		if(is_array($config)) {
			foreach($config as $key => $val) {
				$this->$key = $val;
			}
		}

		log_message('info', 'Database Driver Class Initialized');
	}

	public function initialize(): bool {
		if($this->conn_id) {
			return TRUE;
		}
		$this->conn_id = $this->db_connect($this->pconnect);
		return $this->db_set_client_charset($this->char_set);
	}

	/**
	 * DB connect
	 *
	 * This is just a dummy method that all drivers will override.
	 *
	 * @param bool $persistent
	 * @return mixed
	 */
	public abstract function db_connect($persistent = FALSE): mixed;

		/**
	 * Set client character set
	 *
	 * @param string
	 * @return    bool
	 */
	public abstract function db_set_client_charset($charset): bool;

	public abstract function reconnect();

	public abstract function db_select(string $database):bool;

	public abstract function query(string $sql, array $binds = array(), bool $return_object = false): DBDriver;
	public abstract function result(): array|false;
	public abstract function getColumn(string $get = '*'): string|array|null;
	public abstract function getRow(int $row = -1): array|null;

	/**
	 * @param array $callable An array of callable function with name of variable to assign the result of the function.<br>The structure is as follows: <code>array('VariableName' => array('call' => array('class' => $this, 'method' => 'functionName', 'arguments' => array('%columnName%'))))</code>
	 * @return array|false Returns an associative array of strings representing the fetched row in the result set, where each key in the array represents the name of one of the result set's columns or NULL if there are no more rows in resultset and if the query failed then <b>FALSE</b>.<br>If two or more columns of the result have the same field names, the last column will take precedence.
	 */
	public abstract function result_call(array $callable): array|false;
	public abstract function num_rows(): int;
	public abstract function exists(string $table, string $field, string $value): bool;
	public abstract function affected_rows(): int;
	public abstract function insert_id(): int;
	public abstract function error(): string;
	public abstract function error_no(): int;
	public abstract function close();

	/**
	 * Persistent database connection
	 *
	 * @return    mixed
	 */
	public function db_pconnect(): mixed {
		return $this->db_connect(TRUE);
	}
	public function is_success(): bool {
		return $this->queryStatus;
	}
}