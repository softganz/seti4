<?php
$debug=false;

function r_watchdog_log($module = NULL, $keyword = NULL, $message = NULL, $uid = NULL, $keyid = NULL, $fldname = NULL) {
	mydb()->_watchlog = false;

	if (!mydb()->table_exists('watchdog')) return false;

	mydb()->_watchlog = false;

	$watch = (Object) [
		'date' => date('Y-m-d H:i:s'),
		'uid' => $uid ? $uid : i()->uid,
		'ip' => ip2long(i()->ip),
		'module' => SG\getFirst($module),
		'keyword' => SG\getFirst($keyword),
		'message' => SG\getFirst($message),
		'url' => preg_match('/IIS/i',$_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'] : $_SERVER['REQUEST_URI'],
		'referer' => SG\getFirst($_SERVER['HTTP_REFERER']),
		'browser' => $_SERVER['HTTP_USER_AGENT'],
		'keyid' => $keyid,
		'fldname' => $fldname,
	];

	// if (i()->username == 'softganz') echo 'LOG : '.mydb()->_query.'<br />';

	mydb()->_watchlog = false;
	$r = mydb::query(
		'INSERT INTO %watchdog%
		( `date` , `uid` , `ip` , `module` , `keyword` , `message` , `url` , `referer` , `browser`, `keyid`, `fldname` )
		VALUES
		(:date, :uid, :ip, :module, :keyword, :message, :url, :referer, :browser, :keyid, :fldname );',
		$watch);
	mydb()->_watchlog = true;

	// if (i()->username == 'softganz') echo 'WATCH : '.$stmt.print_o($watch,'$watch').print_o($r,'$r').'<br />';
}
?>