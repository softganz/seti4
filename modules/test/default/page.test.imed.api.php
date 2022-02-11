<?php
/**
* Module :: Description
* Created 2021-01-11
* Modify  2021-01-11
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

class TestImedApi extends Page {
	var $apiUrl;
	var $apiRequest;
	var $token;

	function __construct() {
		$this->apiUrl = 'https://khonsongkhla.com';
		$this->apiRequest = post('api');
		// $this->token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjIsImlhdCI6MTY0MjA0NDI1MywiZXhwIjoxNjQyMDYyMjUzLCJ0eXBlIjoiYWNjZXNzIn0.qhEngGu2qSMEZCqBo6H5KK4jBqqtCklT8LkRfubY0kc';
		// $this->token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjIsImlhdCI6MTY0MjA0NDI1MywiZXhwIjoxNjQ0NjM2MjUzLCJ0eXBlIjoicmVmcmVzaCJ9.1q1fIYtlWUOkQ34p7BNslT8l8GV7ADaSqmNUwRJKVgk';
		// debugMsg($_SESSION['skdc'],'$skdc');
		$this->skdc = $_SESSION['skdc'];
		$this->token = $this->skdc->tokens->access->token;
		// debugMsg($this,'$this');
	}

	function build() {
		if ($this->apiRequest) {
			return $this->{$this->apiRequest}();
		}

		$apiUrl = $this->apiUrl . '/v1/member';
		$api = '/v1/users/roles';
		$api = '/v1/member';
		// $api = '/v1/member/3909800643679/aid/service';

		$headers = [
			"Accept: application/json",
			"Authorization: Bearer ".$this->token,
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

		if ($data->code) return new ErrorMessage([
			'code' => $data->code,
			'text' => $data->message.'<a class="sg-action btn" href="'.url('test/imed/api',['api' => 'login']).'" data-rel="box" data-width="full">LOGIN</a>'
		]);

		debugMsg($data, '$data');
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Test :: iMed API User Role',
				'trailing' => new Row([
					'children' => [
						'<a class="sg-action btn" href="'.url('test/imed/api',['api' => 'login']).'" data-rel="box" data-width="full">LOGIN</a>',
						'<a class="sg-action btn" href="'.url('test/imed/api',['api' => 'logout']).'" data-rel="box" data-width="full">LOGOUT</a>',

						'<a class="sg-action btn" href="'.url('test/imed/api',['api' => 'refresh']).'" data-rel="box" data-width="full">Refresh</a>',
						'<a class="sg-action btn" href="'.url('test/imed/api',['api' => 'info']).'" data-rel="box" data-width="full">Info</a>',
					],
				]),
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Table([
						'children' => array_map(
							function($item) {
								return [
									$item->title.$item->firstname.' '.$item->lastname,
									'<a class="sg-action" href="'.url('test/imed/api', ['api' => 'serviceList', 'id' => $item->personalCode]).'" data-rel="box" data-width="full">Aid</a>',
									'<a class="sg-action" href="'.url('test/imed/api', ['api' => 'publicList', 'id' => $item->personalCode]).'" data-rel="box" data-width="full">Public</a>',
								];
							},
							SG\getFirst($data, [])
						), // children
					]), // Table
					// new DebugMsg($data, '$data'),
					// new DebugMsg($resp, '$resp'),
				], // children
			]), // Widget
		]);
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
			// CURLOPT_POSTFIELDS => 'email=ipanumas%40gmail.com&password=idc.skpo',
			// CURLOPT_POSTFIELDS => 'email=w.chawan%40gmail.com&password=1234567A',
			CURLOPT_POSTFIELDS => 'email=softganz%40gmail.com&password=ska.ebaj2010z',
		]);

		$response = curl_exec($curl);

		curl_close($curl);
		debugMsg($curl, '$curl');

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

	function logout() {
		$apiUrl = $this->apiUrl . '/v1/auth/logout';

		$curl = curl_init();

		// $postData = ['email' => ''];
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

	function refresh() {
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
			CURLOPT_POSTFIELDS => 'refreshToken='.$this->token,
		]);

		$response = curl_exec($curl);
		$data = json_decode($response);

		curl_close($curl);

		$_SESSION['skdc'] = (Object) [
			'id' => $this->skdc->id,
			'email' => $this->skdc->email,
			'tokens' => $data,
		];

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'REFRESH TOKEN',
				'boxHeader' => true,
			]),
			'body' => new Widget([
				'children' => [
					new DebugMsg($data, '$data'),
					new DebugMsg($response),
					new DebugMsg($this, '$this'),
				]
			]),
		]);
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
	function serviceList() {
		$apiUrl = $this->apiUrl . '/v1/member/'.post('id').'/aid/service';

		$headers = [
			"Accept: application/json",
			"Authorization: Bearer ".$this->token,
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

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Patient Service',
				'leading' => _HEADER_BACK,
				'boxHeader' => true,
			]), // AppBar
			'body' => new Widget([
				'children' => [
					$data->code ? $data->code.' : '.$data->message :
					new Table([
						'class' => '-center',
						'thead' => ['ID', 'Date', 'Health Need', 'Social Need', 'Economic Need'],
						'children' => array_map(
							function($item) {
								return [
									$item->id,
									$item->date,
									$item->healthNeed,
									$item->socialNeed,
									$item->economicNeed,
								];
							},
							$data
						), // children
					]), // Table
					// new DebugMsg($data, '$data'),
					// new DebugMsg($resp, '$resp'),
					// new DebugMsg($this->skdc, '$this->skdc'),
				], // children
			]), // Widget
		]);
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