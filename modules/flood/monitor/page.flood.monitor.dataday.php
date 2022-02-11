<?php
/**
* flood_monitor_dataday
*
* @param Object $self
* @return String
*/
function flood_monitor_dataday($self) {
	$ret.='<div class="flood__realtime">';
	$ret.='<h2>ตารางข้อมูล</h2>';
	$ret.='<form><label>ชนิดข้อมูล</label><select class="form-select"><option>เลือกชนิดข้อมูล</option></select><label>เลือกแหล่งข้อมูล</label><select class="form-select"><option>เลือกแหล่งข้อมูล</option></select><label>ช่วงวันที่</label><input type="text" class="sg-datepicker" size="5" /> - <input type="text" class="sg-datepicker" size="5" /> <button>ดู</button></form>';
	$ret.='<div class="flood__box flood__box--graph">กราฟ</div>';
	$ret.='<div class="flood__box flood__box--data">ตารางข้อมูล</div>';
	$ret.='</div><!--flood__main-->';
	return $ret;
}
?>