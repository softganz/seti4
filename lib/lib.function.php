<?php
/**
* Core    :: Function Library
* Created :: 2021-10-24
* Modify  :: 2025-07-12
* Version :: 3
*
* @usage functionName(parameter)
*/

/**
 * Get date and time of today
 *
 * @return Object
 */
function today() {
	if (cfg('server.timezone.offset')) {
		$today= getdate(mktime(date('H')+cfg('server.timezone.offset') , date('i') , date('s'), date('m')  , date('d') , date('Y')));
	} else $today=getdate();
	$today['date']=$today['year'].'-'.sprintf('%02d',$today['mon']).'-'.sprintf('%02d',$today['mday']);
	$today['datetime']=$today['date'].' '.sprintf('%02d',$today['hours']).':'.sprintf('%02d',$today['minutes']).':'.sprintf('%02d',$today['seconds']);
	$today['time']=$today[0];
	unset($today[0]);
	return (Object) $today;
}


/**
 * Set website title
 *
 * @param String $title
 */
function title($str=NULL) { $GLOBALS['title']=$str; };


/**
 * Set & get module data property
 *
 * @param Mixed $name
 * @param Mixed $value
 */
function property($name=NULL,$value=NULL) {
	static $property=array();
	if (is_string($name)) {
		list($module,$name,$propid,$item)=explode(':',$name);
	} else if (is_numeric($name)) {
		$propid=intval($name);
	} else if (is_array($name)) {
		list($module,$name,$propid,$item)=array($name);
	}
	if ($module=='') $module=NULL;
	if ($name=='') $name=NULL;
	if ($propid=='') $propid=NULL;
	if ($item=='') $item=NULL;
	if ($propid) $propid=intval($propid);
	//debugMsg('module='.(is_null($module)?'NULL':$module).' name='.(is_null($name)?'NULL':$name).' propid='.(is_null($propid)?'NULL':$propid).' item='.(is_null($item)?'NULL':$item).' value='.$value);
	if ($module && $name && isset($value)) {
		$property[$module][$propid][$name]=$ret=$value;
		$stmt='INSERT INTO %property% (`module`, `propid`, `name`, `item`, `value`) VALUES (:module, :propid, :name, :item, :value)
						ON DUPLICATE KEY UPDATE `value`=:value; -- {reset: false}';
		mydb::query($stmt,':module',$module, ':propid',is_null($propid)?0:$propid, ':name',$name, ':item', $item?$item:'', ':value',$value);
	} else if ($module && $name && isset($propid) && isset($item)) {
		$stmt='SELECT `value` FROM %property% WHERE `module`=:module AND `propid`=:propid AND `name`=:name AND `item`=:item LIMIT 1; -- {reset: false}';
		$property[$module][$propid][$name]=$ret=mydb::select($stmt,':module',$module, ':propid',$propid, ':name',$name, ':item',$item)->value;
	} else if ($module && $name && isset($propid)) {
		$stmt='SELECT `value` FROM %property% WHERE `module`=:module AND `propid`=:propid AND `name`=:name LIMIT 1; -- {reset: false}';
		$property[$module][$propid][$name]=$ret=mydb::select($stmt,':module',$module, ':propid',$propid, ':name',$name)->value;
	} else if ($module && isset($propid)) {
		$stmt='SELECT `name`, `value` FROM %property% WHERE `module`=:module AND `propid`=:propid; -- {reset: false}';
		foreach ($dbs=mydb::select($stmt,':module',$module, ':propid',$propid)->items as $rs) {
			$property[$module][$propid][$rs->name]=$rs->value;
		}
		$ret=$property[$module][$propid];
	} else if ($module && $name && isset($item)) {
		$stmt='SELECT `name`, `value` FROM %property% WHERE `module`=:module AND `name`=:name AND `propid`=0 AND `item`=:item LIMIT 1; -- {reset: false}';
		$rs=mydb::select($stmt,':module',$module,':name',$name, ':item',$item);
		$ret=$rs->value;
	} else if ($module && $name) {
		$stmt='SELECT `name`, `item`, `value` FROM %property% WHERE `module`=:module AND `name`=:name AND `propid`=0; -- {reset: false}';
		$dbs=mydb::select($stmt,':module',$module,':name',$name);
		foreach ($dbs->items as $rs) if ($rs->item) $ret[$rs->name][$rs->item]=$rs->value; else $ret[$rs->name]=$rs->value;
	} else if ($module) {
		$stmt='SELECT `name`, `item`, `value` FROM %property% WHERE `module`=:module AND `propid`=0; -- {reset: false}';
		$dbs=mydb::select($stmt,':module',$module);
		foreach ($dbs->items as $rs) if ($rs->item) $ret[$rs->name][$rs->item]=$rs->value; else $ret[$rs->name]=$rs->value;
	}
	return $ret;
}


