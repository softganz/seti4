<?php
/**
* iBuy : Green App Shoping
* Created 2020-09-20
* Modify  2020-09-20
*
* @param Object $self
* @return String
*
* @usage green/app/shoping
*/

$debug = true;

function green_app_category($self) {

	$isAdmin = is_admin('ibuy');

	$ret = '<header class="header"><h3>Category</h3></header>';

	$ret .= R::Page('green.category.home', NULL);
	return $ret;
}
?>