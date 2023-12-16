<?php
/**
* SOFTGANZ :: lib.base.php
*
* Softganz Base Library
*
* Copyright (c) 2000-2020 The SoftGanz Group By Panumas Nontapan
* Authors : Panumas Nontapan <webmaster@softganz.com>
*         : http://www.softganz.com/
* ============================================
* This module is core of web application
*
* This program is free software. You can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License.
* ============================================

Created :: 2019-12-08
Modify  :: 2023-12-16
Version :: 3
*/

namespace SG;

if (!defined('_NL')) define('_NL', "\r\n");


function uniqid($len = 10) {
	$token_id = '0'.substr(
		md5(
			str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ")
		)
		,0, $len
	);
	return $token_id;
	// return \uniqid(0);
}

function debug($value = NULL) {
	static $debugValue = false;
	if ($value === true || $value === false) $debugValue = $value;
	return $debugValue;
}



function debugStart() { debug(true); }



function debugStop() { debug(false); }



function print_o() {
	$echo = false;
	$inline = false;
	$args = func_get_args();
	if (empty($args)) return;

	$ret = '';

	$last_arg = $args[count($args) - 1];
	if ($last_arg === 1 || $last_arg === true) {
		$echo = true;
		array_pop($args);
	}
	if ($last_arg === 2) {
		$inline = true;
		array_pop($args);
	}
	foreach ($args as $key => $value) {
		if (is_string($value)) continue;
		$next_value = isset($args[$key+1]) ? $args[$key + 1] : NULL;
		$title = is_string($next_value) ? $next_value : '';
		$varTitle = '<b>'.$title.(is_object($value) ? ' ['.get_class($value).']' : '').'</b>';
		if ($echo) {
			echo $varTitle;
			echo Arrays::value($value, $title);
		} else {
			$ret .= $varTitle;
			$ret .= Arrays::value($value, $title, ['class' => $inline ? '-inline' : '']);
		}
		unset($title);
	}
	return $ret;
}



/**
* Store debug message and display in div class="debug" of page
*
* @param String $msg
* @return String
*/
function debugMsg($msg = NULL, $varname = NULL) {
	\debugMsg($msg, $varname);
}



/**
* Get first value of parameter that not null and not empty string
*
* @param Mixed $arg1..$argn
*
* @return Mixed
*/
function getFirst() {
	for ( $i = 0; $i < func_num_args(); $i++ ) {
		$value = func_get_arg($i);
		if (!(is_null($value) || $value === '')) {
			return $value;
		}
	}
	return NULL;
}

function getFirstInt() {
	for ( $i = 0; $i < func_num_args(); $i++ ) {
		$value = func_get_arg($i);
		if (is_object($value) || is_array($value)) continue;
		if (!(is_null($value) || trim($value) === '')) {
			return intval($value);
		}
	}
	return NULL;
}


/**
* Get API from external website
*
* @param String $host
* @param Int $port
* @param String $username
* @param String $password
*
* @return String
*/
function api($args = []) {
	if (is_string($args)) $args = ['url' => $args];

	$default = '{port: null, username: null, password: null, type: "text"}';
	$options = json_decode($options, $default);

	// Get file from camera with curl function
	$ch = curl_init();

	$options = [
		CURLOPT_URL => $args['url'],
		CURLOPT_RETURNTRANSFER => isset($args['returnTransfer']) ? $args['returnTransfer'] : true,
		// CURLOPT_RETURNTRANSFER => true,

		CURLOPT_SSL_VERIFYHOST => 0,
		CURLOPT_VERBOSE => 0,
	];

	if ($args['method'] == 'post') $options[CURLOPT_POST] = 1;
	if ($args['postField']) $options[CURLOPT_POSTFIELDS] = $args['postField'];

	// curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	// curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_ALL);
	// curl_setopt($ch, CURLOPT_TIMEOUT, 240);
	// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// curl_setopt($ch, CURLOPT_VERBOSE, 0);
	// if (isset($username) && isset($password)) curl_setopt($ch, CURLOPT_USERPWD, $username.':'.$password);
	// if (isset($port)) curl_setopt($ch, CURLOPT_PORT, $port);


	//curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
	//$headers = array("Cache-Control: no-cache",);
	//curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	//curl_setopt($ch, CURLOPT_FILE, $fh);

	curl_setopt_array($ch,$options);

	$result = curl_exec($ch);
	$info = curl_getinfo($ch);
	$info['error'] = curl_error($ch);
	curl_close($ch);

	if ($args['result'] === 'json') {
		if (debug()) debugMsg($result);
		// $info['text'] = $result;
		// $info['result'] = \json_decode($result);
		return \json_decode($result);
	} else if ($args['result'] === 'text') {
		return $result;//['result'];
	} else {
		return $result;
	}
}



