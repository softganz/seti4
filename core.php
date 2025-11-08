<?php
/**
 * Core is a first file for each process request
 *
 * @package core
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , https://www.softganz.com
 * @created :: 2006-12-16
 * @modify  :: 2025-10-23
 * @version :: 29
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

error_reporting(E_ALL);

$coreFolder = preg_replace('/(\/|\\\\)core$/i', '', dirname(__FILE__));

// Set core location on request
if (array_key_exists('core', $_GET)) {
	$setCore = $_GET['core'];
	if ($setCore === 'clear') {
		setcookie('core', NULL, time()-1000,'/');
		unset($_COOKIE['core']);
	} else {
		setcookie('core', $setCore, time()+10*365*24*60*60, '/');
		$_COOKIE['core'] = $setCore;
	}
}
if (array_key_exists('core', $_COOKIE)) {
	$include_path = '/Users/httpdocs/cms/'.$_COOKIE['core'];
	if (is_dir($include_path)) {
		ini_set('include_path', $include_path);
		$coreFolder = $include_path;
	}
}

define('_CORE_FOLDER',          $coreFolder);
define('_CORE_CONTROLLER_FILE', _CORE_FOLDER.'/core/lib/class.core.controller.php');
define('_CORE_LIB_FILE',        _CORE_FOLDER.'/core/lib/class.core.function.php');
define('_CORE_INIT_FILE',       _CORE_FOLDER.'/core/lib/class.core.init.php');
define('_CORE_MODULE_FOLDER',   _CORE_FOLDER.'/core/modules');

if (!defined('_CONFIG_FILE')) define('_CONFIG_FILE', 'conf.web.php');

cfg('core.version.name',        'Seti');
cfg('core.version.major',       4);
cfg('core.version.code',        16);
cfg('core.version',             '4.3.10');
cfg('core.release',             '2025-11-08');
cfg('core.location',            ini_get('include_path'));
cfg('core.folder',              _CORE_FOLDER);
cfg('core.config',              _CONFIG_FILE);

// Current version will compare with version.install for upgrade database table
cfg('core.version.install', '4.0');

// set to the user defined error handler
set_error_handler('sgErrorHandler');
register_shutdown_function('sgShutdown');
spl_autoload_register('sg_autoloader');

// Set Garbage Collection
ini_set('session.gc_divisor', 1000);
ini_set('session.gc_maxlifetime', 1440);
ini_set('session.gc_probability', 1);

// Test error trigger
// echo 'error_reporting()='.error_reporting().'('.E_ALL.')'.'<br />';
//trigger_error("This is a test of trigger error", E_USER_WARNING);

unset($include_path, $coreFolder, $setCore);

if ($_SERVER['SERVER_SOFTWARE'] === 'FrankenPHP') return;

$request = requestString();

$ext = strtolower(substr($request,strrpos($request,'.')+1));

if (preg_match('/^(js|css)\//', $request) || (in_array($ext, ['js', 'css']) && basename($request) != 'theme.css')) {
	die(loadJS($request,$ext));
} else if (in_array($ext, ['ico', 'jpg', 'gif', 'png', 'htm', 'html', 'php', 'xml', 'pdf', 'doc', 'swf'])) {
	die(fileNotFound());
} else if (file_exists(_CORE_CONTROLLER_FILE)) {
	require(_CORE_CONTROLLER_FILE);
	if (file_exists(_CORE_LIB_FILE)) require(_CORE_LIB_FILE);
	if (file_exists(_CORE_INIT_FILE)) require(_CORE_INIT_FILE);
} else {
	die('SORRY!!!! Core Function Not Exists.');
}

/* Core Process Load End */


/* Core Function */

/**
 * Generate file not found from include file
 *
 * @return String
 */
function fileNotFound($msg=NULL) {
	ob_start();
	include('error/404.php');
	$ret = ob_get_contents();
	ob_end_clean();
	return $ret;
}

/** Load module JS file
 * @param String jsfile
 * @return String file content
 * */
