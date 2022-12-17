<?php
/**
* Core Function :: Controller Process Web Configuration and Request
* Created :: 2006-12-16
* Modify  :: 2022-11-20
* Version :: 3
*/

global $R;

$R = new R();
$R->request = $request;

$includeFileList = [
	'lib/lib.define.php',
	'lib/lib.base.php',
	'lib/lib.function.php',
	'lib/lib.sg.php',
	'lib/class.common.php',
	'lib/class.module.php',
	'lib/class.mydb.php',
	'lib/lib.widget.php',
	'lib/class.view.php',
	'lib/class.sg.php',
	'lib/class.poison.php',
	'models/model.basic.php',
	'models/model.user.php',
	'models/model.counter.php',
	// Extend Library
	// 'lib/lib.corefunction.v'.cfg('core.version.major').'.php',
];

foreach ($includeFileList as $file) load_lib($file);

SgCore::processSetting($R);

$httpDomain = str_ireplace('www.', '', parse_url(cfg('domain'), PHP_URL_HOST));
$httpReferer = isset($_SERVER["HTTP_REFERER"])?str_ireplace('www.', '', parse_url($_SERVER["HTTP_REFERER"], PHP_URL_HOST)):'';

//debugMsg('<br />HTTP_USER_AGENT='.$_SERVER['HTTP_USER_AGENT']);

//debugMsg(cfg('domain'));
//debugMsg('0='.$_SERVER['HTTP_X_REQUESTED_WITH'].' 1='.$httpDomain.' 2='.$httpReferer);

$appFormat = '/(Softganz)\/(.*)\s*+\(([a-zA-Z0-9; ].*)\)/i';
$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

//$appFormat = '/(Softganz)\/(.*)\s*+\((.*)\;\s*+(.*)\)/i';
//$userAgent = 'Softganz/0.1.13 (App; Android)';
//$userAgent = 'Mozilla/5.0 (Linux; Android 9; AMN-LX2 Build/HUAWEIAMN-LX2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/85.0.4183.127 Mobile Safari/537.36 Softganz/0.1.03 (OTOU; Android)';

if (isset($R->setting->app)) {
	$userAgent = 'Mozilla/5.0 (Linux; Android 9; Nokia 6.1 Plus Build/PPR1.180610.011; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/75.0.3770.101 Mobile Safari/537.36 '
		. (isset($R->setting->app->dev) ? $R->setting->app->dev : 'Unknown').'/'
		. (isset($R->setting->app->ver) ? $R->setting->app->ver : 'Unknown')
		. ' ('
		. (isset($R->setting->app->type) ? $R->setting->app->type : 'Unknown').'; '
		. (isset($R->setting->app->OS) ? $R->setting->app->OS : 'Unknown').'; '
		. (isset($R->setting->app->theme) ? $R->setting->app->theme.';' : '')
		. ')';
}
//if ($R->setting->app) $userAgent = 'Mozilla/5.0 (Linux; Android 9; Nokia 6.1 Plus Build/PPR1.180610.011; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/75.0.3770.101 Mobile Safari/537.36 Softganz/0.1.13 (Web;Android)';

// debugMsg('$userAgent = '.$userAgent);

$callFromApp = preg_match($appFormat, $userAgent, $out);
if ($callFromApp) {
	//debugMsg('Call from '.($callFromApp ? 'APP' : 'WEB'));
	//debugMsg($_SERVER['HTTP_USER_AGENT']);
	// debugMsg($out,'$out');
	$appAgent = (Object) ['dev' => trim($out[1]), 'ver' => trim($out[2]), 'type' => '', 'OS' => '', 'theme' => ''];
	$appType = preg_split('/\;/', $out[3]);
	$appAgent->type = trim($appType[0]);
	$appAgent->OS = trim($appType[1]);
	if (isset($appType[2])) $appAgent->theme = trim($appType[2]);
	$R->appAgent = $appAgent;
	//debugMsg($appAgent,'$appAgent');

	if ($R->appAgent->dev == 'Softganz') {
		list($appVer,$appSubVer) = explode('.',$R->appAgent->ver);
		$pageClass = '-softganz-app -os-'.strtolower($R->appAgent->OS)
			. ' -app-ver-'.$appVer.'-'.$appSubVer
			. ($R->appAgent->theme ? ' -app-theme-'.$R->appAgent->theme : '');
		page_class($pageClass);
	}
}

//debugMsg(pageInfo());
if (isset($_GET['setting:'])) {
	print_o($R->setting, '$R->setting',1);
	if (!empty($R->options)) print_o($R->options, '$R->options',1);
	if ($R->appAgent) print_o($R->appAgent, '$R->appAgent',1);
	die;
}

/*
die;

$ret .= 'HTTP_X_REQUESTED_WITH = '.$_SERVER['HTTP_X_REQUESTED_WITH'].'<br />';
//$ret .= print_o($_SERVER,'$_SERVER');

if ($callFromApp) page_class('-app'));
$ret .= cfg('page_class');
*/

define('_HOST', $httpDomain);
define('_REFERER', $httpReferer);
define('_CALL_FROM_APP', $callFromApp ? $R->appAgent->type : false);
define('_AJAX', ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest' && $httpDomain==$httpReferer))
	|| preg_match('/^ajax\//i',$request)
	|| isset($_GET['ajax']));
define('_HTML', isset($_REQUEST['html']));
define('_API', isset($_REQUEST['api']));

//die(_AJAX?'AJAX Call':'Normal call');
// echo (_AJAX?'AJAX Call':'Normal call').'<br />';

q($R->request);

$R->timer = new Timer();


// Clear module folder don't exists
$old_error = error_reporting(0);
$_module_folder = [];

foreach (explode(PATH_SEPARATOR,ini_get('include_path')) as $_folder) {
	if (is_dir($_folder.'/modules')) $_module_folder[] = $_folder.'/modules';
	if (is_dir($_folder.'/modules/apps')) $_module_folder[] = $_folder.'/modules/apps';
}
error_reporting($old_error);

cfg('module.folder',$_module_folder);
unset($_module_folder, $_folder); // clear unused variable

// Load configuration file
SgCore::loadConfig('conf.default.php', _CORE_FOLDER.'/core/assets/conf'); // load default config file
SgCore::loadConfig('conf.web.release.php', '.'); // load web config release
SgCore::loadConfig(_CONFIG_FILE, '.'); // load web config file
SgCore::loadConfig('conf.local.php', '.'); // load local config file
error_reporting(cfg('error_reporting'));
//echo 'error after load config '.error_reporting().' : '.decbin(error_reporting()).'<br />';

// Create new mydb database constant
$R->myDb = new MyDb();

// If connect database error, end process
if (!$R->myDb->status) {
	// if not set_theme, cannot find index template file
	set_theme();
	die(SgCore::processIndex('index', message('error','OOOPS!!! Database connection error')));
}

// Not use class MySql anymore
// if (class_exists('MySql')) $R->mysql = new MySql();

// Load config variable from table
SgCore::loadConfig(cfg_db());

if (banIp(getenv('REMOTE_ADDR'))) die('Sorry!!!! You were banned.');

// if ($site_message) {
// 	$ret .= '<p class="notify">'.tr('<h2>Website temporary out of service.</h2><p>My Website is currently out of service ('.$site_message.'). We should be back shortly. Thank you for your patience.</p>','<strong>อุ๊บ!!! เว็บไซท์ของเราให้บริการไม่ทันเสียแล้ว</strong><br /><br />ขออภัยด้วยนะคะ มีความเป็นไปได้สูงว่าเครื่องเซิร์ฟเวอร์กำลังทำงานหนักจนไม่สามารถให้บริการได้ทัน เว็บไซท์จึงหยุดการบริการชั่วคราว อีกสักครู่ขอให้ท่านแวะมาดูใหม่นะคะ').'</p>';
// 	ob_start();
// 	SgCore::loadTemplate('home');
// 	$ret .= ob_get_contents();
// 	ob_end_clean();
// 	die($ret);
// }

if ($request == 'robots.txt') die(cfg('robots.txt'));

// Clear user_access
user_access('reset');

// DEFINE from config
define('_DATE_FORMAT', cfg('dateformat'));
define('_img',cfg('img'));

// Redirect website to other site if set redirect and not admin page
if (cfg('site.redirection') != ''
	&& cfg('site.redirection')!=cfg('domain').$_SERVER['REQUEST_URI']
	&& !in_array(q(0), ['admin','signout']) ) {
	header('Location: '.cfg('site.redirection').'/'.q());
	echo 'Site rediection to <a href="'.cfg('site.redirection').'/'.q().'">'.cfg('site.redirection').'/'.q().'</a>';
	flush();
	die;
}

// Return theme.css store in config theme.(theme.name).css
if (basename($request) === 'theme.css') {
	sendheader('text/css');
	echo '/* Load theme : theme.'.cfg('theme.name').'.css */'._NL;
	die(cfg('theme.'.cfg('theme.name').'.css'));
}

cfg('url.abs', preg_match('/http\:/',cfg('url')) ? cfg('url') : '//'.$_SERVER['HTTP_HOST'].cfg('url'));
if (!cfg('upload.folder')) cfg('upload.folder', cfg('folder.abs').cfg('upload').'/');
if (!cfg('upload.url')) cfg('upload.url', cfg('url').cfg('upload').'/');

SgCore::setLang();

// Start session handler register using database
$session = new Session();
session_set_save_handler(
	[$session, 'open'],
	[$session, 'close'],
	[$session, 'read'],
	[$session, 'write'],
	[$session, 'destroy'],
	[$session, 'gc']
);

/* set the cache expire to 30 minutes */
//session_cache_expire(1);
//ini_set("session.gc_maxlifetime", "10");
//$cache_expire = session_cache_expire();

session_start();

// Set JS Min file, ?jsMin=no/yes/clear
if (array_key_exists('devMode', $_GET)) {
	if (in_array($_GET['devMode'], ['','clear'])) unset($_SESSION['devMode']);
	else $_SESSION['devMode'] = 'yes';
}


/**
* Process normal and AJAX request
*
* do not call debug() before here , It posible mistake roles from table
*/

// Check user login state or new sign in
$R->user = UserModel::checkLogin();
if ($R->user->signInResult) {
	sendHeader('application/json');
	http_response_code($R->user->ok ? _HTTP_OK :_HTTP_ERROR_UNAUTHORIZED);
	die(SG\json_encode($R->user));
} else if ($R->user->signInErrorMessage) {
	$R->message->signInErrorInSignForm = $R->user->signInErrorMessage;
}

$R->user->admin = user_access('access administrator pages');

// Check site status
if (cfg('web.status') == 0 && !$R->user->admin) {
	die(R::View('site.maintenance'));
}

// Set header for AJAX request
if (_AJAX) {
	header('Content-Type: text/html; charset='.cfg('client.characterset'));
	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
}

// Hit counter and store counter/online
if (!_AJAX && mydb::table_exists('%counter_log%')) CounterModel::hit();
$R->counter = cfg('counter');

set_theme();

cfg('core.message',core_version_check());

// Initialize I am
i()->am;

// End of core process




/*************************************************************
* Core class and function library for core process
*
* Manage Resource, Controller and Utilities function
**************************************************************/

//---------------------------------------
// Class R :: Core resource
//---------------------------------------
class R {
	public $request;
	public $appAgent = NULL;
	public $setting;
	public $options;
	public $counter;
	public $timer;
	public $user;
	public $mydb;
	public $message;

	function __construct() {
		$this->setting = (Object) [];
		$this->options = (Object) [];
		$this->message = (Object) [];
	}

	public static function Option($key = NULL, $value = NULL) {
		if (isset($key) && isset($value)) $GLOBALS['R']->options->{$key} = $key;
		return isset($GLOBALS['R']->options->{$key}) ? $GLOBALS['R']->options->{$key} : NULL;
	}

	public static function Setting($key = NULL, $value = NULL) {
		if (empty($key)) {
			return $GLOBALS['R']->setting;
		} else if (is_object($key)) {
			$GLOBALS['R']->setting = $key;
			return $GLOBALS['R']->setting;
		} else if (isset($key) && isset($value)) {
			$GLOBALS['R']->setting->{$key} = $key;
			return isset($GLOBALS['R']->setting->{$key}) ? $GLOBALS['R']->setting->{$key} : NULL;
		}
	}

	public static function Module($moduleName, $className = NULL) {
		$paraArgs = func_get_args();
		$rName = $paraArgs[0];
		$rName = 'module.'.$rName;
		$paraArgs[0] = $rName;
		$ret = call_user_func_array('load_resource', $paraArgs);
		return $ret;
	}

