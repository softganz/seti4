<?php
/**
* Show module toolbar menu
*
* @param NULL
* @return String
*/
function view_flood_monitor_toolbar($self) {
	$basin=SG\getFirst(post('basin'),'UPT');

	$report = q(2);
	$self->theme->navbar='<div class="xsg-tabs"><ul class="tabs"><li class="'.($basin=='UPT'?'active':'').'"><a href="'.url('flood/monitor/'.$report,array('basin'=>'UPT')).'">ลุ่มน้ำคลองอู่ตะเภา</a></li><li class="'.($basin=='NWT'?'active':'').'"><a href="'.url('flood/monitor/'.$report,array('basin'=>'NWT')).'">ลุ่มน้ำคลองนาทวี</a></li><li class="'.($basin=='PMT'?'active':'').'"><a href="'.url('flood/monitor/'.$report,array('basin'=>'PMT')).'">ลุ่มน้ำคลองภูมี</a></li><li class="'.($basin=='MBT'?'active':'').'" ><a href="'.url('flood/monitor/'.$report,array('basin'=>'MBT')).'">ลุ่มน้ำคลองมำบัง</a></li></ul></div>'._NL;


	$basinRs=mydb::select('SELECT * FROM %flood_basin% WHERE `basin`=:basin LIMIT 1',':basin',$basin);
	$self->theme->title=$basinRs->name;
	$ret.='<ul>'._NL;
	$ret.='<li><a href="'.url('flood/monitor/main',array('basin'=>$basin)).'"><i class="icon -viewdoc -info"></i>ข้อมูลสถานี</a>';
	$ret.='<ul>';
	$stations=mydb::select('SELECT * FROM %flood_station% WHERE `basin` = :basin AND `active` IS NOT NULL ORDER BY `sorder` ASC',':basin',$basin);
	foreach ($stations->items as $item) {
		$ret.='<li><a href="'.url('flood/monitor/main',array('basin'=>$basin,'s'=>$item->station)).'"><i class="icon -forward"></i>'.$item->title.'</a></li>';
	}
	$ret.='</ul>';
	$ret.='</li>';
	$ret.='<li><a href="'.url('flood/monitor/realtime',array('basin'=>$basin)).'"><i class="icon -viewdoc -realtime"></i>ข้อมูลปัจจุบัน</a></li>';
	$ret.='<li><a href="'.url('flood/monitor/table',array('basin'=>$basin)).'"><i class="icon -viewdoc -table"></i>รวมข้อมูลตาราง-กราฟ</a></li>';
	//$ret.='<li><a href="'.url('flood/monitor/warehouse',array('basin'=>$basin)).'"><i class="icon2 -warehouse"></i>ค้นคลังข้อมูล</a></li>';
	if (user_access('access flood command center')) {
		$ret.='<li><a href="'.url('flood/monitor/situation',array('basin'=>$basin)).'"><i class="icon -viewdoc -situation"></i>ระบบประเมินสถานการณ์</a></li>';
		$ret.='<li><a href="'.url('flood/monitor/links',array('basin'=>$basin)).'"><i class="icon -viewdoc -situation"></i>ข้อมูลภัยพิบัติทั่วโลก</a></li>';
	}
	$ret.='</ul>';
	//echo $ret;
	$self->theme->toolbar=$ret;
	return $ret;
}
?>