<?php
/**
* Google Recapcha Version 3 testing
*
* @param Object $self
* @return String
*/

$debug = true;

function test_recaptcha_v3($self) {
	$ret = '<h2>Google ReCapcha V3</h2>';

	$secretKey = '6LduEHgUAAAAAGWYqKYSXOlqhnAklGOwcTm0nU9F';
	$siteKey = '6LduEHgUAAAAAJXl62Yttf-BzFV_-BvVInkDIsQd';

	head('<script src="https://www.google.com/recaptcha/api.js?render='.$siteKey.'"></script>');

	$form = new Form(NULL,url('test/recaptcha/v3'));
	$form->addField('text',array('type'=>'text','value'=>'test text'));
	$form->addField('button',array('type'=>'button','value'=>'SUBMIT'));
	$ret .= $form->build();


	$token = $_POST['token'];
	$action = $_POST['action'];
	if (isset($token) && !empty($token)) {
		//get verify response data
		//$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secretKey.'&response='.$token);
		//$responseData = json_decode($verifyResponse);

		$apiResult = getapi('https://www.google.com/recaptcha/api/siteverify?secret='.$secretKey.'&response='.$token);
		$verifyResponse = sg_json_encode($apiResult['result']);
		$responseData = $apiResult['result'];

		if($responseData->success) {
			//Captcha is good
			$ret .= '<b>CAPCHA IS GOOD!!!!</b><br />';
		} else {
			$ret .= '<b>CAPCHA IS BAD!!!!</b><br />';
		}

		$ret .= 'Token = '.$token.'<br />';
		$ret .= 'Response = '.$verifyResponse.'<br />';
		$ret .= print_o($responseData,'$responseData');
		//$ret .= print_o($apiResult,'$apiResult');

	}


	//$ret .= print_o(post(),'post()');

	$ret .= '<script>
	console.log("Test recapcha")
	// when form is submit
	$("form").submit(function() {
		console.log("Form submit")
		// we stoped it
		event.preventDefault();
		// needs for recaptacha ready
		grecaptcha.ready(function() {
			grecaptcha.execute("'.$siteKey.'", {action: "register"})
			.then(function(token) {
				console.log("ReCahcha done")
				console.log(token)
				// Verify the token on the server.
				// add token to form
				$("form").prepend(\'<input type="hidden" name="token" value="\' + token + \'">\');
				$("form").prepend(\'<input type="hidden" name="action" value="register">\');
				// submit form now
				$("form").unbind("submit").submit();
			});
		});
	})
	</script>';
	return $ret;
}
?>