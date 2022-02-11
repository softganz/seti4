<?php
/**
* Post Event in Public Monitor
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function publicmon_post($self) {
	R::View('publicmon.toolbar',$self,'แจ้งเหตุ');

	$ret .= '<div id="public-home-send" class="ui-card public-home-send"><div class="ui-item"><img src="'.model::user_photo(i()->username).'" width="32" height="32" />'.i()->name;
	$ret .= '<textarea class="form-textarea -fill" rows="5" placeholder="รายละเอียดแจ้งเหตุ" /></textarea>';

	$ret .= 'ประเภท<br />';
	$ret .= 'ความเร่งด่วน<br />';
	$ret .= 'ภาพถ่าย<br />';
	$ret .= 'พิกัด<br />';
	$ret .= 'วันที่<br />';
	$ret .= 'ผู้แจ้ง<br />';
	$ret .= '</div>';
	$ret .= '</div>';

	//$ret .= phpinfo();

	return $ret;
}
?>