<?php
/**
 * Ceramic
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020, SGNetworks.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package Ceramic
 * @author Sagnik Ganguly
 * @copyright    Copyright (c) 2020, SGNetworks. (https://sgn.heliohost.org/)
 * @license    http://opensource.org/licenses/MIT	MIT License
 * @link https://ceramic.sgn.heliohost.org/
 * @since    Version 1.0.1
 * @filesource
 */
use JetBrains\PhpStorm\Pure;

defined('CORE_PATH') or exit('No direct script access allowed');

/**
 * Loader Class
 * Loads framework components.
 *
 * @package Ceramic
 * @subpackage Core
 * @category Loader
 * @author Sagnik Ganguly
 * @link https://ceramic.sgn.heliohost.org/user_guide/core/loader
 */
class Loader {
	public const CONTENT_TYPE_HTML = 'text/html', CONTENT_TYPE_JSON = 'application/json';
	//<editor-fold defaultstate="collapsed" desc="Variables">
	/**
	 * @var    bool $loadOnContext If set to true, all the libraries and helpers will be accessible directly from the context. <br><b>[Default: FALSE]</b>
	 */
	public bool $loadOnContext = true;
	/**
	 * @var    bool $renderTemplate If set to false, the templates loaded or created dynamically will not be rendered. <br><b>[Default: TRUE]</b>
	 */
	public bool $renderTemplate = true;
	public ?string $header, $footer = null;
	public bool $loadCommonFilesOnView = false, $loadCommonFiles = true;
	/**
	 * List of paths to load views from
	 *
	 * @var    array
	 */
	protected array $_cm_view_paths = array(VIEW_PATH => true);
	/**
	 * List of paths to load libraries from
	 *
	 * @var    array
	 */
	protected array $_cm_library_paths = array(APP_PATH, FRAMEWORK_PATH);
	/**
	 * List of paths to load models from
	 *
	 * @var    array
	 */
	protected array $_cm_model_paths = array(APP_PATH);
	/**
	 * List of paths to load helpers from
	 *
	 * @var    array
	 */
	protected array $_cm_helper_paths = array(APP_PATH, FRAMEWORK_PATH);
	private $template, $templates = array(), $cmdata = array('ceramic_version' => '', 'renderTime' => '', 'title_prefix' => '', 'title' => '', 'project_name' => '', 'project_version' => '', 'STATIC_DIR' => '', 'STATIC_URL' => '');
	private string $contentType = 'text/html';
	private bool $urlArgumentAsView = false, $pushScriptsToBottom = false;
	private string $urlArgumentName;
	//</editor-fold>

	/**
	 * Loader constructor.
	 */
	public function __construct() {
		// workaround: set $self because $this fails
		$self = $this;
		// register for error logging in case of timeout
		$shutdown = function() use (&$self) {
			$self->shutdown();
		};
		//register_shutdown_function($shutdown);
	}

	private function shutdown() {
		if(!empty($this->footer) && $this->loadCommonFiles) {
			$footerFile = $this->getViewFile($this->footer);
			if(is_file($footerFile) && file_exists($footerFile))
				$this->view($this->footer, $this->cmdata);
		}
	}

	private function getViewFile(string $view): string {
		if(file_exists(VIEW_PATH . "$view.php"))
			$ext = "php";
		elseif(file_exists(VIEW_PATH . "$view.htm"))
			$ext = "htm";
		elseif(file_exists(VIEW_PATH . "$view.html"))
			$ext = "html";
		elseif(file_exists(VIEW_PATH . "$view.phtm"))
			$ext = "phtm";
		elseif(file_exists(VIEW_PATH . "$view.phtml"))
			$ext = "phtml";
		else
			$ext = "";

		return VIEW_PATH . "$view.$ext";
	}

