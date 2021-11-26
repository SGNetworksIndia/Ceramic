<?php
/**
 * Ceramic Version
 *
 * @var	string
 *
 */
const CM_VERSION = '1.0.1';
const ENVIRONMENT_DEVELOPMENT = 0, ENVIRONMENT_PRODUCTION = 1;
define("ENVIRONMENT_NAME", array(ENVIRONMENT_DEVELOPMENT => "devlopment", ENVIRONMENT_PRODUCTION => "production"));
// Define path constants
define("DS", DIRECTORY_SEPARATOR);
define("ROOT", dirname(dirname(dirname(__FILE__))) . DS);
define("APP_PATH", ROOT . 'application' . DS);
define("STATIC_PATH", ROOT . 'assets' . DS);
define("FRAMEWORK_PATH", ROOT . "system" . DS);
define("PUBLIC_PATH", ROOT . "public" . DS);
define("CONFIG_PATH", APP_PATH . "config" . DS);
define("CONTROLLER_PATH", APP_PATH . "controllers" . DS);
define("MODEL_PATH", APP_PATH . "models" . DS);
define("VIEW_PATH", APP_PATH . "views" . DS);
define("CORE_PATH", FRAMEWORK_PATH . "core" . DS);
define('ASSETS_PATH', FRAMEWORK_PATH . "assets" . DS);
define('DB_PATH', FRAMEWORK_PATH . "database" . DS);
define("LIB_PATH", FRAMEWORK_PATH . "libraries" . DS);
define("HELPER_PATH", FRAMEWORK_PATH . "helpers" . DS);
define("UPLOAD_PATH", PUBLIC_PATH . "uploads" . DS);
define("ENVIRONMENT_DIR", array(ENVIRONMENT_DEVELOPMENT => APP_PATH . ENVIRONMENT_NAME[ENVIRONMENT_DEVELOPMENT], ENVIRONMENT_PRODUCTION => APP_PATH . ENVIRONMENT_NAME[ENVIRONMENT_PRODUCTION]));

define("INDEX_PAGE",true);
?>