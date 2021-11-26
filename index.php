<?php
require_once("system/core/Constants.php");
require_once("system/core/Common.php");
//include_once ("application/config/config.php");
//header("Access-Control-Allow-Origin: http://example.com/");
header("Access-Control-Allow-Headers: X-Ceramic-Capture-Request, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST");
/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */
switch (config_item('ENVIRONMENT')) {
	case ENVIRONMENT_DEVELOPMENT:
		define('SHOW_DEBUG_BACKTRACE', true);
		error_reporting(E_ALL);
		ini_set('display_errors', 'On');
		break;
	case ENVIRONMENT_TESTING:
		define('SHOW_DEBUG_BACKTRACE', true);
		error_reporting(E_ALL & ~E_NOTICE);
		ini_set('display_errors', 'On');
		break;
	case ENVIRONMENT_PRODUCTION:
		ini_set('display_errors', 0);
		if (version_compare(PHP_VERSION, '5.3', '>='))
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
		}
		else
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
		}
		break;

	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'The application environment is not set correctly.';
		exit(1);// EXIT_ERROR
}
/*
 * ------------------------------------------------------
 *  Define a custom error handler so we can log PHP errors
 * ------------------------------------------------------
 */
set_error_handler('_error_handler');
set_exception_handler('_exception_handler');
register_shutdown_function('_shutdown_handler');

$captureRequest = (array_key_exists('HTTP_X_CERAMIC_CAPTURE_REQUEST', $_SERVER)) ? $_SERVER['HTTP_X_CERAMIC_CAPTURE_REQUEST'] : true;
$requester = (array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER)) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : '';
$requestURL = "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

/*if($requester == 'XMLHttpRequest' || !$captureRequest) {
	redirect($requestURL);
} else {
	require_once "system/core/Ceramic.php";
	Ceramic::run();
}*/
require_once "system/core/Ceramic.php";
Ceramic::run();