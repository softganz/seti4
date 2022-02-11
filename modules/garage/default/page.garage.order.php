<?php
/**
* Garage : Order Controller
* Created 2017-10-18
* Modify  2020-10-21
*
* @param Object $self
* @param Int $orderId
* @param String $action
* @return String
*
* @usage garage/order/{$orderId}/{$action}
*/

$debug = true;

function garage_order($self, $orderId = NULL, $action = NULL) {
	new Toolbar($self,'ใบสั่งของ','part');

	$orderInfo = R::Model('garage.order.get', $orderId);

	if (empty($action) && empty($orderId)) $action = 'home';
	if (empty($action) && $orderId) $action = 'view';

	$argIndex = 3; // Start argument

	$ret = R::Page(
		'garage.order.'.$action,
		$self,
		$orderInfo,
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