	public static function Model($modelName) {
		$paraArgs = func_get_args();
		$rName = $paraArgs[0];
		$rName = 'r.'.$rName;
		$paraArgs[0] = $rName;
		$ret = call_user_func_array('load_resource', $paraArgs);
		return $ret;
	}

	public static function View($viewName) {
		$paraArgs = func_get_args();
		$rName = $paraArgs[0];
		$rName = 'view.'.$rName;
		$paraArgs[0] = $rName;
		$ret = call_user_func_array('load_resource', $paraArgs);
		return $ret;
	}

	public static function Page($pageName) {
		$paraArgs = func_get_args();
		$paraArgs[0] = 'page.'.$paraArgs[0];
		$ret = call_user_func_array('load_resource', $paraArgs);
		return $ret;
	}

	public static function PageWidget($packageName, $args = []) {
		list($className, $found, $fileName, $resourceType) = SgCore::loadResourceFile('page.'.$packageName);
		if ($found && class_exists($className) && method_exists($className, 'build')) {
			// Found page widget
			return (new $className(...$args))->build();
		} else if ($found && function_exists($className)) {
			// Found page function version, function name = className
			array_unshift($args, new Module());
			$ret = $className(...$args);
			return new Widget(['exeClass' => $args[0], 'child' => $ret]);
		} else {
			return new ErrorMessage([
				'responseCode' => _HTTP_ERROR_NOT_FOUND,
				'text' => 'PAGE NOT FOUND',
			]);
		}
	}

	public static function On($eventName) {
		$paraArgs = func_get_args();
		$ret = NULL;
		if (is_string($paraArgs[0])) {
			$paraArgs[0] = 'on.'.$paraArgs[0];
			$ret = call_user_func_array('load_resource', $paraArgs);
		}
		return $ret;
	}

	public static function Manifest($modulename) {
		$loadResult = SgCore::loadResourceFile('manifest.'.$modulename);
		return $loadResult;
	}

	public static function Asset($assetName) {
		// assetName Format : project:name.ext
		// assetFile Format : {module}.name.ext
		// Location Folder : modules/{}/template/assets, modules/{module}/assets, core/assets
		list($moduleName, $assetName) = explode(':', $assetName);
		// debugMsg($assetName);
		$packageName = 'asset:'.$moduleName.'/'.$assetName;
		// $found = false;
		list($funcName, $found, $fileName, $resourceType, $resultContent) = SgCore::loadResourceFile($packageName);
		return $resultContent;
	}
}

//---------------------------------------
// Class SgCore :: Core Class
//---------------------------------------
class SgCore {
	/**
	* Find template location
	* @param String $filename
	* @param String ext_folder Each folder seperate by ;
	* @return Mixed False on file not found and file location on found
	*/
	static function getTemplate($filename = NULL, $ext_folder = NULL) {
		if (empty($filename)) return false;
		$theme_folder = [];
		if ($ext_folder) {
			foreach ( explode(';',_CORE_FOLDER) as $folder ) {
				$theme_folder[] = $folder.'/'.$ext_folder.'/'.$GLOBALS['theme'].'/';
				$theme_folder[] = $folder.'/'.$ext_folder.'/default/';
			}
		}
		$theme_folder = array_unique(array_merge($theme_folder,cfg('theme.template')));
		$result = false;
		foreach ($theme_folder as $folder) {
			$load_file = $folder.'tpl.'.$filename.'.php';
			if (file_exists($load_file)) {
				$result = $load_file;
				break;
			}
		}

		if (debug('template')) {
			echo '<br />load template <b>'.$filename.'</b>'.($ext_folder?' width extension folder '.$ext_folder:'');
			echo $result ? ' found <b>'.$result.'</b><br />' : ' <font color=red>not found</font><br />';
			print_o($theme_folder,'$theme_folder',1);
		}
		return $result;
	}

	/**
	* Load configuration from file and store into cfg
	* @param Mixed $config_file
	* @param Mixed $folder
	*/
	static function loadConfig($config_file = NULL, $folders = ['./core/assets/conf']) {
		$configArray = [];

		if (is_array($config_file)) {
			// merge array config to current config value
			$configArray = $config_file;
		} else if (is_string($config_file)) {
			// debugMsg($folder);
			// debugMsg($folder, '$srcFolder');
			if (is_string($folders)) $folders = explode(';',$folders);
			list($a,$module,$configExt) = explode('.',$config_file);
			//debugMsg('$module = '.$module.' $configExt = '.$configExt);
			// if (i()->username == 'softganz') {
			// 	debugMsg($folders, '$folders');
			// }

			foreach ($folders as $folder) {
				$each_config_file = $folder.'/'.$config_file;
				//debugMsg('$each_config_file = '.$each_config_file);
				// echo '$each_config_file = '.$each_config_file.'<br />';
				if ( file_exists($each_config_file) and is_file($each_config_file) ) {
					// if (i()->username == 'softganz') debugMsg('START LOAD CONFIG : '.$each_config_file);
					if ($configExt == 'php') {
						include($each_config_file);
						if (isset($cfg) && is_array($cfg)) {
							$configArray = $cfg;
						}
					} else if ($configExt == 'json') {
						$jsonString = file_get_contents($each_config_file);

						// Merge json config file to current config
						// Module json config file was load after database, so it less important than database
						// Current cfg($module) is config from file conf.???.php and config from table variable
						$jsonValue = SG\json_decode($jsonString, cfg($module));

						// if (i()->username == 'softganz') {
						// 	debugMsg('LOAD JSON : '.$each_config_file);
						// 	debugMsg('<pre>'.$jsonString.'</pre>');
						// 	debugMsg(\json_decode($jsonString),'\json_decode($jsonString)');
						// 	debugMsg($jsonValue, '$jsonValue');
						// 	debugMsg(cfg($module), '$cfg['.$module.']');
						// }

						if (isset($jsonValue) && is_object($jsonValue)) {
							cfg($module, $jsonValue);
						}
					}
					break;
				}
			}
		}

		// Add each config key to cfg(), except conf.[module].json
		foreach ($configArray as $configKey => $configValue) {
			unset($jsonValue);
			if (is_array($configValue)) {
				cfg($configKey,$configValue);
			} else if (is_string($configValue) && preg_match('/^\{/', trim($configValue))) {
				$jsonValue = SG\json_decode($configValue, cfg($configKey));
				//debugMsg($jsonValue, '$jsonValue['.$configKey.']');
				if (isset($jsonValue) && is_object($jsonValue)) {
					cfg($configKey, $jsonValue);
				}
			} else {
				cfg($configKey, $configValue);
			}
		}
	}

	/**
	* Find and load template
	* @param String $filename
	* @param String ext_folder Each folder seperate by ;
	* @param Boolean $show_result
	* @return String Result from template file
	*/
	static function loadTemplate($filename = NULL, $ext_folder = NULL, $show_result = true) {
		$template_file = SgCore::getTemplate($filename, $ext_folder);
		if (!$template_file) return;
		$ret = '';
		if ($show_result) {
			require($template_file);
		} else {
			ob_start();
			require($template_file);
			$ret = ob_get_contents();
			ob_end_clean();
		}
		return $ret;
	}

