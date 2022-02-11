<?php
/**
* Show main menu
*
* @return String
*/
function map_menu() {
	$mapGroup=SG\getFirst($_REQUEST['gr'],$_REQUEST['mapgroup']);
	$ret='<nav class="nav iconset -sg-text-right"><a href="javascript:void(0)" data-action="box-close" title="ปิดหน้าต่าง"><i class="icon -close"></i></a></nav>';
	$ui=new ui();
	//		$ui->add('<a class="sg-action" href="'.url('map/layer',array('gr'=>$mapGroup)).'" data-rel="#map-box">กลุ่มแผนที่</a>');
	$ui->add('<a href="#" data-action="clear-map">ล้างแผนที่</a>');
	$ui->add('<a class="sg-action" href="'.url('map/layer',array('gr'=>$mapGroup)).'" data-rel="#map-box">เลือกชั้นของแผนที่</a>');
	$ui->add('<a class="sg-action" href="'.url('map/area',array('gr'=>$mapGroup)).'" data-rel="#map-box">เลือกพื้นที่</a>');
	$ui->add('<a class="sg-action" href="'.url('map/list',array('gr'=>$mapGroup)).'" data-rel="#map-box">รายชื่อทั้งหมด</a>');
	$ui->add('<a class="sg-action" href="'.url('map/list',array('gr'=>$mapGroup,'o'=>'new')).'" data-rel="#map-box">รายชื่อมาใหม่</a>');
	$ret.='<nav><h3>เมนูหลัก</h3>'.$ui->build('ul').'</nav>';
	$ui=new ui();
	$ui->add('<a href="'.url('map/report/daily').'" target="_blank">จำนวนการปักหมุดในแต่ละวัน</a>');
	$ui->add('<a href="'.url('map/report/growth').'" target="_blank">จำนวนการเพิ่มขึ้นของหมุดในแต่ละวัน</a>');

	$ret.='<nav><h3>รายงาน</h3>'.$ui->build('ul').'</nav>';

	return $ret;
}
?>