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
	var $debug;
	var $token;
	var $me;
	var $psuPassport;

	function __construct() {
		parent::__construct([
			'code' => post('code'),
			'scope' => 'openid',
			'token' => (Object) [],
			'me' => (Object) [],
			'psuPassport' => cfg('signin')->psu,
			'debug' => false,
		]);
	}

	function build() {
		if (empty($this->code)) {
			return new ErrorMessage([
				'responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE,
				'text' => 'ข้อมูลที่ได้รับจาก PSU Passport ไม่ครบถ้วน',
			]);
		}

		$_SESSION['state'] = bin2hex(random_bytes(5));
		$_SESSION['psupassport'] = $this->code;

		if ($this->code) {
			$this->token = $this->getToken();

			if ($this->token->access_token) {
				$this->me = $this->getMe();
				// $profile = $this->getProfile();
			}

			// Dummy Token
			// $this->token = (Object) [
			// 	'access_token' => '43546f6f2ce39b004a19d433908aa7f08740a14f',
			// 	'expires_in' => 3600,
			// 	'token_type' => 'Bearer',
			// 	'scope' => 'openid',
			// 	'refresh_token' => '69e8bd4983e197d08b85a29958f2811e62a91d77',
			// ];
			// $this->me = (Object) [
			// 	'user_login' => 'panumas.n',
			// 	'user_email' => 'softganz@gmail.com',
			// 	'description' => 'ภาณุมาศ นนทพันธ์',
			// 	'displayname' => 'PANUMAS NONTAPAN',
			// ];

			if ($this->me->user_email) {
				$userInfo = UserModel::get(['email' => $this->me->user_email]);
				// print_o($userInfo, '$userInfo', 1);
				if ($userInfo->uid) {
					// Already member
					// SignIn as PSU Passport
					$user = UserModel::externalSignIn([
						'email' => $this->me->user_email,
						'token' => $this->token->access_token,
					]);
				} else {
					// Not a member
					// Create new user
					$createUserResult = UserModel::externalUserCreate([
						'prefix' => 'psu-',
						'name' => $this->me->description,
						'email' => $this->me->user_email,
						'signin' => true,
						'token' => $this->token->access_token,
					]);
					// print_o($createUserResult, '$createUserResult',1);
				}
				// debugMsg($userInfo, '$userInfo');
			}
		}

		if (!$this->debug && ($psupassportRetuen = $_SESSION['psupassportRetuen'])) {
			unset($_SESSION['psupassportRetuen']);
			location($psupassportRetuen);
			return;
		}

		$authParam = [
			'response_type' => 'code',
			'client_id' => $this->psuPassport->clientId,
			'state' => $_SESSION['state'],
			'scope' => $this->scope,
			'redirect_uri' => $this->psuPassport->urlCallBack,
		];

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'PSU Passport Auth Complete',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Center([
						'child' => '<a class="btn" href="'.$this->psuPassport->urlAuth.'&'.http_build_query($authParam).'">Login with PSU Passport</a>',
					]),
					new ListTile(['title' => 'Result']),
					new Column([
						'children' => [
							'CODE = '.$this->code,
						],
					]),

					$this->token->access_token ? new Column([
						'children' => [
								$this->me->user_login,
								$this->me->user_email,
								$this->me->description,
								$this->me->displayname,
								new DebugMsg($this->token, '$this->token'),
								new DebugMsg($this->me, '$this->me'),
							// $profile,
						], //children
					]) : NULL,

					$this->token->error ? new Container([
						'child' => 'TOKEN ERROR : '.$this->token->error.' : '.$this->token->error_description
					]) : NULL,

					// new DebugMsg(post(), 'post()'),
					// new DebugMsg($this->psuPassport, '$this->psuPassport'),
				], // children
			]), // Widget
		]);
	}

	function getToken() {
		// Get Access Token
		// grant_type: client_credentials,authorization_code

		$curl = curl_init();

		curl_setopt_array($curl,
			[
				CURLOPT_URL => $this->psuPassport->urlAccessToken,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => [
					'grant_type' => 'authorization_code',
					'code' => $this->code,
					'client_id' => $this->psuPassport->clientId,
					'client_secret' => $this->psuPassport->clientSecret,
					'redirect_uri' => $this->psuPassport->urlCallBack
				],
			]
		);

		$accessResponse = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		return json_decode($accessResponse);
	}

	function getMe() {
		// Get User Profile
		$curl = curl_init();
		$headers = [
			'Authorization: Bearer '.$this->token->access_token,
		];

		curl_setopt_array($curl, [
			CURLOPT_URL => $this->psuPassport->urlMe,
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

		curl_close($curl);
		return $profile;
	}

	function getProfile() {
		// Get User Profile
		$curl = curl_init();
		$headers = [
			'Authorization: Bearer '.$this->token->access_token,
		];

		curl_setopt_array($curl, [
			CURLOPT_URL => $this->psuPassport->urlProfile,
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

		curl_close($curl);
		return $profile;
	}
}
?>