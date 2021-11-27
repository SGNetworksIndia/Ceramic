<?php
/**
 * @since    Version 1.0.1
 * @author    Sagnik Ganguly
 * @copyright    Copyright (c) 2020, Sagnik Ganguly
 * @copyright    Copyright (c) 202, SGNetworks (https://sgn.heliohost.org/)
 * @package    Ceramic
 * @filesource
 */

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\NoReturn;
use JetBrains\PhpStorm\Pure;

defined('CORE_PATH') or defined('INDEX_PAGE') or exit('No direct script access allowed');

/**
 * Common Functions
 * Loads the base classes and executes the request.
 *
 * @package        CodeIgniter
 * @subpackage    CodeIgniter
 * @category    Common Functions
 * @author        EllisLab Dev Team
 * @link        https://codeigniter.com/user_guide/
 */
// ------------------------------------------------------------------------

/**
 * Reference to the CI_Controller method.
 *
 * Returns current CI instance object
 *
 * @return \Controller
 */
#[Pure] function &getCMControllerInstance(): Controller {
	return Controller::get_instance();
}

#[Pure] function &getCeramicInstance(): Ceramic {
	return Ceramic::get_instance();
}

spl_autoload_register(function($className) {
	$className = str_replace("\\", DS, $className);
	if(str_contains($className, 'Ceramic')) {
		if(!class_exists($className)) {
			$className = str_replace('Ceramic', '', $className);
			$file = FRAMEWORK_PATH . "libraries" . DS . "$className.php";
			if(file_exists($file))
				require_once $file;
		}
	}
});

if(!function_exists('is_php')) {
	/**
	 * Determines if the current version of PHP is equal to or greater than the supplied value
	 *
	 * @param string
	 *
	 * @return bool TRUE if the current version is $version or higher
	 */
	function is_php(string $version): bool {
		static $_is_php;
		if(!isset($_is_php[$version])) {
			$_is_php[$version] = version_compare(PHP_VERSION, $version, '>=');
		}
		return $_is_php[$version];
	}
}

function rsearch(string $folder, string $pattern, bool $multiple = false): string|array {
	$dir = new RecursiveDirectoryIterator($folder);
	$ite = new RecursiveIteratorIterator($dir);
	$fileList = "";
	if($multiple) {
		$files = new RegexIterator($ite, $pattern, RegexIterator::GET_MATCH);
		$fileList = array();
		foreach($files as $file) {
			$fileList = array_merge($fileList, $file);
		}
	} else {
		foreach($ite as $file) {
			if(str_contains($file, $pattern)) {
				$fileList = $file;
			}
		}
	}
	return $fileList;
}

function fileExists(string $fileName): bool {
	static $dirList = [];
	if(file_exists($fileName)) {
		return true;
	}
	$directoryName = dirname($fileName);
	if(!isset($dirList[$directoryName])) {
		$fileArray = glob($directoryName . '/*', GLOB_NOSORT);
		$dirListEntry = [];
		foreach($fileArray as $file) {
			$dirListEntry[strtolower($file)] = true;
		}
		$dirList[$directoryName] = $dirListEntry;
	}
	return isset($dirList[$directoryName][strtolower($fileName)]);
}

// recursive directory scan
function recursiveScan(string $dir, string $pattern = '/*'): array {
	$tree = glob(rtrim($dir, '/') . $pattern);
	$files = array();
	if(is_array($tree)) {
		foreach($tree as $file) {
			if(is_dir($file)) {
				$files[] = $file;
				recursiveScan($file);
			} elseif(is_file($file)) {
				$files[] = $file;
			}
		}
	}
	return $files;
}

/**
 * find files matching a pattern
 * using PHP "glob" function and recursion
 *
 * @param string $dir - directory to start with
 * @param string $filename
 *
 * @return string|null containing all pattern-matched files
 */
