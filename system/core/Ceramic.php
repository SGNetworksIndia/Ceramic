<?php
defined('CM_VERSION') OR exit('No direct script access allowed');
// system/core/Ceramic.class.php
class Ceramic {
	private static Ceramic $instance;
	public string $contentType = 'text/html';
	public array $headers = array();
	public bool $urlArgumentAsView = false;
	public string $urlArgumentName = "";

	/**
	 * Ceramic constructor.
	 */
	public function __construct() {
		self::$instance =& $this;
	}

	public static function run() {
		self::init();
		self::autoload();
		(new Ceramic)->dispatch();
	}

	// Initialization
	private static function init() {
		// Define platform, controller, action, for example:
		// index.php?e=admin&c=Goods&v=add&a=view
		// index.php/admin/Goods/add/view
		// index.php/environment/controller/view/argument
		/*
		 * ------------------------------------------------------
		 *  Load the global functions
		 * ------------------------------------------------------
		 */
		// Load configuration file
		$GLOBALS['config'] = include_once CONFIG_PATH . "config.php";
		require_once(CORE_PATH . 'Common.php');
		timer();
		require_once CORE_PATH . "Lang.php";
		require_once CORE_PATH . "Template.php";
		require_once CORE_PATH . "Controller.php";
		require_once CORE_PATH . "Loader.php";
		require_once DB_PATH . "DB.php";
		require_once CORE_PATH . "Model.php";

		$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$baseURL = config_item('base_url');
		$indexPage = config_item("index_page");
		$urlPath = trim($baseURL.$url, '/');
		$mvc = find_mvc($urlPath);

		if(!empty($baseURL)){
			if(!empty($indexPage)){
				$cv = substr($url, strpos($url, $baseURL));
				$cv = ($baseURL == '/') ? $cv : substr($cv, strpos($cv, "$baseURL/") + strlen($baseURL));
			}
		} else {
			$cv = substr($url, strpos($url, "/index.php") + strlen("/index.php"));
		}
		$cv = explode('/',$cv);
		if(!empty($cv)) {
			$a = "";
			if(!empty($cv[4])) {
				$e = $cv[1];
				$a = $cv[4];
				define("PLATFORM", $e);
				define("CURR_CONTROLLER_PATH", CONTROLLER_PATH . PLATFORM . DS);
				define("CURR_VIEW_PATH", VIEW_PATH . PLATFORM . DS);
			} elseif(!empty($cv[3])) {
				if(in_array($cv[1], ENVIRONMENT_NAME)){
					$e = $cv[1];
					define("PLATFORM", $e);
					define("CURR_CONTROLLER_PATH", CONTROLLER_PATH . PLATFORM . DS);
					define("CURR_VIEW_PATH", VIEW_PATH . PLATFORM . DS);
				} else {
					$a = $cv[3];
					define("CURR_CONTROLLER_PATH", CONTROLLER_PATH . DS);
					define("CURR_VIEW_PATH", VIEW_PATH . DS);
				}
			} elseif(!empty($cv[2])) {
				define("CURR_CONTROLLER_PATH", CONTROLLER_PATH . DS);
				define("CURR_VIEW_PATH", VIEW_PATH . DS);
			} else {
				define("CURR_CONTROLLER_PATH", CONTROLLER_PATH . DS);
				define("CURR_VIEW_PATH", VIEW_PATH . DS);
			}
			$c = $mvc['controller']['class'];
			$f = $mvc['controller']['file'];
			$p = $mvc['controller']['page'];
			$v = $mvc['view'];
			define("CONTROLLER", $c);
			define("CONTROLLER_PAGE", $p);
			define("CONTROLLER_FILE", $f);
			define("VIEW", $v);
			define("ARGUMENT", $a);
		} else {
			define("PLATFORM", $_REQUEST['e'] ?? '');
			define("CONTROLLER", $_REQUEST['c'] ?? '');
			define("VIEW", $_REQUEST['v'] ?? '');
			define("ARGUMENT", $_REQUEST['a'] ?? '');
			define("CURR_CONTROLLER_PATH", CONTROLLER_PATH . PLATFORM . DS);
			define("CURR_VIEW_PATH", VIEW_PATH . PLATFORM . DS);
		}

		// Start session
		session_start();
	}

