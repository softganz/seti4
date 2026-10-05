<?php
/**
 * Log      :: Log Model
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2024-06-26
 * Modified :: 2026-10-05
 * Version  :: 7
 *
 * @uses import('model:module.modelname.php')
 * @uses new LogModel([])
 * @uses LogModel::function($conditions)
 */

use Softganz\DB, Softganz\JsonDataModel;

class LogModel {
	public static function save($data = []) {
		// $module = null, $keyword = null, $message = null, $uid = null, $keyid = null, $fldname = null) {
		$data = (object) array_replace(
			[
				'userId' => null,
				'module' => null,
				'keyword' => null,
				'message' => null,
				'keyId' => null,
				'fieldName' => null,
			],
			(array) $data
		);

		if (!DB::tableExists('watchdog')) return false;

		$data = (Object) [
			'date' => date('Y-m-d H:i:s'),
			'uid' => SG\getFirst($data->userId, i()->uid ?? null),
			'ip' => ip2long(i()->ip ?? null),
			'module' => SG\getFirst($data->module),
			'keyword' => SG\getFirst($data->keyword),
			'message' => SG\getFirst($data->message),
			'url' => preg_match('/IIS/i', $_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'] : $_SERVER['REQUEST_URI'],
			'referer' => SG\getFirst($_SERVER['HTTP_REFERER'] ?? null),
			'browser' => $_SERVER['HTTP_USER_AGENT'],
			'keyId' => $data->keyId,
			'fieldName' => $data->fieldName,
		];

		try {
			$insertId = DB::query([
				'INSERT INTO %watchdog%
				( `date`, `uid` , `ip` , `module` , `keyword` , `message` , `url` , `referer` , `browser`, `keyid`, `fldname` )
				VALUES
				(:date, :uid, :ip, :module, :keyword, :message, :url, :referer, :browser, :keyId, :fieldName );',
				'var' => $data,
				'options' => ['log' => false, 'history' => false]
			])->insertId();
		} catch (Exception $e) {
			return false;
		}

		return $insertId;
	}

	public static function history(array $data = []): int {
		$data = (Object) array_replace_recursive(
			[
				'module' => null,
				'orgId' => null,
				'userId' => i()->uid,
				'nodeId' => null,
				'refId' => null,
				'tagName' => null,
				'createDate' => date('Y-m-d H:i:s'),
				'data' => [],
			],
			(Array) $data
		);

		$data->json = new JsonDataModel($data->data);

		try {
			$insertId = DB::query([
				'INSERT INTO %history%
				(`uid`, `orgId`, `tpid`, `refId`, `tagName`, `createDate`, `json`)
				VALUES
				(
				:userId, :orgId, :nodeId, :refId, :tagName, :createDate
				, :json
				)',
				'var' => $data,
			])->insertId();
		} catch (Exception $exception) {
			return 0;
		}

		return $insertId;
	}
}
?>