function find_file(string $dir, string $filename): ?string {
	$files = recursiveScan($dir);
	$file = null;
	foreach($files as $f) {
		if(config_item('case_insensitive')) {
			$f = strtolower($f);
			$filename = strtolower($filename);
		}
		$filename = basename($filename);
		$path = $f;
		if(is_dir($path)) {
			$file = find_file($path, $filename);
		} elseif(is_file($path) && fileExists($path)) {
			$f = basename($f);
			if($f == $filename) {
				$file = $path;
			}
		}
	}
	return $file;
}

#[ArrayShape(['controller' => "string[]", 'view' => "array|string|string[]"])]
function find_mvc($url): array {
	$c = null;
	$mvc = array('controller' => array('name' => '', 'file' => '', 'class' => '', 'page' => ''), 'view' => '');

	if(!empty($url) && $url != '/') {
		$v = basename($url);
		$urlParts = explode('/', $url);
		$count = count($urlParts);
		$paths = array();
		$paths[] = CONTROLLER_PATH . str_replace('/', DIRECTORY_SEPARATOR, $url) . ".php";
		for($i = 0; $i < ($count - 1); $i++) {
			$s = "";
			for($j = 0; $j <= $i; $j++) {
				$s .= "dirname(";
				if($j == $i)
					$s .= "\$url";
			}
			$s .= str_repeat(")", $i + 1);
			$s .= ";";
			$s = "return $s;";
			$s = eval("$s");
			$s = str_replace('/', DIRECTORY_SEPARATOR, $s);
			$paths[] = realpath(CONTROLLER_PATH . $s . ".php");
		}
		foreach($paths as $path) {
			if(fileExists($path)) {
				$c = $path;
				break;
			}
		}
	} else {
		$c = CONTROLLER_PATH . config_item('default_controller') . ".php";
		$v = "__default";
	}

	if(is_file($c) && file_exists($c)) {
		$c = str_replace('/', DIRECTORY_SEPARATOR, $c);
		$pathinfo = pathinfo($c);
		$name = $pathinfo['filename'];
		$fqcp = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $name;
		$page = str_replace('\\', '/', trim(str_replace(CONTROLLER_PATH, '', $fqcp), DIRECTORY_SEPARATOR));
		$fqcn = str_replace('/', '\\', $page);
		$v = str_replace(basename($fqcn), '', $v);
		$mvc['controller']['file'] = $c;
		$mvc['controller']['name'] = $name;
		$mvc['controller']['class'] = $fqcn;
		$mvc['controller']['page'] = dirname($url);
	} else {
		$mvc['controller']['class'] = str_replace('/', '\\', $url);
		$mvc['controller']['page'] = str_replace('\\', '/', $url);
	}
	$mvc['view'] = $v;
	return $mvc;
}

