<?php
/**
* Core Init :: Init Web
* Created   :: 2023-08-01
* Modify    :: 2024-07-08
* Version   :: 4
*/

global $R;

spl_autoload_register('sg_autoloader');

// Extract template from request url using url format (template)/module/path
if (preg_match('/^\((.*)\)\/(.*)/', $request, $out)) {
	$request = $out[2];
	cfg('template', $out[1]);
	// debugMsg('$template = '.cfg('template'));
	// debugMsg('$request = '.$request);
	// debugMsg($out, '$out');
}

$R = new R();
$R->request = $request;
$R->core = json_decode(file_get_contents(_CORE_FOLDER.'/core/assets/conf/conf.core.json'));

$includeFileList = [
	'lib/lib.define.php',
	'lib/lib.base.php',
	'lib/lib.function.php',
	'lib/lib.sg.php',
	'lib/class.common.php',
	'lib/class.module.php',
	'lib/class.mydb.php',
	'lib/class.view.php',
	'lib/class.sg.php',
	'lib/class.poison.php',
	'models/model.basic.php',
	'models/model.user.php',
	'models/model.counter.php',
	'widgets/class.widget.php',
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

define('_HOST',    $httpDomain);
define('_REFERER', $httpReferer);
define('_CALL_FROM_APP', $callFromApp ? $R->appAgent->type : false);
define('_AJAX',    ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest' && $httpDomain==$httpReferer))
	|| preg_match('/^ajax\//i',$request)
	|| isset($_GET['ajax']));
define('_HTML',    isset($_REQUEST['html']));
define('_API',     isset($_REQUEST['api']));

// die(_AJAX?'AJAX Call':'Normal call');
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
if ($R->core) foreach ($R->core as $key => $value) cfg($key, $value);
SgCore::loadConfig('conf.default.php', _CORE_FOLDER.'/core/assets/conf'); // load default config file
SgCore::loadConfig('conf.core.json', 'conf.d'); // load core config file
SgCore::loadConfig('conf.web.release.php', '.'); // load web config release
SgCore::loadConfig(_CONFIG_FILE, ['conf.d','.']); // load web config file
SgCore::loadConfig('conf.local.php', ['conf.local','.']); // load local config file
error_reporting(cfg('error_reporting'));
//echo 'error after load config '.error_reporting().' : '.decbin(error_reporting()).'<br />';

// Create new mydb database constant
$R->myDb = new MyDb(cfg('db'));
$R->DB = new \Softganz\DB([
	'connection' => [
		'uri' => cfg('db'),
		'characterSetClient' => cfg('db.character_set_client'),
		'characterSetConnection' => cfg('db.character_set_connection'),
		'collationConnection' => cfg('db.collation_connection'),
	]
]);

// If connect database error, end process
if (!$R->myDb->status) {
	// if not set_theme, cannot find index template file
	set_theme();
	die(SgCore::processIndex('index', message('error','OOOPS!!! Database connection error')));
}

// Load config variable from table
SgCore::loadConfig(cfg_db());

if (banRequest(getenv('REMOTE_ADDR'), $_SERVER['HTTP_USER_AGENT'])) die('Sorry!!!! You were banned.');

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

// set the cache expire to 1 minutes
// session_cache_expire(1);
// echo session_cache_expire();

// Start session handler register using database
session_set_save_handler(new Session(), true);
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
	die(\SG\json_encode($R->user));
} else if ($R->user->signInErrorMessage) {
	$R->message->signInErrorInSignForm = $R->user->signInErrorMessage;
}

if (is_object($R->user)) $R->user->admin = user_access('access administrator pages');

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
$logCounter = !(post('logCounter') === 'no') && mydb::table_exists('%counter_log%');
if ($logCounter) CounterModel::hit();
$R->counter = cfg('counter');

set_theme();

cfg('core.message',core_version_check());

// Initialize I am
i()->am;

// End of core process


function sg_autoloader($class) {
	$registerFileList = (Array) R()->core->autoLoader->items;

	if (preg_match('/\\\\/', $class)) {
		$class = end(explode('\\', $class));
	}

	$lowerClass = strtolower($class);
	if (in_array($lowerClass, array_keys($registerFileList))) {
		load_lib($registerFileList[$lowerClass]);
		if (debug('auto')) {
			debugMsg('AUTOLOAD '.$class.' from <b style="color: green">'.$registerFileList[$lowerClass].'</b>');
		}
		return;
	}

	$pieces = preg_split('/(?=[A-Z])/',$class, -1, PREG_SPLIT_NO_EMPTY);
	$endName = strToLower(end($pieces));

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

	if (debug('auto')) {
		debugMsg('AUTOLOAD '.$class.' '.($import ? 'TO <b style="color: green">'.$import.'</b>' : 'not load.'));
		// debugMsg($pieces, '$pieces');
	}

	if ($import) import($import);
}
?>