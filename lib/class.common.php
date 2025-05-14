<?php
/**
* SOFTGANZ :: common class
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

* Created :: 2007-07-09
* Modify  :: 2025-05-14
* Version :: 6
*/

/********************************************
* Class :: sgClass
* Base class of SoftGanz Framework
********************************************/
class sgClass {
	private $_PROPERTY;

	function __construct() {
		$this->_PROPERTY = new stdClass();
		$this->_PROPERTY->count = 0;
		$this->_PROPERTY->empty = true;
	}

	function count($num = NULL) {
		if (!is_null($num)) {
			$this->_PROPERTY->count = $num;
			$this->_PROPERTY->empty = empty($num);
		}
		return $this->count ? $this->count : 0;
	}

	public function __set($name, $value) {
		//debugMsg('SET '.$name.' = '.(is_string($value) ? $value : gettype($value)));
		$this->{$name} = $value;
	}

	public function __get($name) {
		if (isset($this->{$name})) {
			return $this->{$name};
		} else if (isset($this->_PROPERTY->{$name})) {
			return $this->_PROPERTY->{$name};
		}

		/*
		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE
		);
		*/
		return null;
	}

}




/********************************************
* Class :: Cfg
* Cfg class for keep core system configuration
********************************************/
class Cfg {
	var $cfg = array();

	function __construct($cfg_file=NULL) {
		if ( $cfg_file ) foreach ( explode(";",$cfg_file) as $cfile ) $this->load($cfile);
	}

	/**
	Property :: value
	purpose : get configuration value
	*/
	function value($key=NULL,$cfg_value=NULL) {
	global $cfg;
		$cfg_name=isset($this) && get_class($this)=='cfg' ? 'this':'cfg';
		if (isset($cfg_value)) $$cfg_name->cfg[$key]=$cfg_value;
		if (isset($key)) {
			$ret = isset($$cfg_name->cfg[$key]) ? $$cfg_name->cfg[$key] : null;
		} else $ret = $$cfg_name->cfg;
		if (is_object($ret) || is_array($ret)) reset($ret);
		return $ret;
	}

	/**
	Method :: load
	purpose : process configuration line and store in property cfg
	*/
	function load($config_file_list=NULL,$folders=NULL) {
		//echo 'load config from <b>'.$config_file_list.'</b> in folder '.$folders.'<br>';
		if (is_array($config_file_list)) {
			$this->cfg=array_merge($this->cfg,$config_file_list);
			ksort($this->cfg);
		} else if ($config_file_list) {
			$folders=isset($folders)?explode(';',$folders):array('.');
			foreach ( explode(";",$config_file_list) as $config_file ) {
				foreach ($folders as $folder) {
					$each_config_file=$folder.'/'.$config_file;
					if ( file_exists($each_config_file) and is_file($each_config_file) ) {
						include($each_config_file);
						if (isset($cfg) && is_array($cfg)) $this->cfg=array_merge($this->cfg,$cfg);
						break;
					}
				}
			}
		ksort($this->cfg);
		}
	}

	/**
	Method :: add
	purpose : add config value
	*/
	function add($key,$value) {
	global $cfg;
		$cfg_name=get_class($this)=='cfg' ? 'this':'cfg';
		$$cfg_name->cfg[$key]=$value;
	}

	/**
	Method :: delete
	purpose : delete config value
	*/
	function delete($key) {
	global $cfg;
		$cfg_name=get_class($this)=='cfg' ? 'this':'cfg';
		unset($$cfg_name->cfg[$key]);
	}
} // end of class cfg




/********************************************
* Class :: Timer
* Timer class for timer of execution
********************************************/
class Timer {
	var $time;

	function start($key=NULL) { $this->time[$key]["start"] = microtime(); } //start

	function stop($key=NULL) { $this->time[$key]["stop"] = microtime(); } //stop

	function get($key=NULL,$digit=NULL) {
		$result = $this->elapsed($this->time[$key]["start"], $this->time[$key]["stop"])*1000;
		if (isset($digit)) $result = number_format($result,$digit);
		return $result;
	} //getTime

	function build($key=NULL) { return ' <span style="padding:3px;color:red;">timer of '.$key.' is '.$this->get($key).' ms.</span>'; }

