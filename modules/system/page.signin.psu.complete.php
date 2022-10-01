<?php
/**
* SignIn  :: PSU Passport Auth Callback
* Created :: 2022-09-29
* Modify  :: 2022-09-30
* Version :: 1
*
* @return Widget
*
* @usage signin/psu/complete
*/

class SigninPsuComplete extends Page {
	var $code;
	var $scope;
	var $token;

	function __construct() {
		parent::__construct([
			'code' => post('code'),
			'scope' => 'openid',
			'token' => (Object) [],
		]);
	}

	function build() {
		$psuPassport = cfg('signin');
		// $jwt = Jwt::isValid($this->code);

		$apacheHeaders = apache_request_headers();
		$_SESSION['state'] = bin2hex(random_bytes(5));
		$_SESSION['psupassport'] = $this->code;

		if ($this->code) {
			$tokenResponse = $this->getToken();
			$me = $this->getMe();
			// SignIn as PSU Passport
			// $user = UserModel::externalSignIn([
			// 	'email' => 'psupassport@psu.ac.th',
			// 	'token' => $this->code,
			// ]);

			$profile = $this->getProfile();
		}

		$authParam = [
			'response_type' => 'code',
			'client_id' => $psuPassport->getToken->clientId,
			'state' => $_SESSION['state'],
			'scope' => $this->scope,
			'redirect_uri' => $psuPassport->getToken->callBackUrl,
		];

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'PSU Passport Auth Complete',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Center([
						'child' => '<a class="btn" href="https://oauth.psu.ac.th/?oauth=authorize&'.http_build_query($authParam).'">Login with PSU Passport</a>',
					]),
					new ListTile(['title' => 'Result']),
					new Column([
						'children' => [
							'CODE = '.$this->code,
							SG\getFirst($this->loginId, 'NO LOGIN-ID'),
							SG\getFirst($this->loginName, 'NO NAME'),
						],
					]),

					$this->code ? $tokenResponse : NULL,
					new DebugMsg($this->token, '$this->token'),
					debugMsg($me, '$me'),

					$this->token ? $this->getProfile() : NULL,

					new DebugMsg($apacheHeaders, '$apacheHeaders'),
					new DebugMsg($headers, '$headers'),
					new DebugMsg(post(), 'post()'),
					// new DebugMsg($this, '$this'),
					new DebugMsg($_SERVER, '$_SERVER'),
				], // children
			]), // Widget
		]);
	}

	function getToken() {
		$psuPassport = cfg('signin');
		// Get Access Token
		// grant_type: client_credentials,authorization_code

		$curl = curl_init();
		$accessUrl = $psuPassport->accessToken->accessTokenUrl.'?oauth=token';
		// $headers = [
		// 	"Authorization: Bearer ".$this->code,
		// 	'client_id' => $psuPassport->getToken->clientId,
		// 	'client_secret' => $psuPassport->getToken->clientSecret,
		// ];

		// $curl = curl_init();
		// curl_setopt_array($curl, array(
		// 	CURLOPT_URL => $accessUrl,
		// 	CURLOPT_RETURNTRANSFER => true,
		// 	CURLOPT_ENCODING => '',
		// 	CURLOPT_MAXREDIRS => 10,
		// 	CURLOPT_TIMEOUT => 0,
		// 	CURLOPT_FOLLOWLOCATION => true,
		// 	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		// 	CURLOPT_CUSTOMREQUEST => 'POST',
		// 	CURLOPT_HTTPHEADER => $headers,
		// ));
		// $queryParam = [
		// 	'code' => $this->code,
		// 	'grant_type' => 'authorization_code',
		// 	'scope' => $this->scope,
		// 	'client_id' => $psuPassport->getToken->clientId,
		// 	'client_secret' => $psuPassport->getToken->clientSecret,
		// 	'redirect_uri' => $psuPassport->getToken->callBackUrl,
		// ];
		// $params = [
		//   CURLOPT_URL =>  $accessUrl.'&'.http_build_query($queryParam),
		//   CURLOPT_RETURNTRANSFER => true,
		//   CURLOPT_MAXREDIRS => 10,
		//   CURLOPT_TIMEOUT => 30,
		//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		//   // CURLOPT_CUSTOMREQUEST => "GET",
		//   CURLOPT_POST => true,
		//   CURLOPT_NOBODY => false,
		//   CURLOPT_POSTFIELDS => $queryParam,
		//   CURLOPT_HTTPHEADER => [
		//     "cache-control: no-cache",
		//     "content-type: application/x-www-form-urlencoded",
		//     "accept: *",
		//     "accept-encoding: gzip, deflate",
		//   ],
		// ];
		// curl_setopt_array($curl, $params);

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://oauth.psu.ac.th?oauth=token',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => array(
				'grant_type' => 'authorization_code',
				'code' => $this->code,
				'client_id' => 'oauthpsu1759',
				'client_secret' => 'edab08fa992167bc97d787c929ece82c',
				'redirect_uri' => 'https%3A%2F%2F1t1u.psu.ac.th%2Fsignin%2Fpsu%2Fcomplete'
			),
		));

		$accessResponse = curl_exec($curl);
		$this->token = json_decode($accessResponse);
		$err = curl_error($curl);

		curl_close($curl);

		return new Container([
			'children' => [
				new ListTile(['title' => 'Access Token']),
				new DebugMsg('Access Token URL = '.$accessUrl),
				new DebugMsg('Access Token Response = '.$accessResponse),
				new DebugMsg('ERROR = '.$err),
				new DebugMsg($params, '$params'),
			],
		]);
	}

	function getToken2() {
		$psuPassport = cfg('signin');
		$client_id = $psuPassport->getToken->clientId;
		$client_secret = $psuPassport->getToken->clientSecret;
		$redirect_uri= $psuPassport->getToken->callBackUrl;
		$authorization_code = $this->code;
		$tokenUrl = $psuPassport->accessToken->accessTokenUrl.'?oauth=token';
		$url = '['.$tokenUrl.']('.$tokenUrl.')';

		$data = array(
			'client_id' => $client_id,
			'client_secret' => $client_secret,
			'redirect_uri' => $redirect_uri,
			'code' => $authorization_code
		);

		$options = array(
			'http' => array(
			    'header'  => "Content-type: application/json\r\n",
			    'method'  => 'POST',
			    'content' => json_encode($data)
			)
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);

		return $result;
	}

	function getMe() {
		// Get User Profile
		$curl = curl_init();
		$headers = [
			'Authorization: Bearer '.$this->token->access_token,
		];

		curl_setopt_array($curl, [
			CURLOPT_URL => 'https://oauth.psu.ac.th?oauth=me',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_HTTPHEADER => $headers,
		]);

		$response = curl_exec($curl);
		$profile = json_decode($response);

		$this->loginId = SG\getFirst($profile->{"login-id"}, 'NO LOGIN-ID');
		$this->loginName = SG\getFirst($profile->{"first-name-th"}, 'NO NAME');

		curl_close($curl);
		return new Container([
			'children' => [
				new ListTile(['title' => 'Profile Token']),
				new DebugMsg($response),
				new DebugMsg($profile, '$profile'),
			],
		]);
	}

	function getProfile() {
		// Get User Profile
		$curl = curl_init();
		$headers = [
			'Authorization: Bearer '.$this->token->access_token,
		];

		curl_setopt_array($curl, [
			CURLOPT_URL => 'https://oauth.psu.ac.th?oauth=profile',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_HTTPHEADER => $headers,
		]);

		$response = curl_exec($curl);
		$profile = json_decode($response);

		$this->loginId = SG\getFirst($profile->{"login-id"}, 'NO LOGIN-ID');
		$this->loginName = SG\getFirst($profile->{"first-name-th"}, 'NO NAME');

		curl_close($curl);
		return new Container([
			'children' => [
				new ListTile(['title' => 'Profile Token']),
				new DebugMsg($response),
				new DebugMsg($profile, '$profile'),
			],
		]);
	}
}
?>