	/**
	 * Loads the specified view
	 *
	 * @param string $view The name of the view to load
	 * @param array $data [optional] An array of data to be loaded to the view
	 */
	public function view(string $view, array $data = array()) {
		static $firstTime = true;
		static $loadingController, $loadingViewMethod;
		if($firstTime) {
			$firstTime = false;
			if(!empty($this->header) && $this->loadCommonFiles) {
				$headerFile = $this->getViewFile($this->header);
				if(is_file($headerFile) && file_exists($headerFile))
					$this->view($this->header, $data);
			}
		}
		if(file_exists(VIEW_PATH . "$view.php"))
			$ext = "php";
		elseif(file_exists(VIEW_PATH . "$view.htm"))
			$ext = "htm";
		elseif(file_exists(VIEW_PATH . "$view.html"))
			$ext = "html";
		elseif(file_exists(VIEW_PATH . "$view.phtm"))
			$ext = "phtm";
		elseif(file_exists(VIEW_PATH . "$view.phtml"))
			$ext = "phtml";
		else
			$ext = "";
		$file = VIEW_PATH . "{$view}.{$ext}";
		$dbt = debug_backtrace();
		$vc = $dbt[1]['class'];
		$vm = $dbt[2]['function'];
		if($vc != $loadingController && !is_null($loadingController)) {
			$loadingController = null;
			$loadingViewMethod = null;
		}
		if(is_null($loadingController)) {
			$loadingController = $vc;
			$baseURL = config_item('base_url');
			$baseURL = (str_ends_with($baseURL, '/')) ? $baseURL : $baseURL . '/';
			$this->cmdata['ceramic_version'] = CM_VERSION;
			$this->cmdata['renderTime'] = $this->getRenderTime();
			$this->cmdata['project_name'] = config_item('project_name');
			$this->cmdata['project_version'] = config_item('project_version');
			$this->cmdata['STATIC_DIR'] = STATIC_PATH;
			$this->cmdata['STATIC_URL'] = $baseURL . 'static/';
			$this->cmdata['title_prefix'] = (array_key_exists('title_prefix', $data)) ? $data['title_prefix'] : ucwords($vc);
			$this->cmdata['title'] = (array_key_exists('title', $data)) ? $data['title'] : ucwords($vm);
			if(is_null($loadingViewMethod))
				$loadingViewMethod = $vm;
		} else {
			if($vm == $loadingViewMethod) {
				$loadingViewMethod = $vm;
				$this->cmdata['renderTime'] += $this->getRenderTime();
			}
		}
		if(empty($data) || !is_array($data)) {
			$data = $this->cmdata;
		} elseif(count($data) > 0) {
			$data = array_merge($data, $this->cmdata);
		}
		$cm_globals = get_globals();
		if(!empty($cm_globals))
			$data = array_merge($data, $cm_globals);
		if(!empty($data)) {
			extract($data);
		}
		if($this->renderTemplate) {
			$template = new Template($file);
			$template->pushScriptsToBottom = $this->pushScriptsToBottom;
			$template->set("renderTime", $data['renderTime']);
			$template->set("ceramic_version", $data['ceramic_version']);
			$template->set("STATIC_URL", $data['STATIC_URL']);
			$template->set("title", $data['title']);
			$template->set("title_prefix", $data['title_prefix']);
			$template->set("project_name", $data['project_name']);
			$template->set("project_version", $data['project_version']);
			foreach($data as $k=>$v) {
				if(is_string($v) || is_int($v))
					$template->set($k, $v);
			}
			if(!empty($this->templates)) {
				foreach($this->templates as $k => $tpl) {
					if($tpl instanceof Template) {
						$template->setFormat($tpl->getFormat(), $k);
						$template->setData($tpl->getData(), $k);
					}
				}
			} else {
				if($this->template instanceof Template) {
					$template->setFormat($this->template->getFormat());
					$template->setData($this->template->getData());
				}
			}
			echo $template->render($data);
		} else {
			include_once($file);
		}
	}

	/**
	 * Get the number of seconds it took to render the page
	 *
	 *
	 * @return float The number of seconds it took to render the page
	 */
	private function getRenderTime(): float {
		return timer();
	}

	public function __destruct() {
		$this->shutdown();
	}

	/**
	 * @param bool $loadOnContext
	 *
	 * @return Loader
	 */
	public function setLoadOnContext(bool $loadOnContext): Loader {
		$this->loadOnContext = $loadOnContext;
		return $this;
	}

	/**
	 * @param bool $renderTemplate
	 * @return Loader
	 */
	public function setRenderTemplate(bool $renderTemplate): Loader {
		$this->renderTemplate = $renderTemplate;
		return $this;
	}

	/**
	 * @param string $contentType
	 * @return Loader
	 */
	public function setContentType(string $contentType): Loader {
		$this->contentType = $contentType;
		$CM =& getCeramicInstance();
		$CM->contentType = $this->contentType;
		$this->header("Content-type: $contentType");
		return $this;
	}

	/**
	 * @param string $header
	 * @param bool $replace
	 * @param int|null $response_code
	 *
	 * @return Loader
	 */
	public function header(string $header, bool $replace = true, int $response_code = null): Loader {
		$CM =& getCeramicInstance();
		$CM->headers[] = array('header' => $header, 'replace' => $replace, 'code' => $response_code);
		return $this;
	}

	/**
	 * @param bool $urlArgumentAsView
	 * @return Loader
	 */
	public function setUrlArgumentAsView(bool $urlArgumentAsView): Loader {
		$this->urlArgumentAsView = $urlArgumentAsView;
		$CM =& getCeramicInstance();
		$CM->urlArgumentAsView = $this->urlArgumentAsView;
		return $this;
	}

	/**
	 * @param string $urlArgumentName
	 * @return Loader
	 */
	public function setUrlArgumentName(string $urlArgumentName): Loader {
		$this->urlArgumentName = $urlArgumentName;
		$CM =& getCeramicInstance();
		$CM->urlArgumentName = $this->urlArgumentName;
		return $this;
	}

