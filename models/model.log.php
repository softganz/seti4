<?php
/**
* Log     :: Log Model
* Created :: 2024-06-26
* Modify  :: 2024-06-26
* Version :: 1
*
* @param Array $args
* @return Object
*
* @usage import('model:module.modelname.php')
* @usage new LogModel([])
* @usage LogModel::function($conditions)
*/

class LogModel {
	function __construct($args = []) {
	}

	// public static function get($id, $options = '{}') {
	// 	$defaults = '{debug: false}';
	// 	$options = \SG\json_decode($options, $defaults);
	// 	$debug = $options->debug;

	// 	$result = NULL;

	// 	return $result;
	// }

	// public static function items($conditions, $options = '{}') {
	// 	$conditions = (Object) $conditions;
	// 	$defaults = '{debug: false}';
	// 	$options = \SG\json_decode($conditions->options, $defaults);
	// 	$debug = $options->debug;
	// 	unset($conditions->options);

	// 	$result = (Object) [];

	// 	return $result;
	// }

	public static function save($args = []) {
		// $module = NULL, $keyword = NULL, $message = NULL, $uid = NULL, $keyid = NULL, $fldname = NULL) {
		$args = (Object) $args;

		mydb()->_watchlog = false;

		if (!mydb()->table_exists('watchdog')) return false;

		mydb()->_watchlog = false;

		$data = (Object) [
			'date' => date('Y-m-d H:i:s'),
			'uid' => SG\getFirst($args->userId, i()->uid),
			'ip' => ip2long(i()->ip),
			'module' => SG\getFirst($args->module),
			'keyword' => SG\getFirst($args->keyword),
			'message' => SG\getFirst($args->message),
			'url' => preg_match('/IIS/i',$_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'] : $_SERVER['REQUEST_URI'],
			'referer' => SG\getFirst($_SERVER['HTTP_REFERER']),
			'browser' => $_SERVER['HTTP_USER_AGENT'],
			'keyId' => $args->keyId,
			'fieldName' => $args->fieldName,
		];

		// debugMsg($data, '$data');
		// if (i()->username == 'softganz') echo 'LOG : '.mydb()->_query.'<br />';

		mydb()->_watchlog = false;
		$r = mydb::query(
			'INSERT INTO %watchdog%
			( `date` , `uid` , `ip` , `module` , `keyword` , `message` , `url` , `referer` , `browser`, `keyid`, `fldname` )
			VALUES
			(:date, :uid, :ip, :module, :keyword, :message, :url, :referer, :browser, :keyId, :fieldName );',
			$data
		);
		// debugMsg(mydb()->_query);
		mydb()->_watchlog = true;

		// if (i()->username == 'softganz') echo 'WATCH : '.$stmt.print_o($data,'$data').print_o($r,'$r').'<br />';
	}
}
?>