<?php
/**
* Core    :: Core Function
* Created :: 2023-08-01
* Modify  :: 2024-02-06
* Version :: 3
*/

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
	$error = error_reporting(E_ALL);
	$libFile = _CORE_FOLDER.'/core/'.($folder ? $folder.'/' : '').$file;
	// echo 'LOAD LIB '.$libFile.(file_exists($libFile) ? ' COMPLETE' : ' <font color="red">NOT FOUND</font>').' [error_reporting = '.$error.']<br />'."\r\n";
	if (file_exists($libFile)) {
		require_once($libFile);
		error_reporting($error);
		return $libFile;
	}
	error_reporting($error);

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
		$perm = cfg('perm') ? cfg('perm') : (Object) [];
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
		// $i = (Object) ['a' => 'aaaa'];//$GLOBALS['user']; //\SG\getFirst(R()->user,(Object)[]);
		// $i = &$GLOBALS['R']->user;//value;
		// $i = $R->user;
		// print_o(R()->user,'(i)R()->user',1);
		// print_o($GLOBALS['R'],'$GLOBALS[R]');
		// print_o($i,'$i',1);
	}
	// if (!isset($R->user->admin)) $R->user->admin = user_access('access administrator pages');

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

	$getTemporaryTheme = $_GET['theme'];

	$themes = [];
	if (isset($name)) $themes[] = $name;
	if ($getTemporaryTheme == ':clear') {
		setcookie('theme', '', time()-3600, cfg('cookie.path'), cfg('cookie.domain'));
		unset($_COOKIE['theme']);
	} else if ($getTemporaryTheme && is_string($getTemporaryTheme)) {
		$themes[] = $getTemporaryTheme;
		setcookie('theme', $getTemporaryTheme, time()+10*365*24*60*60, cfg('cookie.path'), cfg('cookie.domain'));
	}
	if ($_COOKIE['theme']) $themes[] = $_COOKIE['theme'];
	$themes[] = cfg('theme.name');
	$themes[] = 'default';

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
	if (function_exists('i') && isset(i()->uid) && i()->uid == 1) return true;

	// admin have all privileges
	if (function_exists('i') && i()->ok && in_array('admin',i()->roles)) return true;

	$role=explode(',',$role);

	// need method check privileges
	if (in_array('method permission',$role)) return true;

	// check for member
	if (function_exists('i') && i()->ok) {
		// collage all member roles
		if (!array_key_exists(i()->uid, $member_roles)) {
			$member_roles[i()->uid] = array_merge($roles['anonymous'], $roles['member']);
			foreach (i()->roles as $name) {
				if (is_array($roles[$name])) {
					$member_roles[i()->uid] = array_merge($member_roles[i()->uid], $roles[$name]);
				}
			}
			$roles_user = cfg('roles_user');
			if (is_object($roles_user) && array_key_exists(i()->username, (Array) $roles_user)) {
				$member_roles[i()->uid] = array_merge($member_roles[i()->uid], explode(',', $roles_user->{i()->username}));
			}
			$member_roles[i()->uid] = array_unique($member_roles[i()->uid]);
			asort($member_roles[i()->uid]);
		}

		if ($debug) echo '$member_roles['.i()->uid.']='.implode(',',$member_roles[i()->uid]).'<br />';

		/* user have permission in roles */
		if ($debug && $str = implode(',',array_intersect($role,$member_roles[i()->uid]))) {
			echo 'roles permission is <b>'.$str.'</b><br />';
		}
		if (array_intersect($role,$member_roles[i()->uid])) {
			return true;
		}

		/* check permission of owner content */
		if ($urole) {
			if ($debug) {
				echo in_array($urole,$member_roles[i()->uid]) ? 'user role is <b>'.$urole.'</b>'.($uid === i()->uid ? ' and is owner permission':' but not owner').'<br />' : '';
			}
			if ($uid === i()->uid && in_array($urole,$member_roles[i()->uid])) {
				return true;
			}
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

	$url = preg_match('/^(\/\/|http\:\/\/|https\:\/\/)/', $q, $out) ? '' : cfg('url');

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
	$ret = cfg('url.domain').(cfg('url.domain') ? '' : $url) . $ret;
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
		if (preg_match('/^(.*)\*/', $idx, $out)) {
			$banPattern = $out[1];
			if (preg_match('/^'.preg_quote($banPattern).'/', $ip)) {
				$is_ban = true;
				break;
			}
		} else if ($idx == $ip && $currentTime < $ban->end) {
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
	//	if (_AJAX) return $str;
	// echo '$url = '.$url.'</br />';
	if (!preg_match('/^(http:\/\/|https:\/\/|ftp:\/\/)/i',$url)) {
		// $url=cfg('domain').url($url,$get,$frement); // Not use cfg('domain') if bug , uncomment this line
		$url = url($url, $get, $frement);
	}
	// echo $url;
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
	static $count = 0;
	$post = $_REQUEST;
	if ( is_long($key) ) {
		$flag = $key;
		unset($key);
	}

	// Function deprecated in php 8
	// $magic_quote = get_magic_quotes_gpc();
	// if ( $magic_quote == 1 ) $post = arrays::convert($post,_STRIPSLASHES);

	// echo (++$count).'. '.date('H:i:s').' key = '.$key.' flag = '.$flag.' access = '.user_access('input format type script').'<br>';

	if (!user_access('input format type script')) $flag = $flag + _STRIPTAG;

	if ($flag) $post = Arrays::convert($post, $flag);

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
function error($code, String $message) {
	if (strtolower($message) === 'access denied') {
		R::Model('watchdog.log', NULL, 'Access denied');
	}
	if ($code) http_response_code($code);
	if (_AJAX) return ['responseCode' => $code, 'text' => $message];
	return new ErrorMessage(['responseCode' => $code, 'text' => $message]);
}

/**
 * Return success value
 * @param String/Array/Object $message
 * @return Array
 */
function success($message) {
	if (is_object($message) || is_array($message)) {
		$message = (Object) $message;
		if (!$message->responseCode) $message->responseCode = _HTTP_OK;
		$result = new Message($message);
	} else {
		$result = new Message([
			'responseCode' => _HTTP_OK,
			'text' => $message,
		]);
	}
	return $result;
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
	} else {
		$args = ['type' => $type, 'text' => $text, 'module' => $module, 'options' => $options];
	}

	$responseCode = \SG\getFirst($args['code'], $args['responseCode']);
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
		if (empty($description)) $description = R::View('error', $key);

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