/**
 * Generate usermenu item
 *
 * @param Array $menuItems
 * @param Boolean $is_first
 * @return String
 */
function _user_menu($menuItems, $is_first = true) {
	$ret = '<ul'.($is_first?' id="user-menu" class="user-menu"':'').'>'._NL;
	foreach ($menuItems as $menuKey => $item) {
		if (!isset($item->container)) $item->container = (Object)[];
		if (!isset($item->attr)) $item->attr = (Object)[];
		$item->container->class = ($item->_level == 'head' ? $item->_level.' ' : '')
				.($item->container->class ? $item->container->class : '');
		$ret .= '<li id="user-menu-'.$menuKey.'" '
				. sg_implode_attr($item->container) .'>';
		if ($item->_url) {
			$item->attr->class = ($item->_level == 'head' ? $item->_level.' ' : '')
					. (isset($item->attr->class) ? $item->attr->class : '')
					;
			if (isset($item->attr->title)) $item->attr->title = addslashes($item->attr->title);
			$ret .= '<a href="'.$item->_url.'" ';
			$ret .= sg_implode_attr($item->attr);
			$ret .= '>';
		}
		$ret .= $item->_text;
		if ($item->_url) $ret .= '</a>';

		$submenus = (Object) [];
		foreach ($item as $submenuKey => $submenu) {
			if (is_object($submenu) && $submenu->_level) {
				$submenus->$submenuKey = $submenu;
			}
		}
		if ((Array) $submenus) {
			$ret .= _NL._user_menu($submenus, false);
		}
		$ret .= '</li>'._NL;
	}
	$ret .= '</ul>'._NL;
	return $ret;
}


/**
 * Generate user menu for ribbon
 *
 * @param Mixed
 * @return String on no parameter
 *
 * @usage
 * user_menu(name[:option] , text , url [ , {key: value, ...}] // Add top level menu
 * user_menu(top_name[:option] , sub_name , text , url [ , {key: value, ...} ] // Add pulldown menu
 * user_menu() // Make menu
 */
function user_menu() {
	static $items = NULL;
	if (is_null($items)) $items = (Object)[];

	$args = func_get_args();
	if (!isset($args[0])) {
		if ($items && is_object($items) && count((array)$items)) {
			return _user_menu($items);
		}
		return;
	}

	list($level, $option) = explode(':', $args[0]);

	if ($level && $option == 'remove') {
		unset($items->{$level});
	} else if (!isset($items->{$level}) || $option == 'replace') {
		// Set new menu item as top menu
		$items->{$level} = (Object)[];
		$items->{$level}->_level = 'head';
		$items->{$level}->_text = $args[1];
		$items->{$level}->_url = $args[2];
		if (isset($args[3])) $items->{$level}->attr = sg_json_decode($args[3]);
		if (isset($args[4])) $items->{$level}->container = sg_json_decode($args[4]);
		if ($option == 'first') property_reorder($items, $level, 'top');
	} else {
		// Set submenu
		$items->{$level}->{$args[1]} = (Object)[];
		$items->{$level}->{$args[1]}->_level = $level;
		$items->{$level}->{$args[1]}->_text = $args[2];
		$items->{$level}->{$args[1]}->_url = $args[3];
		if ($args[4]) $items->{$level}->{$args[1]}->attr = sg_json_decode($args[4]);
		if ($args[5]) $items->{$level}->{$args[1]}->container = sg_json_decode($args[5]);
	}
}


