<?php
/**
* SignIn  :: Get PSU Passport User Information
* Created :: 2022-09-30
* Modify  :: 2022-09-30
* Version :: 1
*
* @return Widget
*
* @usage signin/psu/user
*/

class SigninPsuUser extends Page {

	function __construct() {
		parent::__construct([
			'code' => post('code'),
		]);
	}

	function build() {
		$psuPassport = cfg('signin');

		// $params = [];
		// // $params['grant_type'] = $psuPassport->accessToken->type;
		// // $params['client_id'] = $psuPassport->getToken->clientId;
		// // $params['client_secret'] = $psuPassport->getToken->clientSecret;
		// // $params['oauth'] = $psuPassport->accessToken->token;

		// $url = $psuPassport->getToken->accessTokenUrl.'me';//$this->code;
		// // $url = $psuPassport->getToken->accessTokenUrl;
		// // $url = $psuPassport->accessToken->accessTokenUrl;

		// $ch = curl_init();

		// $headers = [
		// 	// "Accept: application/json",
		// 	"Authorization: Bearer ".$psuPassport->accessToken->token,
		// ];

		// curl_setopt( $ch, CURLOPT_URL, $url );
		// curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		// curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		// curl_setopt( $ch, CURLOPT_POSTFIELDS,$params);

		// $apiAuth = $psuPassport->accessToken->accessTokenUrl.'?oauth=authorize';
		// $headers = [
		// 	// "Accept: application/json",
		// 	// "Authorization: Bearer ".$psuPassport->accessToken->token,
		// 	"Authorization: Bearer ".$this->code,
		// 	'client_id' => $psuPassport->getToken->clientId,
		// 	'client_secret' => $psuPassport->getToken->clientSecret,
		// ];

		// $curl = curl_init();

		// curl_setopt_array($curl, array(
		// 	CURLOPT_URL => $apiAuth,
		// 	CURLOPT_RETURNTRANSFER => true,
		// 	CURLOPT_ENCODING => '',
		// 	CURLOPT_MAXREDIRS => 10,
		// 	CURLOPT_TIMEOUT => 0,
		// 	CURLOPT_FOLLOWLOCATION => true,
		// 	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		// 	CURLOPT_CUSTOMREQUEST => 'GET',
		// 	CURLOPT_HTTPHEADER => $headers,
		// ));
		// $authResponse = curl_exec($curl);
		// $authResponseResult = json_decode($authResponse);



		$accessUrl = $psuPassport->accessToken->accessTokenUrl.'?oauth=token';
		$headers = [
			"Authorization: Bearer ".$this->code,
			'client_id' => $psuPassport->getToken->clientId,
			'client_secret' => $psuPassport->getToken->clientSecret,
		];

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $accessUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_HTTPHEADER => $headers,
		));
		$response = curl_exec($curl);
		$responseResult = json_decode($response);

		curl_close($curl);
		debugMsg($response);



		$apiUrl = $psuPassport->accessToken->accessTokenUrl.'?oauth=profile';

		$headers = [
			// "Accept: application/json",
			// "Authorization: Bearer ".$psuPassport->accessToken->token,
			"Authorization: Bearer ".$this->code,
			'client_id' => $psuPassport->getToken->clientId,
			'client_secret' => $psuPassport->getToken->clientSecret,
		];

		$curl = curl_init();

		// curl_setopt_array($curl, [
		// 	CURLOPT_URL => $apiUrl,
		// 	CURLOPT_HTTPHEADER => $headers,
		// 	CURLOPT_RETURNTRANSFER => true,
		// 	CURLOPT_ENCODING => '',
		// 	CURLOPT_MAXREDIRS => 10,
		// 	CURLOPT_TIMEOUT => 0,
		// 	CURLOPT_FOLLOWLOCATION => true,
		// 	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		// 	CURLOPT_CUSTOMREQUEST => 'POST',
		// 	// CURLOPT_POSTFIELDS => sg_implode_attr($data, '&', '{quote: ""}'),
		// ]);


		curl_setopt_array($curl, array(
			CURLOPT_URL => $apiUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_HTTPHEADER => $headers,
		));
		$response = curl_exec($curl);
		$responseResult = json_decode($response);

		curl_close($curl);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Get PSU Passport User Information',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					'<a class="btn" href="https://oauth.psu.ac.th/?oauth=authorize&response_type=code&client_id=oauthpsu1759&redirect_uri=https%3A%2F%2F1t1u.psu.ac.th%2Fsignin%2Fpsu%2Fcomplete">Login with PSU Passport</a>',
					'AUTH URL = '.$apiAuth,
					'AUTH Response = '.$authResponse,

					new DebugMsg('URL = '.$apiUrl),
					new DebugMsg($headers, '$headers'),
					new DebugMsg($response),
					new DebugMsg($responseResult, '$responseResult'),
					new DebugMsg(post(), 'post()'),
					new DebugMsg($params, '$params'),
					new DebugMsg($psuPassport, '$psuPassport'),
					// new DebugMsg($_SERVER, '$_SERVER'),
				], // children
			]), // Widget
		]);
	}

}
?>