	/**
	* Load Resource File and return array
	* @param String $packageName exp [form/]module[.submodule].method
	* @param Boolean $debugResourceFile
	* @return Mixed
	*/
	static function loadResourceFile($packageName, $debugResourceFile = false) {
		static $loadCount = 0;
		static $debugFunc = [];
		static $loadFiles = [];
		static $loadCfg = [];

		$srcPackageName = $packageName;
		$resourceFileToLoad = '';
		$found = false;
		$resourceType = '';
		$resultContent = NULL;
		$coreFolder = rtrim(_CORE_FOLDER,'/');
		$mainFolder = '';
		$paths = [];
		$fileName = '';
		$funcName = NULL;
		$isDebugable = true;
		$debugLoadfile = debug('load') || $debugResourceFile;
		$template = cfg('template');
		if (cfg('template.add')) {
			$template = cfg('template.add').';'.$template;
		}

		$loadCount++;

		// Remove .php extension
		$packageName = preg_replace('/\.php$/i', '', $packageName);

		if (preg_match('/^(r|widget|view|page|api|on|manifest|module)\.(.*)/i',$packageName,$out)) {
			// Begin with keyword and follow by .
			list(, $resourceType, $package) = $out;
			$request = explode('.',$package);
			$module = $request[0];
		} else if (preg_match('/^(.*)\:(.*)/', $packageName, $out)) {
			// Begin with keyword and follow by :
			// Have / (folder) in package name
			list(, $resourceType, $package) = $out;
			if (preg_match('/^(.*)\/(.*)/', $package, $out)) {
				list(, $packageFolder, $package) = $out;
			}
			$request = explode('.',$package);
			if ($resourceType === 'asset') {
				$module = $packageFolder;
			} else {
				$module = $request[0];
			}
			// if ($debugLoadfile) {
			// 	debugMsg('resourceType = <b>'.$resourceType.'</b>, packageName = <b>'.$package.'</b>, packageFolder = <b>'.$packageFolder.'</b>');
			// 	debugMsg($request, '$request');
			// }
		} else {
			return false;
		}

		$subModule = isset($request[1]) ? $request[1] : NULL;

		$loadAction = in_array($resourceType, ['asset']) ? 'content' : 'include';

		if ($debugLoadfile) $caller = get_caller(__FUNCTION__);
		// debugMsg($caller,'$caller');

		$debugStr = '<div>Debug of '.__FUNCTION__.'() #'.$loadCount.' in <b>'.($caller['class'] ? $caller['class'] : '').($caller['type'] ? $caller['type'] : '').($caller['function'] ? $caller['function'].'(\''.$srcPackageName.'\')' : '').'</b> from '.$caller['file'].' line '.$caller['line'].' '
			. '<a href="#" onclick="$(this).next().toggle();return false;">Caller</a><div class="loadfunction__detail -hidden" style="border: 1px #ccc solid; margin: 0 8px; padding: 8px; border-radius: 8px;">'.(isset($caller['from'])?'Call from '.$caller['from']:'').'</div>'._NL
			.'</div>'._NL
			. 'Start load <b>'.($resourceType?' Resource '.strtoupper($resourceType).'':'Page').'</b> from package <b>'.$package.'</b> '._NL
			. 'module = <b>'.$module.'</b>'.($subModule ? ' , Sub Module = <b>'.$subModule.'</b>' : '').'<br />'._NL;

		$importOnly = $caller['function'] === 'import';
		if (is_dir('./modules/'.$module)) $mainFolder .= '.;';
		$mainFolder .= $coreFolder;

		if (in_array($resourceType, ['r', 'widget', 'view', 'page', 'on', 'asset']) && $template) {
			foreach (explode(';', $template) as $item)
				if ($item) $paths[] = 'modules/'.$module.'/template/'.trim($item);
		}

		switch ($resourceType) {
			case 'manifest' : // Manifest Resource
				$fileName = 'manifest.';
				if (is_dir(_CORE_FOLDER.'/modules/'.$module)) {
					$paths[] = 'modules/'.$module;
				} else if (is_dir(_CORE_MODULE_FOLDER.'/'.$module)) {
					$paths[] = 'core/modules/'.$module;
				} else {
					$paths[] = 'core/modules/system';
				}
				break;

			case 'module' :
				$fileName = 'module.';
				$funcName = 'module_';
				if (is_dir(_CORE_FOLDER.'/modules/'.$module)) {
					$paths[] = 'modules/'.$module;
				} else if (is_dir(_CORE_MODULE_FOLDER.'/'.$module)) {
					$paths[] = 'core/modules/'.$module;
				} else {
					$paths[] = 'core/modules/system';
				}
				break;

			case 'widget' : // Widget Resource
				$fileName = 'widget.';
				if ($subModule) $paths[] = 'modules/'.$module.'/'.$subModule.'/widgets';
				if ($subModule) $paths[] = 'modules/'.$module.'/'.$subModule;
				$paths[] = 'modules/'.$module.'/widgets';
				if (is_dir(_CORE_MODULE_FOLDER.'/'.$module)) {
					if ($subModule) $paths[] = 'core/modules/'.$module.'/'.$subModule;
					$paths[] = 'core/modules/'.$module.'/widgets';
				} else {
					$paths[] = 'core/widgets';
				}
				// debugMsg($paths, '$paths');
				break;

			case 'model' : // Model Resource
				$fileName = 'model.';
				$paths[] = 'modules/'.$module.'/template/'.$template;
				if ($subModule) $paths[] = 'modules/'.$module.'/'.$subModule.'/models';
				$paths[] = 'modules/'.$module.'/models';
				if (is_dir(_CORE_MODULE_FOLDER.'/'.$module)) {
					if ($subModule) $paths[] = 'core/modules/'.$module.'/'.$subModule.'/models';
					$paths[] = 'core/modules/'.$module.'/models';
				}
				$paths[] = 'core/models';
				break;

			// @deprecated
			case 'r' : // Model Resource
				$fileName = 'r.';
				$funcName = 'r_';
				$paths[] = 'modules/'.$module.'/r';
				if (is_dir(_CORE_MODULE_FOLDER.'/'.$module)) {
					$paths[] = 'core/modules/'.$module.'/r';
				}
				$paths[] = 'core/models';
				break;

			// @deprecated
			case 'view' : // View Resource
				$fileName = 'view.';
				$funcName = 'view_';
				$className = 'View'.implode('', array_map(function ($v) {return strtoupper(substr($v, 0,1)).strtolower(substr($v,1));},$request));
				//$paths[]='modules/'.$module;
				if ($subModule) $paths[] = 'modules/'.$module.'/'.$subModule;
				$paths[] = 'modules/'.$module.'/default';
				$paths[] = 'modules/'.$module;
				if (is_dir(_CORE_MODULE_FOLDER.'/'.$module)) {
					if ($subModule) $paths[] = 'core/modules/'.$module.'/'.$subModule;
					$paths[] = 'core/modules/'.$module.'/default';
					$paths[] = 'core/modules/'.$module;
				}
				$paths[] = 'core/view';
				break;

			case 'on' : // Event Resource
				$fileName = 'on.';
				$funcName = 'on_';
				$paths[] = 'modules/'.$module.'/r';
				$paths[] = 'core/modules/'.$module.'/r';
				$paths[] = 'core/models';
				break;

			case 'api' : // Page Resource
				$fileName = 'api.';
				$className = implode('', array_map(function ($v) {return strtoupper(substr($v, 0,1)).strtolower(substr($v,1));},$request)).'Api';

				$paths[] = 'modules/'.$module.'/template/'.$template;
				if ($subModule) {
					if (isset($request[2]) && is_string($request[2])) $paths[] = 'modules/'.$module.'/'.$subModule.'/'.$request[2];
					$paths[] = 'modules/'.$module.'/'.$subModule;
				}
				$paths[] = 'modules/'.$module.'/api';
				if ($subModule) {
					$paths[] = 'core/modules/'.$module.'/'.$subModule;
				}
				$paths[] = 'core/modules/'.$module.'/api';
				$paths[] = 'core/modules/api';
				break;

			case 'page' : // Page Resource
				$fileName = 'page.';
				$funcName = ''; // for page function of old version
				$className = implode('', array_map(function ($v) {return strtoupper(substr($v, 0,1)).strtolower(substr($v,1));},$request));

				if ($subModule) {
					if (isset($request[2]) && is_string($request[2])) $paths[] = 'modules/'.$module.'/'.$subModule.'/'.$request[2];
					$paths[] = 'modules/'.$module.'/'.$subModule;
				}
				$paths[] = 'modules/'.$module.'/default';
				$paths[] = 'modules/'.$module;

				// Is in core module
				if (is_dir(_CORE_MODULE_FOLDER.'/'.$module)) {
					if ($subModule) {
						if (isset($request[2]) && is_string($request[2])) $paths[] = 'core/modules/'.$module.'/'.$subModule.'/'.$request[2];
						$paths[] = 'core/modules/'.$module.'/'.$subModule;
					}
					$paths[] = 'core/modules/'.$module.'/default';
					$paths[] = 'core/modules/'.$module;
				} else {
					$paths[] = 'core/modules/system';
				}
				break;

			case 'package':
				$paths[] = 'modules/'.$packageFolder;
				$paths[] = 'core/'.$packageFolder;
				break;

			case 'asset':
				$paths[] = 'modules/'.$packageFolder.'/assets';
				$paths[] = 'core/'.$packageFolder.'/assets';
				break;
		}

		// Load module configuration file in json format
		if (!in_array($module, $loadCfg)) {
			$cfgPaths = [$coreFolder.'/modules/'.$module, $coreFolder.'/core', '.'];
			$cfgFile = 'conf.'.$module.'.json';
			// debugMsg($cfgPaths,'$cfgPaths');
			foreach ($cfgPaths as $path) {
			 	SgCore::loadConfig($cfgFile, $path);
			}
			$loadCfg[] = $module;
		}


		$fileName .= implode('.',$request);
		if (!in_array($resourceType, ['asset'])) {
			$fileName .= (preg_match('/\.php$/i', $package) ? '' : '.php');
		}
		if(!is_null($funcName)) $funcName .= implode('_',$request);
		if ((($funcName && function_exists($funcName)) || ($className && class_exists($className)) ) && array_key_exists($packageName, $loadFiles)) {
			// Resource file was loaded
			$found = true;
			$debugStr .= '<font color="green">Function '.$funcName.'() was already load.</font><br />'._NL;
		} else {
			// Load resource file
			if (cfg('template.add')) $debugStr .= 'template.add='.cfg('template.add').'<br />';

			$debugStr .= '<div>Request <b>'.implode(($resourceType == 'page' ? '/' : '.'),$request).'</b> and load filename <b>'.$fileName.'</b>'
				. ($funcName ? ' then call function <b>'.$funcName.'() </b>' : '')
				. '</div>'._NL;

			$debugStr .= 'Main folder = <b>'.$mainFolder.'</b><br />';
			$debugStr .= 'Path = <b>'.implode('; ',$paths).'</b><br />'._NL;

			foreach (explode(';', $mainFolder) as $mainFolderItem) {
				// debugMsg('$mainFolderItem = '.$mainFolderItem.' $paths = '.implode(';',$paths));
				foreach ($paths as $path) {
					$folderName = $mainFolderItem.'/'.$path;
					$isPathExists = is_dir($folderName);
					$debugStr .= 'Locate file in folder '.$folderName.' <b>(folder '.($isPathExists ? 'exists' : 'not exists').')</b><br />'._NL;
					if (!$isPathExists) continue;

					$resourceFileToLoad = $folderName.'/'.$fileName;
					// debugMsg('$resourceFileToLoad = '.$resourceFileToLoad);

					// Check function exists, if not set function return found
					if (file_exists($resourceFileToLoad)) {
						$loadFiles[$packageName] = $resourceFileToLoad;
						if ($loadAction == 'content') {
							$resultContent = file_get_contents($resourceFileToLoad);
							$found = true;
							$debugStr .= '<span style="color:green;">Found and get file content <b>'.$resourceFileToLoad.'</b></span><br />'._NL;
						} else {
							require_once($resourceFileToLoad);
							// debugMsg($loadFiles,'$loadFiles');

							// Set Debug Load Resource from $debugLoadResource in resource file
							// If you don't want to show in debug mode, add $debugLoadResource = false in top of resource file
							$debugFunc[$funcName] = $isDebugable = !(isset($debugLoadResource) && $debugLoadResource === false);
							$debugStr .= '<span style="color:green;">Found and load file <b>'.$resourceFileToLoad.'</b></span><br />'._NL;

							// Check function exists, if not set function return found
							if ($importOnly) {
								$found = true;
							} else if ($className && class_exists($className)) {
								$debugStr .= '<span style="color:green;">Found Execute class <b>'.$className.'()</b>.</span><br />'._NL;
								$found = true;
								break;
							} else if ($funcName && function_exists($funcName)) {
								$debugStr .= '<span style="color:green;">Found Execute function <b>'.$funcName.'()</b>.</span><br />'._NL;
								$found = true;
								break;
							} else if ($funcName || $className) {
								// If has $funcName or $className but not found class or function
								// Then continue load next file
								// If not effect, please remove

								// if (class_exists($className)) {
								// 	$debugStr .= '<span style="color:green;">Found Execute class '.$className.'().</span><br />'._NL;
								// 	$found = true;
								// 	break;
								// } else if (function_exists($funcName)) {
								// 	$debugStr .= '<span style="color:green;">Found Execute function '.$funcName.'().</span><br />'._NL;
								// 	$found = true;
								// 	break;
								// } else {
								// 	// Execute function '.$funcName.'() is not exist.
								// }
							} else {
								$found = true;
							}
						}
					} else {
						$resourceFileToLoad = '';
					}
					if ($found) break;
				}
				if ($found) break;
			}
		}

		// $debugStr .= print_o(debug_backtrace(), '$backtrace');


		if ($debugLoadfile && ($isDebugable || debug('force'))) debugMsg($debugStr);

		if (class_exists($className)) {
			return [$className, $found, $resourceFileToLoad, $resourceType];
		} else {
			return [$funcName, $found, $resourceFileToLoad, $resourceType, $resultContent];
		}
	}

	/**
	* Load widget request from tag <div class="widget" ></div>
	*
	* @param String $name , widget-request , widget-addons
	* @param Object $para
	* @return String
	*/
	static function loadWidget($name, $para) {
		static $lists = [];
		static $folders = [];
		static $counter = 0;

		$result = '';
		// $result.='name='.$name.'<br />'.print_o($para,'$para');

		if (!empty($para->{'widget-request'})) {
			$wpara = '';
			foreach ($para as $k => $v) {
				if (preg_match('/widget-|style/',$k)) continue;
				list(,$dk) = explode('-',$k);
				if ($dk) $wpara .= '/'.$dk.'/'.$v;
			}
			//$result.='gadget #'.(++$counter).' para='.($para->{'widget-request'}).$wpara;
			$widget_result = gadget($para->{'widget-request'}.$wpara);
		} else if (!empty($para->{'data-url'})) {
			// Load widget from page url
			list($module) = explode('/',$para->{'data-url'});
			$pageName = str_replace('/', '.', $para->{'data-url'});

			$paraArgs = [];
			foreach ($para as $k => $v) {
				if ($k == 'data-url') continue;
				if (preg_match('/data-para-/', $k) && $v != '') {
					$paraArgs[] = $v;
				}
			}
			R::Manifest($module);
			$widget_result = R::PageWidget($pageName, $paraArgs);
			// debugMsg('widget module = '.$module.' $pageName = '.$pageName);
			// $widget_result .= print_o($paraArgs,'$paraArgs');
			// debugMsg($widget_result,'$widget_result');
		} else {
			// Load widget from filename widget.name.php function=widget_name
			if (empty($folders)) {
				$folders = cfg('theme.template');
				$folders[] = _CORE_FOLDER.'/core/widgets/';
			}

			$is_debug = debug('widget');

			if ($is_debug) debugMsg('<b>Start load widget '.$name.'</b> from widget folders '.implode(' , ',$folders));

			foreach ($folders as $folder) {
				$filename = $folder.'widget.'.$name.'.php';
				if (file_exists($filename)) {
					$lists[] = $name;
					if ($is_debug) debugMsg('<em style="color:#f60;font-weight:bold;">Load widget file '.$filename.' found</em>');
					include_once($filename);
					break;
				}
				if ($is_debug) debugMsg('Load widget file '.$filename.' not found.');
			}

			$func_name = 'widget_'.$name;
			if (function_exists($func_name)) {
				list($widget_result) = call_user_func($func_name, $para);
			}
			// $widget_result = 'Widget '.$widget_result;
		}

		if (is_object($widget_result) && method_exists($widget_result, 'build')) {
			unset($widget_result->appBar);
			$widget_result = $widget_result->build();
		}

		// Result of $widget_result must be string
		$widget_result = trim($widget_result);

		if (!empty($para->{'data-header'}) && !in_array(strtolower($para->{'option-header'}), ['0','no'])) {
			$header = '<h2>'.($para->{'data-header-url'}?'<a href="'.$para->{'data-header-url'}.'">':'').'<span>'.SG\getFirst($para->{'data-header'},$para->id).'</span>'.($para->{'data-header-url'}?'</a>':'').'</h2>'._NL;
		}

		if (trim($para->{'data-option-replace'}) == 'yes') {
			$result .= $widget_result;
		} else {
			$result .= _NL.'<!-- Start widget '.$name.' -->'._NL
				. $header
				. ($widget_result ? '<div class="widget-content container">'._NL. $widget_result._NL._NL. '</div>'._NL : '')
				. '<!-- End of widget '.$name.' -->'._NL;
		}
		return $result;
	}