/**
 * Convert option string seperate by colon ( , ) into array with each option key
 *
 * @param Mixed $o
 * @param String $key
 * @return Mixed
 *
 * option->_src = source text
 * option->_text = text
 * option->_value = value in array
 * option->{$key} = true if key was exists
 */
function option($o=NULL,$key=NULL) {
	$option = (Object) [];
	if ($o && is_string($o)) {
		$option->_src=$o;
		$option->_text='\''.implode('\',\'',explode(',',$o)).'\'';
		$option->_value=explode(',',$o);
		foreach (explode(',',$o) as $ok) $option->$ok=true;
	} else if ($o && is_object($o)) {
		$option->_src=$o;
		foreach ($o as $k=>$v) {
			$option->_value[]=$v;
			$option->$k=$v;
		}
	}
	return isset($key) ? $option->$key:$option;
}


function options($module=NULL,$orgid=NULL,$tpid=NULL) {
	static $optionsAll=array();
	$prevStr=$module.'.options.';
	if ($module && !array_key_exists($module, $optionsAll)) {
		$optionsAll[$module]=new stdClass();
		foreach (cfg() as $key => $value) {
			if (substr($key,0,strlen($prevStr))!=$prevStr) continue;
			$optionsAll[$module]->{substr($key,strlen($prevStr))}=$value;
		}
		//debugMsg(print_o($optionsAll[$module],'$optionsAll['.$module.']'));
	}
	if ($module) {
		$options=$optionsAll[$module];
		if ($orgid) {
			$options->getOptionFromOrg=true;
			$options->orgid=$orgid;
		}
		if ($tpid) {
			$options->getOptionFromProject=true;
			$options->tpid=$tpid;
		}
	} else {
		$options=$optionsAll;
	}
	return $options;
}


/**
 * Get api from external website
 *
 * @param String $host
 * @param Int $port
 * @param String $username
 * @param String $password
 *
 * @return String
 */
function getapi($host,$port=NULL,$username=NULL,$password=NULL) {
	// Get file from camera with curl function
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $host);
	if ($username && $password) curl_setopt($ch, CURLOPT_USERPWD, $username.':'.$password);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_ALL);
	if ($port) curl_setopt($ch, CURLOPT_PORT, $port);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_VERBOSE, 0);
	//curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
	//$headers = array("Cache-Control: no-cache",);
	//curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	//curl_setopt($ch, CURLOPT_FILE, $fh);

	$result = curl_exec($ch);
	$info = curl_getinfo($ch);
	$info['error'] = curl_error($ch);
	curl_close($ch);
	if (substr($result,0,1)=='{') {
		$info['result']=json_decode($result);
	} else {
		$info['result']=$result;
	}
	return $info;
}


/**
 * Get first value of parameter that not null and not empty string
 *
 * @param Mixed $arg1..$argn
 *
 * @return Mixed
 */
function get_first() {
	$args = func_get_args();
	foreach ($args as $key => $value) {
		if (!(is_null($value) || $value === '')) {
			return is_string($value) ? trim($value) : $value;
		}
	}
	return NULL;
}


/**
 * Show notify box
 *
 * @param String $str
 *
 * @return String
 */
function notify($str='',$time=5000) {
	if (is_array($str)) $str=implode(' , ',$str);
	$ret='<script type="text/javascript">$(document).ready(function(){notify("'.addslashes($str).'",'.$time.');});</script>';
	return $ret;
}


/**
 * PHP Evaluate
 *
 * @param String $str
 * @param String $prefix
 * @param String $postfix
 *
 * @return String
 */
function eval_php($str=null,$prefix=NULL,$postfix=NULL) {
	if (!isset($str)) return false;
	try {
		ob_start();
		$return = @eval('?>'.$str);
		if ( $return === false && ( $error = error_get_last() ) ) {
			echo '<font color="red">EVAL ERROR</font>';
		}
		return $prefix.ob_get_clean().$postfix;
	} catch (Throwable $ex) {
		return '<font color="red">EVAL ERROR</font>';
	}
}


