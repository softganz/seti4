<?php
/**
* flood_monitor_warehouse
*
* @param Object $self
* @return String
*/
function flood_monitor_warehouse($self) {
	$ret.='<div class="flood__realtime">';
	$ret.='<h2>คลังข้อมูล</h2>';
	$ret.='<form><label>ชนิดข้อมูล</label><select class="form-select"><option>เลือกชนิดข้อมูล</option></select><label>ช่วงปีพุทธศักราช</label><select class="form-select"><option>2558</option><option>2557</option></select><button>ดู</button></form>';
	$ret.='<div class="flood__box flood__box--data">ตารางข้อมูล</div>';
	$ret.='</div><!--flood__main-->';
	return $ret;
}
?>