	/**
	* Load extension file
	* @param String $name
	*/
	static function loadExtension($name) {
		static $lists = [];
		static $folders = [];
		if (in_array($name,$lists)) return;
		if (empty($folders)) {
			$folders=cfg('theme.template');
			$folders[]=cfg('core.location').'extensions/';
		}
		$is_debug=debug('extension');
		if ($is_debug) echo 'load extension '.$name.'<br />';
		if ($is_debug) print_o($folders,'extension folders',1);
		foreach ($folders as $folder) {
			$file=$folder.$name.'.extension.php';
			if ($is_debug) echo 'load extension file '.$file;
			if (file_exists($file)) {
				$lists[]=$name;
				if ($is_debug) echo ' <em style="color:#f60;">found</em><br />';
				include_once($file);
				break;
			}
			if ($is_debug) echo ' not found<br />';
		}
	}

	/**
	* Do module method from request menu item
	* @param Array $menu
	* @return String
	*/
	static function processMenu($menu, $prefix = 'page') {
		$module = $menu['call']['module'];
		$auth_code = $menu['access'];
		$is_auth = user_access($auth_code);
		$debugLoadfile = debug('load');

		// Create self object
		// debugMsg('$module = '.$module);
		if (class_exists($module)) {
			$exeClass = new $module($module);
			$exeClass->module = $module;
		} else {
			$exeClass = new Module($module);
			$exeClass->module = $module;
			cfg('page_id', $module);
		}

		R::Module($module.'.init', $exeClass);

		$options = SG\json_decode($menu['options']);
		$verify = $options->verify ? R::Model($options->verify,$exeClass) : true;

		if ($verify === false) {
			return [$exeClass, true, message('error', 'Access denied', NULL)];
		} else if (is_string($verify)) {
			return [$exeClass, true, $verify];
		} else if ($is_auth === false) {
			return [$exeClass, true, message('error', 'Access denied', NULL, $options->signform)];
		}

		$menuArgs = array_merge([$module], is_array($menu['call']['arg']) ? $menu['call']['arg'] : [] );

		// Load request from package function file page.package.method[.method].php
		$funcName = $funcArg = [];
		foreach ($menuArgs as $value) {
			if (is_numeric($value) || $value == '*' || preg_match('/^[0-9]/', $value)) break;
			$funcName[] = $value;
		}

		$found = false;

		do {
			$funcArg = array_slice($menuArgs, count($funcName));
			$pageFile = $prefix.'.'.implode('.', $funcName);


			if ($debugLoadfile) {
				debugMsg('<div style="color: blue;">Load Page <b>'.$pageFile.'.php</b> in SgCore::processMenu()</div>');
				// debugMsg($funcName,'$funcName');
				// debugMsg($funcArg,'$funcArg');
			}

			$loadResult = list($retClass, $found, $filename) = SgCore::loadResourceFile($pageFile);

			if ($debugLoadfile) {
				// debugMsg(''.($found?'Found ':'Not found ').'<b>'.$retClass.'</b> in <b>'.$pageFile.'</b><br />');
				// debugMsg($loadResult,'$loadResult');
				debugMsg('<div style="color: blue;">Load Page <b>'.$pageFile.'.php</b> complete.</div>');
			}

			array_pop($funcName);
		} while (!$found && count($funcName) >= 1);

		if ($found && class_exists($retClass) && method_exists($retClass, 'build')) {
			$ret = (new $retClass(...$funcArg))->build();
			if ($ret->exeClass) {
				$exeClass = $ret->exeClass;
				$exeClass->module = $module;
			}
		} else if ($found && function_exists($retClass)) {
			$ret = $retClass(...array_merge([$exeClass], $funcArg));
		} else $ret = NULL;

		return [$exeClass, $found, $ret];
	}

	static function processIndex($page = 'index', $text = NULL) {
		global $request_result;
		$request_result = $text;
		$result = SgCore::loadTemplate($page, NULL, false);
		return $result;
	}

	/**
	* Process variable and replace with value
	* @param String $html
	* @return String
	*/
	static function processVariable($html) {
		$vars = [
			'q' => q(),
			'domain' => cfg('domain'),
			'url' => cfg('url'),
			'upload_folder' => cfg('upload_folder'),
			'theme' => cfg('theme'),
			'_HOME_STICKY' => _HOME_STICKY,
		];

		// Searching textarea and pre
		preg_match_all('#\<textarea.*\>.*\<\/textarea\>#Uis', $html, $foundTxt);
		preg_match_all('#\<pre.*\>.*\<\/pre\>#Uis', $html, $foundPre);

		// replacing both with <textarea>$index</textarea> / <pre>$index</pre>
		$html=str_replace($foundTxt[0], array_map(function($el){ return '<textarea>'.$el.'</textarea>'; }, array_keys($foundTxt[0])), $html);
		$html=str_replace($foundPre[0], array_map(function($el){ return '<pre>'.$el.'</pre>'; }, array_keys($foundPre[0])), $html);

		// Replace {$var} with $vars[var]
		$html=preg_replace_callback(
			'#{\$(.*?)}#',
			function($match) use ($vars){
				return $vars[$match[1]];
			},
			$html
		);

		// Replace {url:} with url()
		$html=preg_replace_callback(
			'#{(url\:)(.*?)}#',
			function($match){
				return url($match[2]);
			},
			$html
		);

		// Replace {tr:} with url()
		$html=preg_replace_callback(
			'#{(tr\:)(.*?)}#',
			function($match){
				$para=preg_split('/,/', $match[2]);
				return tr($para[0],$para[1]);
			},
			$html
		);

		// Replacing back with content
		$html=str_replace(array_map(function($el){ return '<textarea>'.$el.'</textarea>'; }, array_keys($foundTxt[0])), $foundTxt[0], $html);
		$html=str_replace(array_map(function($el){ return '<pre>'.$el.'</pre>'; }, array_keys($foundPre[0])), $foundPre[0], $html);
		return $html;
	}

	/*
	* Process Setting
	*
	* https://domain.com?setting:[app[=yes]][theme[=name]]
	* https://domain.com?options:option1[,option2[,option3[...]]]
	*/
	static function processSetting(&$R) {
		$scriptName = str_replace('/', '.', ltrim($_SERVER['SCRIPT_NAME'],'/'));
		$cookie_id = substr($scriptName, strpos($scriptName, '.') + 1, strpos($scriptName, '.',strpos($scriptName, '.')+1) - strpos($scriptName, '.') - 1); // folder/domain.com/index.php => domain
		$cookieKey = _ON_LOCAL ? 'setting:'.$cookie_id : 'setting';
		$setting = isset($_COOKIE[$cookieKey]) ? json_decode($_COOKIE[$cookieKey]) : new \stdClass();
		$hasSetting = false;
		$hasOptions = false;

		//echo 'ID = '.$cookie_id. ' = '.$cookieKey.' VALUE = '.$_COOKIE[$cookieKey].'<br />';

		foreach (array_keys($_GET) as $key) {
			if (preg_match('/^options\:/', $key)) $hasOptions = $key;
		}
		if ($hasOptions) {
			foreach (explode(',', substr($hasOptions, 8)) as $value) {
			 	$R->option($value, $value);
			 }
		}

		if (isset($_GET['setting:'])) return R::setting($setting);

		foreach (array_keys($_GET) as $key) {
			if (preg_match('/^setting\:/', $key)) {$hasSetting = true;}
		}
		if (!$hasSetting) return R::setting($setting);

		if (isset($_GET['setting:app'])) {
			if (($getSettingApp = $_GET['setting:app']) && $getSettingApp != '{}') {
				$setting->app = SG\json_decode($getSettingApp);
			} else {
				unset($setting->app);
			}
		}

		$setting->theme = isset($_GET['setting:theme']) ? $_GET['setting:theme'] : NULL;
		if (empty($setting->theme)) unset($setting->theme);

		$settingJson = trim(json_encode($setting));
		if (in_array($settingJson, ['{}', 'null'])) {
			setcookie($cookieKey,'',time()-3600,cfg('cookie.path'),cfg('cookie.domain'));
			unset($_COOKIE[$cookieKey]);
		} else {
			setcookie($cookieKey, $settingJson, time()+365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));
		}
		return R::setting($setting);
	}

	/**
	 * Do request process from url address and return result in string
	 * @return String
	 */
	static function processController($loadTemplate = true, $pageTemplate = NULL) {
		global $page,$request_time,$request_process_time;
		$request = R()->request;
		$method_result = '';
		$request_result = '';
		$isLoadHomePage = false;
		$requestFilePrefix = 'page';
		$isDebugProcess = debug('process');

		if ($isDebugProcess) $process_debug = 'process debug of <b>'.$request.'</b> request<br />'._NL;

		if (isset($GLOBALS['message'])) $request_result .= $GLOBALS['message'];
		if (cfg('web.readonly')) $request_result .= message('status',cfg('web.readonly_message'));

		R()->timer->start($request);

		if (!isset($request) || empty($request) || ($request == 'home') || ($request == cfg('web.homepage'))) {
			// Check for splash page
		 	// Show splash if not visite site in time
			if (cfg('web.splash.time') > 0 && $splashPage = url_alias('splash') && empty($_COOKIE['splash'])) {
				cfg('page_id','splash');
				location('splash');
			}

			// Show home page
			$home = cfg('web.homepage');
			$isLoadHomePage = true;
			if (empty($home)) {
				ob_start();
				SgCore::loadTemplate('home');
				$request_result .= ob_get_contents();
				ob_end_clean();
				$request = '';
			} else {
				R()->request = $request = $home;
				q($request);
				// debugMsg('$home='.$home.' $request='.$request.' q()='.q()).' cfg(page_id)='.cfg('page_id');
				$manifest = R::Manifest(q(0));
				$menu = menu($request);
			}
		} else if ($request) {
			// Load Module Manifest
			// is API?
			if (preg_match('/^api\/(.*)/', $request, $out)) {
				$request = $out[1];
				q($request);
				$requestFilePrefix = 'api';
			}
			if (q(0)) $manifest = R::Manifest(q(0));
			if ($url_alias = url_alias($request)) {
				// check url alias
				$process_debug .= '<p><strong>'.$request.'</strong> is url alias of <strong>'.$url_alias->system.'</strong></p>';
				$request = $url_alias->system;
				q($request);
				$process_debug .= print_o(q(0,'all'),'$q');
				$manifest = R::Manifest(q(0));
				$menu = menu($request);
			} else if ($menu = menu($request)) {
				// debugMsg('Do request menu');
			} else {
				// Do request from R::Page
				//debugMsg('Do request from R::page');
				menu(q(0),q(0).' page',q(0),'__controller',1,true,'static');
				$menu = menu($request);
			}
		}

		if ($isDebugProcess  && $manifest) $process_debug .= 'Manifest module file : '.print_o($manifest,'$manifest').'<br />';

		// Load Page On Request
		if ($manifest[1] && $menu) { // This is a core version 4
			if ($isDebugProcess) $process_debug .= 'Load core version 4 <b>'.$request.'</b><br />';
			// list($exeClass, $found, $pageResultWidget) = SgCore::processMenu($menu);
		} else { // Page no manifest
			if ($isDebugProcess) $process_debug .= 'Load core version 4 on no manifest and no class<br />';
			// list($exeClass, $found, $pageResultWidget) = SgCore::processMenu($menu);
		}
		list($exeClass, $found, $pageResultWidget) = SgCore::processMenu($menu, $requestFilePrefix);

		// Set page id to home
		if ($isLoadHomePage) cfg('page_id','home');

		if ($found) {
			// Set splash page was show
			if (cfg('web.splash.time')) {
				setcookie('splash',true,time()+cfg('web.splash.time')*60,cfg('cookie.path'),cfg('cookie.domain')); // show splash if not visite site
			}

			// Build Widget Class to String
			if (is_object($pageResultWidget) && method_exists($pageResultWidget, 'build')) {
				// print_o($pageResultWidget,'$pageResultWidget',1);
				// Case widget, Call method build()
				$request_result = $pageResultWidget->build();
				// Create App Bar
				if ($pageResultWidget->appBar) {
					if (is_object($pageResultWidget->appBar) && method_exists($pageResultWidget->appBar, 'build')) {
						if ($pageResultWidget->appBar->removeOnApp && is_object(R()->appAgent)) {
							// don't show appBar
						} else {
							$exeClass->appBarText = $pageResultWidget->appBar->build();
						}
					} else if (is_object($pageResultWidget->appBar->title)) {
						$exeClass->theme->toolbar = $pageResultWidget->appBar->title;
						$exeClass->theme->title = $pageResultWidget->appBar->title;
					} else {
						$exeClass->theme->title = $pageResultWidget->appBar->title;
					}
					$exeClass->appBar = $pageResultWidget->appBar;
					$exeClass->sideBar = $pageResultWidget->sideBar;
				}

				if ($pageResultWidget->floatingActionButton) {
					$exeClass->floatingActionButton = $pageResultWidget->floatingActionButton;
				}
			} else if (is_array($pageResultWidget) || is_object($pageResultWidget)) {
				$request_result = $pageResultWidget;
			} else {
				// Result is String, join
				$request_result .= $pageResultWidget;
			}

			// Generate result by content type
			if (cfg('Content-Type') == 'text/xml') {
				die(process_widget($request_result));
			} else if (!_AJAX && is_array($request_result) && isset($request_result['location'])) {
				location($body['location']);
				die;
			} else if (_AJAX || is_array($request_result) || is_object($request_result)) {
				// print_o($request_result, '$request_result',1);

				// Check error result
				$ajaxError = (Object) ['responseCode' => NULL, 'text' => NULL];
				if (is_object($pageResultWidget) && $pageResultWidget->widgetName === 'ErrorMessage' && $pageResultWidget->responseCode) {
					$ajaxError->responseCode = $pageResultWidget->responseCode;
					$ajaxError->text = $pageResultWidget->text;
				} else if (is_object($request_result) && $request_result->responseCode) {
					$ajaxError->responseCode = $request_result->responseCode;
					$ajaxError->text = $request_result->text;
				} else if (is_array($request_result) && $request_result['responseCode']) {
					$ajaxError->responseCode = $request_result['responseCode'];
					$ajaxError->text = $request_result['text'];
				}
				// Send error with json
				if ($ajaxError->responseCode) {
					sendHeader('application/json');
					http_response_code($ajaxError->responseCode);
					die(SG\json_encode($ajaxError));
				}

				if (is_array($request_result) || is_object($request_result)) {
					sendHeader('application/json');
					$request_result = SG\json_encode($request_result);
				}

				// Show AppBar as Box Header
				if (is_object($pageResultWidget->appBar) && $pageResultWidget->appBar->boxHeader && method_exists($pageResultWidget->appBar, 'build')) {
					$pageResultWidget->appBar->showInBox = true;
					$request_result = $pageResultWidget->appBar->build() . $request_result;
				}

				die(debugMsg().process_widget($request_result));
			} else if (_HTML && (is_array($request_result) || is_object($request_result))) {
				die(process_widget(print_o($request_result,'$request_result')));
			} else if (_HTML) {
				die(process_widget($request_result));
			}
		} else {
			http_response_code(_HTTP_ERROR_NOT_FOUND);
			R::Model('watchdog.log','system','Page not found');
			// Set header to no found and noacrchive when url address is load function page
			if ($q == str_replace('.', '/', $package)) {
				header('HTTP/1.0 404 Not Found');
				head('<meta name="robots" content="noarchive" />');
			}
			$request_result .= '<div class="pagenotfound">
			<h1>Not Found</h1>
			<p>ขออภัย ไม่มีหน้าเว็บนี้อยู่ในระบบ</p><p>The requested URL <b>'.$_SERVER['REQUEST_URI'].'</b> was not found on this server.</p>'
			. (user_access('access debugging program') ? '<p><strong> Load file detail</strong><br />'.print_o($menuArgs,'$request').'<br />File : <strong>'.$mainFolder.$pageFile.'</strong><br />Routine : <strong>function '.$retFunc.'()</strong></p>' : '')
			. '<hr>
			<address>copyright <a href="http://'.$_SERVER['HTTP_HOST'].'">http://'.$_SERVER['HTTP_HOST'].'</a> Allright reserved.</address>
			</div>'._NL;
		}

		// Start Render Page, result is string
		$request_result = R::View('render.page',$exeClass,$request_result);
		$request_result = process_widget($request_result);

		R()->timer->stop($request);

		if ($isDebugProcess) $process_debug .= print_o($menu,'$menu');
		if ($isDebugProcess) $process_debug .= print_o(q(0,'all'),'$q');

		if (debug('menu')) debugMsg(menu(),'$menu');
		if ($isDebugProcess) debugMsg($process_debug.(isset($GLOBALS['process_debug'])?print_o($GLOBALS['process_debug']):''));
		$request_time[$request] = R()->timer->get($request,5);
		$request_process_time = $GLOBALS['request_process_time']+R()->timer->get($request);
		if (debug('timer')) debugMsg('Request process time : '.$request_process_time.' ms.'.print_o($request_time));

		if (debug('html')) debugMsg(htmlview($request_result,'html tag'));
		if (debug('config')) {
			$cfg = cfg();
			array_walk_recursive($cfg, '__htmlspecialchars');
			debugMsg($cfg,'cfg');
		}


		if ($pageTemplate) $page = $pageTemplate;
		else if (empty($page)) $page = 'index';
		if ($loadTemplate) echo SgCore::processIndex($page, $request_result);
		return $request_result;
	}

	/**
	* Set current language
	* @param String $lang
	* @param String
	*/
	static function setLang($lang = NULL) {
		//echo 'lang='.$_GET['lang'].'='.$_REQUEST['lang'].'='.post('lang').'='.$lang.'='.$_COOKIE['lang'];
		if ($lang) {
			// do nothing
		} else if ($lang=$_GET['lang']) {
			if ($lang=='clear') {
				setcookie('lang',NULL,time()-100,cfg('cookie.path'),cfg('cookie.domain'));
			} else {
				setcookie('lang',$lang,time()+10*365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));
			}
			//$_COOKIE['lang']=$lang;
			//echo 'lang='.$_REQUEST['lang'].'='.post('lang').'='.$lang.'='.$_COOKIE['lang'];
			//echo cfg('cookie.path').' '.cfg('cookie.domain');
		} else if (array_key_exists('lang', $_COOKIE) && $lang=$_COOKIE['lang']) {
			$lang=$_COOKIE['lang'];
		}
		cfg('lang',$lang);
		return $lang;
	}
}