function loadJS($requestFile, $ext) {
	$dir = explode('/', dirname($requestFile));
	// $firstFolder = reset($dir);
	$module = end($dir);

	if (preg_match('/^(js|css)\//', $requestFile)) {
		// Core js/css
		$fileName = _CORE_FOLDER.'/core/'.$requestFile;
	} else if (is_dir(_CORE_FOLDER.'/core/modules/'.$module)) {
		// Module is core module
		$fileName .=  _CORE_FOLDER.'/core/modules/'.$module.'/'.basename($requestFile);
	} else {
		// Module is user module
		$fileName .=  _CORE_FOLDER.'/modules/'.$requestFile;
	}

	if ($ext == 'js') {
		$headerType = 'text/javascript'.'; charset=utf-8';
	} else if ($ext == 'css') {
		$headerType = 'text/css'.'; charset=utf-8';
	} else if ($ext == 'png') {
		$headerType = 'image/png';
	} else if ($ext == 'gif') {
		$headerType = 'image/gif';
	}	else {
		$headerType = 'text/plain';
	}

	header('Content-Type: '.$headerType);

	// echo "Request = $requestFile \rFirstFolder = $firstFolder \rModule = $module \rFile location = $fileName\r\r";
	if (file_exists($fileName)) require($fileName);
	// else echo '// '.$requestFile.' '.$fileName.' not found!!!';

	// $logFile = '/tmp/js_load.log';
	// $logMessage = date('Y-m-d H:i:s') . " | Loaded: $fileName | Referer: " . $_SERVER['HTTP_REFERER'] . "\n";
	
	// error_log($logMessage, 3, $logFile);

	return;
}


/**
 * Get request string from first key of $_GET
 *
 * @return String
 */
function requestString() {
	$request = '';
	reset($_GET);

	$key = key($_GET);
	$value = $key != '' ? $_GET[$key] : '';

	reset($_GET);

	// debugMsg($_GET, '$_GET');
	// debugMsg('_URL = '._URL);

	if ($key && empty($value)) {
		$folder = isset($_SERVER['DOCUMENT_URI']) ? dirname($_SERVER['DOCUMENT_URI']) : '';
		$request_string = $_SERVER['QUERY_STRING'];
		$pattern = '%^'.preg_quote(addslashes($folder)).'%';

		// debugMsg('$folder = ['.$folder.']<br>$request_string1 = '.$request_string.'<br>reg = '.$pattern);

		$request_string = preg_replace($pattern, '', $request_string);

		// debugMsg('$request_string2 = ['.$request_string.']');

		$request_string = ltrim($request_string, '/');
		// if (preg_match('/^index.php/i', $request_string,$out)) $request_string = substr($request_string, 10);
		list($key) = explode('&', $request_string);
		$request = str_replace('%2F', '/', $key);
		if (substr($request, -1) == '=') $request = substr($request, 0, -1);

		// debugMsg('__FILE__ = '.__FILE__);
	}

	$request = trim($request, '/');
	// preg_replace('pattern', replacement, subject)
	// $request = preg_replace('/happy\/communeinfo\.com\//', '', $request);

	// debugMsg('$request = ['.$request.'] @'.date('Y-m-d H:i:s'));
	// debugMsg('<pre>'.print_r($_SERVER,1).'</pre>');

	// $_SERVER['SCRIPT_FILENAME']
	// $_SERVER['DOCUMENT_URI']
	return $request;
}

// Load all config file
function initConfig($configFolder) {
	SgCore::loadConfig('conf.default.php', _CORE_FOLDER.'/core/assets/conf'); // load default config file
	SgCore::loadConfig('conf.core.json', $configFolder); // load core config file
	SgCore::loadConfig(_CONFIG_FILE, $configFolder); // load web config file
	SgCore::loadConfig(_CONFIG_FILE, 'conf.local'); // load local config file
}

// Set auto loader file
function sg_autoloader($class) {
	$debug = debug('autoload');
	$registerFileList = (Array) R()->core->autoLoader->items;

	if (preg_match('/\\\\/', $class)) {
		$classList = explode('\\', $class);
		$class = end($classList);
	}

	$lowerClass = strtolower($class);
	if (in_array($lowerClass, array_keys($registerFileList))) {
		load_lib($registerFileList[$lowerClass]);
		if ($debug) {
			debugMsg('AUTOLOAD '.$class.' from register list of <b style="color: green">'.$registerFileList[$lowerClass].'</b>');
		}
		return;
	}

	$pieces = preg_split('/(?=[A-Z])/',$class, -1, PREG_SPLIT_NO_EMPTY);
	$endName = strToLower(end($pieces));

	$import = '';

	switch ($endName) {
		case 'model':
			array_pop($pieces);
			$import = 'model:'.implode('.', $pieces).'.php';
			break;
		case 'widget':
			array_pop($pieces);
			$import = 'widget:'.implode('.', $pieces).'.php';
			break;
	}
	$import = strToLower($import);

	if ($debug) {
		debugMsg('AUTOLOAD '.$class.' '.($import ? 'from <b style="color: green">import("'.$import.'")</b>' : 'not load.'));
	}

	if ($import) import($import);
}

/**
 * Set & get config value
 *
 * @param Mixed $key
 * @param Mixed $value
 * @return Mixed
 */
