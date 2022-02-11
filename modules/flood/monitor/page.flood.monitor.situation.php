<?php
/**
* flood_monitor_situation
*
* @param Object $self
* @return String
*/
function flood_monitor_situation($self) {
	$basin=post('basin');

	R::View('flood.monitor.toolbar',$self);

	$ret.='<ul class="flood--situation--list">';
	//$ret.='<li><a href="'.url('flood/monitor/hydrograph').'">ระบบคาดการณ์ล่วงหน้า (Unit Hydrograph)</a></li>';
	$ret.='<li><a class="button" href="'.url('flood/monitor/rainavg',array('basin'=>$basin)).'">ข้อมูลปริมาณน้ำฝนเฉลี่ย</a></li>';
	$ret.='<li><a class="button" href="'.url('flood/monitor/protocol',array('basin'=>$basin)).'">ขั้นตอนการประเมินสถานการณ์และการเตือนภัยน้ำท่วม (Protocol)</a></li>';
	$ret.='<li><a class="button" href="'.url('flood/monitor/center').'" target="_blank">Displays for Command Center</a></li>';
	$ret.='</ul>';
	return $ret;
}
?>