//---------------------------------------
// Core Function
//---------------------------------------

/**
 * function R :: Access core resource
 */
function R() {return $GLOBALS['R'];}

/**
 * Import library in module folder and found
 * @param String $packageName ex. widget|page|view:module[.submodule], module/folder/file
 * @return boolean
 */
function import($packageName) {
	$found = false;
	list($funcName, $found, $fileName, $resourceType) = SgCore::loadResourceFile($packageName);
	return $found;
}

/**
 * Web Page Controller
 * Find and load web page from file
 */
function controller($loadTemplate = true, $pageTemplate = NULL) {
	return SgCore::processController($loadTemplate, $pageTemplate);
}

/**
 * Load Library
 * @param String $file
 * @param String $folder
 * @return Mixed String Library Filename or False
 */
function load_lib($file, $folder = NULL) {
	$libFile = _CORE_FOLDER.'/core/'.($folder ? $folder.'/' : '').$file;
	// echo 'LOAD LIB '.$libFile.(file_exists($libFile) ? ' COMPLETE' : ' <font color="red">NOT FOUND</font>').'<br />';
	if (file_exists($libFile)) {
		require_once($libFile);
		return $libFile;
	}

	return false;
}

/**
 * Load function in module folder and call it
 * @param String $module exp [form/]module[.submodule].method as package name
 * @param Mixed $parameter
 * @return Mixed
 */
function load_resource($packageName) {
	$ret = NULL;
	$args = func_get_args();

	$debugLoadfile = debug('load');
	$debugStr = '';

	$loadResult = list($funcName, $found, $fileName, $resourceType) = SgCore::loadResourceFile($packageName);
	// debugMsg('<b>'.($found ? 'FOUND' : 'NOT FOUND').' : '.$resourceType.'</b> FILE NAME = '.$fileName. ' FUNCTION NAME = '.$funcName);

	array_shift($args);
	if ($found) {
		if (class_exists($funcName)) {
			$debugStr .= '<span style="color:green;">Execute class <b>'.$funcName.'()</b> complete.</span>';

			//$args = array_merge([$exeClass], $funcArg);
			// debugMsg($args, '$args_resource');
			// if ($resourceType == 'page') array_shift($args);
			// debugMsg($args, '$args_resource');
			// debugMsg($args,'$args');
			$instance = new $funcName(...$args);

			// debugMsg('Loaded resource ans create result from class '.$funcName.' get_class = '.get_class($instance));

			//$instance->theme = (Object)['option' => (Object)[]];
			if (method_exists($instance, 'build')) {
				$ret = $instance->build();
			} else {
				$ret = 'No Build Method on '.get_class($instance);
			}
			//debugMsg('Result is '.(is_string($ret) ? $ret : 'Object'));
			//debugMsg($exeClass, '$exeClass');
			// $ret = (new $funcName())->build();
		} else if (function_exists($funcName)) {
			$debugStr .= '<span style="color:green;">Execute function <b>'.$funcName.'()</b> complete.</span>';
			$ret = call_user_func_array($funcName, $args);
		} else {
			$debugStr .= '<span style="color:red;">Execute function <b>'.$funcName.'()</b> is not exist.</span>';
		}
	} else {
		$debugStr .= '<span style="color:red;">Resource <b>'.$packageName.'</b> is not exist.</span>';
	}

	if ($debugLoadfile) debugMsg($debugStr);
	//debugMsg('Load resource '.$funcName.' '.(function_exists($funcName) ? 'FUNCTION EXISTS':'FUNCTION NOT EXISTS'));
	//debugMsg($loadResult,'$loadResult');
	return $ret;
}

/**
 * Process widget tag <div class="widget WidgetName" ...></div>
 * @param String $html
 * @return String
 */
function process_widget($html) {
	if (!is_string($html)) return $html;

	// Replace {$var} with vars and {url:} with url()
	$html = SgCore::processVariable($html);

	$html_recent = $html;
	$result = '';
	$widget_ldq = '<div class="widget ';
	$widget_rdq = '</div>';


	/*
	 * $matches[0] = ข้อความก่อนหน้า+widget
	 * $matches[1] = ข้อความก่อนหน้า (ไม่รวม widget)
	 * $matches[2] = widget tag <div class="widget ....></div>
	 * $matches[3] = widget name
	 * $matches[4] = widget attribute
	 * $matches[5] = widget inner html
	 */

	// Searching textarea and pre
	preg_match_all('#\<textarea.*\>.*\<\/textarea\>#Uis', $html, $foundTextarea);
	preg_match_all('#\<pre.*\>.*\<\/pre\>#Uis', $html, $foundPre);


	// replacing both with <textarea>$index</textarea> / <pre>$index</pre>
	if ($foundTextarea[0]) {
		$html = str_replace($foundTextarea[0], array_map(function($el) { return '<textarea>'.$el.'</textarea>'; }, array_keys($foundTextarea[0])), $html);
		//debugMsg($foundTextarea, '$foundTextarea');
	}
	if ($foundPre[0]) {
		$html = str_replace($foundPre[0], array_map(function($el) { return '<pre>'.$el.'</pre>'; }, array_keys($foundPre[0])), $html);
		//debugMsg($foundPre, '$foundPre');
	}

	preg_match_all('/(.*)('.$widget_ldq.'(.*)\"[\s](.*)>(.*)'.preg_quote($widget_rdq,'/').')(.*)/msU', $html, $matches);

	// debugMsg($matches, '$matches');




	// $pattern_short = '{<div\s+class="widget\s*>((?:(?:(?!<div[^>]*>|</div>).)++|<div[^>]*>(?1)</div>)*)</div>}misU';
	// $matchcount = preg_match_all($pattern_short, $html, $matches);

	// debugMsg($matches, '$matches');
	if ($matches[2]) {
		foreach ($matches[1] as $idx => $htmltag) {
			$widget_tag = $matches[2][$idx];
			$widget_attr = trim($matches[4][$idx]);
			list($widget_name) = explode(' ',trim(strtolower($matches[3][$idx])));

			$pattern = '/([\\w\-]+)\s*=\\s*("[^"]*"|\'[^\']*\'|[^"\'\\s>]*)/';
			preg_match_all($pattern, $widget_attr, $attr_matches, PREG_SET_ORDER);
			$attrs = [];
			foreach ($attr_matches as $attr) {
				if (($attr[2][0] == '"' || $attr[2][0] == "'") && $attr[2][0] == $attr[2][strlen($attr[2])-1]) {
					$attr[2] = substr($attr[2], 1, -1);
				}
				$name = trim(strtolower($attr[1]));
				$value = html_entity_decode($attr[2]);
				$attrs[$name] = $value;
			}

			$widget_request = SgCore::loadWidget($widget_name,(object)$attrs);
			// $widget_request.='Loading widget <strong>'.$widget_name.'</strong> with '.$widget_attr;
			// $widget_request.=print_o($attrs,'$attrs');
			$regWidgetContentTag = '/<div class=\"widget\-content\">(.*?)<\/div>/i';
			if ($attrs["data-option-replace"]=='yes') {
				$widet_result = $widget_request;
			} else if (preg_match($regWidgetContentTag,$widget_tag)) {
				$widet_result = preg_replace($regWidgetContentTag,$widget_request,$widget_tag);
			} else {
				$widet_result = preg_replace('/<\/div>$/i',$widget_request.'</div>',$widget_tag);
			}
			$result .= $matches[1][$idx].$widet_result;
			$html_recent = substr($html_recent, strlen($matches[0][$idx]));
		}
		$result.=$html_recent;
	} else {
		$result=$html;
	}

	// Replacing back with content
	if ($foundTextarea[0]) {
		$result = str_replace(array_map(function($el){ return '<textarea>'.$el.'</textarea>'; }, array_keys($foundTextarea[0])), $foundTextarea[0], $result);
	}
	if ($foundPre[0]) {
		$result = str_replace(array_map(function($el){ return '<pre>'.$el.'</pre>'; }, array_keys($foundPre[0])), $foundPre[0], $result);
	}

	//	$result.=htmlview($html);
	//	$result.=str_replace("\n",'<br />',htmlspecialchars(print_r($matches,1)));
	return $result;
}