function cfg($key = NULL, $new_value = NULL, $action = NULL) {
	static $cfg = [];
	// Set config with array
	if (is_array($key)) {
		$cfg = array_merge($cfg, $key);
		ksort($cfg);
	} else if (isset($key) && isset($new_value)) {
		$cfg[$key] = $new_value;
		ksort($cfg);
	}

	// Remove config name and value
	if ($action == 'delete' && array_key_exists($key, $cfg)) unset($cfg[$key]);

	// Return value of key
	if (isset($key) && is_string($key)) {
		$ret = array_key_exists($key,$cfg) ? $cfg[$key] : null;
		if (is_string($ret) && substr(trim($ret), 0, 1) == '{') {
			$ret = json_decode($ret);
		}
	} else {
		$ret = $cfg;
	}

	if (is_array($ret)) reset($ret);
	return $ret;
}

/**
 * send header
 *
 * @param String $type eg. text/html, text/css, text/xml,
 */
function sendHeader($type = 'text/html') {
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	header("Date: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Pragma: no-cache"); // HTTP/1.0
	header('Content-Type: '.$type.'; charset='.cfg('client.characterset'));
	cfg('Content-Type', $type);
}


/**
 * Store debug message and display in div class="debug" of page
 *
 * @param String $msg
 * @return String
 */
function debugMsg($msg = NULL, $varname = NULL) {
	static $debugMsg = '';
	$callerFrom = debug_backtrace()[0]['file'].' @line '.debug_backtrace()[0]['line'];
	// echo '<br><br><br><br><br><br><pre>'.print_r(debug_backtrace(),1).'</pre>';

	// No need to check "access debugging program" because will check in index.tpl.php

	if (isset($msg)) {
		$msg = in_array('DebugMsg', get_declared_classes()) ? (new DebugMsg($msg, $varname, $callerFrom))->build() : '<div class="debug-msg">'.print_r($msg, 1).'</div>';

		$debugMsg .= $msg;
		return $msg;
	}

	return $debugMsg;
}

/**
 * Custom error handler
 * @param integer $code
 * @param string $description
 * @param string $file
 * @param interger $line
 * @param mixed $context
 * @return boolean
 */
function sgErrorHandler($code, $description, $file = null, $line = null, $context = null) {
	$displayErrors = strtolower(ini_get("display_errors"));

	// $message = 'Debug Error: <b>'.$description.'</b><br>code : <b>'.$code.'</b>, display : <b>'.$displayErrors.'</b> in file <b>' . $file . '</b>, line <b>' . $line . '</b>'.'<br />error_reporting : '.decbin(error_reporting()).' error code : '.decbin($code);
	$message = 'Debug Error: <b>'.$description.'</b><br>code : <b>'.$code.'</b>, display : <b>'.$displayErrors.'</b> error_reporting : '.decbin(error_reporting()).' error code : '.decbin($code);

	// echo 'show_error = '.(cfg('show_error') ? 'TRUE' : FALSE);
	// echo error_reporting()."\n";
	// error_reporting(0);

	$description = '<ul><li>'.implode('</li><li>', explode("\n", $description)).'</li></ul>';

	if (sgIsFatalError($code)) echo sgFatalError($code, $description, $file, $line);

	if ($displayErrors === 'off') {
		return false;
	} else if (!(error_reporting() & $code)) {
		// This error code is not included in error_reporting
		return false;
	}

	//$errstr=str_replace("\n", "<br />\n", $errstr);
	list($error, $log) = sgMapErrorCode($code);
	$data = [
		'level' => $log,
		'code' => $code,
		'error' => $error,
		'description' => $description,
		'file' => $file,
		'line' => $line,
		'context' => $context,
		'path' => $file,
		'message' => '<b>'.$error . '</b> (' . $code . ') in file <b>' . $file . '</b>, line <b>' . $line . '</b>' . $description
	];
	debugMsg('<p class="error">'.$data['message'].'</p><p>'.$message.'</p>');
	return true;
}

/**
 * Map an error code into an Error word, and log location.
 *
 * @param int $code Error code to map
 * @return array Array of error word, and log location.
 */
function sgMapErrorCode($code) {
	$error = $log = null;
	switch ($code) {
		case E_PARSE:
		case E_ERROR:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
		case E_USER_ERROR:
			$error = 'Fatal Error';
			$log = LOG_ERR;
			break;
		case E_WARNING:
		case E_USER_WARNING:
		case E_COMPILE_WARNING:
		case E_RECOVERABLE_ERROR:
			$error = 'Warning';
			$log = LOG_WARNING;
			break;
		case E_NOTICE:
		case E_USER_NOTICE:
			$error = 'Notice';
			$log = LOG_NOTICE;
			break;
		case E_STRICT:
			$error = 'Strict';
			$log = LOG_NOTICE;
			break;
		case E_DEPRECATED:
		case E_USER_DEPRECATED:
			$error = 'Deprecated';
			$log = LOG_NOTICE;
			break;
		default :
			break;
	}
	return [$error, $log];
}