	function debug($debug=NULL,$key=NULL) { if (cfg($debug) || debug('timer')) return $this->build($key); }

	function elapsed($a, $b) {
		list($a_micro, $a_int) = explode(' ',$a);
		list($b_micro, $b_int) = explode(' ',$b);

		$a_micro = floatval($a_micro);
		$a_int = intval($a_int);
		$b_micro = floatval($b_micro);
		$b_int = intval($b_int);

		if ($a_int > $b_int) {
			return ($a_int - $b_int) + ($a_micro - $b_micro);
		}
		else if ($a_int == $b_int) {
			if ($a_micro > $b_micro) {
				return ($a_int - $b_int) + ($a_micro - $b_micro);
			}
			else if ($a_micro<$b_micro) {
				return ($b_int - $a_int) + ($b_micro - $a_micro);
			}
			else {
				return 0;
			 }
		}
		else { // $a_int < $b_int
			return ($b_int - $a_int) + ($b_micro - $a_micro);
		}
	} //elapsed
} // end of class Timer




/********************************************
* Class :: Session
* Session class for php session management using database
*
* REMARK :: TO DEBUG SIGN IN SESSION, REMOVE CLASS form.signform in library-xx.xx.js
********************************************/
class Session implements SessionHandlerInterface {

	public function __construct() {}

	public function __destruct() {
		return session_write_close();
		return false;
	}

	public function open($path,$name) {return true;}

	public function close() {return true;}

	public function read($sess_id) {
		$res = mydb::select('SELECT `sess_data` FROM %session% WHERE `sess_id` = :id LIMIT 1',':id',$sess_id);
		$sess_data = $res->_num_rows && $res->sess_data ? $res->sess_data : '';
		//echo 'Session read '.print_o($res,'$res');
		return $sess_data;
	}

	public function write($sess_id = NULL, $data = NULL) {
		$debug = false;

		// NOTES : If user mydb::query , cannot use :sess_id, :data
		$mydb = new MyDb(cfg('db'));

		if ($debug) echo 'Session write of '.$sess_id.'<br />data = '.$data.'<br />';

		if(preg_match('/^(user\|)(.*)/', $data, $out)) {
			$userInfo = unserialize($out[2]);
		}
		if ($debug) print_o($userInfo, '$userInfo', 1);

		mydb::query(
			'INSERT INTO %session%
			(`sess_id`, `user`, `sess_start`, `sess_last_acc`, `sess_data`)
			VALUES
			(
				"'.$mydb->escape($sess_id).'"
				, "'.$mydb->escape($userInfo->username).'"
				, NOW()
				, NOW()
				, "'.$mydb->escape($data).'"
			)
			ON DUPLICATE KEY UPDATE
				`sess_last_acc` = NOW()
				, `user` = "'.$mydb->escape($userInfo->username).'"
				, `sess_data` = "'.$mydb->escape($data).'"
			'
		);

		if ($debug) echo '$sess_id = '.$sess_id.'<br />';
		if ($debug) echo 'query = '.$mydb->_query.'<br />';

		if ($userInfo->ok && $userInfo->username) {
			$mydb->query(
				'UPDATE %session% SET
				`user` = "'.$mydb->escape($userInfo->username).'"
				, `expire` = "'.$mydb->escape($userInfo->remember).'"
				WHERE `sess_id` = "'.$mydb->escape($sess_id).'"
				LIMIT 1'
			);

			if ($debug) echo 'query = '.$mydb->_query.'<br />';
		}
		return true;
	}

	public function destroy($sess_id) {
		$GLOBALS['R']->myDb = new MyDb(cfg('db'));
		mydb::query('DELETE FROM %session% WHERE sess_id = :id LIMIT 1',':id',$sess_id);
		return true;
	}

