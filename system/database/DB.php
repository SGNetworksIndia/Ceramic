<?php
defined('CORE_PATH') or exit('No direct script access allowed');
$db = array();
if(file_exists(APP_PATH . "config/database.php"))
	include_once APP_PATH . "config/database.php";

if(isset($db['default'])) {
	$db_host = $db['default']['hostname'];
	$db_user = $db['default']['username'];
	$db_password = $db['default']['password'];
	$db_name = $db['default']['database'];
	$db_driver = $db['default']['dbdriver'];
	$db_debug = $db['default']['dbdebug'];
	$db_charset = $db['default']['char_set'];
	$db_collation = $db['default']['dbcollat'];
}

/**
 * Initialize the database
 *
 * @param string|string[] $params
 * @param bool $query_builder_override Determines if query builder should be used or not
 * @link    https://codeigniter.com/user_guide/database/
 *
 * @category    Database
 * @author    EllisLab Dev Team
 */
function &DB($params = '', $query_builder_override = NULL) {
	$query_builder = true;
	// Load the DB config file if a DSN string wasn't passed
	if(is_string($params) && !str_contains($params, '://')) {
		// Is the config file in the environment folder?
		if(!file_exists($file_path = APP_PATH . 'config/' . ENVIRONMENT . '/database.php')
		   && !file_exists($file_path = APP_PATH . 'config/database.php')) {
			show_error('The configuration file database.php does not exist.');
		}

		include($file_path);

		// Make packages contain database config files,
		// given that the controller instance already exists
		if(class_exists('CI_Controller', FALSE)) {
			foreach(get_instance()->load->get_package_paths() as $path) {
				if($path !== APP_PATH) {
					if(file_exists($file_path = $path . 'config/' . ENVIRONMENT . '/database.php')) {
						include($file_path);
					} elseif(file_exists($file_path = $path . 'config/database.php')) {
						include($file_path);
					}
				}
			}
		}

		if(!isset($db) or count($db) === 0) {
			show_error('No database connection settings were found in the database config file.');
		}

		if($params !== '') {
			$active_group = $params;
		}

		if(!isset($active_group)) {
			show_error('You have not specified a database connection group via $active_group in your config/database.php file.');
		} elseif(!isset($db[$active_group])) {
			show_error('You have specified an invalid database connection group (' . $active_group . ') in your config/database.php file.');
		}

		$params = $db[$active_group];
	} elseif(is_string($params)) {
		/**
		 * Parse the URL from the DSN string
		 * Database settings can be passed as discreet
		 * parameters or as a data source name in the first
		 * parameter. DSNs must have this prototype:
		 * $dsn = 'driver://username:password@hostname/database';
		 */
		if(($dsn = @parse_url($params)) === FALSE) {
			show_error('Invalid DB Connection String');
		}

		$params = array(
			'dbdriver' => $dsn['scheme'],
			'hostname' => isset($dsn['host']) ? rawurldecode($dsn['host']) : '',
			'port' => isset($dsn['port']) ? rawurldecode($dsn['port']) : '',
			'username' => isset($dsn['user']) ? rawurldecode($dsn['user']) : '',
			'password' => isset($dsn['pass']) ? rawurldecode($dsn['pass']) : '',
			'database' => isset($dsn['path']) ? rawurldecode(substr($dsn['path'], 1)) : ''
		);

		// Were additional config items set?
		if(isset($dsn['query'])) {
			parse_str($dsn['query'], $extra);

			foreach($extra as $key => $val) {
				if(is_string($val) && in_array(strtoupper($val), array('TRUE', 'FALSE', 'NULL'))) {
					$val = var_export($val, TRUE);
				}

				$params[$key] = $val;
			}
		}
	}

	// No DB specified yet? Beat them senseless...
	if(empty($params['dbdriver'])) {
		show_error('You have not selected a database type to connect to.');
	}

	// Load the DB classes. Note: Since the query builder class is optional
	// we need to dynamically create a class that extends proper parent class
	// based on whether we're using the query builder class or not.
	if($query_builder_override !== NULL) {
		$query_builder = $query_builder_override;
	}
	// Backwards compatibility work-around for keeping the
	// $active_record config variable working. Should be
	// removed in v3.1
	elseif(!isset($query_builder) && isset($active_record)) {
		$query_builder = $active_record;
	}

	require_once(FRAMEWORK_PATH . 'database/DBDriver.php');

	/*if(!isset($query_builder) or $query_builder === TRUE) {
		require_once(FRAMEWORK_PATH . 'database/QueryBuilder.php');
		if(!class_exists('CI_DB', FALSE)) {
			class DB extends DBQueryBuilder {
				public function db_connect($persistent = FALSE): object {
					// TODO: Implement db_connect() method.
				}

				public function reconnect() {
					// TODO: Implement reconnect() method.
				}

				public function db_select(string $database) {
					// TODO: Implement db_select() method.
				}
			}
		}
	} elseif(!class_exists('DB', FALSE)) {
		class DB extends DBDriver {
			public function db_connect($persistent = FALSE): object {
				// TODO: Implement db_connect() method.
			}

			public function reconnect() {
				// TODO: Implement reconnect() method.
			}

			public function db_select(string $database) {
				// TODO: Implement db_select() method.
			}
		}
	}*/

	// Load the DB driver
	$driver_file = FRAMEWORK_PATH . 'database/drivers/' . $params['dbdriver'] . '/Driver.php';
	$query_builder_file = FRAMEWORK_PATH . 'database/QueryBuilder.php';

	file_exists($driver_file) or show_error("Invalid DB driver: {$driver_file}");
	require_once($driver_file);

	// Instantiate the DB adapter
	//$driver = '' . $params['dbdriver'];
	$DB = new Driver($params);

	/*// Check for a subdriver
	if(!empty($DB->subdriver)) {
		$driver_file = FRAMEWORK_PATH . 'database/drivers/' . $DB->dbdriver . '/subdrivers/' . $DB->dbdriver . '_' . $DB->subdriver . '_driver.php';

		if(file_exists($driver_file)) {
			require_once($driver_file);
			$driver = 'DB_' . $DB->dbdriver . '_' . $DB->subdriver . '';
			$DB = new $driver($params);
		}
	}*/

	if($query_builder) {
		require_once($query_builder_file);
		$QB = new QueryBuilder();
		$DB->queryBuilder = $QB;
	}
	$DB->initialize();
	return $DB;
}

?>