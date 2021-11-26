<?php
use JetBrains\PhpStorm\NoReturn;

defined('CORE_PATH') OR exit('No direct script access allowed');
// Base Controller
class Controller {
	/**
	 * Reference to the CM singleton
	 *
	 * @var    object
	 */
	private static Controller $instance;
	public Lang $lang;
	public Loader $load;
	// Base Controller has a property called $loader, it is an instance of Loader class(introduced later)
	//protected $loader;
	private bool $forceHTTPS = false;

	public function __construct() {
		$headerFile = config_item('header');
		$footerFile = config_item('footer');
		$forceHTTPS = config_item('force_https');
		$forceHTTPS = (!empty($forceHTTPS) && $forceHTTPS);

		$this->forceHTTPS($forceHTTPS);

		$this->load = new Loader();
		$this->lang = new Lang();

		if(!empty($headerFile))
			$this->load->header = $headerFile;
		if(!empty($footerFile))
			$this->load->footer = $footerFile;



		self::$instance =& $this;
		$this->checkAuthentication();
		// Assign all the class objects that were instantiated by the
		// bootstrap file (CodeIgniter.php) to local class variables
		// so that CM can run as one big super object.
		/*foreach(is_loaded() as $var => $class) {
			$this->$var =& load_class($class);
		}
		$this->load =& load_class('Loader', 'core');
		$this->load->initialize();*/
	}

	public function forceHTTPS(bool $val = true){
		$this->forceHTTPS = $val;
		if($this->forceHTTPS === true){
			if(!$_SERVER['HTTPS']){
				redirect("https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
			}
		}
	}

	private function checkAuthentication() {
		$base_url = config_item('base_url');
		$authentication = config_item('authentication');
		$authentication = (!$authentication) ? array() : $authentication;

		$base_url = (empty($base_url)) ? '/' : $base_url;

		if(array_key_exists('controller', $authentication) && array_key_exists('session', $authentication)) {
			$controller = $authentication['controller'];
			$session = $authentication['session'];
			$referrer = (array_key_exists('referrer', $authentication) && $authentication['referrer']) ? '?ref=' . CURRENT_PAGE : '';
			$type = (array_key_exists('type', $authentication)) ? $authentication['type'] : 0; // 0=BOTH, 1=SESSION, 2=COOKIE

			$protocol = ($_SERVER['HTTPS']) ? 'https://' : 'http://';
			$url = "$protocol{$_SERVER['HTTP_HOST']}$base_url{$controller}$referrer";

			if(strtolower(CONTROLLER) != strtolower($controller)) {
				if((!isset($_SESSION[$session]) && !isset($_COOKIE[$session])) || (empty($_SESSION[$session]) && empty($_COOKIE[$session])))
					redirect($url);
			}
		}
	}
	
	/**
	 * Get the CM singleton
	 *
	 * @static
	 * @return    object
	 */
	public static function &get_instance(): Controller {
		return self::$instance;
	}

	#[NoReturn] public function redirect($url, $wait = 0) {
		if($wait > 0)
			sleep($wait);
		header("Location:$url");
		exit;
	}

	protected function __common() {}

	protected function __default() {}

	// we will look at this in the view
	/*function load_view($view, $args) {
		foreach($args as $vname => $vvalue) {
			$$vname = $vvalue;
		}
		require_once(VIEW_PATH . "{$view}.php");
	}*/
}