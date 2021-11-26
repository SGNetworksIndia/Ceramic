<?php
/**
 * Ceramic
 *
 * An open source Model-View-Controller (MVC) application development framework for PHP
 *
 * This content is released under the Exclusive Copyright of the author
 *
 * Copyright (c) 2020, SGNetworks & Sagnik Ganguly
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), the rights
 * to only use any number of copies of the Software, subject to the following conditions:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITH RIGHTS TO USE ANY NUMBER OF COPIES OF THE SOFTWARE AND
 * RESTRICTED FROM COPY, MODIFY, MERGE, PUBLISH, DISTRIBUTE, SUBLICENSE, AND/OR SELL
 * COPIES OF THE SOFTWARE.
 *
 * @package	Ceramic
 * @author	Sagnik Ganguly
 * @copyright	Copyright (c) 2020, SGNetworks & Sagnik Ganguly. (https://sgn.heliohost.org)
 * @link	https://ceramic.mooo.com
 * @since	Version 1.0.1
 * @filesource
 */
defined("CORE_PATH") OR exit("No direct script access allowed");

class Autoindex {
	public $container, $heading, $body, $links, $footer;
	
	function __construct(){
		$containerObj = json_encode(array("class"=>"cm-ai-container","id"=>""), JSON_NUMERIC_CHECK);
		$headingObj = json_encode(array("class"=>"cm-ai-header","id"=>""), JSON_NUMERIC_CHECK);
		$bodyObj = json_encode(array("class"=>"cm-ai-body","id"=>""), JSON_NUMERIC_CHECK);
		$linksObj = json_encode(array("class"=>"cm-ai-link","id"=>""), JSON_NUMERIC_CHECK);
		$footerObj = json_encode(array("class"=>"cm-ai-footer","id"=>""), JSON_NUMERIC_CHECK);
		$this->container = json_decode($containerObj);
		$this->heading = json_decode($headingObj);
		$this->body = json_decode($bodyObj);
		$this->links = json_decode($linksObj);
		$this->footer = json_decode($footerObj);
	}
	
	function render() {
		$dbt = debug_backtrace();
		$callingClassName = $dbt[1]['class'];
		$style = '<style type="text/css">';
		$style .= '.cm-ai-container{
						font-size: 1.5em;
						border: 1px solid #ddd;
						border-radius: 10px;
						overflow: hidden;
					}
					.cm-ai-container > .cm-ai-header {
						background-color: #eee;
						padding: 15px;
						font-weight: bold;
						font-size: 1.5em;
						border-radius: 10px 10px 0 0;
						text-align: center;
					}
					.cm-ai-container > .cm-ai-body {
						border-radius: 0 0 10px 10px;
					}
					.cm-ai-container > .cm-ai-body > .cm-ai-link {
						display: block;
						border-top: 1px solid #ddd;
						border-radius: 0;
						padding: 10px;
						text-decoration: none;
					}
					/*.cm-ai-container > .cm-ai-body > .cm-ai-link:last-of-type{
						border-radius: 0 0 10px 10px;
					}*/
					.cm-ai-container > .cm-ai-body > .cm-ai-link:hover{
						background-color: #f9f9f9;
					}
					.cm-ai-container > .cm-ai-footer {
						background-color: #efefef;
						font-size: .7em;
						padding: 5px;
						border-top: 1px solid #ddd;
						border-radius: 0 0 10px 10px;
						text-align: center;
					}';
		$style .= '</style>';
		$html = "";
		try {
			$class = new ReflectionClass($callingClassName);
		} catch(ReflectionException $e) {
		}
		$methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
		$count = 0;
		$base_url = (substr(config_item('base_url'), 0, 1) === '/') ? config_item('base_url') : '/' . config_item('base_url');
		$base_url = (substr($base_url, -1) == '/') ? $base_url : $base_url . '/';
		foreach($methods as $k => $v) {
			if($v->class == $callingClassName) {
				$count++;
			}
		}
		