	public function gc($ttl = 86400) {
		$GLOBALS['R']->myDb = new MyDb(cfg('db'));
		$end = date('Y-m-d H:i:s',time()-$ttl);
		mydb::query('DELETE FROM %session% WHERE sess_last_acc < :end',':end',$end);

		$watch = (Object) [
			'date' => 'func.NOW()',
			'uid' => \SG\getFirst(i()->uid,'func.NULL'),
			'ip' => ip2long(i()->ip),
			'module' => 'session',
			'keyword' => 'gc',
			'message' => 'gc was execute',
			'url' => preg_match('/IIS/i',$_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'] : $_SERVER['REQUEST_URI'],
			'referer' => $_SERVER['HTTP_REFERER'],
			'browser' => $_SERVER['HTTP_USER_AGENT'],
		];

		mydb()->_watchlog = false;
		mydb::query(
			'INSERT INTO %watchdog%
			( `date` , `uid` , `ip` , `module` , `keyword` , `message` , `url` , `referer` , `browser` )
			VALUES
			(:date, :uid, :ip, :module, :keyword, :message, :url, :referer, :browser );',
			$watch
		);

		// echo 'end '.$end.' '.mydb()->_query;die;
		return true;
		return true;
	}
}




/********************************************
* Class :: Arrays
* Arrays class for array data
********************************************/
class Arrays {
	static function value($arr=array(),$name='', $options = array()) {
		if ($name && is_object($arr)) {$prefix='->';$suffix='';}
		else if ($name && is_array($arr)) {$prefix='[';$suffix=']';}
		else $prefix=$suffix='';
		$result = '<ul class="array-value '.(isset($options['class']) ? $options['class'] : '').'" style="margin:0 0 0 15px;padding:0px;">'._NL;
		if ( is_object($arr) || (is_array($arr) and count($arr) > 0) ) {
			foreach ( $arr as $key=>$value ) {
				$vtype = GetType($value);
				$result .= '<li><span style="color:#ff9a56">'.$name.$prefix.$key.$suffix.'</font> <font color=gray>['.$vtype.']</font> : ';
				switch ($vtype) {
					case 'boolean' : $result .= $value ? 'true' : 'false'; break;
					case 'array' : $result .= self::value($value,$name.$prefix.$key.$suffix); break;
					case 'object' : $result .= self::value($value,$name.$prefix.$key.$suffix); break;
					default : $result .= '<font color="#ff9a56">'.htmlSpecialChars($value).'</font>'; break;
				}
				$result .= '</li>'._NL;
			}
		} else $result .= '<li>(empty)</li>'._NL;
		$result .= '</ul>'._NL;
		return $result;
	}

	static function table($arr=array(),$name="") {
		if (is_resource($arr)) {
			if (!function_exists('db_fetch_object')) return;
			while ($item=db_fetch_object($arr)) $data[]=(array)$item;
			$arr=$data;
		}
		if (!is_array($arr) || empty($arr)) return false;
		$header=each($arr);
		$header=array_keys($header['value']);
		$ret .= '<table class="item" cellspacing=0 cellpadding=2><caption>'.$name.'</caption><tr>';
		foreach ($header as $key) $ret .='<th>'.$key.'</th>';
		$ret .= '</tr>';
		foreach ($arr as $item) {
			$ret .= '<tr valign="top" class="'.(++$no%2?'odd':'even').'">';
			foreach ($item as $value) $ret .= '<td>'.$value.'</td>';
			$ret .= '</tr>';
		}
		$ret .= '</table>';
		return $ret;
	}

	static function convert($arr=array(),$flags=NULL) {
		$result = array();
		if ( ! is_array($arr) or count($arr) == 0 ) return $result;
		foreach ( $arr as $key=>$value ) {
			if ( is_array($value) ) {
				if ( $flags & _KEYTOLOWER ) $key = StrToLower($key);
				if ( $flags & _KEYTOUPPER ) $key = StrToUpper($key);
				$result[$key] = Arrays::Convert($value,$flags) ;
			} else {
				if ( is_numeric($flags) && ($flags & _REMOVEEMPTY) && is_string($value) && trim($value) == '' ) {
					unset($result[$key]);
				} else {
					if (is_numeric($flags) && is_String($value)) {
						if ( $flags & _TRIM ) $value = trim($value);
						if ( $flags & _STRIPSLASHES ) $value = stripSlashes($value);
						if ( $flags & _ADDSLASHES ) $value = addSlashes($value);
						if ( $flags & _HTMLSPECIALCHARS ) $value = htmlSpecialChars($value,ENT_QUOTES);
						if ( $flags & _NEWLINE_TO_BR ) $value = nl2br($value);
						if ( $flags & _URLENCODE ) $value = urlEncode($value);
						if ( $flags & _STRIPTAG ) $value = sg_strip_tags($value);
					}
					if (is_numeric($flags) && $flags & _KEYTOLOWER ) $key = strToLower($key);
					if (is_numeric($flags) && $flags & _KEYTOUPPER ) $key = strToUpper($key);
					$result[$key] = $value;
				}
			}
		}
		return $result;
	}

