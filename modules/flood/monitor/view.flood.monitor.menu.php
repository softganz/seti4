<?php
/**
* Show module toolbar menu
*
* @param NULL
* @return String
*/
function view_flood_monitor_menu($self) {
	$basin=SG\getFirst(post('basin'),'UPT');
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