/**
* @param Object $args
* @param Numeric $flag , at last argument 1 = argument is array
* @return Object
*/
function object_merge_recursive() {
	$args = func_get_args();
	//$flag = is_numeric($args[count($args) - 1]) ? array_pop($args) : 0;
	$firstArg = array_shift($args);
	$result = is_object($firstArg) ? clone $firstArg : $firstArg;
	if (debug()) debugMsg($firstArg, '\SG\ObjectMerge $firstArg');
	if (debug()) debugMsg($args,'\SG\ObjectMerge $args');

	foreach ($args as $arg) {
		if (gettype($arg) == 'NULL') continue;
		foreach ($arg as $key => $value) {
			if (is_object($value)) {
				//debugMsg('Merge object key '.$key);
				if (!isset($result->{$key})) $result->{$key} = new \stdClass();
				$result->{$key} = object_merge_recursive($result->{$key}, $value);
			} else if (is_array($value)) {
				//debugMsg('ARRAY KEY = '.$key);
				//debugMsg($value,'$value');
				//debugMsg($result->{$key},'$result');
				//debugMsg('gettype(reset($value) = '.gettype(reset($value)));
				if (!isset($result->{$key})) $result->{$key} = Array();
				if (gettype(reset($value)) == 'object') {
					//debugMsg($value,'$valueObject');
					$result->{$key} = $value;
				} else {
					//debugMsg($value,'$valueArray');
					$result->{$key} = object_merge_recursive($result->{$key}, $value);
				}
			} else if (is_array($result)) {
				//debugMsg($result,'is_array($result)');
				$result[$key] = $value;
			} else {
				if (debug()) debugMsg('SET $result->'.$key.' = '.$value.' ('.gettype($value).')');
				$result->{$key} = $value;
			}
		}
	}
	return $result;
}


function isWidget($var) {
	return is_object($var) && method_exists($var, 'build');
}

/**
* @param Mixed $arg1[,$arg2,...]
* @return Object
* Last arg is default value
* Eg. \SG\json_decode($option2,$option1,$default)
*/
function json_decode($json, $default = '{}') {
	$regex = '/(,|\{)[ \t\n]*(\w+)[ ]*:[ ]*/';
	$result = (Object) array();
	// Last argument is default, so reverse argument to process last argument first
	$args = array_reverse(func_get_args());

	if (debug()) debugMsg($args,'\SG\json_decode $args');

	foreach ($args as $i=>$json) {
		//debugMsg('parameter = '.$json);
		if (is_string($json)) $json = trim($json);

		if (is_array($json)) ;
		else if (is_object($json)) ;
		else if (is_string($json) && in_array(substr($json,0,1), array('{','['))) {
			$json = preg_replace($regex,'$1"$2":',$json);
			if (debug()) debugMsg('\SG\json_decode $jsonString = '.$json);
			$json = \json_decode($json);
			if (debug()) debugMsg($json, '\SG\json_decode $jsonObject');
		} else if (is_string($json) && substr($json,0,1) == 'O') {
			$json = unserialize($json);
		} else {
			$json = NULL;
		}
		//debugMsg('START MERGE #'.$i);
		//debugMsg($result, 'First');
		//debugMsg($json, 'With');
		$result = object_merge_recursive($result, $json);
		//debugMsg($result, '$sg_json_decode result #'.$i);
	}
	//debugMsg($result, '$sg_json_decode');
	return $result;
}

