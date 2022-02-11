<?php
/**
* Garage :  Requisition Controller
* Created 2018-10-31
* Modify  2020-10-21
*
* @param Object $self
* @param Int $rcvId
* @param String $action
* @return String
*
* @usage garage/req/{$reqId}/{$action}
*/

$debug = true;

function garage_req($self, $reqId = NULL, $action = NULL) {
	new Toolbar($self,'ใบเบิกของ','part');

	$reqInfo = R::Model('garage.req.get', $reqId);

	if (empty($action) && empty($reqId)) $action = 'home';
	if (empty($action) && $reqId) $action = 'view';

	$argIndex = 3; // Start argument

	$ret = R::Page(
		'garage.req.'.$action,
		$self,
		$reqInfo,
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