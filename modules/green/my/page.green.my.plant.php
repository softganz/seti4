<?php
/**
* Green :: My Plant Controller
* Created 2020-11-13
* Modify  2020-11-14
*
* @param Object $self
* @param Int $plantId
* @return String
*
* @usage green/my/plant[/$Id][/$action]
*/

$debug = true;

function green_my_plant($self, $plantId = NULL, $action = NULL) {
	if (empty($plantId) && empty($action)) $action = 'home';
	if ($plantId && empty($action)) $action = 'view';

	$argIndex = 3; // Start argument

	//debugMsg('PAGE ID = '.$plantId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
	//debugMsg(func_get_args(), '$args'));

	$ret = R::Page(
		'green.my.plant.'.$action,
		$self,
		$plantId,
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