function json_encode($input) {
	if (defined('JSON_UNESCAPED_UNICODE')) {
		return \json_encode($input,JSON_UNESCAPED_UNICODE+JSON_PRETTY_PRINT+JSON_UNESCAPED_SLASHES);
	} else {
		return preg_replace_callback('/\\\\u([0-9a-zA-Z]{4})/', function ($matches) {
			return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UTF-16');
		}, \json_encode($input));
	}
}

function isJson($string) {
	json_decode($string);
	return json_last_error() === JSON_ERROR_NONE;

			if (empty($config)) {
			// Delete on empty
			mydb::query(
				'DELETE FROM %property% WHERE `module` = "PROJECT" AND `name` = "SETTING" AND `propId` = :projectId LIMIT 1',
				[':projectId' => $this->projectId]
			);
		} else if (preg_match('/^[\[\{]/', $config)) {
			// Check JSON Valid
			$configDecode = json_decode($config);
			if (empty($configDecode)) {
				return ['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'รูปแบบของ JSON ไม่ถูกต้อง'];
			} else {
				// JSON Valid then save
				property($propertyKey, \SG\json_encode($configDecode));
			}
		} else {
			return ['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'รูปแบบของ JSON ไม่ถูกต้อง'];
		}
}

/**
 * @param $value
 * @return mixed
 */
function escapeJsonString($value) { # list from www.json.org: (\b backspace, \f formfeed)
	$escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
	$replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
	$result = str_replace($escapers, $replacements, $value);
	return $result;
}

function json_escape($value) {
	$escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
	$replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
	$escapers = ['"'];
	$replacements = ['\"'];
	// $result = str_replace($escapers, $replacements, $value);
	if (is_array($value)) {
		foreach ((Array) $value as $key => $item) {
			$value[$key] = json_escape($item);
		}
		return $value;
	} else {
		return str_replace($escapers, $replacements, $value);
	}
}

function confirm() {
	return strtoupper(post('confirm')) == strtoupper(_CONFIRM_VALUE);
}



function dropbox($text, $option = NULL) {
	$dropbox = new \Dropbox();
	return $dropbox->build($text, $option);
}



/**
* Grenerate QR-Code Image
* @param String $url // Url with urlencode()
*/
function qrcode($url, $options = '{}') {
	$defaults = '{showUrl: true, domain: false, width: 160, height: 160, imgWidth: "100%", imgHeight: "100%"}';
	$options = json_decode($options, $defaults);

	if (preg_match('/^(http\:|https\:)/', $url, $out)) {
		// Full url address
	} else {
		$domain = $options->domain ? $options->domain : _DOMAIN;
	}
	$urlEncode = $domain.urlencode($url);
	$qrCode = '<img class="-qrcode" src="https://chart.googleapis.com/chart?cht=qr&chl='.$urlEncode.'&chs='.$options->width.'x'.$options->height.'&choe=UTF-8&chld=L|2" alt="QR-Code" width="'.$options->imgWidth.'" height="'.$options->imgHeight.'">'
		. ($options->showUrl ? '<span class="-url">'.urldecode($urlEncode).'</span>' : '');
	return $qrCode;
}



/**
* Show field for inline edit
*
* @param String/Array $fld
* @param String $text
* @param Boolean $is_edit
* @param String $input_type
* @param Array $data
* @return String
*/
function inlineEdit($fld = [], $text = NULL, $is_edit = NULL, $input_type = 'text', $data = [], $emptytext = '...') {
	$ret = '';
	$attr = '';

	if (is_string($fld)) {$t = $fld; $fld = array(); $fld['fld'] = $t;}

	$dataOptions = (Object) [];
	if (array_key_exists('options', $fld)) {
		$dataOptions = json_decode($fld['options']);
		unset($fld['options']);
	}

	if (isset($fld['container'])) {
		$container = json_decode($fld['container']);
		unset($fld['container']);
	}

	if (isset($fld['desc'])) {
		$desc = $fld['desc'];
		unset($fld['desc']);
	}

	if (isset($fld['posttext'])) {
		$posttext = $fld['posttext'];
		unset($fld['posttext']);
	}

	if (is_null($fld['min-value'])) unset($fld['min-value']);
	if (is_null($fld['max-value'])) unset($fld['max-value']);

	$ret .= '<span'
		.' class="widget-inlineedit inline-edit-item'.($container->class ? ' '.$container->class : '').($input_type ? ' -'.$input_type : '').'"'
		. ($container->id ? ' id="'.$container->id.'"' : '')
		. ($fld['updateUrl'] ? ' data-update-url="'.$fld['updateUrl'].'"' : '')
		. '>';
	if ($fld['label']) $ret .= '<label class="inline-edit-label">' . $fld['label'] . '</label>';


	$class = getFirst($fld['class'],$dataOptions->class);
	if ($dataOptions->class) unset($fld['class']);

	if ($is_edit) {

		if (is_string($data)) $data = explode(',', '==เลือก==,' . $data);
		else if (is_array($data) && count($data) > 0) $data = array('==เลือก==') + $data;

		if ($fld['fld']) $attr .= 'data-fld="' . $fld['fld'] . '" ';
		unset($fld['fld']);


		if (array_key_exists('value', $fld)) {
			$value = htmlspecialchars($fld['value']);
			unset($fld['value']);
		}
		//debugMsg(sg_json_encode($dataOptions));

		$placeholder = getFirst($dataOptions->placeholder, $emptytext);

		if ($input_type == 'textarea' && $fld['button'] != 'no') $fld['button'] = 'yes';

		foreach ($fld as $k => $v) $attr .= 'data-' . $k . '="' . $v . '" ';

		if ($dataOptions) $attr .= ' data-options=\''.sg_json_encode($dataOptions).'\'';


		if (is_array($text)) {
			$ret .= '<ul>'._NL;
			foreach ($text as $k => $v) {
				$ret .= '<li><span class="inline-edit-field" data-fld="'.$k.':'.$fld.'" data-type="'.$input_type.'" '._NL.'data-data="'.htmlspecialchars(json_encode($data)).'" data-value="'.htmlspecialchars($v).'">'.getFirst(trim($v),$emptytext).'</span></li>'._NL;
			}
			$ret .= '</ul>'._NL;
		} else {
			$text = trim($text);
			$value = isset($value) ? $value : $text;
			$require = $fld['require']?'<span class="require">!</span>':'';
			if ($input_type == 'datepicker') {
				$value = $value ? sg_date($value, 'd/m/Y') : date('d/m/Y');
			}
			if (is_null($text) || $text == '') {
				$text = '<span class="placeholder -no-print">'.$placeholder.'</span>';
				$class .= ' -empty';
			}
			else if ($fld['ret'] == 'nl2br') $text = trim(nl2br($text));
			else if ($fld['ret'] == 'html') $text = trim(sg_text2html($text));
			else if ($fld['ret'] == 'text') $text = trim(str_replace("\n",'<br />',$text));
			else if ($fld['ret'] == 'money' && $text != '') $text = number_format(sg_strip_money($text), 2);
			else if (substr($fld['ret'], 0, 4) == 'date' && $text) {
				list($retType, $retFormat) = explode(':', $fld['ret']);
				if (!$retFormat) $retFormat = 'ว ดดด ปปปป';
				$text = sg_date($value, $retFormat);
			}
			if ($input_type == "textfield") {
				$ret .= '<span class="inline-edit-view">'.$text.'</span>';
			} else if (in_array($input_type, ['radio', 'checkbox'])) {
				list($choice, $label, $info) = explode(':', $text);
				$choice = trim($choice);
				$name = getFirst($fld['name'], $fld['fld']);
				if ($label == '' && strpos($text, ':') == false) $label = $choice;
				$label = trim($label);
				$ret .= '<label><input class="inline-edit-field '
					.'-'.$input_type
					.($class ? ' '.$class : '').'" '
					.($dataOptions->id ? 'id="'.$dataOptions->id.'"' : '')
					.'type="'.$input_type.'" '
					.'data-type="'.$input_type.'" '
					.'name="'.$name.'" '
					.'value="'.$choice.'"'
					.(isset($value) && $value == $choice ? ' checked="checked"':'')
					.' onclick="" '
					.$attr
					.' style="width: 1.1em; min-width: 1.1em; vertical-align:middle;" '
					.'/> '
					.$label
					.'</label>'
					.$require
					.($info ? '<sup class="sg-info" title="'.$info.'">?</sup>' : '')
					.$posttext;
			} else {
				$ret .= '<span class="inline-edit-field '
					.'-'.$input_type
					.($class ? ' '.$class : '')
					.'" '
					.($dataOptions->id ? 'id="'.$dataOptions->id.'"' : '')
					.'onclick="" '
					.$attr
					.' data-type="'.$input_type.'" '
					.'data-value="'.htmlspecialchars($value).'" '
					.($data ? ' data-data="'.htmlspecialchars(\json_encode($data)).'"' : '')
					.' title="คลิกเพื่อแก้ไข">'
					.'<span>'.$text.'</span>'
					.'</span>'
					.$require
					.$posttext;
			}
			if ($desc) $ret .= '<div class="inline-edit-desc">'.$desc.'</div>';
		}
	} else {
		$ret .= '<span class="inline-edit-view '
			.'-'.$input_type
			.($class ? ' '.$class : '').'" '
			.'>';
		if (is_array($text)) {
			foreach ($text as $k => $v) $ret .= implode(' , ', $text);
		} else {
			if ($fld['ret'] == 'html') {
				$ret .= trim(sg_text2html($text));
			} else if ($fld['ret'] == 'text') {
				$ret .= trim(str_replace("\n", '<br />', $text));
			} else if ($input_type == "money") {
				$ret .= number_format(sg_strip_money($text), 2);
			} else if (in_array($input_type, array('radio', 'checkbox'))) {
				list($choice, $label, $info) = explode(':', $text);
				$choice = trim($choice);
				$name = getFirst($fld['name'],$fld['fld']);
				if ($label == '' && strpos($text, ':') == false) $label = $choice;
				$label = trim($label);
				$ret .= '<input type="'.$input_type.'" '
					.($fld['value'] == $choice ? 'checked="checked" readonly="readonly" disabled="disabled"' : 'disabled="disabled"')
					.' style="margin:0;margin-top: -1px; display:inline-block;min-width: 1em; vertical-align: middle;" /> '
					.$label;
			} else if (substr($fld['ret'], 0, 4) == 'date') {
				$format = substr($fld['ret'], 5);
				$ret .= $text ? sg_date($text, $format) : '';
			} else {
				$ret .= $text;
			}
		}
		$ret .= $posttext;
		$ret .= '</span>';
	}
	$ret .= '</span>';
	return $ret;
}



/**
* Exlpode address to array
*
* @param $address
* @return Array
*/
function explode_address($address = '',$areacode = NULL) {
	$address = trim(preg_replace('/  /',' ',$address));
	$result = [
		'house' => '',
		'village' => '',
		'tambon' => '',
		'ampur' => '',
		'changwat' => '',
		'villageCode' => '',
		'tambonCode' => '',
		'tambonCode' => '',
		'ampurCode' => '',
		'changwatCode' => '',
		'zipCode' => '',
	];

	if (preg_match('/\s*([0-9]{5})$/',$address,$out)) {
		$result['zipCode'] = $out[1];
	} else {
		$result['zipCode'] = '';
	}

	if (preg_match('/(.*)((หมู่|หมู่ที่|ม\.)\s*([0-9]+))/',$address,$out)) {
		if (in_array($out[3], ['หมู่','หมู่ที่','ม.']) && is_numeric($out[4])) {
			$result['village'] = $out[4];
			// $house = $out[1];
			$address = str_replace($out[2], '', $address);
			$address = trim(preg_replace('/  /',' ',$address));
		}
		// debugMsg($house);
		// debugMsg($out,'$outVillage');
	}

	// If Tambon
	if (preg_match('/(.*)(แขวง|ตำบล|ต\.)(.*)/',$address,$out)) {
		if (!isset($house)) $house = trim($out[1]);
		list($tambon) = explode(' ',trim($out[3]));
		$result['tambon'] = $result['tambonName'] = $tambon;
		// debugMsg($out,'$outTambon');
	}

	// If Ampur
	if (preg_match('/(.*)(เขต|อำเภอ|อ\.)(.*)/',$address,$out)) {
		if (!isset($house)) $house = trim($out[1]);
		list($ampur) = explode(' ',trim($out[3]));
		$result['ampur'] = $result['ampurName'] = $ampur;
		//debugMsg($out,'$outAmpur');
	}

	// If Changwat
	if (preg_match('/(.*)(จังหวัด|จ\.)(.*)/',$address,$out)) {
		if (!isset($house)) $house = trim($out[1]);
		list($changwat) = explode(' ',trim($out[3]));
		$result['changwat'] = $result['changwatName'] = $changwat;
		//debugMsg($out,'$outChangwat');
	}

	if (!isset($house)) $house = $address;


	$result['house'] = trim($house);

	if (empty($areacode)) {
		$stmt = 'SELECT cos.`subdistid` `areacode`, CONCAT(`subdistname`, " ", `distname`, " ", `provname`) `address`
			FROM %co_subdistrict% cos
				LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(cos.`subdistid`,4)
				LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(cos.`subdistid`,2)
			WHERE cos.`subdistname` = :tambonName AND cod.`distname` = :ampurName AND cop.`provname` = :changwatName
			LIMIT 1
			';
		$rs = \mydb::select($stmt, ':tambonName', $result['tambonName'], ':ampurName', $result['ampurName'], ':changwatName', $result['changwatName']);
		//debugMsg($rs,'$rs');
		$areacode = $rs->areacode;
	}

	$result['tambonCode'] = substr($areacode,4,2);
	$result['ampurCode'] = substr($areacode,2,2);
	$result['changwatCode'] = substr($areacode,0,2);
	$result['villageCode'] = $result['village'] ? sprintf('%02d',$result['village']) : '';

	if (empty($result['tambonCode'])) $result['tambonCode'] = '';
	if (empty($result['ampurCode'])) $result['ampurCode'] = '';
	if (empty($result['changwatCode'])) $result['changwatCode'] = '';
	if (empty($result['villageCode'])) $result['villageCode'] = '';
	$result['areaCode'] = NULL;
	if ($areacode) {
		$result['areaCode'] = str_pad($result['changwatCode'],2,'0',STR_PAD_RIGHT)
		. str_pad($result['ampurCode'],2,'0',STR_PAD_RIGHT)
		. str_pad($result['tambonCode'],2,'0',STR_PAD_RIGHT)
		. str_pad($result['villageCode'],2,'0',STR_PAD_RIGHT);
	}
	if (strlen($result['areaCode'] == 6 && $result['villageCode'])) {
		$result['areaCode'] .= $result['villageCode'];
	}
	//debugMsg($result,'$result');
	return $result;
}



/**
* Implode address
*
* @param Record Set $rs
* @return String
*/
function implode_address($rs, $type = 'long') {
	if (is_array($rs)) $rs = (Object) $rs;
	$areaCode = getFirst($rs->areaCode, $rs->areacode);
	$isBangkok = substr($areaCode, 0, 2) === '10';

	$words['short'] = [
		'village' => 'ม.',
		'tambon' => $isBangkok ? 'แขวง' : 'ต.',
		'ampur' => $isBangkok ? 'เขต' : 'อ.',
		'changwat' => $isBangkok ? 'จังหวัด' : 'จ.',
		'zip' => ' ',
	];
	$words['long'] = [
		'village' => 'หมู่ที่ ',
		'tambon' => $isBangkok ? 'แขวง' : 'ตำบล',
		'ampur' => $isBangkok ? 'เขต' : 'อำเภอ',
		'changwat' => $isBangkok ? 'จังหวัด' : 'จังหวัด',
		'zip' => 'รหัสไปรษณีย์',
	];

	$ampurName = getFirst($rs->ampurName, $rs->distname);
	$tambonName = getFirst($rs->tambonName, $rs->subdistname);
	$changwatName = getFirst($rs->changwatName, $rs->provname);
	$zipCode = getFirst($rs->zipCode, $rs->zip, $rs->zipcode);

	$result = trim($rs->house.($rs->soi?' ซอย'.$rs->soi:'')
		.($rs->road?' ถนน'.$rs->road:'')
		.($rs->village?' '.$words[$type]['village'].intval($rs->village):'')
		.($rs->villname?' บ้าน'.$rs->villname:'')
		.($tambonName?' '.$words[$type]['tambon'].$tambonName:'')
		.($ampurName?' '.$words[$type]['ampur'].$ampurName:'')
		.($changwatName?' '.$words[$type]['changwat'].$changwatName:'')
		.($zipCode ? ' '.$zipCode : ''));
	return $result;
}



/**
* Convert parameter Array/JSON/Object to Object
*
* @param Mixed $param
* @return Object
*/
function paramToObject($param) {
	if (is_string($param) && preg_match('/^{/',$param)) {
		$param = \SG\json_decode($param);
	} else if (is_object($param)) {
		// Do nothing
	} else if (is_array($param)) {
		$param = (Object) $param;
	} else {
		$id = $param;
		unset($param);
		$param->id = $id;
	}
	return $param;
}


class Arrays {
	static function value($arr = [], $name = '', $options = []) {
		if ($name && is_object($arr)) {$prefix = '->'; $suffix = '';}
		else if ($name && is_array($arr)) {$prefix = '['; $suffix = ']';}
		else $prefix = $suffix = '';

		$result = '<ul class="array-value '.(isset($options['class']) ? $options['class'] : '').'" style="margin:0 0 0 15px;padding:0px;">'._NL;
		if ( is_object($arr) || (is_array($arr) and count($arr) > 0) ) {
			foreach ( $arr as $key=>$value ) {
				$vtype = GetType($value);
				$result .= '<li><span style="color:#ff9a56">'.$name.$prefix.$key.$suffix.'</font> <font color=gray>['.(is_object($value) ? get_class($value).' ' : '').$vtype.']</font> : ';
				switch ($vtype) {
					case 'boolean' : $result .= $value ? 'true' : 'false'; break;
					case 'array' : $result .= Arrays::value($value,$name.$prefix.$key.$suffix); break;
					case 'object' : $result .= Arrays::value($value,$name.$prefix.$key.$suffix); break;
					default : $result .= '<font color="#ff9a56">'.$value.'</font>'; break;
				}
				$result .= '</li>'._NL;
			}
		} else {
			$result .= '<li>(empty)</li>'._NL;
		}
		$result .= '</ul>'._NL;

		return $result;
	}
}
?>
