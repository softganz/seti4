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

function r_green_my_verify($self, $shopId = NULL) {
	if (!i()->ok) {
		return R::View('signform', '{showTime: false, time: -1}');
	} else {
		$shopSelected = R::Page('green.shop.select', $shopId);
		return $shopSelected;
	}
}
?>