	/**
	 * @param bool $pushScriptsToBottom
	 * @return Loader
	 */
	public function setPushScriptsToBottom(bool $pushScriptsToBottom = true): Loader {
		$this->pushScriptsToBottom = $pushScriptsToBottom;
		return $this;
	}

	/**
	 * @param string|null $header
	 * @return Loader
	 */
	public function setHeader(?string $header): Loader {
		$this->header = $header;
		return $this;
	}

	/**
	 * @param string|null $footer
	 * @return Loader
	 */
	public function setFooter(?string $footer): Loader {
		$this->footer = $footer;
		return $this;
	}

	/**
	 * @param bool $loadCommonFilesOnView
	 * @return Loader
	 */
	public function setLoadCommonFilesOnView(bool $loadCommonFilesOnView): Loader {
		$this->loadCommonFilesOnView = $loadCommonFilesOnView;
		return $this;
	}

	/**
	 * @param bool $loadCommonFiles
	 * @return Loader
	 */
	public function setLoadCommonFiles(bool $loadCommonFiles): Loader {
		$this->loadCommonFiles = $loadCommonFiles;
		return $this;
	}

	/**
	 * Get an instance of Template to render
	 *
	 * @return \Template An instance of Template class
	 */
	#[Pure] public function getTemplate(): Template {
		return new Template();
	}

	/**
	 * Set an object of Template to the layout
	 *
	 * @param \Template $template The object of template instance to set to the layout
	 */
	public function setTemplate(Template $template) {
		$this->template = $template;
	}

	/**
	 * Add an object of Template to the layout
	 *
	 * @param \Template $template The object of template instance to add to the layout
	 */
	public function addTemplate(Template $template) {
		$this->templates[] = $template;
	}

	/**
	 * Loads the specified library
	 *
	 * @param string $lib The name of the library to load
	 */
	public function library(string $lib) {
		include LIB_PATH . "$lib.php";
		$className = strtolower(basename($lib));
		$class = ucfirst($className);
		if(class_exists($class)) {
			if($this->loadOnContext)
				getCMControllerInstance()->$className = new $class(); else
				$this->$className = new $class();
		}
	}

	/**
	 * Loads the specified helper
	 *
	 * @param string $helper The name of the helper to load
	 */
	public function helper(string $helper) {
		include HELPER_PATH . "{$helper}_helper.php";
	}

	/**
	 * Loads the specified model
	 *
	 * @param string $model The name of the library to load
	 */
	public function model(string $model) {
		$path = realpath(MODEL_PATH . "$model.php");
		$path = (!file_exists($path)) ? realpath(MODEL_PATH . "{$model}_model.php") : $path;
		if(file_exists($path)) {
			include_once $path;
			$pathInfo = pathinfo($path);
			$path = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'];

			$class = rtrim(str_replace(MODEL_PATH, '', $path), '/\\');
			$className = str_replace(array('_model', 'model_'), '', strtolower(basename($class)));
			if(class_exists($class)) {
				if($this->loadOnContext) {
					getCMControllerInstance()->$className = Model::getInstance($class);
				} else
					$this->$className = Model::getInstance($class);
			} else {
				show_error("The class related to the model <b>$model</b> not found!");
			}
		} else {
			show_error("The file related to the model <b>$model</b> not found!");
		}
	}

	/**
	 * Database Loader
	 *
	 * @param mixed $params Database configuration options
	 * @param bool $return Whether to return the database object
	 * @param bool|null $query_builder Whether to enable Query Builder
	 *                    (overrides the configuration setting)
	 *
	 * @return object|bool Database object if $return is set to TRUE,
	 *                    FALSE on failure, CI_Loader instance in any other case
	 */
	public function database(string $params = '', bool $return = false, bool $query_builder = null): object|bool {
		// Grab the super object
		$CMController =& getCMControllerInstance();

		// Do we even need to load the database class?
		if($return === false && $query_builder === null && isset($CMController->db) && is_object($CMController->db) && !empty($CMController->db->conn_id)) {
			return false;
		}

		require_once(FRAMEWORK_PATH . 'database/DB.php');

		if($return === TRUE) {
			return DB($params, $query_builder);
		}

		// Initialize the db variable. Needed to prevent
		// reference errors with some configurations
		$CMController->db = '';

		// Load the DB class
		$CMController->db =& DB($params, $query_builder);
		return $this;
	}

	/**
	 * Get Package Paths
	 *
	 * @param bool $include_base Whether to include BASEPATH (default: FALSE)
	 *
	 * @return    array A list of all package paths.
	 */
	public function get_package_paths(bool $include_base = false): array {
		return ($include_base === true) ? $this->_cm_library_paths : $this->_cm_model_paths;
	}
}