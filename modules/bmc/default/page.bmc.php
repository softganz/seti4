<?php
/**
* Green :: BMC Controller
* Created 2020-12-07
* Modify  2020-12-07
*
* @param Object $self
* @param Int $bmcId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage bmc/{id}/method/{tranId}
*/

$debug = true;

function bmc($self, $bmcId = NULL, $action = NULL, $tranId = NULL) {
	$ret = '';

	$isAdmin = is_admin('green');
	//$isOfficer = $isAdmin || user_access('access ibuys customer');
	
	//if (!$isOfficer) return message('error', 'Access Denied');

	//if (!is_numeric($bmcId)) {$action = $bmcId; unset($bmcId);} // Action as mainId and clear

	if (empty($action) && empty($bmcId)) $action = 'home';
	else if (empty($action) && $bmcId) $action = 'view';

	$Info = $bmcId ? R::Model('bmc.get', $bmcId) : NULL;

	$argIndex = 3; // Start argument

	//debugMsg('PAGE CONTROLLER Id = '.$bmcId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
	//debugMsg(func_get_args(), '$args');

	$ret = R::Page(
		'bmc.'.$action,
		$self,
		$Info,
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