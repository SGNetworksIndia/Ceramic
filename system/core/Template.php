<?php

defined('CORE_PATH') OR exit('No direct script access allowed');
/**
 * Simple template engine class (use [@tag] tags in your templates).
 *
 * @link http://www.broculos.net/ Broculos.net Programming Tutorials
 * @author Nuno Freitas <nunofreitas@gmail.com>
 * @version 1.0
 */
class Template {
	const CM_TEMPLATE_VARIABLES_FORMATTER = 'variables';
	public bool $pushScriptsToBottom = false;
	/**
	 * The filename of the template to load.
	 *
	 * @access protected
	 * @var string
	 */
	protected $file;
	/**
	 * An array of values for replacing each tag on the template (the key for each value is its corresponding tag).
	 *
	 * @access protected
	 * @var array
	 */
	protected $values = array();
	protected $formats = array();
	private array $scripts = array();
	private string $scriptRegex = "/(<script>([\S\s]*)<\/script>)/", $bodyEndRegex = "/(<\/body>)/smi";

	/**
	 * Creates a new Template object and sets its associated file.
	 *
	 * @param string $file the filename of the template to load
	 */
	public function __construct($file = null) {
		$this->file = $file;
		$this->formats = array("variables"=>array("start"=>"", "end"=>""));
		$this->scripts = (empty($_SESSION['cm-template-scripts'])) ? array() : $_SESSION['cm-template-scripts'];
	}

	/**
	 * Merges the content from an array of templates and separates it with $separator.
	 *
	 * @param array $templates an array of Template objects to merge
	 * @param string $separator the string that is used between each Template object
	 * @return string
	 */
	static public function merge($templates, $separator = "\n") {
		/**
		 * Loops through the array concatenating the outputs from each template, separating with $separator.
		 * If a type different from Template is found we provide an error message.
		 */
		$output = "";

		foreach ($templates as $template) {
			$content = (get_class($template) !== "Template")
				? "Error, incorrect type - expected Template."
				: $template->output();
			$output .= $content . $separator;
		}

		return $output;
	}

	/**
	 * Outputs the content of the template, replacing the keys for its respective values.
	 *
	 * @return string
	 */
	public function output() {
		/**
		 * Tries to verify if the file exists.
		 * If it doesn't return with an error message.
		 * Anything else loads the file contents and loops through the array replacing every key for its value.
		 */
		if (!file_exists($this->file)) {
			return "Error loading template file ($this->file).<br />";
		}
		$output = file_get_contents($this->file);

		foreach ($this->values as $key => $value) {
			$tagToReplace = "{{".$key."}}";
			$replace = ($this->format->variables)?"<b>{$value}</b>":$value;
			$output = str_replace($tagToReplace, $replace, $output);
		}

		return $output;
	}

	public function getFormatter($key){
		$obj = new Formatter($key);
		return $obj;
	}

	public function setFormatter(Formatter $formatter){
		$this->formats = $formatter->format;
	}

	public function important(string $key){
		if(!$this->formats[$key]['start'])
			$this->formats[$key]['start'] = '<strong>';
		else
			$this->formats[$key]['start'] .= '<strong>';

		if(!$this->formats[$key]['end'])
			$this->formats[$key]['end'] = '</strong>';
		else
			$this->formats[$key]['end'] = '</strong>'.$this->formats[$key]['end'];
		$this->formats[$key]['start'] = htmlentities(html_entity_decode($this->formats[$key]['start']));
		$this->formats[$key]['end'] = htmlentities(html_entity_decode($this->formats[$key]['end']));
	}

	public function emphasize(string $key){
		if(!$this->formats[$key]['start'])
			$this->formats[$key]['start'] = '<em>';
		else
			$this->formats[$key]['start'] .= '<em>';

		if(!$this->formats[$key]['end'])
			$this->formats[$key]['end'] = '</em>';
		else
			$this->formats[$key]['end'] = '</em>'.$this->formats[$key]['end'];
		$this->formats[$key]['start'] = htmlentities(html_entity_decode($this->formats[$key]['start']));
		$this->formats[$key]['end'] = htmlentities(html_entity_decode($this->formats[$key]['end']));
	}

	public function underline(string $key){
		if(!$this->formats[$key]['start'])
			$this->formats[$key]['start'] = '<u>';
		else
			$this->formats[$key]['start'] .= '<u>';

		if(!$this->formats[$key]['end'])
			$this->formats[$key]['end'] = '</u>';
		else
			$this->formats[$key]['end'] = '</u>'.$this->formats[$key]['end'];
		$this->formats[$key]['start'] = htmlentities(html_entity_decode($this->formats[$key]['start']));
		$this->formats[$key]['end'] = htmlentities(html_entity_decode($this->formats[$key]['end']));
	}