	/**
	Method :: Search
	purpose : search string or string in array from any array

	use :
	string arrays::search(string needle , array haystack )

	return needle string if found in array
	*/
	static function search($needle=NULL,$haystack=array()) {
		$result=false;

		foreach ( $haystack as $k=>$v ) {
			if ( Is_Array($v) ) {
				if ( $result = arrays::search($needle,$v) ) break;
			} else {
				$v=preg_replace('/ /','',$v);
				if ( preg_match("/$needle/i",$v) ) {
					return $result=$v;
				}
			}
		}
		return $result;
	}
} // end of class Arrays




/********************************************
* Class :: Cache
* Cache class for manage cache
********************************************/
class Cache {
	public static function add($cid, $data, $expire, $headers) {
		$data = \SG\json_encode($data);
		mydb::query(
			'INSERT INTO %cache%
			(`cid`, `data`, `expire`, `created`, `headers`)
			VALUES
			(:cid, :data, :expire, :created, :headers)
			ON DUPLICATE KEY UPDATE
			`cid` = :cid',
			[
				':cid' => $cid,
				':data' => $data,
				':expire' => $expire,
				':created' => time(),
				':headers' => $headers,
			]
		);
		// echo mydb()->_query;
	}

	public static function get($cid) {
		$result = mydb::select('SELECT * FROM %cache% c WHERE c.`cid` = :cid LIMIT 1', [':cid' => $cid]);
		if ($result->count()) {
			mydb::clearProp($result);
			$result->data = preg_match('/^{/', $result->data) ? \SG\json_decode($result->data) : unserialize($result->data);
			$result->data->token = $result->data->session;
			$result->remain = $result->expire - time();
		}
		return $result;
	}

	public static function clear($cid) {
		mydb::query('DELETE FROM %cache% WHERE cid = :cid LIMIT 1', [':cid' => $cid]);
	}

	public static function clear_expire() {
		$ctime = time();
		mydb::query('DELETE FROM %cache% WHERE (expire > 0) and (expire - '.$ctime.' < 0)');
		// *** Cause sign in error
		//mydb::query('OPTIMIZE TABLE %cache%');
	}
} //--- End of class Cache




/*********************************
Class  :: classFile
**********************************/
class classFile {
	var $format=array();
	var $upload=NULL;
	var $folder=NULL;
	var $destination=NULL;
	var $replace=false;
	var $check_valid_name=true;

	function __construct($upload=null,$folder=null,$format=null) {
		if (isset($upload)) $this->upload=(object)$upload;
		if (isset($folder)) $this->folder=$folder;
		if (isset($format)) $this->format=$format;
		if ($this->upload->name) {
			$this->upload->_file=sg_explode_filename($this->upload->name,'pic');
			$this->filename=$this->upload->_file->name;
			$this->destination=$this->folder.$this->filename;
		}
	}

	function set_upload($upload) {
		$this->upload=(object)$upload;
		$this->filename=sg_valid_filename($upload->name);
	}

	function set_folder($folder=NULL) {
		$this->folder=$folder?$folder:cfg('upload.folder').i()->username.'/';
		return $this->upload_folder;
	}

	function set_filename($filename) {
		$this->filename=$filename;
		$this->upload->_file=sg_explode_filename($filename);
		return $this->filename;
	}

	function valid_format() { return in_array($this->upload->type,$this->format); }

	function valid_extension() { return in_array($this->upload->_file->ext,$this->format); }

	function valid_size($size) { return $this->upload->size <= $size;}

	function duplicate() { return file_exists($this->folder.$this->filename) && is_file($this->folder.$this->filename);}

