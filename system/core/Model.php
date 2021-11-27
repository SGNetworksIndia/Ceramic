<?php
defined('CORE_PATH') or exit('No direct script access allowed');
// system/core/Model.php
// Base Model Class

/**
 * @property Driver db
 */
class Model {
	private static mixed $instance = NULL;
	private static array $loadedModels = array();

	protected function __construct() {

	}

	/**
	 * @param string|null $fqcn
	 *
	 * @return mixed
	 */
	static public function getInstance(?string $fqcn = null): mixed {
		$class = get_called_class();
		self::$instance = (!empty($fqcn)) ? self::loadClass($fqcn) : self::loadClass($class);
		return self::$instance;
	}

	private static function loadClass(string $fqcn) {
		if(!array_key_exists('loadedModels', $GLOBALS))
			$GLOBALS['loadedModels'] = array();

		$loadedModels = $GLOBALS['loadedModels'];
		$instance = (array_key_exists($fqcn, $loadedModels)) ? $loadedModels[$fqcn] : new $fqcn();
		$GLOBALS['loadedModels'][$fqcn] = $instance;

		return $instance;
	}

	/**
	 * __get magic
	 *
	 * Allows models to access CI's loaded classes using the same
	 * syntax as controllers.
	 *
	 * @param string $key
	 */
	public function __get($key) {
		// Debugging note:
		//	If you're here because you're getting an error message
		//	saying 'Undefined Property: system/core/Model.php', it's
		//	most likely a typo in your model code.
		return getCMControllerInstance()->$key;
	}

	/**
	 * Database Loader
	 *
	 * @param mixed $params Database configuration options
	 * @param bool $return Whether to return the database object
	 * @param bool $query_builder Whether to enable Query Builder
	 *                    (overrides the configuration setting)
	 *
	 * @return object|bool Database object if $return is set to TRUE,
	 *                    FALSE on failure, CI_Loader instance in any other case
	 */
	protected function load_database($params = '', $return = false, $query_builder = null): object|bool {
		// Grab the super object
		$CM =& getCMControllerInstance();

		// Do we even need to load the database class?
		if($return === false && $query_builder === null && isset($CM->db) && is_object($CM->db) && !empty($CM->db->conn_id)) {
			return false;
		}

		require_once(FRAMEWORK_PATH . 'database/DB.php');

		if($return === true) {
			return DB($params, $query_builder);
		}

		// Initialize the db variable. Needed to prevent
		// reference errors with some configurations
		$CM->db = '';

		// Load the DB class
		$CM->db =& DB($params, $query_builder);
		$this->db = $CM->db;
		return $this;
	}

}

?>