<?php
/**
* Function:: Common Function
* Created :: 2007-07-09
* Modify  :: 2024-05-27
* Version :: 3
*
* @param Array $args
* @return Widget
*
* @usage new Widget([key => value,...])
*/

function sg_budget_year($date) {
	return sg_date($date,'Y')+(sg_date($date,'m')>=10?1:0);
}

function sg_clone($object) {
	if (!is_object($object)) return (object) NULL;
	return version_compare(phpversion(), '5.0') < 0 ? $object : clone($object);
}

function sg_encrypt($txt,$key){
	srand((double)microtime()*1000000);
	$encrypt_key = md5(rand(0,32000));
	$ctr=0;
	$tmp = "";
	for ($i=0;$i<strlen($txt);$i++) {
		if ($ctr==strlen($encrypt_key)) $ctr=0;
		$tmp.= substr($encrypt_key,$ctr,1) . (substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1));
		$ctr++;
	}
	return base64_encode(_crypt_key_ed($tmp,$key));
}

function sg_decrypt($txt,$key){
	$txt = _crypt_key_ed(base64_decode($txt),$key);
	$tmp = "";
	for ($i=0;$i<strlen($txt);$i++) {
		$md5 = substr($txt,$i,1);
		$i++;
		$tmp.= (substr($txt,$i,1) ^ $md5);
	}
	return $tmp;
}

function sg_rand_password($length = 8) {
	$randomPassword = "";
	srand((double)microtime()*1000000);
	for($i=0;$i<$length;$i++) {
		$randnumber = rand(48,120);
		while (($randnumber >= 58 && $randnumber <= 64) || ($randnumber >= 91 && $randnumber <= 96)) {
			$randnumber = rand(48,120);
		}
		$randomPassword .= chr($randnumber);
	}
	return $randomPassword;
}

function sg_generate_token($length = 16) {
	$token_id = substr(
		md5(
			str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ")
		)
		,0, $length
	);
	return $token_id;
}

/*
 * Convert date format
 * @param $para1
 * @param $para2
 * @return String
 *
 * example
 *
 * Date ( [string Format] ) //--- Show Current Date with format
 * Date ( DateString,FormatString ) Show Date with format
 */
