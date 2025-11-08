<?php
/**
* API     :: API Model
* Created :: 2023-11-13
* Modify  :: 2025-06-08
* Version :: 5
*
* @param Array $args
* @return Object
*
* @usage import('model:module.modelname.php')
* @usage new ApiModel([])
* @usage ApiModel::function($conditions)
*/

use Softganz\DB;
use Softganz\JsonDataModel;
use Softganz\DBException;

class ApiModel {
	function __construct($args = []) {}

	public static function send($args = [], &$curlOptions = []) {
		set_time_limit(3600);
		ini_set('memory_limit', '4095M'); // 4 GBs minus 1 MB
		if (is_string($args)) $args = ['url' => $args];

		// debugMsg('Send to '.$args['url']);

		$ch = curl_init();

		$headers = [
			// 'APIKEY: '.'ChfR12-Api-Key '.$args['auth']['value'],
			// 'Authorization: Basic :'.$args['auth']['value'],
			// 'Content-Type: application/json',
			// 'Content-Type: Content-type: application/x-www-form-urlencoded',
		];
		if ($args['auth']) $headers[] = $args['auth']['key'].': '.$args['auth']['value'];
		if ($args['contentType']) $headers[] = 'Content-Type: '.$args['contentType'];

		$curlOptions = [
			CURLOPT_URL => $args['url'],
			CURLOPT_RETURNTRANSFER => isset($args['returnTransfer']) ? $args['returnTransfer'] : true,
			CURLOPT_SSL_VERIFYHOST => 1,
			CURLOPT_VERBOSE => 0,
			CURLOPT_HTTPHEADER => $headers,
			// CURLOPT_HEADER => true,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => strtoupper(SG\getFirst($args['method'], 'GET')),
			CURLOPT_SSL_VERIFYPEER => false,
		];

		if ($args['method'] == 'post') $curlOptions[CURLOPT_POST] = 1;
		if ($args['postFields']) {
			if ($args['contentType'] === 'application/json') {
				// Send parameter as json
				$curlOptions[CURLOPT_POSTFIELDS] = json_encode($args['postFields'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
			} else {
				// Send parameter as query
				$curlOptions[CURLOPT_POSTFIELDS] = http_build_query($args['postFields'], '', '&');
			}
		}

		if ($args['debug']) debugMsg($args, '$args');
		if ($args['debug']) debugMsg($curlOptions, '$curlOptions');

		curl_setopt_array($ch, $curlOptions);

		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		$info['error'] = curl_error($ch);
		curl_close($ch);

		if ($args['debug']) debugMsg($result, '$result');
		if ($args['debug']) debugMsg($info, '$info');

		if ($args['result'] === 'json') {
			// if (debug()) debugMsg($result);
			return json_decode($result);
		} else if ($args['result'] === 'text') {
			return $result;
		} else {
			return $result;
		}
	}

	public static function waitInfo($conditions = []) {
		$info = DB::select([
			'SELECT * FROM %api_wait% %WHERE% LIMIT 1',
			'where' => [
				'%WHERE%' => [
					$conditions['id'] ? ['`apiId` = :apiId', ':apiId' => $conditions['id']] : NULL,
					$conditions['key'] ? ['`apiKey` = :apiKey', ':apiKey' => $conditions['key']] : NULL,
				]
			], // where
		]);

		return $info;
	}

	public static function resend($options = []) {
		set_time_limit(3600);
		ini_set('memory_limit', '4095M'); // 4 GBs minus 1 MB

		$ch = curl_init();

		curl_setopt_array($ch, $options);

		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		$info['error'] = curl_error($ch);
		curl_close($ch);

		return \json_decode($result);

		// if ($args['result'] === 'json') {
		// 	if (debug()) debugMsg($result);
		// 	// $info['text'] = $result;
		// 	// $info['result'] = \json_decode($result);
		// 	return \json_decode($result);
		// } else if ($args['result'] === 'text') {
		// 	return $result;//['result'];
		// } else {
		// 	return $result;
		// }
	}

	public static function sendLater($args = []) {
		if (empty($args['apiModel'])) return false;

		$data = [
			':userId' => i()->uid,
			':apiKey' => $args['apiKey'],
			':apiModel' => $args['apiModel'],
			':status' => 'WAITING',
			':sendResult' => SG\json_encode($args['sendResult']),
			':curlParam' => SG\json_encode($args['curlParam']),
			':created' => date('U'),
		];
		DB::query([
			'INSERT INTO %api_wait%
			(`userId`, `apiKey`, `apiModel`, `status`, `sendResult`, `curlParam`, `created`)
			VALUES
			(:userId, :apiKey, :apiModel, :status, :sendResult, :curlParam, :created)',
			'var' => $data
		]);
		// debugMsg(mydb()->_query);
	}

	public static function sendComplete($args = []) {
		if (empty($args['apiModel'])) return false;

		$args['curlParam'][10015] = json_decode($args['curlParam'][10015]);

		$data = [
			':userId' => i()->uid,
			':apiKey' => $args['apiKey'],
			':apiModel' => $args['apiModel'],
			':status' => 'COMPLETE',
			':sendResult' => new JsonDataModel((Object) $args['sendResult']),
			':curlParam' => new JsonDataModel((Object) $args['curlParam']),
			':created' => date('U'),
		];

		try {
			DB::query([
				'INSERT INTO %api_wait%
				(`userId`, `apiKey`, `apiModel`, `status`, `sendResult`, `curlParam`, `created`)
				VALUES
				(
					:userId
					, :apiKey
					, :apiModel
					, :status
					, :sendResult
					, :curlParam
					, :created
				)',
				'var' => $data,
			]);
		} catch (DBException $exception) {
		}
		debugMsg(R('query'));
	}

	public static function getQueue($conditions = []) {
		$conditions = SG\json_decode(
			$conditions,
			(Object) [
				'status' => '',
				'options' => ['start' => 0, 'items' => 10, 'orderBy' => 'apiId', 'sortOrder' => 'DESC']
			]
		);

		// debugMsg($conditions, '$conditions');
		$result = DB::select([
			'SELECT *, FROM_UNIXTIME(`created`) `created` FROM %api_wait% %WHERE% $ORDER$ $LIMIT$',
			'where' => [
				'%WHERE%' => [
					$conditions->status ? ['`status` = :status', ':status' => $conditions->status] : NULL
				]
			],
			'var' => [
				'$ORDER$' => 'ORDER BY '.$conditions->options['orderBy'].' '.$conditions->options['sortOrder'],
				'$LIMIT$' => 'LIMIT '.$conditions->options['start'].','.$conditions->options['items'],
			]
		]);
		// debugMsg(mydb()->_query);
		return $result;
	}

	/**
	 * $options - Bool updateStatus
	 *
	 */
	public static function getWaiting($options = []) {
		$result = DB::select('SELECT * FROM %api_wait% WHERE `status` = "WAITING" LIMIT 10');
		if ($options['updateStatus']) {
			DB::query('UPDATE %api_wait% SET `status` = "SENDING" WHERE `status` = "WAITING" LIMIT 10');
		}
		return $result;
	}
}
?>