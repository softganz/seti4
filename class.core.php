<?php
/**
 * Core is a first file for process each request
 *
 * @package core
 * @version 4
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , https://www.softganz.com
 * @created 2006-12-16
 * @modify  2022-02-10
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

error_reporting(E_ALL);

$coreFolder = dirname(__FILE__).'/';

// Set core location on request
if (array_key_exists('core', $_GET)) {
	$setCore = $_GET['core'];
	if ($setCore == 'clear') {
		setcookie('core', NULL, time()-1000,'/');
		unset($_COOKIE['core']);
	} else {
		setcookie('core', $setCore, time()+10*365*24*60*60, '/');
		$_COOKIE['core'] = $setCore;
	}
}
if (array_key_exists('core', $_COOKIE)) {
	$include_path = '/Users/httpdocs/cms/'.$_COOKIE['core'];
	if (file_exists($include_path)) {
		ini_set('include_path', $include_path);
		$coreFolder = $include_path.'/';
	}
}

define('_CORE_FOLDER', $coreFolder);
if (!defined('_CONFIG_FILE')) define('_CONFIG_FILE', 'conf.web.php');

cfg('core.version.name', 'Seti');
cfg('core.version.code', 5);
cfg('core.version.major', '4');
cfg('core.version', '4.1.00');
cfg('core.location', ini_get('include_path'));
cfg('core.release', '2021-12-21');
cfg('core.folder', _CORE_FOLDER);
cfg('core.config', _CONFIG_FILE);

// Current version will compare with version.install for upgrade database table
cfg('core.version.install', '4.0');

if (!cfg('domain')) cfg('domain', ($_SERVER["REQUEST_SCHEME"] ? $_SERVER["REQUEST_SCHEME"] : 'http').'://'.$_SERVER['HTTP_HOST']);
if (!cfg('domain.short')) cfg('domain.short', $_SERVER['HTTP_HOST']);
//if (!cfg('cookie.domain')) cfg('cookie.domain', $_SERVER['HTTP_HOST']);

cfg('folder.abs', dirname(isset($_SERVER['PATH_TRANSLATED']) ? $_SERVER['PATH_TRANSLATED'] : $_SERVER['SCRIPT_FILENAME']).'/');
cfg('url', in_array(dirname($_SERVER['PHP_SELF']), ['/','\\']) ? '/' : dirname($_SERVER['PHP_SELF']).'/');

define('_DOMAIN', cfg('domain'));
define('_DOMAIN_SHORT', cfg('domain.short'));
define('_ON_LOCAL', cfg('domain.short') == 'localhost');
define('_ON_HOST', cfg('domain.short') != 'localhost');
define('_URL', cfg('url'));
define('_url', cfg('url'));


// set to the user defined error handler
set_error_handler('sgErrorHandler');

// Test error trigger
//echo 'error_reporting()='.error_reporting().'('.E_ALL.')'.'<br />';
//trigger_error("This is a test of trigger error", E_USER_WARNING);

unset($include_path,$coreFolder,$setCore);

$request = requestString();

$ext = strtolower(substr($request,strrpos($request,'.')+1));
if (preg_match('/^(js|css)\//', $request) || (in_array($ext, ['js','css']) && basename($request) != 'theme.css')) {
	die(loadJS($request,$ext));
} else if (in_array($ext, ['ico','jpg','gif','png','htm','html','php','xml','pdf','doc','swf'])) {
	die(fileNotFound());
} else if (file_exists(_CORE_FOLDER.'system/core/class.corefunction.php')) {
	require(_CORE_FOLDER.'system/core/class.corefunction.php');
} else if (file_exists(_CORE_FOLDER.'class.corefunction.php')) {
	require(_CORE_FOLDER.'class.corefunction.php');
} else {
	die('SORRY!!!! Core Function Not Exists.');
}

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
	$firstFolder = reset($dir);
	$module = end($dir);

	$fileName = _CORE_FOLDER;
	if (preg_match('/^(js|css)\//', $requestFile)) {
		$fileName .= $requestFile;
	} else {
		$fileName .=  'modules/'.$module.'/'.basename($requestFile);
	}

	if ($ext == 'js') {
		$headerType = 'text/javascript'.'; charset=utf-8';
	} else if ($ext == 'css') {
		$headerType = 'text/css'.'; charset=utf-8';
	} else if ($ext == 'png') {
		$headerType = 'image/png';
		//echo '$headerType='.$headerType.' '.$fileName.(file_exists($fileName) ? ' exists' : 'no exists');
	} else if ($ext == 'gif') {
		$headerType = 'image/gif';
	}	else {
		$headerType = 'text/plain';
	}

	header('Content-Type: '.$headerType);

	//echo "Request = $requestFile \rFirstFolder = $firstFolder \rModule = $module \rFile location = $fileName\r\r";
	if (file_exists($fileName)) require($fileName);
	return;
}


/**
 * Get request string from first key of $_GET
 *
 * @return String
 */