function sgSendLog($data = []) {
	$forceSend = $_GET['forceSendLog'];
	$sendLogToUrl = cfg('error')->sendLog->toUrl;
	$domainNotSendLog = (Array) cfg('error')->sendLog->domainNotSend;
	
	if (empty($sendLogToUrl)) return;

	$data = array_replace_recursive(
		[
			'url' => _DOMAIN.$_SERVER['REQUEST_URI'],
			'referer' => $_SERVER["HTTP_REFERER"],
			'agent' => $_SERVER['HTTP_USER_AGENT'],
			'date' => date('Y-m-d H:i:s'),
			'user' => function_exists('i') ? i()->uid : NULL,
			'name' => function_exists('i') ? i()->name : NULL,
			'type' => NULL,
			'file' => NULL,
			'line' => NULL,
			'description' => NULL,
			'data' => (Object) [
				'get' => (Object) Request::get(),
				'post' => (Object) Request::post(),
			]
		],
		(Array) $data
	);

	if (isset($data['forceSend'])) {
		$forceSend = true;
		unset($data['forceSend']);
	}

	if ($data['description'] && (is_object($data['description']) || is_array($data['description']))) {
		$data['description'] = json_encode(
			$data['description'],
			JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
		);
	}

	if ($forceSend || !in_array(_DOMAIN_SHORT, $domainNotSendLog)) {
		ApiModel::send(
			[
				'url' => $sendLogToUrl,
				'method' => 'post',
				'postFields' => $data,
				'returnTransfer' => true,
				'result' => 'json',
				'debug' => false,
			],
			$curlOptions
		);
	}
}

function sgFatalError($code, $description, $file, $line) {
	$accessDebug = function_exists('user_access') ? user_access('access debugging program') : NULL;
	$isAdmin = (function_exists('i') && i()->uid == 1) || $accessDebug;
	$reportFileName = $file;
	$debugMsg = debugMsg();

	if (!$isAdmin) {
		$reportFileName = basename($file);
		$reportFileName = preg_replace('/^class\.|func\./', '', $reportFileName);
		$reportFileName = preg_replace('/\.php$/', '', $reportFileName);
	}

	sgSendLog([
		'type' => 'Fatal Error',
		'file' => $file,
		'line' => $line,
		'description' => 'Error at line <b>'.$line.'</b><br />'.$description,
	]);

	$msg = 'There is error in <b>'.$reportFileName.'</b> '
		. 'line <b>'.$line.'</b>. '
		. 'Please report to webmaster.'
		. ($isAdmin ? '<br /><br />Error at line <b>'.$line.'</b><br />'.$description : '');

	$url = _DOMAIN.$_SERVER['REQUEST_URI'];

	return '<html><head><title>Fatal error</title></head>
	<body>
	<table width="100%" height="100%">
	<tr>
		<td></td>
		<td width="80%">
			<div style="border: 1px solid rgb(210, 210, 210); border-radius: 8px; background-color: rgb(241, 241, 241); padding: 30px;">
			<h1>Fatal error'.($isAdmin ? '<span style="font-size: 0.6em;"> @PHP Version '.phpversion().'</span>' : '').'</h1>
			<p>The requested URL <b>'.$url.'</b> was error.</p>
			<p>'.$msg.'</p>'
			. '<hr>
			<address>copyright <a href="//'.$_SERVER['SERVER_NAME'].'">'.$_SERVER['SERVER_NAME'].'</a> Allright reserved.</address>
			</div>
		</td>
		<td></td>
	</tr>
	'
	.($isAdmin && $debugMsg ? '<tr><td></td><td>'.$debugMsg.'<style>.debug-msg {padding: 16px; border:1px #ccc solid; margin: 16px 0; border-radius: 8px; background-color: #fafafa;}</style></td><td></td></tr>' : '').'
	</table>
	</body>
	</html>';
}

function sgIsFatalError($code) {
	return in_array($code, [E_PARSE, E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR]);
}

function sgShutdown() {
	global $R;
	$error = error_get_last();
	if ( sgIsFatalError($error["type"]) ) {
		sgErrorHandler( $error["type"], $error["message"], $error["file"], $error["line"] );
	}
	if (is_object($R->myDb) && method_exists($R->myDb,'close')) {
		$R->myDb->close();
	}
}
?>