/**
 * Process installation new module
 *
 * @param String $module
 * @return String
 */
function process_install_module($module) {
	if (empty($module)) return;

	$manifest_module_file = R::Manifest($module);

	if (empty($manifest_module_file)) {
		$ret = false;
	} else {
		// Add Permission
		$perm = cfg('perm');
		$modulePerm = cfg($module.'.permission');
		$modulePermExtend = R::Module($module.'.permission');
		if ($modulePermExtend) $modulePerm .= ($modulePerm ? ',':'').$modulePermExtend;
		if ($modulePerm) {
			$perm->{$module} = $modulePerm;
			cfg_db('perm', $perm);
		}

		$ret .= '<h3>Permission of module '.$module.'</h3>'.$perm->{$module}.'<br /><br />';

		// Process installation
		$ret .= '<h3>Installation of module '.$module.'</h3>';
		$ret .= R::Module($module.'.install').'<br /><br />';
	}
	return $ret;
}


//---------------------------------------
// Core Utility Function
//---------------------------------------

/**
 * Get current web viewer information and set value to key
 *
 * @param String $key
 * @param String  $value
 * @return object
 */
function i($key = NULL, $value = NULL) {
	// static $i;
	global $R;
	if (!isset($R->user)) return;
	// echo 'call i()<br />';
	// if (!isset($i)) {
	if ($key === 'R') {
		// print_o(get_caller(__FUNCTION__),'$caller',1);
		// print_o($value, '$value',1);
		// $i = (Object) ['a' => 'aaaa'];//$GLOBALS['user']; //SG\getFirst(R()->user,(Object)[]);
		// $i = &$GLOBALS['R']->user;//value;
		// $i = $R->user;
		// print_o(R()->user,'(i)R()->user',1);
		// print_o($GLOBALS['R'],'$GLOBALS[R]');
		// print_o($i,'$i',1);
	}
	// if (!isset($R->user->admin)) $R->user->admin = user_access('access administrator pages');
	if (!isset($R->user->am)) {
		if ($R->user->ok && module_install('ibuy') && mydb::table_exists('%ibuy_customer%') && mydb::columns('ibuy_customer','custtype')) {
			$R->user->am = mydb::select('SELECT `custtype` FROM %ibuy_customer% WHERE `uid` = :uid LIMIT 1',':uid',$R->user->uid)->shop_type;
		}
		if (!isset($R->user->am)) $R->user->am = '';
	}
	// if (!$i->server) {
	// 	$i->ip = GetEnv('REMOTE_ADDR');
	// 	$i->server = &$_SERVER;
	// }
	if (isset($key) && isset($value)) $i->{$key} = $value;
	return $R->user;
}

/**
 * Set & set configuration to database
 * @param String $name
 * @param Mixed $value
 * @return Mixed
 */
function cfg_db($name = NULL, $value = NULL) {
	if (isset($name) && isset($value)) {
		// Update $value to db config by key $name
		if (is_object($value)) $write_value =  sg_json_encode($value);
		else if (is_array($value) || is_bool($value) || is_numeric($value)) $write_value = serialize($value);
		else $write_value = $value;
		mydb::query('INSERT INTO %variable% ( `name` , `value` ) VALUES ( :name, :value) ON DUPLICATE KEY UPDATE `value` = :value',':name',$name,':value',$write_value);
		$result=$value;
		cfg($name,$value);
	} else if (isset($name)) {
		$rs = mydb::select('SELECT `name`, `value` FROM %variable% WHERE name = :name LIMIT 1; -- {reset: true}',':name',$name);
		$result = ($rs->_num_rows) ? __is_serialized($rs->value)?unserialize($rs->value) : $rs->value : NULL;
	} else {
		$dbs = mydb::select('SELECT `name`, `value` FROM %variable%');
		$conf = [];
		if (isset($dbs->items) && $dbs->items) {
			foreach ($dbs->items as $item) {
				$conf[$item->name] = __is_serialized($item->value) ? unserialize(trim($item->value)) : $item->value;
			}
		}
		$result = $conf;
	}
	return $result;
}

/**
 * Delete configuration from database
 * @param String $name
 */
function cfg_db_delete($name) {
	if (isset($name)) {
		mydb::query('DELETE FROM %variable% WHERE name=:name LIMIT 1',':name',$name);
		cfg($name,NULL,'delete');
	}
}

/**
 * Set & get table with keyword
 * @param String $key
 * @param String $new_value
 * @param String $prefix
 * @param String $db
 * @return String
 */
function db($key = NULL, $new_value = NULL, $prefix = NULL, $db = NULL) {
	static $items = [];
	static $src = [];
	$ret=NULL;
	if (isset($key) && isset($new_value)) {
		$src[$key]=$new_value;
		$tablename=(isset($db)?'`'.$db.'`.':''); // Set database name
		if (preg_match('/\`([a-zA-Z0-9_].*)\`/',$new_value,$out)) {
			$tablename.=$new_value; // Use new_value on `table` or `db`.`table` format
		} else {
			$tablename.='`'.cfg('db.prefix').$new_value.'`'; // Add prefix on table format
		}
		$items[$key]=$tablename;
		$ret=$items;
	} else if (!isset($key) && isset($prefix)) {
		// Change all table items to new prefix value
		foreach ($src as $key=>$value) $items[$key]=$prefix.$value;
		$ret=$items;
	} else if (isset($key)) {
		// if key return value in condition
		// key format `table_name` return `table_name`
		// key format %table_name% return value in `$items[table_name]`
		// key format table_name and key in array items return items[table_name]
		// key format table_name and key not in items return db.prefix+table_name
		if (preg_match('/\`([a-zA-Z0-9_].*)\`/',$key,$out)) {
			$ret=$key;
		} else if (preg_match('/\%([a-zA-Z0-9_].*)\%/',$key,$out)) {
			$ret=array_key_exists($out[1],$items)?$items[$out[1]]:'`'.cfg('db.prefix').$out[1].'`';
		} else {
			$ret=array_key_exists($key,$items)?$items[$key]:'`'.cfg('db.prefix').$key.'`';
		}
	} else {
		$ret=$items;
	}
	return $ret;
}

/**
 * Convert request string into array and get each index
 * @param Mixed $from explode into array if is string
 * @param Mixed $all numeric or 'all'
 * @param String $return_type
 * @return Mixed
 *
 * If first param is string -> explode request string to array and store in $q
 * If only first param (from) -> get all value start from first param
 * If first param and second param (from,to) -> get all value between from and to
 */
function q($from = NULL, $to = NULL, $return_type = 'array') {
	static $q = [];
	static $rq=NULL;
	if (is_string($from)) {
		$rq=$from;
		$q= explode('/',$from);
		foreach ($q as $k=>$v) if (trim($v)=='') unset($q[$k]); else $q[$k]=trim($v);
		return;
	}
	if ($to==='all') $to=count($q);
	if (isset($from) && !isset($to)) $ret = array_key_exists($from,$q) ? $q[$from]:NULL;
	else if (isset($from) && isset($to)) $ret = array_slice($q,$from,$to);
	else $ret = $rq;
	if ($return_type==='string' && is_array($ret)) $ret = implode('/',$ret);
	return $ret;
}

/**
 * Set theme folder
 * @param String $name Theme name
 * @param String $style Stylesheet file
 * @return Array $template_folder
 *
 * set theme configuration etc theme , theme.name , theme.absfolder , theme.stylesheet
 */
function set_theme($name = NULL, $style = 'style.css') {
	/*
	themes -> current url+themes
	./themes -> current url+./themes
	../themes -> current url+../themes
	/themes -> /themes
	/folder/themes -> /folder/themes
	*/

	$themes = [];
	if (isset($name)) $themes[]=$name;
	if ($_GET['theme']) $themes[]=$_GET['theme'];
	if ($_COOKIE['theme']) $themes[]=$_COOKIE['theme'];
	$themes[]=cfg('theme.name');
	$themes[]='default';

	foreach ($themes as $name) {
		$theme_folder=cfg('folder.abs').cfg('theme.folder').'/'.$name.'/';
		$css_file=$theme_folder.$style;
		if (is_dir($theme_folder)) break;
	}

	cfg('theme.name',$name);
	cfg('theme.absfolder',cfg('folder.abs').cfg('theme.folder').'/'.cfg('theme.name'));
	cfg('theme',(substr(cfg('theme.folder'),0,1)=='/' ? cfg('theme.folder') : cfg('url').cfg('theme.folder')).'/'.$name.'/');

	if (isset($_GET['pageclass'])) {
		$_COOKIE['pageclass'] = $pageClass = $_GET['pageclass'];
		if ($pageClass) {
			setcookie('pageclass',$pageClass,time()+365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));
		} else {
			setcookie('pageclass','',time()-3600,cfg('cookie.path'),cfg('cookie.domain'));
		}
	}

	if ($_COOKIE['pageclass']) page_class('-'.$_COOKIE['pageclass']);

	//set style sheet
	if (isset($_GET['style']) && $_GET['style']=='') {
		cfg('theme.stylesheet',cfg('theme').$style);
		setcookie('style','',time()-3600,cfg('cookie.path'),cfg('cookie.domain'));
	} else if (isset($_GET['style']) && $_GET['style']!='') {
		cfg('theme.stylesheet',$_GET['style']);
		setcookie('style',$_GET['style'],time()+365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));
	} else if ($_COOKIE['style']) {
		cfg('theme.stylesheet',cfg('theme.name',$_COOKIE['style']));
	} else {
		cfg('theme.stylesheet',file_exists($css_file)?cfg('theme').$style:'/themes/'.$name.'/'.$style);
	}

	// create theme folder list

	// add current theme folder
	$template_folder[] = cfg('theme.absfolder').'/';
	// add default theme folder
	$template_folder[] = cfg('folder.abs').cfg('theme.folder').'/default/';
	// add root theme folder
	$template_folder[] = cfg('folder.abs').cfg('theme.folder').'/';
	$template_folder[] = _CORE_FOLDER.'/core/assets/template/';
	$template_folder=array_unique($template_folder);

	foreach ($template_folder as $key=>$_folder) if (!is_dir($_folder)) unset($template_folder[$key]);
	cfg('theme.template',$template_folder);

	return $template_folder;
}

/**
 * Check user permission to each menu from roles or user's role or is owner's content
 * @param String $role
 * @param String $urole
 * @param Integer $uid
 * @param Boolean $debug
 * @return Boolean
 */