	function check_upload_folder() {
		if (!file_exists($this->folder)) {
			mkdir($this->folder);
			if (cfg('upload.folder.chmod')) chmod($this->folder,cfg('upload.folder.chmod'));
		}
	}

	function generate_nextfile($name = 'pic', $digit = 20) {
		$new_filename = sg_generate_nextfile($this->folder, $name, $this->upload->_file->ext, $digit);
		$this->upload->_file = sg_explode_filename($new_filename);
		$this->set_filename($this->upload->_file->name);
	}

	function copy() {
		$src_file=$this->upload->tmp_name;
		$dest_file=$this->folder.$this->filename;

		$this->check_upload_folder();

		if (!copy($src_file,$dest_file)) return false;

		if (cfg('upload.file.chmod') && file_exists($dest_file) && is_file($dest_file)) chmod($dest_file,cfg('upload.file.chmod'));
		return true;
	}
}//--- End Of Class classFile




/********************************************
* Class :: Firebase
* Firebase is a Google Realtime database
********************************************/
class Firebase {
	function __construct($url,$table) {
		$this->url = $url;
		$this->table = $table;
	}

	function post($data) {
		$url = 'https://'.$this->url.'.firebaseio.com/'.$this->table.'.json';
		$data_string = json_encode($data);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string))
		);

		ob_start();
		$ret = curl_exec($ch);
		ob_end_clean();
		return $ret;
	}

	function put($key,$data) {
		$url = 'https://'.$this->url.'.firebaseio.com/'.$this->table.'/'.$key.'.json';
		//$putData->{$key}=$data;
		$data_string = json_encode($data);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);

		/*
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch,  CURLOPT_RETURNTRANSFER, false);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
		curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 10);

		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		*/
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string))
			);

		ob_start();
		$ret = curl_exec($ch);
		ob_end_clean();

		//debugMsg($url);
		//debugMsg($data,'$data');
		//debugMsg($data_string);
		//debugMsg($ret);

		return $ret;
	}

	function set($key,$data) {
		$url = 'https://'.$this->url.'.firebaseio.com/'.$this->table.'/'.$key.'.json';
		//$putData->{$key}=$data;
		$data_string = json_encode($data);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);

		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string))
			);

		ob_start();
		$ret = curl_exec($ch);
		ob_end_clean();

		//debugMsg($url);
		//debugMsg($data,'$data');
		//debugMsg($data_string);
		//debugMsg($ret);

		return $ret;
	}

	function functions($funcName, $data, $options = '{}') {
		$firebaseCfg = cfg('firebase');
		$url = 'https://'.$firebaseCfg['functions'].'-'.$this->url.'.cloudfunctions.net/'.$funcName;

		$tokenList = cfg('imed.token');
		$data['token'] = $tokenList->firebase;

		$data_string = json_encode($data);

		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_URL => $url,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $data_string,
			CURLOPT_TIMEOUT => 1,
			//CURLOPT_TIMEOUT_MS => 100,
			CURLOPT_CONNECTTIMEOUT => 1,
			CURLOPT_RETURNTRANSFER => false,
		));

		ob_start();
		$ret = curl_exec($ch);
		ob_end_clean();

		//debugMsg('URL = '.$url);
		//debugMsg($data_string);
		//debugMsg($data,'$data');
		//debugMsg($ret);

		return $ret;
	}
} // End of class Firebase


/*********************************
Class  :: Jwt
**********************************/

class Jwt {
	public static function generate($headers, $payload, $secret = 'secret') {
		$headers_encoded = Jwt::base64url_encode(json_encode($headers));

		$payload_encoded = Jwt::base64url_encode(json_encode($payload));

		$signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $secret, true);
		$signature_encoded = Jwt::base64url_encode($signature);

		$jwt = "$headers_encoded.$payload_encoded.$signature_encoded";

