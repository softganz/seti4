<?php
/**
* iBuy : Green App Account
* Created 2020-09-18
* Modify  2020-09-18
*
* @param Object $self
* @return String
*
* @usage green/app/account
*/

$debug = true;

function green_app_my($self) {
	if (!i()->ok) return R::View('signform', '{showTime: false, time: -1}');

	$ret = R::Page('green.my', NULL);

	return $ret;
}
?>