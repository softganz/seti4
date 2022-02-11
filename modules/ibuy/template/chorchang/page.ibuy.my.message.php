<?php
/**
* Module Method
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_my_message($self) {
	$ret = '';
	$ret .= '<header class="header"><h3>ข้อความ</h3></header>';

	$ret .= message('status', 'ไม่มีข้อความ');
	return $ret;
}
?>