function isMobileDevice(){
	$aMobileUA = array(
			'/iphone/i' => 'iPhone',
			'/ipod/i' => 'iPod',
			'/ipad/i' => 'iPad',
			'/android/i' => 'Android',
			'/blackberry/i' => 'BlackBerry',
			'/webos/i' => 'Mobile'
		);

	//Return true if Mobile User Agent is detected
	foreach($aMobileUA as $sMobileKey => $sMobileOS){
		if(preg_match($sMobileKey, $_SERVER['HTTP_USER_AGENT'])){
			return $sMobileOS;
		}
	}
	//Otherwise return false..
	return false;
}


/**
 * Convert array parameter to name parameter and convert option string separate with , to array with option key is true
 *
 * @param Mixed
 * @return Object
 */
function para() {
	$result=new stdClass();
	$args=func_get_args();
	$argc=func_num_args();
	$from=0;
	if (is_numeric($args[$argc-1])) $from=array_pop($args);
	//echo print_o($args,'$args').'<br />From : '.$from.'<br />';

	// set first argument to main parameter
	$para=array_shift($args);
	if (is_array($para)) /*do nothing */;
	else if (is_string($para)) $para=explode('/',$para);
	else if (is_object($para)) $para=(array)$para;
	else $para=array();
	if ($from) $para=array_slice($para,$from);
	array_walk($para,'__trim');

	// set other argument to be default
	foreach ($args as $item) {
		if (!is_string($item)) continue;
		if (preg_match('/([a-zA-Z0-9\-_]*)=(.*)/',$item,$out)) $result->{$out[1]}=$out[2];
	}

	$_src='';
	while ($para) {
		$key=array_shift($para);
		// หาก key เป็น object ให้เอาค่าทั้งหมดมา
		if (is_object($key)) {
			$_src.='Object=(';
			foreach ($key as $k=>$v) {
				$result->{$k}=$v;
				$_src.=$k.'='.(is_object($v)?'(Object)':$v).',';
			}
			$_src=substr($_src,0,-1);
			$_src.=')/';
			continue;
		}
		// หาก key ไม่เป็น string หรือ ว่างเปล่า แสดงว่าผิดพลาด
		if (!is_string($key) || trim($key)=='') {
			$_src.='(*error key is '.gettype($key).'*)/';
			continue;
		}
		// หาก key มีเครื่องหมาย = ให้แยก key ออกเป็น key กับ value เช่น key คือ detail=สวัสดี
		if (preg_match('/^([a-zA-Z0-9\-_]*)=(.*)/s',$key,$out)) {
			$result->{$out[1]}=$out[2];
			$_src.=$key.',';
			continue;
		}
		$value=array_shift($para);
	/*
			echo 'Key='.$key.' Value='.$value.'<br />';
			if ($value && is_string($value) && preg_match('/^([a-zA-Z0-9\-_]*)=(.*)/s',$value,$out)) {
				echo 'Out = '.print_r($out,1).'<br />';
				if (empty($out[1])) continue;
				$result->{$out[1]}=$out[2];
				$_src.=$key.'='.(is_object($value)?'(Object)':$value).'/';
				continue;
			}
	*/
		$result->{$key}=$value;
		$_src.=$key.'/'.(is_object($value)?'(Object)':$value).'/';
	}

	$_src=substr($_src,0,-1);
	if (isset($result->_src)) unset($result->_src);
	$result->_src=$_src;
	if (isset($result->option) && is_string($result->option)) {
		$option=$result->option;
		unset($result->option);
		$result->option=option($option);
	}
	return $result;
}


function _crypt_key_ed($txt,$encrypt_key) {
	$encrypt_key = md5((String) $encrypt_key);
	$ctr=0;
	$tmp = '';
	for ($i=0;$i<strlen($txt);$i++){
			if ($ctr==strlen($encrypt_key)) $ctr=0;
			$tmp.= substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1);
			$ctr++;
	}
	return $tmp;
}

