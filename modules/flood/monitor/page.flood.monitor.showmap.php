<?php
function flood_monitor_showmap($self) {
	$ret.='<h3>แผนที่แสดงจุดติดตั้งสถานีและสถานะของสถานี</h3><ul class="flood-monitor-center-expand"><li><a href="'.url('flood/monitor/center',array('show'=>'map')).'" target="_blank"><i class="icon -material">search</i></a></li></ul>'._NL;
	$ret.=R::Page('flood.monitor.water',NULL);
	//'<div xdata-load="'.url('flood/monitor/water').'" style="height:100%"></div>';
	return $ret;
}
?>