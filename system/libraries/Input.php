<?php
defined('CORE_PATH') OR exit('No direct script access allowed');

class Input {
	private $get, $post, $session, $cookie;

	public function __construct() {
		$this->get = $_GET;
		$this->post = $_POST;
		$this->session = $_SESSION;
		$this->cookie = $_COOKIE;
	}

	public function get(string $param = '', string $default = '') {
		if(is_array($this->get) && !empty($this->get)){
			if(!empty($param)) {
				$return = (array_key_exists($param, $this->get)) ? $this->get[$param] : $default;
			} else {
				$return = $this->get;
			}
			return $return;
		} else
			return false;
	}

	public function post(string $param = '', string $default = '') {
		if(is_array($this->post) && !empty($this->post)){
			if(!empty($param)) {
				$return = (array_key_exists($param, $this->post)) ? $this->post[$param] : $default;
			} else {
				$return = $this->post;
			}
			return $return;
		} else
			return false;
	}

	public function session(string $param = '', string $default = '') {
		if(is_array($this->session) && !empty($this->session)){
			if(!empty($param)) {
				$return = (array_key_exists($param, $this->session)) ? $this->session[$param] : $default;
			} else {
				$return = $this->session;
			}
			return $return;
		} else
			return false;
	}

	public function cookie(string $param = '', string $default = '') {
		if(is_array($this->cookie) && !empty($this->cookie)){
			if(!empty($param)) {
				$return = (array_key_exists($param, $this->cookie)) ? $this->cookie[$param] : $default;
			} else {
				$return = $this->cookie;
			}
			return $return;
		} else
			return false;
	}

	/**
	 * Set cookie
	 * Accepts an arbitrary number of parameters (up to 7) or an associative
	 * array in the first parameter containing all the values.
	 *
	 * @param string|mixed[] $name Cookie name or an array containing parameters
	 * @param string $value Cookie value
	 * @param int $expire Cookie expiration time in seconds
	 * @param string $domain Cookie domain (e.g.: '.yourdomain.com')
	 * @param string $path Cookie path (default: '/')
	 * @param string $prefix Cookie name prefix
	 * @param bool $secure Whether to only transfer cookies via SSL
	 * @param bool $httponly Whether to only makes the cookie accessible via HTTP (no javascript)
	 *
	 * @return    void
	 */
	public function set_cookie($name, $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = null, $httponly = null) {
		if(is_array($name)) {
			// always leave 'name' in last place, as the loop will break otherwise, due to $$item
			foreach(array('value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly', 'name') as $item) {
				if(isset($name[$item])) {
					$$item = $name[$item];
				}
			}
		}
		if($prefix === '' && config_item('cookie_prefix') !== '') {
			$prefix = config_item('cookie_prefix');
		}
		if($domain == '' && config_item('cookie_domain') != '') {
			$domain = config_item('cookie_domain');
		}
		if($path === '/' && config_item('cookie_path') !== '/') {
			$path = config_item('cookie_path');
		}
		$secure = ($secure === null && config_item('cookie_secure') !== null) ? (bool)config_item('cookie_secure') : (bool)$secure;
		$httponly = ($httponly === null && config_item('cookie_httponly') !== null) ? (bool)config_item('cookie_httponly') : (bool)$httponly;
		if(!is_numeric($expire)) {
			$expire = time() - 86500;
		} else {
			$expire = ($expire > 0) ? time() + $expire : 0;
		}
		setcookie($prefix . $name, $value, $expire, $path, $domain, $secure, $httponly);
	}

	/**
	 * Get Request Method
	 *
	 * Return the request method
	 *
	 * @param	bool	$upper	Whether to return in upper or lower case
	 *				(default: FALSE)
	 * @return 	string
	 */
	public function method($upper = FALSE)
	{
		return ($upper)
			? strtoupper($this->server('REQUEST_METHOD'))
			: strtolower($this->server('REQUEST_METHOD'));
	}

	/**
	 * Fetch an item from the SERVER array
	 *
	 * @param	mixed	$index		Index for item to be fetched from $_SERVER
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	mixed
	 */
	public function server($index, $xss_clean = NULL)
	{
		return $_SERVER[$index];
	}

	// ------------------------------------------------------------------------

	/**
	 * Magic __get()
	 *
	 * Allows read access to protected properties
	 *
	 * @param	string	$name
	 * @return	mixed
	 */
	/*public function __get($name)
	{
		if ($name === 'raw_input_stream')
		{
			isset($this->_raw_input_stream) OR $this->_raw_input_stream = file_get_contents('php://input');
			return $this->_raw_input_stream;
		}
		elseif ($name === 'ip_address')
		{
			return $this->ip_address;
		}
	}*/
}