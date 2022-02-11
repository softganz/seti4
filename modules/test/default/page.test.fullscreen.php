<?php
/**
* Test : Full Screen
* Created 2020-09-18
* Modify  2020-09-18
*
* @param Object $self
* @return String
*
* @usage test/fullscreen
*/

$debug = true;

function test_fullscreen($self) {
	cfg('web.fullpage', true);

	$ret = '<header class="header"><h3>FULL SCREEN</h3></header>';

	for ($i = 1; $i <= 100; $i++) {
		$ret .= 'Line '.$i.'<br />';
	}

	$ret .= '<style type="text/css">
	.page.-main {position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: green; border: 2px #f60 solid;}
	.header {}
	header.header>h3 {background-color: #0000ab; color: #fff;}
	</style>';
	return $ret;
}
?>