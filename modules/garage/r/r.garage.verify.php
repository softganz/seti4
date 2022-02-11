<?php
/**
* Module : Module Method
* Created 2020-08-01
* Modify  2020-08-01
*
* @param Object $self
* @param Int $var
* @return String/Boolean/Location
*
* @usage garage/verify
*/

$debug = true;

function r_garage_verify($self, $shopInfo = NULL, $right = NULL) {
	if (!i()->ok) {
		cfg('navigator.garage', cfg('navigator.garage.verify'));
		return R::View('signform');
	} else if ($shopInfo && $right) {
		$checkRight = R::Model('garage.right',$shopInfo, $right);
		if (!$checkRight) {
			location('garage/app');
			return false;
		} else {
			return true;
		}
	}
	return true;
}
?>