		return $jwt;
	}

	public static function isValid($jwt, $secret = 'secret') {
		// split the jwt
		$tokenParts = explode('.', $jwt);
		$header = base64_decode($tokenParts[0]);
		$payload = base64_decode($tokenParts[1]);
		$signature_provided = $tokenParts[2];

		// echo '$jwt = '.$jwt.'<br /><br />';
		// echo '$header = '.$header.'<br /><br />';
		// echo '$payload = '.$payload.'<br /><br />';
		// check the expiration time - note this will cause an error if there is no 'exp' claim in the jwt
		$expiration = json_decode($payload)->exp;
		$is_token_expired = ($expiration - time()) < 0;

		// echo 'expiration = '.$expiration.' '.date('Y-m-d H:i:s', $expiration).'<br /><br />';
		// build a signature based on the header and payload using the secret
		$base64_url_header = Jwt::base64url_encode($header);
		$base64_url_payload = Jwt::base64url_encode($payload);
		$signature = hash_hmac('SHA256', $base64_url_header . "." . $base64_url_payload, $secret, true);
		$base64_url_signature = Jwt::base64url_encode($signature);

		// verify it matches the signature provided in the jwt
		$is_signature_valid = ($base64_url_signature === $signature_provided);

		// echo '$base64_url_signature = '.$base64_url_signature.'<br /><br />';
		// echo '$signature_provided = '.$signature_provided.'<br /><br />';

		return (Object) [
			'header' => json_decode($header),
			'payload' => json_decode($payload),
			'expiration' => $expiration,
			'signature' => $base64_url_signature,
			'signatureProvided' => $signature_provided,
			'valid' => $is_token_expired || !$is_signature_valid ? 0 : 1,
		];
		if ($is_token_expired || !$is_signature_valid) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	public static function base64url_encode($str) {
	    return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
	}
}

class Url {
	/**
	 * Generate url for anchor
	 * @param String $url
	 * @param String $get
	 * @param String $frement
	 * @return String
	 */
	static function link($url = NULL, $get = NULL, $frement = NULL, $subdomain = NULL) {
		$ret = '';
		if (isset($get) && is_array($get)) {
			foreach ($get as $k => $v) if (!is_null($v)) $get_a .= $k.'='.$v.'&';
			$get = rtrim($get_a, '&');
			if (empty($get)) unset($get);
		}
		if (substr($url,0,2) === '//') ; // do nothing
		else if (substr($url,0,1) === '/') $url = substr($url,1);

		$fullUrl = preg_match('/^(\/\/|http\:\/\/|https\:\/\/)/', $url, $out) ? '' : cfg('url');

		if (cfg('clean_url')) {
			$ret .= isset($url) ? $url : cfg('clean_url_home');
			if ( isset($get) ) $ret .= '?'.$get;
		} else {
			$ret .= $url ? '?'.$url : '';
			if ( isset($get) ) $ret .= ($url ? '&' : '?').$get;
		}
		if ($frement) $ret .= '#'.$frement;
		//	echo 'url alias of '.$ret.' = '.url_alias($ret)->system.'<br >';
		if ($url_alias = url_alias_of_system($ret)) $ret = $url_alias->system;
		$ret = cfg('url.domain').(cfg('url.domain') ? '' : $fullUrl) . $ret;
		return $ret;
	}

	static function file($url, $get = NULL) {
		if (isset($get)) $get = preg_replace('/^\?/', '', $get);
		$ret = '';
		$ret .= $url;
		if (isset($get)) $ret .= '?'.$get;
		if ($frement) $ret .= '#'.$frement;
		$ret = cfg('url') . $ret;
		return $ret;
	}

	static function js($url, $get = NULL) {
		if (isset($get)) $get = preg_replace('/^\?/', '', $get);
		$ret = '';
		if (cfg('clean_url')) {
			$ret .= $url;
			if (isset($get)) $ret .= '?'.$get;
		} else {
			$ret .= '?'.$url;
			if (isset($get)) $ret .= '&'.$get;
		}
		if ($frement) $ret .= '#'.$frement;
		//	echo 'url alias of '.$ret.' = '.url_alias($ret)->system.'<br >';
		if ($url_alias = url_alias_of_system($ret)) $ret = $url_alias->system;
		$ret = cfg('url') . $ret;
		return $ret;
	}
}

class Request {
	/**
	 * Get post value from $_POST
	 * @param String $key
	 * @param Integer $flag
	 *
	 * @return Array
	 */
	public static function post($key = NULL, $flag = _TRIM) {
		static $count = 0;
		$post = $_POST;
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
			return (Object) $post;
		}
	}
}
?>