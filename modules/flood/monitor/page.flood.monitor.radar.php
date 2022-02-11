<?php
/**
* Flood Monitor : radar
*
* @param Object $self
* @return String
*/
function flood_monitor_radar($self) {
	$self->theme->title.=' : เรดาห์';
	$ret.='<iframe name="stp-loop" src="'.cfg('flood.monitor.radar.gif').'" height="550px" width="100%" align="middle" scrolling="no" frameborder="0"></iframe>';
	return $ret;
}
?>