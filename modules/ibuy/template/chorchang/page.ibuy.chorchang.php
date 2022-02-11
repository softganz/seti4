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

function ibuy_chorchang($self) {
	$ret = '';
	$ret .= '<header class="header"><h3>Welcome to ช.ช้าง หาดใหญ่</h3></header>';


	$ret .= '<div class="sg-load" data-url="'.url('ibuy').'"><div style="padding: 64px;"><span class="loader -rotate -center" style="margin: 64px auto;"></span><div></div>';
	//$ret .= R::Page('ibuy', $self);
	return $ret;
}
?>