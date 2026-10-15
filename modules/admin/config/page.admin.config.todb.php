<?php
function admin_config_todb($self,$key) {
	$self->theme->title='Variable to DB';
	if ($key) cfg_db($key,cfg($key));
	$ret .= message('status','บันทึกค่าของ <em>'.$key.'</em> ไว้ในฐานข้อมูลเรียบร้อย');
	return $ret;
}
?>