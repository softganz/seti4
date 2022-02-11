<?php
/**
* Garage : ApRcv Controller
* Created 2020-10-20
* Modify  2020-10-20
*
* @param Object $self
* @param Int $rcvId
* @param String $action
* @return String
*
* @usage garage/aprcv/{$Id}/{$action}
*/

$debug = true;

function garage_aprcv($self, $rcvId = NULL, $action = NULL) {
	new Toolbar($self,'ใบรับของ','part');

	$rcvInfo = R::Model('garage.aprcv.get', $rcvId);

	if (empty($action) && empty($rcvId)) $action = 'home';
	if (empty($action) && $rcvId) $action = 'view';

	$argIndex = 3; // Start argument

	$ret = R::Page(
		'garage.aprcv.'.$action,
		$self,
		$rcvInfo,
		func_get_arg($argIndex),
		func_get_arg($argIndex+1),
		func_get_arg($argIndex+2),
		func_get_arg($argIndex+3),
		func_get_arg($argIndex+4)
	);

	if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';
	
	return $ret;
}


?>