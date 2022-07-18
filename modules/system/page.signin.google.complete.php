<?php
/**
* Module :: Description
* Created 2022-07-16
* Modify  2022-07-16
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

class SigninGoogleComplete extends Page {
	function build() {
		$credential = $_POST['credential'];

		$jwt = Jwt::isValid($credential);

		// Check value different between email
		// $jwt->payload->nbf (running)
		// $jwt->payload->jti
		// $jwt->payload->sub (same on email, diff on other email)
		// $jwt->signature
		// $jwt->signatureProvided

		// SignIn complete
		if ($jwt->payload->email) {
			$user = UserModel::externalSignIn([
				'email' => $jwt->payload->email,
				'token' => $jwt->payload->jti
			]);
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Sign In With Google Complate',
			]), // AppBar
			'body' => new Column([
				'children' => [
					'SIGNIN '.($jwt->payload->email ? 'YES' : 'NO'),
					'name = '.$jwt->payload->name,
					'given_name = '.$jwt->payload->given_name,
					'email = '.$jwt->payload->email,
					'<img src="'.$jwt->payload->picture.'" />',
					'iat = '.date('Y-m-d H:i:s', $jwt->payload->iat),
					'exp = '.date('Y-m-d H:i:s', $jwt->payload->exp),

					'<h2>JWT</h2>',

					print_o($user, '$user'),
					print_o($jwt, '$jwt'),
					// $ret .= nl2br("\n");

					// $ret .= '<h2>$_COOKIE</h2>';
					// $ret .= 'g_csrf_token = '.$_COOKIE['g_csrf_token'].'<br />';
					// $ret .= '<pre>'.print_r($_COOKIE, 1).'</pre>';

					// $ret .= '<h2>$_POST</h2>';
					// foreach ($_POST as $key => $value) {
					//  	$ret .= $key.' = '.$value.'<br />';
					//  }

					// $ret .= '<h2>$_SERVER</h2>';
					// foreach ($_SERVER as $key => $value) {
					//  	$ret .= $key.' = '.$value.'<br />';
					//  }

				], // children
			]), // Widget
		]);
	}
}
?>