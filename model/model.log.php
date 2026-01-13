<?php
/**
 * Log     :: Log Model
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2024-06-26
 * Modify  :: 2026-01-13
 * Version :: 4
 *
 * @param Array $args
 * @return Object
 *
 * @usage import('model:module.modelname.php')
 * @usage new LogModel([])
 * @usage LogModel::function($conditions)
 */

use Softganz\DB;

class LogModel {
	function __construct($args = []) {
	}

	public static function save($args = []) {
		// $module = NULL, $keyword = NULL, $message = NULL, $uid = NULL, $keyid = NULL, $fldname = NULL) {
		$args = (Object) $args;

		if (!DB::tableExists('watchdog')) return false;

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

		try {
			$dbResult = DB::query([
				'INSERT INTO %watchdog%
				( `date`, `uid` , `ip` , `module` , `keyword` , `message` , `url` , `referer` , `browser`, `keyid`, `fldname` )
				VALUES
				(:date, :uid, :ip, :module, :keyword, :message, :url, :referer, :browser, :keyId, :fieldName );',
				'var' => $data,
				'options' => ['log' => false, 'history' => false]
			]);
		} catch (Exception $e) {
			return false;
		}

		return $dbResult->insertId();
	}
}
?>