function requestString() {
	$result = NULL;
	reset($_GET);

	$key = key($_GET);
	$value = $key != '' ? $_GET[$key] : '';

	reset($_GET);

	if ($key && empty($value)) {
		$request_string = $_SERVER['QUERY_STRING'];
		$request_string = ltrim($request_string, '/');
		// if (preg_match('/^index.php/i', $request_string,$out)) $request_string = substr($request_string, 10);
		list($key) = explode('&', $request_string);
		$result = str_replace('%2F', '/', $key);
		if (substr($result, -1) == '=') $result = substr($result, 0, -1);
	}
	return $result;
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
	} else $ret = $cfg;

	if (is_object($ret) || is_array($ret)) reset($ret);
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
	$isDebugGlobal = true;
	if (is_array($msg) && is_null($varname)) {
		$varname = $msg[1];
		$msg = $msg[0];
		$isDebugGlobal = false;
	}
	if (is_object($msg) || is_array($msg)) {
		if (function_exists('print_o')) {
			$msg = print_o($msg,$varname);
		} else {
			$msg = print_r($msg,1);
		}
	}
	if (isset($msg)) {
		if (preg_match('/^(SELECT|UPDATE|INSERT|DELETE)/i', $msg, $out)) {
			$msg = '<pre>'.$msg.'</pre>';
		}
		$msg = "\r\n".'<div class="debug-msg">'.$msg.'</div>'."\r\n";
		if ($isDebugGlobal) $debugMsg .= $msg; else if (user_access('access debugging program')) return $msg;
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
	//echo '<p>Debug Error: code : '.$code.' display '.$displayErrors.' : ['.$code.'] : '.$description.' in [' . $file . ', line ' . $line . ']'.'<br />error_reporting : '.decbin(error_reporting()).' error code : '.decbin($code).'</p>';
	error_reporting(0);

	if (sgIsFatalError($code)) echo sgFatalError($code,$description,$file,$line);

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
		'message' => $error . ' (' . $code . '): ' . $description . ' in [' . $file . ', line ' . $line . ']'
	];
	debugMsg('<p class="error">'.$data['message'].'</p>'."\n");
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

function sgFatalError($code, $description, $file, $line) {
	$uid = function_exists('i') ? i()->uid : NULL;
	$accessAdmin = function_exists('user_access') ? user_access('access administrator pages') : NULL;
	$isAdmin = $uid == 1 || $accessAdmin;
	$reportFileNmae = $file;
	if (!$isAdmin) {
		$reportFileNmae = basename($file);
		$reportFileNmae = preg_replace('/^class\.|func\./', '', $reportFileNmae);
		$reportFileNmae = preg_replace('/\.php$/', '', $reportFileNmae);
	}

	$msg = '<b>Fatal error: </b>'.$description.' in <b>'.$reportFileNmae.'</b> '
		. 'line <b>'.$line.'</b>. '
		. 'Please '
		. '<a href="https://www.softganz.com/bug?f='.$reportFileNmae.'&l='.$line.'&d='.date('Y-m-d H:i:s').'&u='.(isset($_SERVER['REQUEST_SCHEME'])?$_SERVER['REQUEST_SCHEME']:'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'" target="_blank">'
		. 'report to webmaster'
		. '</a>.';

	$msgHelp = '';
	$debugMsg = debugMsg();

	return '<html><head><title>Fatal error</title></head>
	<body>
	<table width="100%" height="100%">
	<tr>
		<td></td>
		<td width="80%">
			<div style="border: 1px solid rgb(210, 210, 210); border-radius: 8px; background-color: rgb(241, 241, 241); padding: 30px;">
			<h1>Fatal error'.($isAdmin ? '<span style="font-size: 0.6em;"> @PHP Version '.phpversion().'</span>' : '').'</h1>
			<p>The requested URL <b>'.$_SERVER['REQUEST_URI'].'</b> was error.</p>
			<p>'.$msg.'</p>'
			. ($msgHelp ? '<p><font color="gray">'.$msgHelp.'</font></p>' : '')
			. '<hr>
			<address>copyright <a href="http://'.$_SERVER['HTTP_HOST'].'">'.$_SERVER['HTTP_HOST'].'</a> Allright reserved.</address>
			</div>
		</td>
		<td></td>
	</tr>
	'
	.($isAdmin && $debugMsg ? '<tr><td></td><td><div style="border:1px solid rgb(210, 210, 210);background-color: rgb(241, 241, 241);padding:30px;-moz-border-radius:10px;">'.$debugMsg.'</div></td><td></td></tr>' : '').'
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

register_shutdown_function('sgShutdown');
?>