// ------------------------------------------------------------------------
if(!function_exists('is_really_writable')) {
	/**
	 * Tests for file writability
	 * is_writable() returns TRUE on Windows servers when you really can't write to
	 * the file, based on the read-only attribute. is_writable() is also unreliable
	 * on Unix servers if safe_mode is on.
	 *
	 * @link    https://bugs.php.net/bug.php?id=54709
	 *
	 * @param string
	 *
	 * @return    bool
	 */
	function is_really_writable($file): bool {
		// If we're on a Unix server with safe_mode off we call is_writable
		if(DIRECTORY_SEPARATOR === '/' && (is_php('5.4') or !ini_get('safe_mode'))) {
			return is_writable($file);
		}
		/* For Windows servers and safe_mode "on" installations we'll actually
		 * write a file then read it. Bah...
		 */
		if(is_dir($file)) {
			$file = rtrim($file, '/') . '/' . md5(mt_rand());
			if(($fp = @fopen($file, 'ab')) === false) {
				return false;
			}
			fclose($fp);
			@chmod($file, 0777);
			@unlink($file);
			return true;
		} elseif(!is_file($file) or ($fp = @fopen($file, 'ab')) === false) {
			return false;
		}
		fclose($fp);
		return true;
	}
}
// ------------------------------------------------------------------------
if(!function_exists('load_class')) {
	/**
	 * Class registry
	 * This function acts as a singleton. If the requested class does not
	 * exist it is instantiated and set to a static variable. If it has
	 * previously been instantiated the variable is returned.
	 *
	 * @param string    the class name being requested
	 * @param string    the directory where the class should be found
	 * @param mixed    an optional argument to pass to the class constructor
	 *
	 * @return    object
	 */
	function &load_class(string $class, string $directory = 'libraries', string $param = null): object {
		static $_classes = array();
		// Does the class exist? If so, we're done...
		if(isset($_classes[$class])) {
			return $_classes[$class];
		}
		$name = false;
		// Look for the class first in the local application/libraries folder
		// then in the native system/libraries
		foreach(array(APP_PATH, FRAMEWORK_PATH) as $path) {
			if(file_exists($path . $directory . '/' . $class . '.php')) {
				$name = $class;
				if(class_exists($name, false) === false) {
					require_once("$path$directory/$class.php");
				}
				break;
			}
		}
		// Is the request a class extension? If so we load it too
		if(file_exists(APP_PATH . $directory . '/' . config_item('subclass_prefix') . $class . '.php')) {
			$name = config_item('subclass_prefix') . $class;
			if(class_exists($name, false) === false) {
				require_once(APP_PATH . "$directory/$name.php");
			}
		}
		// Did we find the class?
		if($name === false) {
			// Note: We use exit() rather than show_error() in order to avoid a
			// self-referencing loop with the Exceptions class
			set_status_header(503);
			echo 'Unable to locate the specified class: ' . $class . '.php';
			exit(5); // EXIT_UNK_CLASS
		}
		// Keep track of what we just loaded
		is_loaded($class);
		$_classes[$class] = isset($param) ? new $name($param) : new $name();
		return $_classes[$class];
	}
}
// --------------------------------------------------------------------
if(!function_exists('is_loaded')) {
	/**
	 * Keeps track of which libraries have been loaded. This function is
	 * called by the load_class() function above
	 *
	 * @param string
	 *
	 * @return    array
	 */
	function &is_loaded(string $class = ''): array {
		static $_is_loaded = array();
		if($class !== '') {
			$_is_loaded[strtolower($class)] = $class;
		}
		return $_is_loaded;
	}
}
// ------------------------------------------------------------------------
if(!function_exists('get_config')) {
	/**
	 * Loads the main config.php file
	 * This function lets us grab the config file even if the Config class
	 * hasn't been instantiated yet
	 *
	 * @param array
	 *
	 * @return    array
	 */
	function get_config(array $replace = array()): array {
		static $config;
		if(empty($config)) {
			$file_path = APP_PATH . 'config/config.php';
			$found = false;
			if(file_exists($file_path)) {
				$found = true;
				require($file_path);
			}
			// Is the config file in the environment folder?
			if(file_exists($file_path = APP_PATH . 'config/' . ENVIRONMENT_NAME[ENVIRONMENT] . '/config.php')) {
				require($file_path);
			} elseif(!$found) {
				set_status_header(503);
				echo "The configuration file ($file_path) does not exist.";
				exit(3); // EXIT_CONFIG
			}
			// Does the $config array exist in the file?
			if(!isset($config) or !is_array($config)) {
				set_status_header(503);
				echo "Your config file ($file_path) does not appear to be formatted correctly.";
				exit(3); // EXIT_CONFIG
			}
		}
		// Are any values being dynamically added or replaced?
		foreach($replace as $key => $val) {
			$config[$key] = $val;
		}
		return $config;
	}
}
// ------------------------------------------------------------------------
if(!function_exists('config_item')) {
	/**
	 * Returns the specified config item
	 *
	 * @param string
	 *
	 * @return    mixed
	 */
	function config_item(string $item): mixed {
		static $_config;
		if(empty($_config)) {
			// references cannot be directly assigned to static variables, so we use an array
			//$_config[0] =& get_config();
			$_config[0] = get_config();
		}
		return $_config[0][$item] ?? null;
	}
}

function timer(bool $reset = false): float {
	static $start;
	if(is_null($start)) {
		$start = microtime(true);
	} else {
		$diff = round((microtime(true) - $start), 4);
		$start = ($reset) ? null : $start;
		return $diff;
	}
	return microtime(true);
}

