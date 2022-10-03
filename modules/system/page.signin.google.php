<?php
/**
* SignIn 	:: Description
* Created :: 2022-07-16
* Modify  :: 2022-07-16
* Version	:: 1
*
* @return Widget
*
* @usage signin/google
*/

class SigninGoogle extends Page {

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Sign In With Google',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					'<script src="https://accounts.google.com/gsi/client" async defer></script>
					<div id="g_id_onload"
						data-client_id="530187295990-lb8kuro5entopcdvrqa9g6mlcjrohmm3.apps.googleusercontent.com"
						data-login_uri="'._DOMAIN.url('signin/google/complete').'"
						data-auto_prompt="false">
					</div>
					<div class="g_id_signin"
						data-type="standard"
						data-size="large"
						data-theme="outline"
						data-text="sign_in_with"
						data-shape="rectangular"
						data-logo_alignment="left">
					</div>'
				], // children
			]), // Widget
		]);
	}
}
?>