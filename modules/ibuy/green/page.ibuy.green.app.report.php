<?php
/**
* iBuy : Green App Shoping
* Created 2020-09-20
* Modify  2020-09-20
*
* @param Object $self
* @return String
*
* @usage ibuy/green/app/shoping
*/

$debug = true;

function ibuy_green_app_report($self) {

	$isAdmin = is_admin('ibuy');

	$ret = '<header class="header"><h3>Report</h3></header>';

	return $ret;
}
?>