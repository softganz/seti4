<?php
/**
* Module Method
* Created 2019-09-05
* Modify  2019-09-05
*
* @param Object $self
* @return String
*/

$debug = true;

function signin($self, $options = '{}') {
	if (post('u')) {
		$options = new stdClass();
		$options->username = post('u');
		$options->password = post('pw');
		if(post('rel')) $options->rel = post('rel');
		if(post('ret')) $options->ret = post('ret');
		$ret .= R::View('signform', $options);
	} else if (i()->ok) {
		location('my');
	} else {
		$options = SG\json_decode($options);

		if(post('time')) $options->time = post('time');
		if(post('ret')) $options->ret = post('ret');
		if(post('rel')) $options->rel = post('rel');
		if(post('signret')) $options->signret = post('signret');
		if(post('showTime')) $options->showTime = post('showTime');
		if(post('showGuide') === '0') $options->showGuide = false;
		if(post('showInfo') === '0') $options->showInfo = false;
		if(post('showRegist') === '0') $options->showRegist = false;

		$ret .= '<header class="header -box -hidden"><h3>@Secure Sign In</h3></header>';
		$ret .= R::View('signform', $options);
	}

	return $ret;
}
?>