<?php
/**
* Module : Module Method
* Created 2020-08-01
* Modify  2020-08-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage garage/appaid/{$paidId}
*/

$debug = true;

function garage_appaid($self, $paidId = NULL) {
	$shopInfo = R::Model('garage.get.shop');

	R::Model('garage.verify', $self, $shopInfo, 'FINANCE');

	new Toolbar($self,'จ่ายชำระหนี้','finance');

	$paidInfo = R::Model('garage.appaid.get', $paidId);

	if (empty($action) && empty($paidId)) $action = 'home';
	if (empty($action) && $paidId) $action = 'view';

	$argIndex = 3; // Start argument

	$ret = R::Page(
		'garage.appaid.'.$action,
		$self,
		$paidInfo,
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