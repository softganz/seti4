<?php
/**
* iMed :: Khon Songkhla Data Center Model
* Created 2022-02-20
* Modify 	2022-02-28
*
* @return Object
*
* @usage new ImedKhonSongkhlaModel([])
* @usage ImedKhonSongkhlaModel::function($conditions, $options)
*
* API Docs : https://documenter.getpostman.com/view/1655636/UVXdPeA6
*/

class ImedKhonSongkhlaModel {
	var $apiUrl = 'https://sk-api.cqc-songkhlapao.com';
	var $user = 'email=softganz%40gmail.com&password=ska.ebaj2010';
	var $auth;
	// var $token;
	// var $skdc;

	function __construct() {
		$this->auth = cfg('khonsongkhla.auth');
	}

	private function saveToken() {
		cfg_db('khonsongkhla.auth', $this->auth);
	}

	function login() {
		$apiUrl = $this->apiUrl . '/v1/auth/login';

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $apiUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => $this->user,
		]);

		$response = curl_exec($curl);

		curl_close($curl);

		$loginResult = json_decode($response);

		// debugMsg($loginResult, '$loginResult');

		if ($loginResult->tokens) {
			$this->auth = $loginResult;
			$this->saveToken();
		}

		return $this->auth;
	}

	function logout() {
		$apiUrl = $this->apiUrl . '/v1/auth/logout';

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $apiUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => 'refreshToken=init',
		]);

		$response = curl_exec($curl);

		curl_close($curl);

		$data = json_decode($response);
		debugMsg($data,'$data');
		$_SESSION['skdc'] = (Object) [
			'id' => $data->user->id,
			'email' => $data->user->email,
			'tokens' => $data->tokens,
		];

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'LOGIN TOKEN',
				'boxHeader' => true,
			]),
			'body' => new Widget([
				'children' => [
					new DebugMsg($data, '$data'),
					new DebugMsg($response),
				]
			]),
		]);
	}

	function refreshToken() {
		$apiUrl = $this->apiUrl . '/v1/auth/refresh-tokens';

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $apiUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => 'refreshToken='.$this->auth->tokens->refresh->token,
			// CURLOPT_POSTFIELDS => 'refreshToken=init',
		]);

		$response = curl_exec($curl);
		$refreshToken = json_decode($response);

		curl_close($curl);

		// debugMsg($this->auth->tokens->refresh->token);
		if ($refreshToken->refresh) {
			$this->auth->tokens = $refreshToken;
			$this->saveToken();
			// debugMsg('REFRESH TOKEN COMPLETE');
		} else {
			$refreshToken = $this->login();
			// debugMsg('REFRESH TOKEN ERROR!!!!! LOGIN AGAIN!!!!');
		}
		// debugMsg($refreshToken, '$refreshToken');
		return $refreshToken;
	}

	function info() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'LOGIN INFO',
				'boxHeader' => true,
			]),
			'body' => new Widget([
				'children' => [
					new DebugMsg('Token :<br />'.$this->token),
					new DebugMsg($this->skdc, '$this->skdc'),
				]
			]),
		]);
	}

	function addPublicService($data) {
		$apiUrl = $this->apiUrl . '/v1/member/'.$data->cid.'/public/service';

		$headers = [
			"Accept: application/json",
			"Authorization: Bearer ".$this->auth->tokens->access->token,
		];

		// require_once 'HTTP/Request2.php';
		// $request = new HTTP_Request2();
		// $request->setUrl($apiUrl);
		// $request->setMethod(HTTP_Request2::METHOD_POST);
		// $request->setConfig(array(
		// 	'follow_redirects' => TRUE
		// ));
		// $request->addPostParameter((Array) $data);
		// try {
		// 	$response = $request->send();
		// 	if ($response->getStatus() == 200) {
		// 		debugMsg($response->getBody());
		// 	} else {
		// 		debugMsg('Unexpected HTTP status: ' . $response->getStatus() . ' ' . $response->getReasonPhrase());
		// 	}
		// 	return $response;
		// }
		// 	catch(HTTP_Request2_Exception $e) {
		// 	debugMsg('Error: ' . $e->getMessage());
		// 	return $e;
		// }

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $apiUrl,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
		  // CURLOPT_POSTFIELDS => 'date=2565-01-27&healthNeed=mind&socialNeed=none&economicNeed=none&refer=none&source=none&serviceUnit=none',
			CURLOPT_POSTFIELDS => sg_implode_attr($data, '&', '{quote: ""}'),
		]);

		$response = curl_exec($curl);
		$serviceResult = json_decode($response);

		curl_close($curl);

		// debugMsg($serviceResult, '$serviceResult');
		return $serviceResult;
	}

	function getPublicServiceList($cid) {
		$apiUrl = $this->apiUrl . '/v1/member/'.$cid.'/public/service';

		$headers = [
			"Accept: application/json",
			"Authorization: Bearer ".$this->auth->tokens->access->token,
		];

		$curl = curl_init($apiUrl);
		curl_setopt($curl, CURLOPT_URL, $apiUrl);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		//for debug only!
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$resp = curl_exec($curl);
		curl_close($curl);
		$data = json_decode($resp);
		return $data;
	}

	function deletePublicService($data) {
		$data = (Object) $data;
		$apiUrl = $this->apiUrl . '/v1/member/'.$data->cid.'/public/service/'.$data->id;

		$headers = [
			"Accept: application/json",
			"Authorization: Bearer ".$this->auth->tokens->access->token,
		];

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $apiUrl,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'DELETE',
		]);

		$response = curl_exec($curl);
		$serviceResult = json_decode($response);

		curl_close($curl);

		// debugMsg($serviceResult, '$serviceResult');
		return $serviceResult;
	}

	function addAidService($data) {
		$apiUrl = $this->apiUrl . '/v1/member/'.$data->cid.'/aid/service';

		$headers = [
			"Accept: application/json",
			"Authorization: Bearer ".$this->auth->tokens->access->token,
		];

		debugMsg($headers, '$headers');
		debugMsg(sg_implode_attr((Array) $data), '&');
		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $apiUrl,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
		  // CURLOPT_POSTFIELDS => 'date=2565-01-27&healthNeed=mind&socialNeed=none&economicNeed=none&refer=none&source=none&serviceUnit=none',
			CURLOPT_POSTFIELDS => sg_implode_attr((Array) $data, '&', '{quote: ""}'),
		]);

		$response = curl_exec($curl);
		$serviceResult = json_decode($response);

		curl_close($curl);

		debugMsg($serviceResult, '$serviceResult');
		return $serviceResult;
	}

	function getAidServiceList($cid) {
		$apiUrl = $this->apiUrl . '/v1/member/'.$cid.'/aid/service';

		$headers = [
			"Accept: application/json",
			"Authorization: Bearer ".$this->auth->tokens->access->token,
		];

		$curl = curl_init($apiUrl);
		curl_setopt($curl, CURLOPT_URL, $apiUrl);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		//for debug only!
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$resp = curl_exec($curl);
		curl_close($curl);
		$data = json_decode($resp);
		return $data;
	}

	function deleteAidService($data) {
		$data = (Object) $data;
		$apiUrl = $this->apiUrl . '/v1/member/'.$data->cid.'/aid/service/'.$data->id;

		$headers = [
			"Accept: application/json",
			"Authorization: Bearer ".$this->auth->tokens->access->token,
		];

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $apiUrl,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'DELETE',
		]);

		$response = curl_exec($curl);
		$serviceResult = json_decode($response);

		curl_close($curl);

		// debugMsg($serviceResult, '$serviceResult');
		return $serviceResult;
	}

	function publicList() {
		$apiUrl = $this->apiUrl . '/v1/member/'.post('id').'/public/service';

		$headers = [
			'Accept: application/json',
			'Authorization: Bearer '.$this->token,
		];

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $apiUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_POSTFIELDS => 'healthNeed=mind',
		]);

		$response = curl_exec($curl);

		curl_close($curl);

		$data = json_decode($response);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Patient Service',
				'leading' => _HEADER_BACK,
				'boxHeader' => true,
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Table([
						'class' => '-center',
						'thead' => ['ID', 'Date', 'healthActivity', 'socialActivity', 'economicActivity'],
						'children' => array_map(
							function($item) {
								return [
									$item->id,
									$item->date,
									$item->healthActivity,
									$item->socialActivity,
									$item->economicActivity,
								];
							},
							$data
						), // children
					]), // Table
					// new DebugMsg($data, '$data'),
					// new DebugMsg($response, '$response'),
				], // children
			]), // Widget
		]);
	}
}
?>