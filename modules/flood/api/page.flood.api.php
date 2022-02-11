<?php
function flood_api($self) {
	//$self->theme->title='Flood API';
	$ret='<h2>Flood API</h2>';
	$ret.='<ul>';
	$ret.='<li>รายชื่อกล้องในลุ่มน้ำ => http://www.hatyaicityclimate.org/flood/api/camlist => <a href="http://www.hatyaicityclimate.org/flood/api/camlist" target="_blank">example</a></li>';
	$ret.='<li>ภาพจากกล้องในลุ่มน้ำ => http://www.hatyaicityclimate.org/flood/api/camera/[camId] => <a href="http://www.hatyaicityclimate.org/flood/api/camera/1" target="_blank">example</a></li>';
	$ret.='</ul>';
	return $ret;
}
?>