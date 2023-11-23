<?php
/**
* API     :: API Model
* Created :: 2023-11-13
* Modify  :: 2023-11-23
* Version :: 2
*
* @param Array $args
* @return Object
*
* @usage import('model:module.modelname.php')
* @usage new ApiModel([])
* @usage ApiModel::function($conditions)
*/

use Softganz\DB;

class ApiModel {
	function __construct($args = []) {
	}

	public static function send($args = [], &$options = []) {
		set_time_limit(3600);
		ini_set('memory_limit', '4095M'); // 4 GBs minus 1 MB
		if (is_string($args)) $args = ['url' => $args];

		// debugMsg('Send to '.$args['url']);

		$default = '{port: null, username: null, password: null, type: "text"}';
		$options = json_decode($options, $default);

		// Get file from camera with curl function
		$ch = curl_init();

		$headers = [
			// 'APIKEY: '.'ChfR12-Api-Key '.$args['auth']['value'],
			// 'Authorization: Basic :'.$args['auth']['value'],
			// 'Content-Type: application/json',
			// 'Content-Type: Content-type: application/x-www-form-urlencoded',
		];
		if ($args['auth']) $headers[] = $args['auth']['key'].': '.$args['auth']['value'];
		if ($args['contentType']) $headers[] = 'Content-Type: '.$args['contentType'];

		$options = [
			CURLOPT_URL => $args['url'],
			CURLOPT_RETURNTRANSFER => isset($args['returnTransfer']) ? $args['returnTransfer'] : true,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_VERBOSE => 0,
			CURLOPT_HTTPHEADER => $headers,
			// CURLOPT_HEADER => true,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => strtoupper(SG\getFirst($args['method'], 'GET')),
		];

		if ($args['method'] == 'post') $options[CURLOPT_POST] = 1;
		if ($args['postFields']) $options[CURLOPT_POSTFIELDS] = json_encode($args['postFields']);
		// $options[CURLOPT_POSTFIELDS] = '{
		// 	"plan_id": "1"
		// }';
		// $options = [
		// 	CURLOPT_URL => 'https://medata.nhso.go.th/v1/api/chfr12/gethealthplan',
		// 	CURLOPT_RETURNTRANSFER => true,
		// 	CURLOPT_ENCODING => '',
		// 	CURLOPT_MAXREDIRS => 10,
		// 	CURLOPT_TIMEOUT => 0,
		// 	CURLOPT_FOLLOWLOCATION => true,
		// 	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		// 	CURLOPT_CUSTOMREQUEST => 'GET',
		// 	CURLOPT_POSTFIELDS =>'{
		// 		"plan_id": "21"
		// 	}',
		// 	CURLOPT_HTTPHEADER => [
		// 		'Content-Type: application/json',
		// 		'ChfR12-Api-Key: pWJDAM6oqm7ozsQ89jA9cQ==pUsdrle28412ad2190',
		// 		// 'Cookie: TS01e88bc2=013bd252cb38fc39ff7bae73b3643b966cb88ecd48d803a94d7a9a1a046f403b8a3cf3dd15f9a54fcf24cda9eca40c40ef21c89b05'
		// 	],
		// ];


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
		// curl_setopt($ch, CURLOPT_FILE, $fh);

		// debugMsg($options, '$options');

		curl_setopt_array($ch, $options);

		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		$info['error'] = curl_error($ch);
		curl_close($ch);

		if ($args['result'] === 'json') {
			if (debug()) debugMsg($result);
			return \json_decode($result);
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

		$data = [
			':userId' => i()->uid,
			':apiKey' => $args['apiKey'],
			':apiModel' => $args['apiModel'],
			':status' => 'COMPLETE',
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

	public static function getWaiting() {
		$result = DB::select('SELECT * FROM %api_wait% WHERE `status` = "WAITING" LIMIT 10');
		DB::query('UPDATE %api_wait% SET `status` = "SENDING" WHERE `status` = "WAITING" LIMIT 10');
		return $result;
	}
}
?>