if (!function_exists('array_walk_recursive')) {
    function array_walk_recursive(&$input, $funcname, $userdata = "") {
        if (!is_callable($funcname)) return false;
        if (!is_array($input)) return false;
       foreach ($input AS $key => $value) {
            if (is_array($input[$key])) array_walk_recursive($input[$key], $funcname, $userdata);
            else {
                $saved_value = $value;
                if (!empty($userdata)) $funcname($value, $key, $userdata); else $funcname($value, $key);
                if ($value != $saved_value) $input[$key] = $value;
            }
        }
        return true;
    }
}

/**
 * do php command string in php script
 *
 * @param String $str
 * @return String
 */
function do_php($str=null) {
	if ($str) {
		ob_start();
		eval ('?>'.$str);
		return ob_get_clean();
	}
}

function print_o() {
	$echo = false;
	$inline = false;
	$args=func_get_args();
	if (empty($args)) return;

	$ret = '';

	$last_arg=$args[count($args)-1];
	if ($last_arg === 1 || $last_arg === true) {
		$echo = true;
		array_pop($args);
	} else if ($last_arg === 2) {
		$inline = true;
		array_pop($args);
	}

	foreach ($args as $key => $value) {
		if (is_string($value)) continue;
		$next_value = isset($args[$key+1]) ? $args[$key + 1] : NULL;
		$title = is_string($next_value) ? $next_value : '';
		if ($echo) {
			echo '<em>'.$title.'</em>';
			echo Arrays::value($value,$title);
		} else {
			$ret .= '<em>'.$title.'</em>';
			$ret .= Arrays::value($value, $title, array('class' => $inline ? '-inline' : ''));
		}
		unset($title);
	}
	return $ret;
}

function object_merge() {
	$result=array();
	foreach (func_get_args() as $arg) {
		if (is_object($arg)) $arg=(array)$arg;
		if (is_array($arg)) $result=array_merge($result,$arg);
	}
	return (object)$result;
}

/*
* @param Object $args
* @param Numeric $flag , at last argument 1 = argument is array
* @return Object
*/
function object_merge_recursive() {
	$args = func_get_args();
	//$flag = is_numeric($args[count($args) - 1]) ? array_pop($args) : 0;

	$firstArg = array_shift($args);
	$result = is_object($firstArg) ? clone $firstArg : $firstArg;

	if (is_null($result)) $result = (Object) [];

	foreach ($args as $arg) {
		foreach ($arg as $key => $value) {
			if (is_object($value)) {
				//debugMsg('Merge object key '.$key);
				$result->{$key} = object_merge_recursive($result->{$key}, $value);
			} else if (is_array($value)) {
				//debugMsg('ARRAY KEY = '.$key);
				//debugMsg($value,'$value');
				//debugMsg($result->{$key},'$result');
				if (!isset($result->{$key})) $result->{$key} = array();
				$result->{$key} = object_merge_recursive($result->{$key}, $value);
			} else if (is_array($result)) {
				$result[$key] = $value;
			} else {
				$result->{$key} = $value;
			}
		}
	}
	return $result;
}

/**
 * @return string
 * @param string
 * @desc Strip forbidden tags and delegate tag-source check to removeEvilAttributes()
 */
function html_lt($str) {
	$str=str_replace('<','&lt;', $str);
	return $str;
}

function nls2p($str) {
	return '<p>'.preg_replace('#([\r\n]\s*?[\r\n]){2,}#', '</p>$0<p>', $str).'</p>';
	//	return str_replace('<p></p>', '', '<p>'.preg_replace('#([\r\n]\s*?[\r\n]){2,}#', '</p>$0<p>', $str).'</p>');
}

function nl2br2($string) {
	$string = str_replace(array("\r\n\r\n", "\r\r", "\n\n"), "<br /><br />", $string);
	$string = str_replace('<br /><br />', "<br /><br />"._NL._NL, $string);
	return $string;
}

function br2p($string) {
	return preg_replace('#<p>[\n\r\s]*?</p>#m', '', '<p>'.preg_replace('#(<br\s*?/?>){2,}#m', '</p><p>', $string).'</p>');
}

