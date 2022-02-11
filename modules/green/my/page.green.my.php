<?php
/**
* Green : My Controller
* Created 2020-11-16
* Modify  2020-11-16
*
* @param Object $self
* @param String $action
* @param Int $var
* @return String
*
* @usage module/{$Id}/method
*/

$debug = true;

function green_my($self, $action = NULL, $tranId = NULL) {
	$ret = '';

	if (empty($action) && empty($tranId)) $action = 'home';

	$argIndex = 3; // Start argument

	//debugMsg('PAGE GREEN.MY = Action = '.$action.' TranId = '.$tranId.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
	//$ret .= print_o(func_get_args(), '$args');

	$ret = R::Page(
		'green.my.'.$action,
		$self,
		$tranId,
		func_get_arg($argIndex),
		func_get_arg($argIndex+1),
		func_get_arg($argIndex+2),
		func_get_arg($argIndex+3),
		func_get_arg($argIndex+4)
	);

	//debugMsg('TYPE = '.gettype($ret));
	if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

	return $ret;
}
?>