	private static function autoload() {
		spl_autoload_register(array(__CLASS__, 'load'));
	}

	private function dispatch() {
		// Instantiate the controller class and call its action method

		if(!headers_sent()) {
			if(!empty($this->headers)) {
				foreach($this->headers as $headers) {
					$header = $headers['header'];
					$replace = $headers['replace'];
					$response_code = $headers['code'];
					header($header, $replace, $response_code);
				}
			}
		}

		if(!empty(CONTROLLER)) {
			$file = CONTROLLER_FILE;
			$page = (!empty(VIEW))?CONTROLLER_PAGE.'/'.VIEW : CONTROLLER.'/__default';
			$page_url = (empty(VIEW) || VIEW == '__default')?CONTROLLER_PAGE.'/' : $page;

			define('CURRENT_PAGE', $page_url);

			if(file_exists($file)) {
				if(!class_exists(CONTROLLER)) {
					include_once($file);
					$controller_name = CONTROLLER;
					$controller = new $controller_name;
					if(method_exists($controller, '__common')) {
						if(is_callable(array($controller, '__common'))) {
							$controller->__common();
						}
					}
					if(!empty(VIEW)) {
						$action_name = VIEW;
						if(method_exists($controller, $action_name)) {
							if(is_callable(array($controller, $action_name))) {
								if(!empty(ARGUMENT))
									$controller->$action_name(ARGUMENT);
								else
									$controller->$action_name();
							} else {
								show_403($page);
							}
						} else {
							show_404($page);
						}
					} else {
						if(method_exists($controller, '__default')){
							if(is_callable(array($controller, '__default'))) {
								$controller->__default();
							} else {
								show_403($page);
							}
						} else {
							show_404($page);
						}
					}
				} else {
					show_500($page);
				}
			} else {
				show_404($page);
			}
		} else {
			$controller = config_item("default_controller");
			$page = (!empty(VIEW))?$controller.'/'.VIEW : $controller.'/__default';
			if(!empty($controller)) {
				$file = find_file(APP_PATH . "controllers", "$controller.php");
				if(!empty($file) && file_exists($file)) {
					if(!class_exists($controller)) {
						include_once($file);
						$controller_name = $controller;
						$controller = new $controller_name;
						if(method_exists($controller, '__common')) {
							if(is_callable(array($controller, '__common'))) {
								$controller->__common();
							}
						}
						if(!empty(VIEW)) {
							$action_name = VIEW;
							if(method_exists($controller, $action_name)) {
								if(is_callable(array($controller, $action_name))) {
									if(!empty(ARGUMENT))
										$controller->$action_name(ARGUMENT);
									else
										$controller->$action_name();
								} else {
									show_403();
								}
							} else {
								show_404();
							}
						} else {
							if(method_exists($controller, '__default')) {
								if(is_callable(array($controller, '__default'))) {
									$controller->__default();
								} else {
									show_403($page);
								}
							} else {
								show_404($page);
							}
						}
					} else {
						show_500($page);
					}
				} else {
					$page = (!empty(VIEW))?$controller.'/'.VIEW : $controller.'/__default';
					show_404($page);
				}
			}
		}
	}

	// Define a custom load method

	/**
	 * Reference to the CM_Controller method.
	 * Returns current CM instance object
	 *
	 * @return \Ceramic
	 */
	public static function &get_instance(): Ceramic {
		return self::$instance;
	}

	// Routing and dispatching

	private static function load($classname) {
		// Here simply autoload app’s controller and model classes
		if(str_ends_with($classname, "Controller")) {
			// Controller
			require_once CURR_CONTROLLER_PATH . "$classname.php";
		} elseif(str_ends_with($classname, "Model")) {
			// Model
			require_once MODEL_PATH . "$classname.php";
		}
	}
}