		$containerAttrs = $headingAttrs = $bodyAttrs = $linksAttrs = $footerAttrs = "";
		foreach($this->container as $k=>$v){
			$containerAttrs .= " {$k}=\"{$v}\"";
		}
		foreach($this->heading as $k=>$v){
			$headingAttrs .= " {$k}=\"{$v}\"";
		}
		foreach($this->body as $k=>$v){
			$bodyAttrs .= " {$k}=\"{$v}\"";
		}
		foreach($this->links as $k=>$v){
			$linksAttrs .= " {$k}=\"{$v}\"";
		}
		foreach($this->footer as $k=>$v){
			$footerAttrs .= " {$k}=\"{$v}\"";
		}
		$i = 1;
		foreach($methods as $k => $v) {
			if($v->class == $callingClassName) {
				if($v->name != '__common' && $v->name != '__construct'){
					$controllerName = $v->class;
					$viewMethod = ($v->name == '__default') ? '' : $v->name;
					$viewMethodName = ($viewMethod == '') ? 'Home' : $viewMethod;
					$viewMethodName = ucwords($viewMethodName);
					if(empty($html)) {
						$html = "\n<div {$containerAttrs}>\n";
						$html .= "<div {$headingAttrs}>{$controllerName}</div>\n";
						$html .= "<div {$bodyAttrs}>\n";
						$html .= "<a {$linksAttrs} href='{$base_url}{$controllerName}/{$viewMethod}'>{$viewMethodName}</a>\n";
					} else {
						if($i == $count) {
							$html .= "<a {$linksAttrs} href='{$base_url}{$controllerName}/{$viewMethod}'>{$viewMethodName}</a>\n";
							$html .= "</div>\n";
							$html .= "<div {$footerAttrs}>Generated using Ceramic Autoindex</div>\n";
							$html .= "</div>\n";
						} else {
							$html .= "<a {$linksAttrs} href='{$base_url}{$controllerName}/{$viewMethod}'>{$viewMethodName}</a>\n";
						}
					}
				}
			}
			$i++;
		}
		if(!empty($html)) {
			echo $style;
			echo $html;
		}
	}

	/**
	 * @param string|null $layout The path to the layout template to inflate
	 */
	function inflate(string $layout = null) {
		if(!empty($layout)) {
			if(file_exists(VIEW_PATH . "{$layout}.php"))
				$ext = "php"; elseif(file_exists(VIEW_PATH . "{$layout}.htm"))
				$ext = "htm";
			elseif(file_exists(VIEW_PATH . "{$layout}.html"))
				$ext = "html";
			elseif(file_exists(VIEW_PATH . "{$layout}.phtm"))
				$ext = "phtm";
			elseif(file_exists(VIEW_PATH . "{$layout}.phtml"))
				$ext = "phtml";
			$file = VIEW_PATH . "{$layout}.{$ext}";
		} else {
			$file = ASSETS_PATH . 'autoindex/template.html';
		}
		$dbt = debug_backtrace();
		$callingClassName = $dbt[1]['class'];
		$style = '<style type="text/css">';
		$style .= '.cm-ai-container{
						font-size: 1.5em;
						border: 1px solid #ddd;
						border-radius: 10px;
						overflow: hidden;
					}
					.cm-ai-container > .cm-ai-header {
						background-color: #eee;
						padding: 15px;
						font-weight: bold;
						font-size: 1.5em;
						border-radius: 10px 10px 0 0;
						text-align: center;
					}
					.cm-ai-container > .cm-ai-body {
						border-radius: 0 0 10px 10px;
					}
					.cm-ai-container > .cm-ai-body > .cm-ai-link {
						display: block;
						border-top: 1px solid #ddd;
						border-radius: 0;
						padding: 10px;
						text-decoration: none;
					}
					/*.cm-ai-container > .cm-ai-body > .cm-ai-link:last-of-type{
						border-radius: 0 0 10px 10px;
					}*/
					.cm-ai-container > .cm-ai-body > .cm-ai-link:hover{
						background-color: #f9f9f9;
					}
					.cm-ai-container > .cm-ai-footer {
						background-color: #efefef;
						font-size: .7em;
						padding: 5px;
						border-top: 1px solid #ddd;
						border-radius: 0 0 10px 10px;
						text-align: center;
					}';
		$style .= '</style>';
		$links = "";
		try {
			$class = new ReflectionClass($callingClassName);
		} catch(ReflectionException $e) {
		}
		$methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
		$count = 0;
		$base_url = (substr(config_item('base_url'), 0, 1) === '/') ? config_item('base_url') : '/' . config_item('base_url');
		$base_url = (substr($base_url, -1) == '/') ? $base_url : $base_url . '/';
		$cmtemplate = new Template($file);
		foreach($methods as $k => $v) {
			if($v->class == $callingClassName) {
				$count++;
			}
		}
		$i = 1;
		foreach($methods as $k => $v) {
			if($v->class == $callingClassName) {
				if($v->name != '__common' && $v->name != '__construct'){
					$controllerName = $v->class;
					$viewMethod = ($v->name == '__default') ? '' : $v->name;
					$viewMethodName = ($viewMethod == '') ? 'Home' : $viewMethod;
					$viewMethodName = ucwords($viewMethodName);
					if(empty($html)) {
						$title = $controllerName;
						$links .= "<a class='cm-ai-link' href='{$base_url}{$controllerName}/{$viewMethod}'>{$viewMethodName}</a>\n";
					} else {
						if($i == $count) {
							$links .= "<a class='cm-ai-link' href='{$base_url}{$controllerName}/{$viewMethod}'>{$viewMethodName}</a>\n";
						} else {
							$links .= "<a class='cm-ai-link' href='{$base_url}{$controllerName}/{$viewMethod}'>{$viewMethodName}</a>\n";
						}
					}
				}
			}
			$i++;
		}
		$credits = "Generated using Ceramic Autoindex";
		$cmtemplate->set('cm_ai_styles', $style);
		$cmtemplate->set('cm_ai_title', $title);
		$cmtemplate->set('cm_ai_links', $links);
		$cmtemplate->set('cm_ai_credits', $credits);
		echo $cmtemplate->render();
	}
}