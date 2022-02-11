<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_help($self) {
	$ret.='<h3>ระบบช่วยเหลือ iMed@Home v'.cfg('imed.version').'</h3>';
	$ret.='<ul>
	<li><a class="sg-action" href="'.url('imed/help/patient/add').'" data-rel="#imed-app">การเพิ่มผู้ป่วย</a></li>
	<li><a class="sg-action" href="'.url('imed/help/input/birth').'" data-rel="#imed-app">รูปแบบของวันที่</a></li>
	<li><a class="sg-action" href="'.url('imed/help/input/address').'" data-rel="#imed-app">วิธีการป้อนที่อยู่</a></li>
	</ul>';
	return $ret;
}
?>