function user_access($role, $urole = NULL, $uid = NULL, $debug = false) {
	static $roles;
	static $member_roles = [];
	if ($role == 'reset') {
		$roles = NULL;
	}
	if (!isset($roles)) {
		foreach (cfg('roles') as $name => $item) {
			$roles[$name]=trim($item) === '' ? [] : explode(',',$item);
		}
		array_walk_recursive($roles,'__trim');
	}
	if ($role === 'reset') return false;

	if ($uid) $uid = intval($uid);

	if ($debug) echo '<br />user access debug role <b>'.$role.($urole?','.$urole:'').($uid?','.$uid:'').'</b> of <b>'.(i()->ok?i()->name.'('.i()->uid.(i()->roles?',':'').implode(',',i()->roles).')':'anonymous').'</b><br />';
	// menu for everyone
	if ($role===true) return true;

	// root have all privileges
	if (isset(i()->uid) && i()->uid==1) return true;

	// admin have all privileges
	if (i() && i()->ok && in_array('admin',i()->roles)) return true;

	$role=explode(',',$role);

	// need method check privileges
	if (in_array('method permission',$role)) return true;

	// check for member
	if (i()->ok) {
		// collage all member roles
		if (!array_key_exists(i()->uid,$member_roles)) {
			$member_roles[i()->uid]=array_merge($roles['anonymous'],$roles['member']);
			foreach (i()->roles as $name) if (is_array($roles[$name])) $member_roles[i()->uid]=array_merge($member_roles[i()->uid],$roles[$name]);
			$roles_user=cfg('roles_user');
			if (is_array($roles_user) && array_key_exists(i()->uid,$roles_user)) {
				$member_roles[i()->uid]=array_merge($member_roles[i()->uid],explode(',',$roles_user[i()->uid]));
			}
			$member_roles[i()->uid]=array_unique($member_roles[i()->uid]);
			asort($member_roles[i()->uid]);
		}

		if ($debug) echo '$member_roles['.i()->uid.']='.implode(',',$member_roles[i()->uid]).'<br />';

		/* user have permission in roles */
		if ($debug && $str=implode(',',array_intersect($role,$member_roles[i()->uid]))) echo 'roles permission is <b>'.$str.'</b><br />';
		if (array_intersect($role,$member_roles[i()->uid])) return true;

		/* check permission of owner content */
		if ($urole) {
			if ($debug) echo in_array($urole,$member_roles[i()->uid]) ? 'user role is <b>'.$urole.'</b>'.($uid===i()->uid?' and is owner permission':' but not owner').'<br />':'';
			if ($uid===i()->uid && in_array($urole,$member_roles[i()->uid])) return true;
		}

		if ($debug) echo 'no role permission<br />';
		return false;
	}

	// anonymous user role
	if ($debug) echo '$roles[anonymous]='.implode(',',$roles['anonymous']).'<br />';
	if ($debug && $str=implode(',',array_intersect($role,$roles['anonymous']))) echo 'roles intersection=<b>'.$str.'</b><br />';
	if (is_array($roles['anonymous']) && array_intersect($role,$roles['anonymous'])) return true;

	if ($debug) echo 'no role permission<br />';
	return false;
}

/**
 * Check module was install
 * @param String $module
 * @return Boolean
 */
function module_install($module) {
	if (empty($module) && !is_string($module)) return false;
	$perms=cfg('perm');
	return isset($perms->$module);
}

/**
 * Check homepage was requested
 * @return Boolean
 */
function is_home() {
	global $R;
	return !isset($R->request) || empty($R->request) || $R->request=='home';
}

/**
 * Check current user is admin or module admin
 * @param String $module
 * @return Boolean
 */
function is_admin($module = NULL) {
	$is_right = false;
	if (is_null($module)) {
		$is_right = user_access('access administrator pages');
	} else if (user_access('access administrator pages,administer contents')) {
		$is_right = true;
	} else if ($module && user_access('administrator '.$module.'s,administer '.$module.'s')) {
		$is_right = true;
	}
	return $is_right;
}

/**
 * Generate url for anchor
 * @param String $q
 * @param String $get
 * @param String $frement
 * @return String
 */
function url($q = NULL, $get = NULL, $frement = NULL, $subdomain = NULL) {
	$ret='';
	if (isset($get) && is_array($get)) {
		foreach ($get as $k=>$v) if (!is_null($v)) $get_a.=$k.'='.$v.'&';
		$get=rtrim($get_a,'&');
		if (empty($get)) unset($get);
	}
	if (substr($q,0,2)==='//') ; // do nothing
	else if (substr($q,0,1)==='/') $q=substr($q,1);

	$url = preg_match('/^(\/\/|http:\/\//|https:\/\//)',$q) ? '' : cfg('url');
	if (cfg('clean_url')) {
		$ret .= isset($q)?$q:cfg('clean_url_home');
		if ( isset($get) ) $ret .= '?'.$get;
	} else {
		$ret .= $q ? '?'.$q:'';
		if ( isset($get) ) $ret .= ($q?'&':'?').$get;
	}
	if ($frement) $ret .= '#'.$frement;
	//	echo 'url alias of '.$ret.' = '.url_alias($ret)->system.'<br >';
	if ($url_alias = url_alias_of_system($ret)) $ret = $url_alias->system;
	//	echo 'url ret='.$ret.'<br />';
	$ret=cfg('url.domain').(cfg('url.domain')?'':$url).$ret;
	return $ret;
}

/**
 * Get url alias
 * @param String $request
 * @return String or False on not found
 */
function url_alias($request = NULL) {
	static $alias = NULL;
	if (!isset($alias)) $alias = mydb::select('SELECT * FROM %url_alias% ORDER BY `alias` ASC');
	if (!isset($request)) return $alias;

	$result = (Object) [];
	foreach ($alias->items as $item) {
		$reg_url_alias = str_replace('/','\/',preg_quote($item->alias));
		$reg = '/^('.$reg_url_alias.'\z|'.$reg_url_alias.'\/)(.*)/';
		$system = preg_replace($reg,$item->system.'/\\2',$request);

		if ($system != $request) {
			$system = trim($system,'/');
			$result->alias = $request;
			$result->system = $system;

			return $result;
		}
	}
	return false;
}

/**
 * Get url alias of system
 * @param String $request
 * @return String or False on not found
 */
function url_alias_of_system($request) {
	$alias = url_alias();

	if (!$alias) return false;
	$result = new stdClass();
	foreach ($alias->items as $item) {
		$reg_url_system = str_replace('/','\/',preg_quote($item->system));
		$reg = '/^('.$reg_url_system.'\z|'.$reg_url_system.'\/)(.*)/';
		$reg_result = preg_replace($reg,$item->alias.'/\\2',$request);
		$reg_result = trim($reg_result,'/');
		if ($reg_result != $request) {
			$result->system = $reg_result;
			$result->alias = $item->alias;
			return $result;
		}
	}
	return false;
}

/**
 * Set / Get page_class config
 * @param String $addClass
 * @return String
 */
function page_class($addClass = NULL) {
	$currentClass = trim(cfg('page_class'));
	if ($addClass) $currentClass .= ' '.$addClass;
	$currentClass = trim($currentClass);
	cfg('page_class', $currentClass);
	return $currentClass;
}

/**
 * This function will return the name string of the function that called $function. To return the
 * caller of your function, either call get_caller(), or get_caller(__FUNCTION__).
 * @param String $function
 * @param Array $use_stack
 * @param String $key
 * @return String
 **/
function get_caller($function = NULL, $use_stack = NULL, $key = NULL) {
	if ( is_array($use_stack) ) {
		// If a function stack has been provided, used that.
		$stack = $use_stack;
	} else {
		// Otherwise create a fresh one.
		$stack = debug_backtrace();
		//echo "\nPrintout of Function Stack: \n\n";
		//print_o($stack,'$stak',1);
		//echo "\n";
	}

	if ($function == NULL) {
		// We need $function to be a function name to retrieve its caller. If it is omitted, then
		// we need to first find what function called get_caller(), and substitute that as the
		// default $function. Remember that invoking get_caller() recursively will add another
		// instance of it to the function stack, so tell get_caller() to use the current stack.
		$function = get_caller(__FUNCTION__, $stack,$key);
	}


	//echo $function.' level='.$level.'<br />';print_o($stack,'$stack',1);
	if ( is_string($function) && $function != "" ) {
		// If we are given a function name as a string, go through the function stack and find
		// it's caller.
		for ($i = 0; $i < count($stack); $i++) {
			$curr_function = $stack[$i];
			// Make sure that a caller exists, a function being called within the main script
			// won't have a caller.
			if ($key=='stack') {
				if ($i==0) continue;
				$stackList.=$curr_function['function'].'() line '.$curr_function['line'].' of file '.$curr_function['file'].'<br />';
				//echo '$stackList='.$stackList.'<br />'._NL;
			} else {
				if ( $curr_function["function"] == $function && ($i + 1) < count($stack) ) {
					$stack[$i + 1]['from']=(!empty($stack[$i + 1]['class'])?$stack[$i + 1]['class'].($stack[$i + 1]['type']?$stack[$i + 1]['type']:'.'):'').$stack[$i + 1]['function'].'() line '.$stack[$i]['line'].' of file '.$stack[$i]['file'];
					//print_o($stack[$i + $level],'$return['.($i + $level).']',1);
					unset($stack[$i + 1]['args']);
					return $key ? $stack[$i + 1][$key]: $stack[$i + 1];
				}
			}
		}
		if ($key=='stack') return $stackList;
	}

	// At this stage, no caller has been found, bummer.
	return "";
}

function pageInfo() {
	$ret = '<h3>Page information @'.date('H:i:s').'</h3>';
	$ret .= 'Page class = '.cfg('page_class').'<br />';
	$ret .= 'HTTP_USER_AGENT = '.$_SERVER['HTTP_USER_AGENT'].'<br />';
	$ret .= 'isMobileDevice = '.isMobileDevice().'<br />';
	$ret .= print_o(R()->appAgent, 'appAgent');
	return $ret;
}

function callFromApp() {
	return R()->appAgent->dev == 'Softganz' ? R()->appAgent : false;
}

/**
 * Check Core version and upgrade
 * @return String
 */
function core_version_check() {
	if (!user_access('access administrator pages')) return;
	if (!mydb()->status) return 'MySql maybe down.';
	$version_current=cfg('core.version.install');
	$version_install=cfg('version.install');
	$version_force='';
	if (post('force')) {
		$version_force=post('force');
	}
	if ($version_install===$version_current && $version_force=='') return;
	if (post('upgrade')=='yes' || $version_force) cfg('version.autoupgrade',true);
	if (!cfg('version.autoupgrade')) return 'ระบบมีความต้องการปรับปรุงจากรุ่น <strong>'.$version_install.'</strong> เป็นรุ่น <strong>'.$version_current.'</strong> แต่การปรับปรุงอัตโนมัติถูกปิด. <a href="'.url(q(),'upgrade=yes').'">เริ่มปรับปรุงรุ่น?</a>';

	$upgrade_folder = _CORE_FOLDER.'/core/upgrade/';
	if (!file_exists($upgrade_folder)) return 'Upgrade folder not extsts.';

	set_time_limit(0); // run very long time

	$d = dir($upgrade_folder);
	while (false !== ($entry = $d->read())) {
		if ( $entry=='.' || $entry=='..' ) continue;
		$upver=substr($entry,0,strrpos($entry,'.'));
		$upgrade_file[$upver] = $entry;
	}
	asort($upgrade_file);
	$d->close();

	$ret.='<h3>Start upgrading from '.$version_install.' to '.$version_current.'</h3>';
	if ($version_force) $ret.='<p>Force upgrade version of '.$version_force.'</p>';
	foreach ($upgrade_file as $upver=>$file) {
		if ($version_force && $upver==$version_force) {
			// Do nothing
		} else if ($upver<=$version_install) {
			$ret.='<p>Upgrade version '.$upver.' from file '.$file.' is unnecessary.</p>';
			continue;
		}
		$ret.='<h4>Upgrade to version '.$upver.'</h4>';
		include_once($upgrade_folder.$file);
		$ret.='<dl>';
		foreach ($result[$upver] as $upgrade_result) {
			$ret.='<dt>'.$upgrade_result[0].'</dt>';
			$ret.='<dd>'.$upgrade_result[1].'</dd>';
		}
		$ret.='</dl>'._NL;
	}
	cfg('web.message',$ret);
	if (!$version_force) cfg_db('version.install',cfg('core.version.install'));
	return;
}

/**
 * Check IP was ban
 * @param String $ip
 * @return boolean
 */
function banIp($ip) {
	$banips = cfg('ban.ip');
	$banCount = count((Array) cfg('ban.ip'));
	$currentTime = date('Y-m-d H:i:s');

	$is_ban = false;

	if (empty($banips)) return $is_ban;

	foreach ($banips as $idx => $ban) {
		if ($currentTime > $ban->end) unset($banips->{$idx});
		if ($idx == $ip && $currentTime < $ban->end) {
			$is_ban = true;
			break;
		}
	}
	$newBanCount = count((Array) $banips);
	if ($newBanCount == 0) cfg_db_delete('ban.ip');
	else if ($newBanCount != $banCount) cfg_db('ban.ip',$banips);
	return $is_ban;
}

/**
 * Translate text from English to current language used my dictiondary
 * @param String $text
 * @param String $translateText
 * @return String
 */
