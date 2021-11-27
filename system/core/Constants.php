<?php
/**
 * Ceramic Version
 *
 * @var    string
 *
 */
const CM_VERSION = '1.1.0';
const ENVIRONMENT_DEVELOPMENT = 0, ENVIRONMENT_PRODUCTION = 1;
const ENVIRONMENT_NAME = [
	ENVIRONMENT_DEVELOPMENT => "development",
	ENVIRONMENT_PRODUCTION => "production",
];
// Define path constants
const DS = DIRECTORY_SEPARATOR;
define("ROOT", dirname(__FILE__, 3) . DS);
const APP_PATH = ROOT . 'application' . DS;
const STATIC_PATH = ROOT . 'assets' . DS;
const FRAMEWORK_PATH = ROOT . "system" . DS;
const PUBLIC_PATH = ROOT . "public" . DS;
const CONFIG_PATH = APP_PATH . "config" . DS;
const CONTROLLER_PATH = APP_PATH . "controllers" . DS;
const MODEL_PATH = APP_PATH . "models" . DS;
const VIEW_PATH = APP_PATH . "views" . DS;
const CORE_PATH = FRAMEWORK_PATH . "core" . DS;
const ASSETS_PATH = FRAMEWORK_PATH . "assets" . DS;
const DB_PATH = FRAMEWORK_PATH . "database" . DS;
const LIB_PATH = FRAMEWORK_PATH . "libraries" . DS;
const HELPER_PATH = FRAMEWORK_PATH . "helpers" . DS;
const UPLOAD_PATH = PUBLIC_PATH . "uploads" . DS;
const ENVIRONMENT_DIR = [
	ENVIRONMENT_DEVELOPMENT => APP_PATH . ENVIRONMENT_NAME[ENVIRONMENT_DEVELOPMENT],
	ENVIRONMENT_PRODUCTION => APP_PATH . ENVIRONMENT_NAME[ENVIRONMENT_PRODUCTION],
];

const INDEX_PAGE = true;
