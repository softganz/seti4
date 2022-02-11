<?php
/**
* iMed : App My Account
* Created 2020-09-23
* Modify  2020-09-23
*
* @param Object $self
* @return String
*
* @usage green/app/account
*/

$debug = true;

function imed_app_my($self) {
	if (!i()->ok) return R::View('signform', '{time:-1, showTime: false}');

	if ($_SESSION['imedapp'] === 'psyc') return R::Page('imed.psyc.my');

	$ret = R::Page('imed.my', NULL);

	return $ret;
}
?>