function sg_date($para1=NULL,$para2=NULL) {
	$lang=cfg('lang');
	$date = NULL;
	$format = cfg('date.format.short');
	$dd = $mm = $yy = $hr = $min = $sec = 0;
	if ( isset($para1) and isset($para2) ) {
		$date = $para1;
		$format = $para2;
	} elseif ( isset($para1) ) {
		//--- if $para1 is date format yyyy-mm-dd
		if ( preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/",$para1,$out) ) $date = $para1; else $format = $para1;
	}

	if ( isset($date) ) {
		if (strlen($date) == 4) $date = $date.'-01-01';
		else if (is_numeric($date)) $date = date('Y-m-d H:i:s',$date);
		$date .= ' ';
		list($d,$t) = explode(" ",$date);
		//debugMsg('date ='.$date.'=>'.$d.'<br />'.print_o(preg_split('/[-\/]+/',$d),'split'));
		if (list($yy,$mm,$dd) = preg_split('/[-\/]+/',$d)) {
			if (strlen($dd) == 4) list($yy,$dd) = array($dd,$yy);
			if ($yy > 2400) $yy -= 543;
		}
		if (!empty($t) && preg_match('/[\.\:]/', $t)) list($hr,$min,$sec) = preg_split('/[\.\:]+/',$t);
		//debugMsg('time ='.$date.'=> $t='.$t.'=> $hr='.$hr.' $min='.$min.' $sec='.$sec.'<br />'.print_o(preg_split('/[\.\:]+/',$t),'split'));
		$dd = intval($dd); $mm = intval($mm); $yy = intval($yy);
		$hr = intval($hr); $min = intval($min); $sec = intval($sec);
		$w = date("w",mktime(0,0,0,$mm,$dd,$yy));
	} else {
		$dd = date("j"); $mm = date("m"); $yy = date("Y"); $hr = date("H"); $min = date("i"); $sec = date("s");
		$w = date("w");
	}
	if ( $dd === 0 ) return;

	$ret = ( date($format,mktime($hr,$min,$sec,$mm,$dd,$yy)) ) ? date($format,mktime($hr,$min,$sec,$mm,$dd,$yy)) : $format;
	$days = array('อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์');
	$months = array('January','February','March','April','May','June','July','August','September','October','November','December');
	$thMonth = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
	$smonths = array('ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.');

	$min15 = $min - ($min % 15);
	$source = array(
		'"ววว"' , '"ว"' ,
		'"ปปปป"', '"ปป"' ,
		'"ดดด"' , '"ดด"',
		'"น15"',
	);
	$replace = array(
		tr($days[$w]) , tr($dd) ,
		sprintf('%04d',$yy+($lang == 'th' ? 543 : 0)) , substr($yy+543,-2) ,
		$thMonth[$mm-1], $smonths[$mm-1],
		sprintf('%02d',$min15),
	);
	$ret = preg_replace($source,$replace,$ret);
	return $ret;
}

/**
 * Replace some charactor of ip with ...
 *
 * @param String $ip
 * @return String
 */
function sg_sub_ip($ip="") {
	if ( empty($ip) ) return "";
	if ( is_numeric($ip) ) $ip=long2ip($ip);
	list($ip1,$ip2,$ip3,$ip4) = explode(".",$ip);
	return "$ip1...$ip4";
}

function sg_require_input($field=NULL,$require=NULL) {
	return (isset($field) && isset($require) && $field===$require) ? 'background-color:#FFDFDF;':'';
}

function sg_valid_daykey($keyno,$key) {
	if (empty($key)) return false;
	return Poison::existDayKey($keyno,$key);
}

function sg_is_email($mail=NULL) {
	$email_regex = "|([\xA1-\xFEa-z0-9_\.\-]+)@([\xA1-\xFEa-z0-9_\-]+\.[\xA1-\xFEa-z0-9\-\._\-]+[\.]*[a-z0-9]\??[\xA1-\xFEa-z0-9=]*)|";
	if ( preg_match("|[\s].*|",$mail) ) return false;
	elseif ( preg_match($email_regex,$mail,$out) ) return true;
	else return false;
}

function sg_invalid_poster_name($name=NULL) {
	$name=trim($name);
	$dbu=mydb::select('SELECT `uid`,`name` FROM %users% WHERE name=:name LIMIT 1',':name',$name);
	if (i()->ok) {
		return $dbu->uid && i()->uid != $dbu->uid;
	} else {
		return $name===$dbu->name;
	}
}

function sg_strtolower($src=NULL) {
	$i=0;
	while ($i<strlen($src)) {
		if ($src[$i]>='A' && $src[$i]<='Z') $src[$i]=strtolower($src[$i]);
		$i++;
	}
	return $src;
}

function sg_file_extension($filename=NULL) { return substr($filename,strrpos($filename,'.')+1);}

function sg_explode_filename($filename=NULL,$check_valid_name=false,$digit=6) {
	$result = (Object) [];

	if (strpos($filename,'/') === false) /* do nothing */;
	else {
		$result->dirname=dirname($filename).'/';
		$filename=substr($filename,strlen($result->dirname));
	}
	$result->basename=substr($filename,0,strrpos($filename,'.'));
	$result->ext=strtolower(substr($filename,strrpos($filename,'.')+1));
	// check for valid file name
	if ($check_valid_name) {
		$result->basename=trim(preg_replace('/[^0-9a-z_\-\.]/i','',$result->basename)); // remove thai charactor
		// replace many .,-,_ with _ , remove _ at front , _ at end
		$result->basename=preg_replace(array('# #','#([_\.\-]){2,}#','#^[_]#','#[_]$#'),array('_','_','',''), $result->basename);
		if ($result->basename=='_') $result->basename=NULL;
		if (empty($result->basename) && is_string($check_valid_name)) {
			$result->basename=$check_valid_name.($digit>0?sprintf('%0'.$digit.'d',1):'');
		}
		//		$result->basename=sg_strtolower($result->basename); // change to lowercase
	}
	$result->name=$result->basename.($result->ext?'.'.$result->ext:'');
	$result->location=$result->dirname.$result->basename.($result->ext?'.'.$result->ext:'');
	return $result;
}

function sg_valid_filename($name=NULL) {
	$name=trim(preg_replace('/[^0-9a-z_\-\.]/i','',$name)); // remove thai charactor
	$name=preg_replace(array("#^\.#si"),array('_'),$name);
	$name=sg_strtolower(str_replace(' ','_',$name));
	if ($name=='.' || $name=='_') $name=NULL;
	return $name;
}

function sg_user_folder($username) {
	$user_folder=cfg('upload.folder').$username.'/';
	if (!file_exists($user_folder)) {
		mkdir($user_folder);
		if (cfg('upload.folder.chmod')) chmod($user_folder,cfg('upload.folder.chmod'));
	}
	return $user_folder;
}

function sg_generate_nextfile($folder, $name, $ext, $digit = 20) {
	if (empty($name)) {
		$prefix = 'W'; // a universal prefix
		$name = strToLower($prefix.chr(rand(65,90)).time());
	}
	do {
		$result = $folder.substr($name.\SG\uniqid().\SG\uniqid(), 0, $digit).'.'.$ext;
	} while (file_exists($result));
	return $result;
}

function sg_remain2day($time=0,$format='d Day h Hr m Min') {
	if (is_string($time)) intval($time);
	$time=intval($time);

	$day=intval($time/(24*60*60));
	$remain=$time % (24*60*60);
	$hour=intval($remain/(60*60));
	$remain=$remain % (60*60);
	$min=intval($remain/60);
	$second=$remain % 60;

	if (empty($day)) unset($day);
	if (empty($hour)) unset($hour);

	if ($day) $result = preg_replace(array('#d Day#s','#h Hr#s','#m Min#s','#s Sec#s'),array($day.' Day',$hour.' Hr',$min.' Min',$second.' Sec'),$format);
	else if ($hour) $result = preg_replace(array('#d Day#s','#h Hr#s','#m Min#s','#s Sec#s'),array('',$hour.' Hr',$min.' Min',$second.' Sec'),$format);
	else if ($min) $result = preg_replace(array('#d Day#s','#h Hr#s','#m Min#s','#s Sec#s'),array('','',$min.' Min',$second.' Sec'),$format);
	else $result = preg_replace(array('#d Day#s','#h Hr#s','#m Min#s','#s Sec#s'),array('','','',$second.' Sec'),$format);
	$result=trim($result);
	if (empty($result)) $result='0 Min';
	return $result;
}

function sg_explode_style($sep,$str=NULL) {
	$result=array();
	if (empty($str)) return $result;
	$style=explode($sep,$str);
	foreach ($style as $item) {
		if (empty($item)) continue;
		list($key,$value)=explode('=',$item);
		$result[$key]=$value;
	}
	return $result;
}

function sg_utf8_to_tis620($string) {
  $str = $string;
  $res = '';
  for ($i = 0; $i < strlen($str); $i++) {
		if (ord($str[$i]) == 224) {
			$unicode = ord($str[$i+2]) & 0x3F;
			$unicode |= (ord($str[$i+1]) & 0x3F) << 6;
			$unicode |= (ord($str[$i]) & 0x0F) << 12;
			$res .= chr($unicode-0x0E00+0xA0);
			$i += 2;
		} else {
			$res .= $str[$i];
		}
  }
  return $res;
}

function sg_tis620_to_utf8($tis) {
	for( $i=0 ; $i< strlen($tis) ; $i++ ){
		$s = substr($tis, $i, 1);
		$val = ord($s);
		if( $val < 0x80 ){
			$utf8 .= $s;
		} elseif ( ( 0xA1 <= $val and $val <= 0xDA ) or ( 0xDF <= $val and $val <= 0xFB ) ){
			$unicode = 0x0E00 + $val - 0xA0;
			$utf8 .= chr( 0xE0 | ($unicode >> 12) );
			$utf8 .= chr( 0x80 | (($unicode >> 6) & 0x3F) );
			$utf8 .= chr( 0x80 | ($unicode & 0x3F) );
		}
	}
	return $utf8;
}

/* convert utf-8 to tis-620 filename on system */
function sg_tis620_file($str) { return cfg('client.characterset')=='utf-8' ? sg_utf8_to_tis620($str) : $str; }

function sg_client_convert($message=NULL) {
	if (cfg('client.characterset')=='tis-620') {
		if (is_array($message)) {
			foreach ($message as $key=>$value) $message[$key]=sg_client_convert($value);
		} else if (is_string($message)) {
			$message=sg_utf8_to_tis620($message);
		}
	}
	return $message;
}

function sg_dump_file($src_file=NULL,$mine_type=NULL) {
	if ( file_exists($src_file) and is_file($src_file) ) {
		if (!$mine_type) {
			$mine_type = getimagesize($src_file);
			$mine_type = $mine_type['mime'];
		}
		Header('Content-type: '.$mine_type);
		readfile($src_file);
		return true;
	} else return false;
}

function __sg_strip_tags_code_callback($m) {
	$str='[code'.str_replace(array('&lt;','"'),array('<','"'),$m[2]).'[/code]';
	return $str;
}
function __sg_strip_tags_attr_callback($m) {
	return sg_strip_attr($m[1]);
}
function sg_strip_tags($source) {
	// Allow these tags
	if (user_access('administer contents,input format type php')) return $source;

	$allowedTags = cfg('topic.allowedtags.normal');
	if (user_access('upload photo')) $allowedTags.=cfg('topic.allowedtags.photo');
	if (user_access('upload video')) $allowedTags.=cfg('topic.allowedtags.video');
	if (user_access('input format type script')) $allowedTags.=cfg('topic.allowedtags.script');

	/*
	$source = str_replace(array('<!--', '-->'), array('&lt;!--', '--&gt;'), $source);
	$source = strip_tags($source, $allowedTags);
	$source = str_replace(array('&lt;!--', '--&gt;'), array('<!--', '-->'), $source);
	$source=preg_replace('/<(.*?)>/ie', "'<'.sg_strip_attr('\\1').'>'", $source);
	*/

	$source = str_replace(array('<!--', '-->'), array('&lt;!--', '--&gt;'), $source);
	/*
	// Old replace
	$source = preg_replace('/\[code(.*?)\[\/code\]/sie','\'[code\'.str_replace(array("<",\'\"\'),array("&lt;",\'"\'),\'\\1\').\'[/code]\'',$source);
	*/
	$source = preg_replace_callback(	'/\[code(.*?)\[\/code\]/si', '__sg_strip_tags_code_callback', $source); // [code]...[/code]
	$source = strip_tags($source, $allowedTags);
	$source = str_replace(array('&lt;!--', '--&gt;'), array('<!--', '-->'), $source);

	/*
	//Old replace
	$source=preg_replace('/<(.*?)>/ie', "'<'.sg_strip_attr('\\1').'>'", $source);
	$source = preg_replace('/\[code(.*?)\[\/code\]/sie','\'[code\'.str_replace(array("&lt;",\'\"\'),array("<",\'"\'),\'\\1\').\'[/code]\'',$source);
	*/

	// This code was comment on 2016-06-30 because it remove all tag include <iframe>
	//$source = preg_replace_callback(	'/<(.*?)>/i', '__sg_strip_tags_attr_callback',$source	);

	//$source = preg_replace_callback(	'/\[code(.*?)\[\/code\]/si', '__sg_strip_tags_code_callback', $source); // [code]...[/code]
	return $source;
}

/**
 * @return string
 * @param string
 * @desc Strip forbidden attributes from a tag
 */
function sg_strip_attr($tagSource) {
	/** Disallow these attributes/prefix within a tag */
	$stripAttrib = 'javascript:|onclick|ondblclick|onmousedown|onmouseup|onmouseover|onmousemove|onmouseout|onkeypress|onkeydown|onkeyup';
	return stripslashes(preg_replace("/$stripAttrib/i", 'forbidden', $tagSource));
}

function sg_summary_text($str=NULL,$length=0) {
	if (empty($length)) $length=cfg('topic.summary_length');
	$result = '';
	if (preg_match('"(.*?)<!--break-->"si',$str,$out)) {
		$result=sg_text2html($out[1].'<!--read more-->');
	} else if (preg_match_all('"<summary>(.*?)</summary>"si',$str,$out) || preg_match_all('"<p id=\"summary\">(.*?)</p>"si',$str,$out)) {
		foreach ($out[1] as $key=>$value) $out[1][$key]=sg_text2html($value.($key==count($out[1])-1?'<!--read more-->':''));
		$result=_NL.'<p>'.implode('</p><p>',$out[1]).'</p>'._NL;
	} else {
		if ($length==0 || strlen($str)<=$length) {
			$result=sg_text2html($str);
		} else {
			if (strtolower(cfg('client.characterset'))=='utf-8') {
				$result=strip_tags(sg_text2html($str));
				$result=sg_utf8_to_tis620($result);
				$result=substr($result,0,$length);
				$result=sg_tis620_to_utf8($result);
			} else {
				$result=substr(strip_tags(sg_text2html($str)),0,$length);
			}
			$result .= '<!--read more-->';
			$result=preg_replace(array('#\r#si','#\n#si','#&nbsp; #s','#  #si'),array('','',' ',''),$result);
		}
	}
	return $result;
}

/**
* Change newline to <LI> tag
* @param String $para
* @return String
*/
function sg_nl2li($para) {
	$patterns = array ("[\r\n]",
								"[\n]");
	$replace = array ("<li>", "<li>");
	$result = preg_replace ($patterns, $replace, "\n".$para);
	return $result;
}

/**
* Left trim new line charector
* @param String $str
* @return String
*/
function sg_ltrim_nl($str="") {
	while ( in_array(ord(substr($str,0,1)),array(10,13)) and strlen($str)>0 )
		$str = substr($str,1);
	return $str;
}

function sg_num2thai($str,$numLang='TH') {
	$numEN=array('0','1','2','3','4','5','6','7','8','9');
	$numTH=array('๐','๑','๒','๓','๔','๕','๖','๗','๘','๙');
	return str_replace($numEN,$numLang=='TH'?$numTH:$numEN,$str);
}

function sg_money2bath($num, $digit = false){
	$num = str_replace(",","",$num);
	if (is_numeric($digit)) {
		$num = number_format($num,$digit,'.','');
		$digit = false;
	}
	$num_decimal = explode(".",$num);
	$num = $num_decimal[0];
	$returnNumWord;
	$lenNumber = strlen($num);
	$lenNumber2 = $lenNumber-1;
	$kaGroup = array("","สิบ","ร้อย","พัน","หมื่น","แสน","ล้าน","สิบ","ร้อย","พัน","หมื่น","แสน","ล้าน");
	$kaDigit = array("","หนึ่ง","สอง","สาม","สี่","ห้า","หก","เจ็ด","แปด","เก้า");
	$kaDigitDecimal = array("ศูนย์","หนึ่ง","สอง","สาม","สี่","ห้า","หก","เจ็ด","แปด","เก้า");
	$ii = 0;
	for ($i = $lenNumber2; $i >= 0; $i--){
		$kaNumWord[$i] = substr($num,$ii,1);
		$ii++;
	}
	$ii = 0;
	for ($i = $lenNumber2; $i >= 0; $i--){
		if (($kaNumWord[$i] == 2 && $i == 1) || ($kaNumWord[$i] == 2 && $i == 7)){
			$kaDigit[$kaNumWord[$i]] = "ยี่";
		} else {
			if ($kaNumWord[$i] == 2){
				$kaDigit[$kaNumWord[$i]] = "สอง";
			}
			if (($kaNumWord[$i] == 1 && $i <= 2 && $i == 0) || ($kaNumWord[$i] == 1 && $lenNumber > 6 && $i == 6)){
				if ($kaNumWord[$i+1] == 0){
					$kaDigit[$kaNumWord[$i]] = "หนึ่ง";
				} else {
					$kaDigit[$kaNumWord[$i]] = "เอ็ด";
				}
			} else if (($kaNumWord[$i] == 1 && $i <= 2 && $i == 1) || ($kaNumWord[$i] == 1 && $lenNumber > 6 && $i == 7)){
				$kaDigit[$kaNumWord[$i]] = "";
			} else {
				if ($kaNumWord[$i] == 1){
					$kaDigit[$kaNumWord[$i]] = "หนึ่ง";
				}
			}
		}
		if ($kaNumWord[$i] == 0){
			if ($i != 6){
				$kaGroup[$i] = "";
			}
		}
		$kaNumWord[$i] = substr($num,$ii,1);
		$ii++;
		$returnNumWord .= $kaDigit[$kaNumWord[$i]].$kaGroup[$i];
	}
	if (!$digit) $returnNumWord .= 'บาท';
	if (isset($num_decimal[1]) && $num_decimal[1]>0){
		//$returnNumWord.="จุด";
		$returnNumWord .= sg_money2bath($num_decimal[1],true);
		$returnNumWord .= 'สตางค์';
	} else {
		if (!$digit) $returnNumWord .= 'ถ้วน';
	}
	return $returnNumWord;
}

/** @Rewritten - SoftGanz Group - July 24, 2004 */
function sg_text2html($text) {
	$text = __sg_encode($text);
	if (load_lib('func.markdown.php','lib')) $text = Markdown($text);
	return $text;
}

function __sg_encode_code_callback($m) {
	$str='<blockquote class="code"><pre>'.htmlspecialchars(str_replace(['"','&nbsp;'],['"',' '],$m[2])).'</pre></blockquote>';
	return $str;
}

function __sg_encode_php_callback($m) {return chr($m[1]);}

function __sg_encode($src=NULL) {
	// add this array item will bug on dreamhost server
	/*(cfg('topic.allow.script')?"":"#<script[^>]*?>.*?</script>#si"), // <script>...</script>*/

	$search = array (

		// Funny image code
		'# :\) #si' ,
		'# :d #si' ,
		'# ;\) #si' ,
		'# \+\) #si' ,
		'# 8\) #si' ,
		'# :p #si' ,
		'# :s #si' ,
		'# :\| #si' ,
		'# :\@ #si' ,
		'# :o #si' ,
		'# \#\) #si' ,

		// paragrap alignment code
		'#=>(.*?)<=\n#', // Center
		'#<=(.*?)\n#', // Left
		// '#=>(.*?)\n#', // Right

		// BBcode encoding
		'#\[hr\]#si', // [hr] for horizontal line
		"#\[b\](.*?)\[/b\]#si", // [b]...[/b] for bolding text.
		"#\[i\](.*?)\[/i\]#si", // [i]...[/i] for italicizing text.
		"#\[u\](.*?)\[/u\]#si", // [u]...[/u] for underlining text.
		'#\[color=(.*?)\](.*?)\[/color\]#si', // [color=red]...[/color]
		"#\[quote(.*?)\](.*?)\[/quote]#si", // [quote]...[/quote]

		"#\[url\](http://)?(.*?)\[/url\]#si", // [url]www.softganz.com[/url]
		"#\[url=(http://)?(.*?)\](.*?)\[/url\]#si", // [url=www.softganz.com][/url]
		"#\[email\](.*?)\[/email\]#si", // [email]user@domain.com[/email]
		"#\[img\](.*?)\[/img\]#si", // [img]image_url_here[/img] code..

		"#<p>\[(left)\](.*?)\[/left\]</p>#si", // [left]...[/left]+2 enter
		"#\[(center)\](.*?)\[/center\]#si", // [center]...[/center]+2 enter
		"#\[(right)\](.*?)\[/right\]#si", // [right]...[/right]+2 enter
		"#\[p (.*?)\](.*?)\[/p\]#si", // [p]...[/p]

		"#\[text\](.*?)\[/text\]#si", // [text]your text here[/text]

		"#\[f:(.*?)\]#si", // [f:funny_name]
		"#\[b:(.*?)\]#si", // [b:bullet_name]

		"#\[html\](.*?)\[/html\]#si", // [text]your text here[/text]

		"#\[\-(.*?)\-\]#si", // [-- text --] // remove this text
		'#<summary>(.*)<\/summary>#', // remove summary tag
		'/([ \n])#([0-9a-zก-๙เแ]+)/i', // change # to hashtag

		// New line code
		'#\r\n#si',
		'#\n\n\n#' ,
		'#[ ]+\n#si',
		'#[ ]{2}#si', // change 2 space into space+&nbsp; remember there is bug on 2 space in tag
	);

	$replace = array (
		'<img class="emotion" alt=":)" src="'._img.'/emotions/smiley-smile.gif" />',
		' <img class="emotion" alt=":d" src="'._img.'/emotions/smiley-laughing.gif" /> ',
		' <img class="emotion" alt=";)" src="'._img.'/emotions/smiley-wink.gif" /> ',
		' <img class="emotion" alt="+)" src="'._img.'/emotions/smiley-good.gif" /> ',
		' <img class="emotion" alt="8)" src="'._img.'/emotions/rolleyes.gif" /> ',
		' <img class="emotion" alt=":p" src="'._img.'/emotions/smiley-tongue-out.gif" /> ',
		' <img class="emotion" alt=":s" src="'._img.'/emotions/smiley-undecided.gif" /> ',
		' <img class="emotion" alt=":|" src="'._img.'/emotions/smiley-frown.gif" /> ',
		' <img class="emotion" alt=":@" src="'._img.'/emotions/smiley-sealed.gif" /> ',
		' <img class="emotion" alt=":o" src="'._img.'/emotions/smiley-surprised.gif" /> ',
		' <img class="emotion" alt="#)" src="'._img.'/emotions/smiley-cool.gif" /> ',

		'<p align="center">\\1</p>'."\n",
		'<p align="left">\\1</p>'."\n",
		// '<p align="right">\\1</p>'."\n",

		'<hr />',
		'<strong>\\1</strong>',
		'<em>\\1</em>',
		'<u>\\1</u>',
		'<font color="\\1">\\2</font>',
		'<blockquote\\1>\\2</blockquote>',

		'<a href="http://\\2" target="_blank" title="click to visit website \\2">\\2</a>',
		'<a href="http://\\2" target="_blank" title="click to visit website \\2">\\3</a>',
		'<a href="mailto:\\1">\\1</a>',
		'<img src=\\1 class="photo" alt="" />',

		'<p align="\\1">\\2</p>',
		'<p align="\\1">\\2</p>',
		'<p align="\\1">\\2</p>',
		'<p \\1>\\2</p>',

		'[html]<pre>\\1</pre>[/html]',

		'<img src="'._img.'/funny/\\1.gif" alt="" />',
		'<img src="'._img.'/bullet/\\1.gif" alt="" />',

		'\\1',

		'<!-- remove -->'.cfg('topic.remove_text'),
		'\\1',  // remove summary tag
		'\\1<a class="hashtag" href="'.url('tags/\\2').'">#\\2</a>',

		"\n",
		'<br />'._NL.'<br />'._NL.'<br />'._NL,
		'<br />'._NL,
		'\\1&nbsp; ',
	);

		//	'\'<blockquote class="code">\'.htmlview(str_replace(\'\"\',\'"\',\'\\2\'),\'Code \\1\',false).\'</blockquote>\'',

	$message=' '.$src.' ';
	$message = preg_replace($search, $replace, $message);
	if (preg_match('/\[code(.*?)\]/i', $message)) {
		$message = preg_replace_callback("#\[code(.*?)\](.*?)\[/code\]#si", '__sg_encode_code_callback', $message); // [code]...[/code]
	}

	$message = preg_replace_callback("'&#(\d+);'", '__sg_encode_php_callback', $message); // evaluate as php


	if (cfg('clickable.make')) {
		$message = preg_replace("#([\t\r\n ])([a-z0-9]+?){1}://([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="\2://\3"'.(cfg('clickable.target')?' target="'.cfg('clickable.target').'"':'').(cfg('clickable.rel')?' rel="'.cfg('clickable.rel').'"':'').'>\3</a>', $message);
		$message = preg_replace("#([\t\r\n ])(www)\.(([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="http://\2.\3" '.(cfg('clickable.target')?' target="'.cfg('clickable.target').'"':'').(cfg('clickable.rel')?' rel="'.cfg('clickable.rel').'"':'').'>\2.\3</a>', $message);
	}

	//REPLACE 'WWW.' LINKS WITH 'HTTP://WWW.'
	//	$message = preg_replace('/([\s][^\w\/])(www\.[a-z0-9\-]+\.[a-z0-9\-]+)/i', '$1http://$2',$message);

	//REPLACE HTTP:// STRINGS WITH LINKS
	//	$message = preg_replace('/([\s])([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i','$1<a target="_blank" href="$2" rel="external nofollow">$2</a>',$message);

	//	$message = preg_replace('/@(\w+)/','<a target="_blank" href="https://twitter.com/$1" rel="external nofollow">@$1</a>',$message);

	$message = trim($message);
	return $message;
	// .(debug('encode')?'<hr /><strong>HTML Result : </strong>'.htmlview($message).'<hr /><strong>Source text :</strong>'.htmlview($src):'');
}

function sg_utf8_substr($str,$start) {
   preg_match_all('/./u', $str, $ar);
   if (func_num_args() >= 3) {
       $end = func_get_arg(2);
       return join('',array_slice($ar[0],$start,$end));
   } else {
       return join('',array_slice($ar[0],$start));
   }
}

function sg_urlencode($str) {
	return urlencode(cfg('client.characterset')=='utf-8' ? sg_utf8_to_tis620($str) : $str);
}

function sg_photo_resize($srcFile, $dstWidth, $dstHeight, $dstFile , $autoSave, $quality=50) {
	if ( file_exists($srcFile) and is_file($srcFile) ) {
		$srcTypes = getimagesize($srcFile);
		$srcSize = FileSize($srcFile);
	} else {
		return false;
	}

	$srcWidth = $srcTypes[0];
	$srcHeight = $srcTypes[1];
	$srcType = $srcTypes['mime'];

	if ( empty($dstFile) ) $dstFile = $srcFile;
	if ( !$autoSave ) $dstFile = dirname($srcFile)."/auto_image_resize.jpg";

	if ( $dstWidth and empty($dstHeight) ) $dstHeight = round((double)($srcHeight*$dstWidth / $srcWidth));
	if ( $autoSave ) {
		// debugMsg('SAVE @'.date('H:i:s').' to '.$dstFile);
		$result = false;
		if ($srcWidth >= $dstWidth && $srcHeight >= $dstHeight) {
			// ini_set('memory_limit', '1024MB');
			BasicModel::watch_log('system', 'Photo Resize', \SG\json_encode(['imageType' => $srcType, 'width' => $srcWidth, 'height' => $srcHeight,'size' => $srcSize, 'file' => $srcFile]));

			// Copy file that size over 6MB to upload/error folder
			if ($srcSize > 6000000) {
				$tmpDescFile = 'upload/error/'.basename($srcFile);
				// debugMsg($tmpDescFile);
				copy($srcFile, $tmpDescFile);
			}

			try {
				if (($srcType == "image/jpeg" or $srcType == "image/pjpeg") and function_exists("imagecreatefromjpeg"))
					$handle = @imagecreatefromjpeg($srcFile);
				else if ($srcType == "image/png" and function_exists("imagecreatefrompng"))
					$handle = @imagecreatefrompng($srcFile);
				else if ($srcType == "image/gif" and function_exists("imagecreatefromgif") )
					$handle = @imagecreatefromgif($srcFile);
				else return false;
			} catch (Exception $e) {
				BasicModel::watch_log('system', 'Photo Resize', \SG\json_encode(['error' => 'YES', 'imageType' => $srcType, 'width' => $srcWidth, 'height' => $srcHeight,'size' => $srcSize, 'file' => $srcFile]));
				return false;
			}
			if (!$handle) return false;

			if ( !function_exists("imagecopyresampled") or !function_exists("imagejpeg") ) return false;
			$srcWidth  = @imagesx($handle);
			$srcHeight = @imagesy($handle);

			$newHandle = @imagecreatetruecolor($dstWidth, $dstHeight);
			if (!$newHandle) return false;

			if (!@imagecopyresampled($newHandle, $handle, 0, 0, 0, 0, $dstWidth, $dstHeight, $srcWidth, $srcHeight)) return false;
			@imagedestroy($handle);

			if ($srcType == "image/jpeg" or $srcType == "image/pjpeg") $result = @imagejpeg($newHandle, $dstFile, $quality);
			else if ($srcType == "image/png") $result = @imagepng($newHandle, $dstFile);
			else if ($srcType == "image/gif") $result = @imagegif($newHandle, $dstFile);
			else $result = false;

			@imagedestroy($newHandle);
		}
		return $result;
	}
}

function sg_seturl($url,$value) {
	$replace=is_object($value) ? $value : object($value);
	$result=preg_replace('/\$([a-zA-Z0-9_]*)/e','$replace->\\1',$url);
	return $result;
}

function sg_status_text($status) {
	static $status_text = [
		0 => 'n/a',
		_DRAFT => 'draft',
		_PUBLISH => 'pubish',
		_WAITING => 'waiting',
		_BLOCK => 'block',
		_LOCK => 'lock'
	];

	return $status_text[$status];
}

/**
* Explode attribute string to array
* @param
* @return Array
*/
function sg_explode_attr($attribs) {
	$pattern = '/(\\w+)\s*=\\s*("[^"]*"|\'[^\']*\'|[^"\'\\s>]*)/';
	preg_match_all($pattern, $attribs, $matches, PREG_SET_ORDER);
	$attrs = array();
	foreach ($matches as $match) {
		if (($match[2][0] == '"' || $match[2][0] == "'") && $match[2][0] == $match[2][strlen($match[2])-1]) {
			$match[2] = substr($match[2], 1, -1);
		}
		$name = strtolower($match[1]);
		$value = html_entity_decode($match[2]);
		switch ($name) {
			case 'class':
				$attrs[$name] = preg_split('/\s+/', trim($value));
				break;
			case 'style':
				// parse CSS property declarations
				break;
			default:
				$attrs[$name] = $value;
		}
	}
	return $attrs;
}

/**
* Implode attrbute array to string format name=value
* @param Array $attributes
* @return String
*/
function sg_implode_attr($attributes = [], $sep = ' ', $options = '{}') {
	$defaultOptions = '{quote: "\""}';
	$options = SG\json_decode($options, $defaultOptions);
	$singQuote = '\'';

	if (is_string($attributes)) return $attributes;

	if (is_object($attributes)) $attributes = (Array) $attributes;
	if (!is_array($attributes)) return;

	$ret = '';
	foreach ($attributes as $attributeKey => $attributeValue) {
		if (is_null($attributeValue)) continue;
		if ($attributeKey === 'data-options') {
			$ret .= $attributeKey
				. '='.$singQuote
				. (is_array($attributeValue) || is_object($attributeValue) ? json_encode($attributeValue, JSON_UNESCAPED_UNICODE) : trim($attributeValue))
				. $singQuote;
		} else if (is_array($attributeValue) || is_object($attributeValue)) {
			$ret .= $attributeKey
				. '='.$singQuote
				. (is_array($attributeValue) || is_object($attributeValue) ? json_encode($attributeValue) : trim($attributeValue))
				. $singQuote;
		} else if (is_string($attributeValue) && preg_match('/^\{/', $attributeValue)) {
			$ret .= $attributeKey.'='.$singQuote.$attributeValue.$singQuote;
		} else {
			$ret .= $attributeKey.'='.$options->quote.$attributeValue.$options->quote;
		}
		$ret .= $sep;
	}
	return trim($ret, $sep);
}

/**
* Remove charactor is not numeric for money
*
* @param String $money
* @return Decimal $money
*/
function sg_strip_money($money) {
	$money=(float) preg_replace('/[^0-9\.\-]/','',$money);
	return $money;
}

function sg_json_decode($json, $default = '{}') {
	$regex = '/(,|\{)[ \t\n]*(\w+)[ ]*:[ ]*/';
	$result = (object) array();
	$args = array_reverse(func_get_args());

	//debugMsg($args,'$args of sg_json_decode');

	foreach ($args as $i=>$json) {
		//debugMsg('parameter = '.$json);
		if (is_string($json)) $json = trim($json);

		if (is_array($json)) ;
		else if (is_object($json)) ;
		else if (is_string($json) && substr($json,0,1) == '{') {
			$json = preg_replace($regex,'$1"$2":',$json);
			$json = json_decode($json);
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


	// "/([a-zA-Z0-9_]+?):/"
	if (is_object($json)) ;
	else if (is_array($json)) $json=(object)$json;
	else if (is_string($json) && substr($json,0,1) == '{') {
		//debugMsg('$json = '.$json);
		//debugMsg(preg_replace("/([a-zA-Z0-9_]+?):/" , "\"$1\":", $json));
		$json = preg_replace('/(,|\{)[ \t\n]*(\w+)[ ]*:[ ]*/','$1"$2":',$json);
		//$json = preg_replace('/":\'?([^\[\]\{\}]*?)\'?[ \n\t]*(,"|\}$|\]$|\}\]|\]\}|\}|\])/','":"$1"$2',$json);
		//debugMsg('$json string = '.$json);
		//$json=json_decode(preg_replace("/([a-zA-Z0-9_]+?):/" , "\"$1\":", $json));
		$json = json_decode($json);
	}

	if (is_object($default)) ;
	else if (is_array($default)) $default=(object)$default;
	else $default=json_decode(preg_replace("/([a-zA-Z0-9_]+?):/" , "\"$1\":", $default));
	foreach ($default as $key => $value) {
		if (!property_exists($json, $key)) $json->{$key}=$value;
	}
	return $json;
}

function sg_json_encode($input) {
	if (defined('JSON_UNESCAPED_UNICODE')) {
		return json_encode($input,JSON_UNESCAPED_UNICODE+JSON_PRETTY_PRINT);
	} else {
		return preg_replace_callback('/\\\\u([0-9a-zA-Z]{4})/', function ($matches) {
			return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UTF-16');
		}, json_encode($input));
	}
}

/**
* Create dropdown icon for click or hover
* @param String $ui
* @param String para
* @return String
*/
function sg_dropbox($ui, $options = '{}') {
	$default = '{
			type: "click",
			class: "leftside -no-print",
			text: "",
			icon: null,
			iconText : "more_vert",
			title: "มีเมนูย่อย"
		}';

	$options = sg_json_decode($options,$default);

	$defaultLink = '<a href="javascript:void(0)" title="'.$options->title.'">'
		. ($options->text!=''?'<span>'.$options->text.'</span>':'')
		. '<i class="icon -'.($options->icon ? $options->icon : 'material').'">'.($options->iconText).'</i>'
		. '</a>';

	$dropLink = \SG\getFirst($options->link, $defaultLink);

	$ret .= '<span class="widget-dropbox sg-dropbox '.$options->type.' '.$options->class.'" data-type="'.$options->type.'"'.($options->url ? ' data-url="'.$options->url.'"' : '').'>'
		. $dropLink
		. '<div class="sg-dropbox--wrapper -wrapper -hidden">'
		. '<div class="sg-dropbox--arrow -arrow"></div>'
		. '<div class="sg-dropbox--content -content">'.$ui.'</div>'
		. '</div>'
		. '</span>';

	return $ret;
}

/**
* Make parent/child tree
* @param Array $items
* @param Array $tree
* @param Array $root
* @return Array
*/
function sg_parseTree($items=array(), $tree=array(), $root=null) {
	$return = array();
	# Traverse the tree and search for direct children of the root
	foreach($tree as $child => $parent) {
		# A direct child is found
		if($parent == $root) {
			# Remove item from tree (we don't need to traverse this again)
			unset($tree[$child]);
			# Append the child into result array and parse its children
			$return[] = array(
									'name' => $child,
									'rs'=>$items[$child],
									'children' => sg_parseTree($items,$tree, $child)
									);
		}
	}
	return empty($return) ? null : $return;
}

/**
* Make tree to array with level
* @param Array $items
* @param Array $tree
* @param Array $rows
* @param Integer $level
* @return Array
*/
function sg_printTreeTable($items,$tree,&$rows = NULL,$level=0) {
	if(!is_null($tree) && count($tree) > 0) {
		foreach($tree as $node) {
			$row=$items[$node['name']];
			$row->treeLevel=$level;
			$rows[]=$row;
			sg_printTreeTable($items,$node['children'],$rows,$level+1);
		}
	}
	return $rows;
}

/**
* Clean XML Seperator String
* @param String $str
* @return
*/
function sg_cleanXlsSepString(&$str) {
	$str = preg_replace("/\t/", "\\t", $str);
	$str = preg_replace("/\r?\n/", "", $str);
	$str = preg_replace("/\n/", "", $str);
	$str = preg_replace("/\r/", "", $str);
	if(substr($str,0,1)!='=' && strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
	$str=strip_tags($str);
}

?>