	public function format(string $key, bool $val){
		$this->formats[$key] = $val;
	}

	public function setFormat($data, $templateID = null) {
		if(is_numeric($templateID)){
			foreach($data as $key => $value) {
				$this->formats[$templateID][$key] = $value;
			}
		} else {
			foreach($data as $key => $value) {
				if(is_array($value)) {
					foreach($value as $k => $v) {
						$this->formats[$key][$k] = $v;
					}
				} else
					$this->formats[$key] = $value;
			}
		}
	}

	/**
	 * Sets a value for replacing a specific tag.
	 *
	 * @param string $key the name of the tag to replace
	 * @param string $value the value to replace
	 */
	public function set($key, $value) {
		$this->values[$key] = $value;
	}

	public function setData($data, $templateID = null) {
		if(is_numeric($templateID)){
			foreach($data as $key => $value) {
				$this->values[$templateID][$key] = $value;
			}
		} else {
			foreach($data as $key => $value) {
				if(is_array($value)) {
					foreach($value as $k => $v) {
						$this->values[$key][$k] = $v;
					}
				} else
					$this->values[$key] = $value;
			}
		}
	}

	public function getData(){
		return $this->values;
	}

	public function render($data = null): array|bool|string {
		//start output buffering (so we can return the content)
		if(!file_exists($this->file)) {
			return "Error loading template file ($this->file).<br />";
		}

		ob_start();
		if(!empty($data))
			extract($data);

		include $this->file;
		$content = ob_get_contents();
		ob_clean();

		if($this->pushScriptsToBottom) {
			if(preg_match($this->scriptRegex, $content, $matches)) {
				$this->scripts[] = $matches[0];
				$content = preg_replace($this->scriptRegex, '', $content);
				if(preg_match($this->bodyEndRegex, $content)) {
					$scripts = implode("\n", $this->scripts) . "\n";
					$content = preg_replace($this->bodyEndRegex, $scripts . '$1', $content);
					unset($_SESSION['cm-template-scripts']);
				} else {
					$_SESSION['cm-template-scripts'] = $this->scripts;
				}
			}
		}
		foreach ($this->values as $key => $value) {
			$replace = $tagToReplace = $varFormat = "";
			if(is_array($value)){
				foreach($value as $k=>$v){
					$tagToReplace = "{{" . $k . "}}";
					$varFormat = $this->getFormat('variables', $key);
					if(!empty($varFormat)) {
						if(is_array($varFormat)) {
							if(!empty($varFormat['start']) && !empty($varFormat['end']))
								$replace = $varFormat['start'] . $v . $varFormat['end'];
							else
								$replace = $v;
						} elseif(is_string($varFormat)){
							$replace = str_replace('{{value}}', $v, $varFormat);
						} elseif(is_bool($varFormat) && $varFormat === true){
							$replace = "<strong>{$v}</strong>";
						} else {
							$replace = $v;
						}
					} else {
						$replace = $v;
					}
					$replace = html_entity_decode($replace);
					$content = str_replace($tagToReplace, $replace, $content);
				}
			} else {
				$tagToReplace = "{{" . $key . "}}";
				$varFormat = $this->getFormat('variables');
				if(!empty($varFormat)) {
					if(is_array($varFormat)) {
						if(!empty($varFormat['start']) && !empty($varFormat['end']))
							$replace = $varFormat['start'] . $value . $varFormat['end'];
						else
							$replace = $value;
					} elseif(is_string($varFormat)){
						$replace = str_replace('{{value}}', $value, $varFormat);
					} elseif(is_bool($varFormat) && $varFormat === true) {
						$replace = "<strong>{$value}</strong>";
					} else {
						$replace = $value;
					}
				} else {
					$replace = $value;
				}
				$replace = html_entity_decode($replace);
				$content = str_replace($tagToReplace, $replace, $content);
			}
		}
		return $content;
	}

	public function getFormat($key = null, $templateID = null){
		if(is_numeric($templateID))
			return (is_null($key))?$this->formats[$templateID]:$this->formats[$templateID][$key];
		else {
			return (is_null($key))?$this->formats:$this->formats[$key];
		}
	}
}
class Formatter {
	public $formatter, $format = array();
	public function __construct($key) {
		$this->formatter = $key;
	}
	public function format($format){
		$this->format[$this->formatter] = htmlentities($format);
	}
}
?>