function tr($text, $translateText = NULL) {
	static $dictionary = [];
	static $load = [];

	$lang = strtolower(cfg('lang'));
	if (empty($load)) {
		$load[] = 'core.en.po';
		include(_CORE_FOLDER.'/core/po/core.en.po');
	}
	$current_lang_po = strtolower('core.'.$lang.'-'.cfg('client.characterset').'.po');
	if (!in_array($current_lang_po,$load)) {
		$load[] = $current_lang_po;
		include(_CORE_FOLDER.'/core/po/'.$current_lang_po);
		//print_o($dict,'$dict',1);
		if ($dict && is_array($dict)) {
			foreach ($dict[$lang] as $key => $value) {
				$dictionary[$lang][strtolower($key)] = $value;
			}
		}
	}

	// Load new dictionary
	if ($text == 'load' && $translateText) {
		$load_lang_po = strtolower($translateText.'.'.$lang.'-'.cfg('client.characterset').'.po');
		$load_lang_file = _CORE_FOLDER.'/core/po/'.$load_lang_po;
		if (!in_array($load_lang_po,$load) && file_exists($load_lang_file)) {
			$load[] = $load_lang_po;
			unset($dict);
			include($load_lang_file);
			if ($dict && is_array($dict)) {
				foreach ($dict[$lang] as $key => $value) {
					$dictionary[$lang][strtolower($key)] = $value;
				}
			}

		}
	}

	// Add array of text into dictionary
	if (is_array($text)) {
		foreach ($text as $lang => $v) {
			foreach ($v as $k => $vv) $dictionary[$lang][strtolower($k)] = $vv;
		}
		return;
	}

	// Start translate text
	$searchKey = strtolower(strip_tags($text));
	$result = $lang != 'en' && $translateText ? $translateText : (is_array($dictionary[$lang]) && array_key_exists($searchKey,$dictionary[$lang]) ? $dictionary[$lang][$searchKey] : $text);
	return $result;
}

/**
 * Add string to website header section
 * @param String $str
 */
function head($key = NULL, $value = NULL, $pos = NULL) {
	static $items = [];
	if ($value===NULL) $value=$key;
	if (preg_match('/[\n]/',$key)) unset($key);
	if (!in_array($value,$items)) {
		if ($pos==-1) {
			$items = [$key => $value] + $items;
		} else if (isset($key) && $key) $items[$key]=$value;
		else $items[]=$value;
	}
	if ((!isset($key) || $key===NULL) && $value===NULL) return $items;
	else if (isset($key) && $key) return $items[$key];
}

/**
 * Set and get program debug
 * @param String $key
 * @return Mixed
 */
function debug($key = NULL) {
	static $items = [];
	if (empty($items)) {
		$debug = '';
		if (isset($_REQUEST['debug'])) $debug = $_REQUEST['debug'];
		if (preg_match('/debug\/([a-z,0-9_]*)/',q(),$out)) $debug.=($debug?',':'').$out[1] ;
		$items['debug'] = $debug?$debug:'none';
		foreach (explode(',',$items['debug']) as $ok) $items[$ok] = true;
	}
	$is_debug = cfg('debug') || user_access('access debugging program');
	return ($is_debug && $key == 'debug') ? $items : ($is_debug && array_key_exists($key,$items));
}

/**
 * Generate module menu
 * @param String $path
 * @param String $title
 * @param String $module
 * @param Array $arg
 * @param String $access
 * @param String $type
 * @param String $default
 * @return Mixed True/False/Array
 */
function menu($path = NULL, $title = NULL, $module = NULL, $method = NULL, $arg = [], $access = NULL, $type = NULL, $options = NULL) {
	/**
	* Items store format
	* [
	*		path' => 'paper/list', // eg. *=any , *0=all numeric
	*	 	'title' => 'Paper topic listing',
	* 	'call' => [
  *			'module' => 'topic',
	* 		'class' => 'topic',
	* 		'method' => 'listing',
	*			'arg' => []
	*		],
	*		'access' => 'access papers',
	*		'type' => 'static'
	* ]
	*/
	static $menuItems = [];
	static $is_sort = false;

	// Set path menu item
	if ( $path && isset($title) && isset($module) ) {
		$q = q(0,'all');
		//		if ($path=='ibuy/*0') echo $path.print_o($q,'$q');
		if (!(strpos($path,'*') === false)) {
			$paths=explode('/',$path);
			foreach ($paths as $i => $v) {
				if ($v === '*' && isset($q[$i])) $paths[$i] = $q[$i];
				else if ($v == '*0' && isset($q[$i]) && is_numeric($q[$i])) $paths[$i] = $q[$i];
			}
			$path = implode('/',$paths);

			// not set menu if path not equal to left of request string
			if (!preg_match('/^'.preg_quote($path,'/').'/',q())) return false;

			// not set menu if this path was ready set
			if (array_key_exists($path, $menuItems)) return false;
		}

		if (is_string($arg)) $arg = explode('/',$arg);
		else if (is_null($arg)) $arg = [];

		$menuItems[$path] = [
			'path' => $path,
			'title' => $title,
			'call' => ['module' => $module, 'method' => $method, 'arg' => $arg],
			'access' => $access,
			'type' => $type,
			'options' => $options,
		];

		$is_sort = false;
		return true;
	}

	if (!$is_sort) {
		krsort($menuItems);
		$is_sort = true;
	}
	if (!isset($path)) return $menuItems;

	// Return request menu item
	if (isset($path)) {
		foreach ($menuItems as $mnu) {
			$pattern = preg_quote($mnu['path'], '/');
			if (preg_match('/^('.$pattern.')/', $path)) {
				$mnu['call']['arg'] = is_numeric($mnu['call']['arg']) ? q($mnu['call']['arg'],'all') : $mnu['call']['arg'];
				return $mnu;
			}
		}
	}
	return false;
}

/**
 * Set header to new location and die
 * @param String $url
 * @param String $get
 * @param String $frement
 * @return relocation to new url address
 */
function location($url = NULL, $get = NULL, $frement = NULL, $str = NULL) {
	//	echo $url;
	//	if (_AJAX) return $str;
	if (!preg_match('/^(http:\/\/|https:\/\/|ftp:\/\/)/i',$url)) {
		// $url=cfg('domain').url($url,$get,$frement); // Not use cfg('domain') if bug , uncomment this line
		$url=url($url,$get,$frement);
	}
	header('Location: '.$url);
	die;
}

/**
 * Get post value from $_POST
 * @param String $key
 * @param Integer $flag
 *
 * @return Array
 */
function post($key = NULL, $flag = _TRIM) {
	$post = $_REQUEST;
	if ( is_long($key) ) {
		$flag = $key;
		unset($key);
	}

	// Function deprecated in php 8
	// $magic_quote = get_magic_quotes_gpc();
	// if ( $magic_quote == 1 ) $post = arrays::convert($post,_STRIPSLASHES);

	if ( $flag ) $post = arrays::convert($post, $flag);
	if ( isset($key) ) {
		return isset($post[$key]) ? $post[$key] : NULL;
	} else {
		return $post;
	}
}

/**
 * View html text in plain text with highlight
 * @param String $html
 * @param String $title
 * @param Boolean $line_no
 *
 * @return String
 */
function htmlview($html, $title = NULL, $line_no = true) {
	$code = explode("\n", trim($html));
	$i = 1;
	$ret .= '<div style="background:#fff;color:#000;">';
	if ($title) $ret .= '<h2>'.stripslashes($title).'</h2>';
	foreach ($code as $line => $syntax) {
		if ($line_no) $ret .= '<font color="gray"><em>'.$i.' : </em></font> ';
		$ret .= str_replace('&nbsp;',' ',highlight_string($syntax,true)).'<br />';
		$i++;
	}
	$ret .= '</div>';
	return $ret;
}

/**
 * Get error message from error code
 * @param String $code
 * @param String $ext_msg
 *
 * @return String
 */
function error($code = NULL, $ext_msg = NULL) {
	$ret=R::View('error',$code,$ext_msg);
	return $ret;
}

/**
 * Generate message box
 * @param String $type
 * @param Mixed $message_list
 * @param String $module
 * @return String
 */
function message($type = NULL, $text = [], $module = NULL, $options = '{class: "signform -accessdenied"}') {
	if (is_array($type)) {
		$args = $type;
		unset($type);
		// echo 'ARRAY';
	} else {
		$args = ['type' => $type, 'text' => $text, 'module' => $module, 'options' => $options];
	}
	// print_r($args);
	// print_o($args,'$args',1);
	$responseCode = SG\getFirst($args['code'], $args['responseCode']);
	if ($responseCode) http_response_code($responseCode);

	if (is_string($args['text'])) $args['text'] = [$args['text']];
	if (is_string($args['msg'])) $args['text'] = [$args['msg']];

	if (empty($args['text'])) return;


	$is_accessdenied = false;

	// /* add watchdog log on Access denied */
	// if (is_string($args['text']) && strtolower($args['text']) == 'access denied') R::Model('watchdog.log',$args['module'],'Access denied');

	// debugMsg($args,'$args');

	$ret = '';
	if ($args['type']) {
		$ret .= '<div class="messages -'.($args['type'] ? $args['type'] : 'status').'">'._NL;
		$ret .= '<dl>'._NL;
	}
	foreach ($args['text'] as $item) {
		list($key) = explode(':',$item);
		$description = trim(substr($item,strlen($key)+1));
		if (strtolower($key) == 'access denied') {
			$is_accessdenied = true;
			break;
		}
		$key = tr(trim($key));
		if (empty($description)) $description = error($key);
		$errmsg .= '<dt>'.$key.'</dt>'._NL;
		if ($description) $errmsg .= '<dd>'.$description .'</dd>'._NL;
	}
	if ($args['type']) {
		$ret .= '</dl>'._NL;
	}
	$ret .= $errmsg;
	if ($args['type']) {
		$ret .= '</div><!--message-->'._NL._NL;
	}

	// Show signform for access denied
	if ($is_accessdenied) {
		if (i()->ok) {
			$ret = '<div id="login" class="login -accessdenied -sg-text-center">';
			$ret .= '<div class=""><h3 style="margin: 0;">'.tr('Hello').' '.i()->name.'</h3></div><p class="notify class="-sg-clearfix" style="margin: 0;">'.tr('Sorry!!! Access denied. Please contact web administrator.','ขออภัย!!! สิทธิ์ในการเข้าใช้งานถูกปฏิเสธ กรุณาติดต่อผู้ดูแลเว็บ').'</p>';
			if ($description) $ret .= '<p class="notify">'.$description.'</p>';
			$ret .= '</div>';
		} else {
			return R::View('signform', $options);
		}
		R::Model('watchdog.log',$args['module'],'Access denied');
	}
	// echo $args['text'];
	// echo $ret;
	// print_r($args);
	// print_o($args['text'],'$args[text]',1);
	return $ret;
	return $args['text'] ? $args['text'] : $ret;
}

/**
 * Generate even tricker
 * @param String $event
 * @param Mixed $arg1 ... $arg9
 */
function event_tricker($event = NULL, &$arg1 = NULL, &$arg2 = NULL, &$arg3 = NULL, &$arg4 = NULL, &$arg5 = NULL, &$arg6 = NULL, &$arg7 = NULL, &$arg8 = NULL, &$arg9 = NULL) {
	//debugMsg($event);
	static $extensions=null;
	if (!isset($extensions)) $extensions = cfg('extensions') ? cfg('extensions') : [];
	/* do extension_event_tricker */
	if ($event && preg_match('/(.*)\.(.*)$/',$event,$out)) {
		$event_name=$out[1];
		$event_id=$out[2];
		if (array_key_exists($event_name,$extensions)) {
			$events=$extensions[$event_name];
			// set for pass by reference
			$args = [$event_id,&$arg1,&$arg2,&$arg3,&$arg4,&$arg5,&$arg6,&$arg7,&$arg8,&$arg9];
			foreach ($events as $e) {
				$event_func=str_replace('.','_',$e);
				SgCore::loadExtension($event_func);
				if (function_exists($event_func)) $ret=call_user_func_array($event_func,$args);
				else cfg('extension_error',$event_func.' not exists');
			}
		}
	}
	return $ret;
}

/**
 * Trim only string type in array
 * @param Mixed $value
 * @return Mixed
 */
function __trim(&$value) {$value = is_string($value) ? trim($value):$value;}

function __htmlspecialchars(&$value) {$value = is_string($value) ? htmlspecialchars($value):$value;}

/**
 * Replace %tablename% with prefix and tablename
 * @param Array $m
 * @return String
 */
function __mydb_db_replace($m) {return ' '.db($m[1]);}

function __is_serialized($val) {
	if (!is_string($val)){ return false; }
	if (trim($val) == '') { return false; }
	if (preg_match('/^(i|s|a|o|d|b):(.*);/si',$val)) { return true; }
	return false;
}

?>