// ------------------------------------------------------------------------
if(!function_exists('get_mimes')) {
	/**
	 * Returns the MIME types array from config/mimes.php
	 *
	 * @return    array
	 */
	function &get_mimes(): array {
		static $_mimes;
		if(empty($_mimes)) {
			$_mimes = file_exists(APP_PATH . 'config/mimes.php') ? include(APP_PATH . 'config/mimes.php') : array();
			if(file_exists(APP_PATH . 'config/' . ENVIRONMENT_NAME[ENVIRONMENT] . '/mimes.php')) {
				$_mimes = array_merge($_mimes, include(APP_PATH . 'config/' . ENVIRONMENT_NAME[ENVIRONMENT] . '/mimes.php'));
			}
		}
		return $_mimes;
	}
}
// ------------------------------------------------------------------------
if(!function_exists('is_https')) {
	/**
	 * Is HTTPS?
	 * Determines if the application is accessed via an encrypted
	 * (HTTPS) connection.
	 *
	 * @return    bool
	 */
	function is_https(): bool {
		if(!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
			return true;
		} elseif(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
			return true;
		} elseif(!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
			return true;
		}
		return false;
	}
}
// ------------------------------------------------------------------------
if(!function_exists('is_cli')) {
	/**
	 * Is CLI?
	 * Test to see if a request was made from the command line.
	 *
	 * @return    bool
	 */
	function is_cli(): bool {
		return (PHP_SAPI === 'cli' or defined('STDIN'));
	}
}
// ------------------------------------------------------------------------
if(!function_exists('show_error')) {
	/**
	 * Error Handler
	 * This function lets us invoke the exception class and
	 * display errors using the standard error template located
	 * in application/views/errors/error_general.php
	 * This function will send the error page directly to the
	 * browser and exit.
	 *
	 * @param string
	 * @param int
	 * @param string
	 *
	 * @return    void
	 */
	#[NoReturn] function show_error(string $message, int $status_code = 500, string $heading = 'An Error Was Encountered') {
		$status_code = abs($status_code);
		if($status_code < 100) {
			$exit_status = $status_code + 9; // 9 is EXIT__AUTO_MIN
			$status_code = 500;
		} else {
			$exit_status = 1; // EXIT_ERROR
		}
		/** @var \Exceptions $_error */
		$_error =& load_class('Exceptions', 'core');
		echo $_error->show_error($heading, $message, 'error_general', $status_code);
		exit($exit_status);
	}
}
// ------------------------------------------------------------------------
if(!function_exists('show_403')) {
	/**
	 * 403 Page Handler
	 * This function is similar to the show_error() function above
	 * However, instead of the standard error template it displays
	 * 403 errors.
	 *
	 * @param string
	 * @param bool
	 *
	 * @return    void
	 */
	#[NoReturn] function show_403(string $page = '', bool $log_error = true) {
		/** @var \Exceptions $_error */
		$_error =& load_class('Exceptions', 'core');
		$_error->show_403($page, $log_error);
		exit(4); // EXIT_UNKNOWN_FILE
	}
}
// ------------------------------------------------------------------------
if(!function_exists('show_404')) {
	/**
	 * 404 Page Handler
	 * This function is similar to the show_error() function above
	 * However, instead of the standard error template it displays
	 * 404 errors.
	 *
	 * @param string
	 * @param bool
	 *
	 * @return    void
	 */
	#[NoReturn] function show_404(string $page = '', bool $log_error = true) {
		/** @var \Exceptions $_error */
		$_error =& load_class('Exceptions', 'core');
		$_error->show_404($page, $log_error);
		exit(4); // EXIT_UNKNOWN_FILE
	}
}
// ------------------------------------------------------------------------
if(!function_exists('show_500')) {
	/**
	 * 500 Page Handler
	 * This function is similar to the show_error() function above
	 * However, instead of the standard error template it displays
	 * 500 errors.
	 *
	 * @param string
	 * @param bool
	 *
	 * @return    void
	 */
	#[NoReturn] function show_500(string $page = '', bool $log_error = true) {
		/** @var \Exceptions $_error */
		$_error =& load_class('Exceptions', 'core');
		$_error->show_500($page, $log_error);
		exit(4); // EXIT_UNKNOWN_FILE
	}
}
// ------------------------------------------------------------------------
if(!function_exists('log_message')) {
	/**
	 * Error Logging Interface
	 * We use this as a simple mechanism to access the logging
	 * class and send messages to be logged.
	 *
	 * @param string    the error level: 'error', 'debug' or 'info'
	 * @param string    the error message
	 *
	 * @return    void
	 */
	function log_message(string $level, string $message) {
		/** @var \Log $_log */
		static $_log;
		if($_log === null) {
			// references cannot be directly assigned to static variables, so we use an array
			$_log[0] =& load_class('Log', 'core');
		}
		$_log[0]->write_log($level, $message);
	}
}
// ------------------------------------------------------------------------
if(!function_exists('set_status_header')) {
	/**
	 * Set HTTP Status Header
	 *
	 * @param int    the status code
	 * @param string
	 *
	 * @return    void
	 */
	function set_status_header(int $code = 200, string $text = '') {
		if(is_cli())
			return;

		if(empty($code) or !is_numeric($code))
			show_error('Status codes must be numeric');

		if(empty($text)) {
			$status = [
				100 => 'Continue',
				101 => 'Switching Protocols',
				200 => 'OK',
				201 => 'Created',
				202 => 'Accepted',
				203 => 'Non-Authoritative Information',
				204 => 'No Content',
				205 => 'Reset Content',
				206 => 'Partial Content',
				300 => 'Multiple Choices',
				301 => 'Moved Permanently',
				302 => 'Found',
				303 => 'See Other',
				304 => 'Not Modified',
				305 => 'Use Proxy',
				307 => 'Temporary Redirect',
				400 => 'Bad Request',
				401 => 'Unauthorized',
				402 => 'Payment Required',
				403 => 'Forbidden',
				404 => 'Not Found',
				405 => 'Method Not Allowed',
				406 => 'Not Acceptable',
				407 => 'Proxy Authentication Required',
				408 => 'Request Timeout',
				409 => 'Conflict',
				410 => 'Gone',
				411 => 'Length Required',
				412 => 'Precondition Failed',
				413 => 'Request Entity Too Large',
				414 => 'Request-URI Too Long',
				415 => 'Unsupported Media Type',
				416 => 'Requested Range Not Satisfiable',
				417 => 'Expectation Failed',
				422 => 'Unprocessable Entity',
				426 => 'Upgrade Required',
				428 => 'Precondition Required',
				429 => 'Too Many Requests',
				431 => 'Request Header Fields Too Large',
				500 => 'Internal Server Error',
				501 => 'Not Implemented',
				502 => 'Bad Gateway',
				503 => 'Service Unavailable',
				504 => 'Gateway Timeout',
				505 => 'HTTP Version Not Supported',
				511 => 'Network Authentication Required',
			];
			if(isset($status[$code]))
				$text = $status[$code];
			else
				show_error('No status text available. Please check your status code number or supply your own message text.');
		}
		if(str_starts_with(PHP_SAPI, 'cgi')) {
			header("Status: $code $text");
			return;
		}

		$server_protocol = (isset($_SERVER['SERVER_PROTOCOL']) && in_array($_SERVER['SERVER_PROTOCOL'], array('HTTP/1.0', 'HTTP/1.1', 'HTTP/2'), true)) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
		header($server_protocol . ' ' . $code . ' ' . $text, true, $code);
	}
}
// --------------------------------------------------------------------
if(!function_exists('_error_handler')) {
	/**
	 * Error Handler
	 * This is the custom error handler that is declared at the (relative)
	 * top of CodeIgniter.php. The main reason we use this is to permit
	 * PHP errors to be logged in our own log files since the user may
	 * not have access to server logs. Since this function effectively
	 * intercepts PHP errors, however, we also need to display errors
	 * based on the current error_reporting level.
	 * We do that with the use of a PHP error template.
	 *
	 * @param int $severity
	 * @param string $message
	 * @param string $filepath
	 * @param int $line
	 *
	 * @return bool
	 */
	function _error_handler(int $severity, string $message, string $filepath, int $line): bool {
		$is_error = (((E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $severity) === $severity);
		/* When an error occurred, set the status header to '500 Internal Server Error'
		 to indicate to the client something went wrong.
		 This can't be done within the $_error->show_php_error method because
		 it is only called when the display_errors flag is set (which isn't usually
		 the case in a production environment) or when errors are ignored because
		 they are above the error_reporting threshold.*/
		if($is_error) {
			set_status_header(500);
		}

		/* Should we ignore the error? We'll get the current error_reporting
		 level and add its bits with the severity bits to find out.*/
		if(($severity & error_reporting()) !== $severity)
			return false;

		/** @var \Exceptions $_error */
		$_error =& load_class('Exceptions', 'core');
		$_error->log_exception($severity, $message, $filepath, $line);

		if(str_ireplace(array('off', 'none', 'no', 'false', 'null'), '', ini_get('display_errors'))) {
			$_error->show_php_error($severity, $message, $filepath, $line);
		}

		/* If the error is fatal, the execution of the script should be stopped because
		 errors can't be recovered from. Halting the script conforms with PHP's
		 default error handling. See http://www.php.net/manual/en/errorfunc.constants.php*/
		if($is_error)
			exit(1); // EXIT_ERROR

		return true;
	}
}
// ------------------------------------------------------------------------
if(!function_exists('_exception_handler')) {
	/**
	 * Exception Handler
	 * Sends uncaught exceptions to the logger and displays them
	 * only if display_errors is On so that they don't show up in
	 * production environments.
	 *
	 * @param Exception $exception
	 *
	 * @return    void
	 */
	#[NoReturn] function _exception_handler(object $exception) {
		/** @var \Exceptions $_error */
		$_error =& load_class('Exceptions', 'core');
		$_error->log_exception('error', 'Exception: ' . $exception->getMessage(), $exception->getFile(), $exception->getLine());
		is_cli() or set_status_header(500);

		if(str_ireplace(array('off', 'none', 'no', 'false', 'null'), '', ini_get('display_errors'))) {
			$_error->show_exception($exception);
		}
		exit(1); // EXIT_ERROR
	}
}
// ------------------------------------------------------------------------
if(!function_exists('_shutdown_handler')) {
	/**
	 * Shutdown Handler
	 * This is the shutdown handler that is declared at the top
	 * of CodeIgniter.php. The main reason we use this is to simulate
	 * a complete custom exception handler.
	 * E_STRICT is purposively neglected because such events may have
	 * been caught. Duplication or none? None is preferred for now.
	 *
	 * @link    http://insomanic.me.uk/post/229851073/php-trick-catching-fatal-errors-e-error-with-a
	 * @return    void
	 */
	function _shutdown_handler() {
		$last_error = error_get_last();
		if(isset($last_error) && ($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING))) {
			_error_handler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
		}
	}
}
// --------------------------------------------------------------------
if(!function_exists('remove_invisible_characters')) {
	/**
	 * Remove Invisible Characters
	 * This prevents sandwiching null characters
	 * between ascii characters, like Java\0script.
	 *
	 * @param string
	 * @param bool
	 *
	 * @return    string
	 */
	function remove_invisible_characters(string $str, bool $url_encoded = true): string {
		$non_displayable = array();
		// every control character except newline (dec 10),
		// carriage return (dec 13) and horizontal tab (dec 09)
		if($url_encoded) {
			$non_displayable[] = '/%0[0-8bcef]/i';    // url encoded 00-08, 11, 12, 14, 15
			$non_displayable[] = '/%1[0-9a-f]/i';    // url encoded 16-31
			$non_displayable[] = '/%7f/i';    // url encoded 127
		}
		$non_displayable[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';    // 00-08, 11, 12, 14-31, 127
		do {
			$str = preg_replace($non_displayable, '', $str, -1, $count);
		} while($count);

		return $str;
	}
}
// ------------------------------------------------------------------------
if(!function_exists('html_escape')) {
	/**
	 * Returns HTML escaped variable.
	 *
	 * @param string|string[] $var The input string or array of strings to be escaped.
	 * @param bool $double_encode $double_encode set to FALSE prevents escaping twice.
	 *
	 * @return string|string[] The escaped string or array of strings as a result.
	 */
	function html_escape(string|array $var, bool $double_encode = true): array|string {
		if(empty($var)) {
			return $var;
		}
		if(is_array($var)) {
			foreach(array_keys($var) as $key) {
				$var[$key] = html_escape($var[$key], $double_encode);
			}
			return $var;
		}
		return htmlspecialchars($var, ENT_QUOTES, config_item('charset'), $double_encode);
	}
}
// ------------------------------------------------------------------------
if(!function_exists('_stringify_attributes')) {
	/**
	 * Stringify attributes for use in HTML tags.
	 * Helper function used to convert a string, array, or object
	 * of attributes to a string.
	 *
	 * @param string|array|object $attributes
	 * @param bool
	 *
	 * @return string|null
	 */
	function _stringify_attributes(string|array|object $attributes, bool $js = false): ?string {
		$attrs = null;
		if(empty($attributes))
			return null;

		if(is_string($attributes))
			return ' ' . $attributes;

		$attributes = (array)$attributes;
		foreach($attributes as $key => $val) {
			$attrs .= ($js) ? $key . '=' . $val . ',' : ' ' . $key . '="' . $val . '"';
		}

		return rtrim($attrs, ',');
	}
}
// ------------------------------------------------------------------------
if(!function_exists('buildURL')) {
	function buildURL(string $uri): string {
		if(str_contains($uri, '?')) {
			$param = parse_url($uri, PHP_URL_QUERY);
			$uri = explode('?', $uri)[0];
		} else {
			$param = func_get_args()[1];
		}
		if(empty($param)) {
			$url = $uri;
		} else {
			$param = (str_contains($param, '?')) ? str_replace('?', '&', $param) : $param;
			$url = (str_contains($uri, '?')) ? $uri . '&' . $param : $uri . '?' . $param;
		}
		return $url;
	}
}
// ------------------------------------------------------------------------
if(!function_exists('redirect')) {
	function redirect(string $uri, string $vars = null, int $delay = 0) {
		$query = $_SERVER['QUERY_STRING'];
		$qm = (str_contains($uri, '?')) ? '&' : '?';

		if(!empty($vars))
			$q = (!$query) ? "$qm$vars" : "$qm$vars&$query";
		else
			$q = (!$query) ? "" : "$qm$vars&$query";

		$url = (!empty($uri)) ? $uri . $q : $_SERVER['HTTP_REFERER'] . $q;
		$url = buildURL($url);
		if(!headers_sent()) {
			if($delay > 0)
				header("Refresh:$delay; url=$url", true, 301);
			else
				header("Location: $url");
			exit;
		} else {
			echo "<script>";
			echo "window.location.href=('$url');";
			echo "</script>";
			echo "You will be redirected shortly. If you are not redirected automatically, please <a href='$url'>click here</a> to redirect";
		}
	}
}
// ------------------------------------------------------------------------
if(!function_exists('function_usable')) {
	/**
	 * Function usable
	 * Executes a function_exists() check, and if the Suhosin PHP
	 * extension is loaded - checks whether the function that is
	 * checked might be disabled in there as well.
	 * This is useful as function_exists() will return FALSE for
	 * functions disabled via the *disable_functions* php.ini
	 * setting, but not for *suhosin.executor.func.blacklist* and
	 * *suhosin.executor.disable_eval*. These settings will just
	 * terminate script execution if a disabled function is executed.
	 * The above described behavior turned out to be a bug in Suhosin,
	 * but even though a fix was committed for 0.9.34 on 2012-02-12,
	 * that version is yet to be released. This function will therefore
	 * be just temporary, but would probably be kept for a few years.
	 *
	 * @link    http://www.hardened-php.net/suhosin/
	 *
	 * @param string $function_name Function to check for
	 *
	 * @return    bool    TRUE if the function exists and is safe to call,
	 *            FALSE otherwise.
	 */
	function function_usable(string $function_name): bool {
		static $_suhosin_func_blacklist;
		if(function_exists($function_name)) {
			if(!isset($_suhosin_func_blacklist)) {
				$_suhosin_func_blacklist = extension_loaded('suhosin') ? explode(',', trim(ini_get('suhosin.executor.func.blacklist'))) : array();
			}
			return !in_array($function_name, $_suhosin_func_blacklist, true);
		}
		return false;
	}
}
// ------------------------------------------------------------------------
if(!function_exists('print_r_pre')) {
	function print_r_pre(mixed $var) {
		echo '<pre>';
		print_r($var);
		echo '</pre>';
	}
}

if(!function_exists('getAutoIncludeFiles')) {
	function getAutoIncludeFiles(string $path, $prev = array()) {
		$files = (empty($prev)) ? array() : $prev;
		if(is_array($cm_incs = config_item('includes'))) {
			foreach($cm_incs as $cm_inc) {
				if(is_dir($cm_inc)) {
					$items = glob($cm_inc . "*", GLOB_MARK);
					foreach($items as $item) {
						$pathInfo = pathinfo($item);
						$isPhp = ((array_key_exists('extension', $pathInfo)) ? $pathInfo["extension"] : '') === "php";

						if(is_file($item) && $isPhp) {
							$files[] = $item;

						} elseif(is_dir($item)) {
							getAutoIncludeFiles($item, $files);
						}
					}
				} else {
					$files[] = $cm_inc;
				}
			}
		}
	}
}
if(!function_exists('autoloadDir')) {
	function autoloadDir($dir) {
		$items = glob($dir . "*", GLOB_MARK);
		foreach($items as $item) {
			$pathinfo = pathinfo($item);
			$isPhp = ((array_key_exists('extension', $pathinfo)) ? $pathinfo["extension"] : '') === "php";

			if(is_file($item) && $isPhp) {
				require_once $item;
			} elseif(is_dir($item)) {
				autoloadDir($item);
			}
		}
	}
}
if(!function_exists('autoload')) {
	function autoload() {
		if(is_array($cm_incs = config_item('includes'))) {
			foreach($cm_incs as $k => $cm_inc) {
				if(is_file($cm_inc) && file_exists($cm_inc))
					require_once($cm_inc);
				elseif(is_dir($cm_inc) && file_exists($cm_inc)) {
					autoloadDir($cm_inc);
				}
			}
		}
	}
}
if(!function_exists('addvars')) {
	function addvars() {
		if(is_array($cm_vars = config_item('globals'))) {
			foreach($cm_vars as $cm_var) {
				if(is_file($cm_var) && file_exists($cm_var)) {
					require_once($cm_var);
					unset($cm_vars, $cm_var);
					foreach(get_defined_vars() as $var => $val) {
						$GLOBALS[$var] = $val;
					}
				}
				/*elseif(is_dir($cm_inc) && file_exists($cm_inc)) {
					autoloadDir($cm_inc);
				}*/
			}
		}
	}
}
if(!function_exists('var_require')) {
	function var_require(string $file): bool|string {
		ob_start();
		require_once($file);
		return ob_get_clean();
	}
}
function get_globals(): array {
	$ark = array_keys($GLOBALS);
	$arr = array();
	foreach($ark as $value) {
		if($value != '_GET' and $value != '_POST' and $value != '_REQUEST' and $value != '_SESSION' and $value != '_COOKIE' and $value != '_FILES' and $value != '_SERVER' and $value != 'GLOBALS')
			array_push($arr, $value);
	}

	$arr2 = array();
	foreach($arr as $value) {
		$arr2[$value] = (array_key_exists('GLOBALS', $GLOBALS)) ? $GLOBALS['GLOBALS'][$value] : $GLOBALS[$value];
	}
	return $arr2;
}

//autoload();
addvars();
