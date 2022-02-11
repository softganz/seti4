<?php
function map_report($self) {
	$ret='<nav class="nav iconset -sg-text-right"><a href="javascript:void(0)" data-action="box-close" title="ปิดหน้าต่าง"><i class="icon -close"></i></a></nav>';
	$ret.='<h2>รายงาน</h2>';
	$ret.='<ul><li><a href="'.url('map/report/daily').'">จำนวนการปักหมุดในแต่ละวัน</a></li>
<li><a href="'.url('map/report/growth').'">จำนวนการเพิ่มขึ้นของหมุดในแต่ละวัน</a></li>
</ul>
';

	return $ret;
}
?>