function property_reorder(&$object,$property,$action) {
	list($action,$dest)=explode(' ',$action);
	if (in_array($action,array('before','after')) && !property_exists($object,$dest)) return false;
	$allproperty=get_object_vars($object);
	$property_value=$object->{$property};
	foreach ($allproperty as $k=>$v) unset($object->{$k});
	if ($action=='top') $object->{$property}=$property_value;
	foreach ($allproperty as $k=>$v) {
		if ($k==$property) {
			continue;
		} else if ($k==$dest) {
			if ($action=='after') $object->{$k}=$v;
			$object->{$property}=$property_value;
			if ($action=='before') $object->{$k}=$v;
		} else $object->{$k}=$v;
	}
	if ($action=='bottom') $object->{$property}=$property_value;
}

/**
 * Code from other
 */

/**
 * Function converts an Javascript escaped string back into a string with specified charset (default is UTF-8).
 * Modified function from http://pure-essence.net/stuff/code/utf8RawUrlDecode.phps
 *
 * @param string $source escaped with Javascript's escape() function
 * @param string $iconv_to destination character set will be used as second paramether in the iconv function. Default is UTF-8.
 * @return string
 */

function unescape($source, $iconv_to = 'UTF-8') {
  $decodedStr = '';
  $pos = 0;
  $len = strlen ($source);
  while ($pos < $len) {
      $charAt = substr ($source, $pos, 1);
      if ($charAt == '%') {
          $pos++;
          $charAt = substr ($source, $pos, 1);
          if ($charAt == 'u') {
              // we got a unicode character
              $pos++;
              $unicodeHexVal = substr ($source, $pos, 4);
              $unicode = hexdec ($unicodeHexVal);
              $decodedStr .= code2utf($unicode);
              $pos += 4;
          }
          else {
              // we have an escaped ascii character
              $hexVal = substr ($source, $pos, 2);
              $decodedStr .= chr (hexdec ($hexVal));
              $pos += 2;
          }
      }
      else {
          $decodedStr .= $charAt;
          $pos++;
      }
  }

  if ($iconv_to != "UTF-8") {
      $decodedStr = iconv("UTF-8", $iconv_to, $decodedStr);
  }

  return $decodedStr;
}

/**
 * Function coverts number of utf char into that character.
 * Function taken from: http://sk2.php.net/manual/en/function.utf8-encode.php#49336
 *
 * @param int $num
 * @return utf8char
 */
function code2utf($num){
  if($num<128)return chr($num);
  if($num<2048)return chr(($num>>6)+192).chr(($num&63)+128);
  if($num<65536)return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
  if($num<2097152)return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128) .chr(($num&63)+128);
  return '';
}

/**
 * Convert exif datetime to php time
 *
 * @param String $exifString
 * @param String $dateFormat
 * @return String
 */
function convertExifToTimestamp($exifString, $dateFormat) {
	$exifPieces = explode(" ", $exifString);
	return date($dateFormat,strtotime(str_replace(":","-",$exifPieces[0])." ".$exifPieces[1]));
}

function cropImage($nw, $nh, $source, $stype, $dest) {
	$size = getimagesize($source);
	$w = $size[0];
	$h = $size[1];

	switch($stype) {
		case 'gif':
		$simg = imagecreatefromgif($source);
		break;
		case 'jpg':
		$simg = imagecreatefromjpeg($source);
		break;
		case 'png':
		$simg = imagecreatefrompng($source);
		break;
	}

	$dimg = imagecreatetruecolor($nw, $nh);

	$wm = $w/$nw;
	$hm = $h/$nh;

	$h_height = $nh/2;
	$w_height = $nw/2;

	if($w> $h) {
		$adjusted_width = $w / $hm;
		$half_width = $adjusted_width / 2;
		$int_width = $half_width - $w_height;
		imagecopyresampled($dimg,$simg,-$int_width,0,0,0,$adjusted_width,$nh,$w,$h);
	} elseif (($w <$h) || ($w == $h)) {
		$adjusted_height = $h / $wm;
		$half_height = $adjusted_height / 2;
		$int_height = $half_height - $h_height;
		imagecopyresampled($dimg,$simg,0,-$int_height,0,0,$nw,$adjusted_height,$w,$h);
	} else {
		imagecopyresampled($dimg,$simg,0,0,0,0,$nw,$nh,$w,$h);
	}

	imagejpeg($dimg,$dest,100);
}
?>