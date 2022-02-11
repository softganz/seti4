<?php
/**
* flood_monitor_main
*
* @param Object $self
* @return String
*/
function flood_monitor_main($self) {
	if (post('s')) return R::Page('flood.monitor.station',$self);

	$basin = SG\getFirst(post('basin'),'UPT');

	$basinRs = mydb::select('SELECT * FROM %flood_basin% WHERE `basin`=:basin LIMIT 1',':basin',$basin);
	//$ret.='<header><h3>สถานการณ์'.$basinRs->name.'</h3></header>';

	$dbs = mydb::select('SELECT * FROM %flood_station% WHERE `basin`=:basin AND `active`>0 ORDER BY `sorder` ASC',':basin',$basin);


	R::View('flood.monitor.toolbar',$self);

	$ret.='<div class="flood__main">'._NL;
	$ret.='<ul class="flood__main__cctv -'.$basin.'">'._NL;
	foreach ($dbs->items as $rs) {
		$ret.='<li class="flood-cctv -'.$rs->station.'">'.__flood_cctv($rs).'</li>'._NL;
	}
	$ret.='</ul>'._NL;
	$ret.='<div id="" style="clear:both;background:#ddd;"><img src="'._url.'file/flood/site/basin-map-'.$basin.'.jpg" width="100%" /></div>'._NL;
	$ret.='<div id="" style="clear:both;background:#ddd;"><img src="'._url.'file/flood/site/'.$basin.'-waterflow.png" width="100%" /></div>'._NL;
	$ret.='<div class="flood--other">'._NL;
	$ret.='<h3>ข้อมูลประกอบเพื่อประเมินสถานการณ์น้ำ</h3>
<div id="realtime-status" class="sg-tabs" style="height:900px;">
<ul class="tabs"><li class="-active"><a href="#r1">ปริมาณน้ำฝนออนไลน์</a></li><li><a href="#r3">เรดาห์สทิงพระ</a></li><li><a href="#r4">แผนที่อากาศกรมอุตุนิยมวิทยา</a></li><li><a href="#r6">ภาพถ่ายดาวเทียมกรมอุตุนิยมวิทยา</a></li><li><a href="#r7" title="HAMWeather.com">พยากรณ์ฝนล่วงหน้า</a></li><li><a href="#r5">ภาพถ่ายดาวเทียมจาก Weather Channel</a></li></ul>
<div id="r1"><iframe width="100%" height="600" src="http://www.songkhla.tmd.go.th/RF/Monitor/" frameborder="0" scrolling="yes" style="margin:0;overflow:auto;"></iframe></div>
<div id="r3" class="-hidden"><iframe name="stp-loop" src="'.cfg('flood.monitor.radar.gif').'" height="550px" width="100%" align="middle" scrolling="no" frameborder="0"></iframe></div>
<div id="r4" class="-hidden"><a href="http://www.tmd.go.th/weather_map.php"><img src="https://tiwrm.hii.or.th/TyphoonTracking/wxImages/lastest_wc.jpg" width="100%" height="100%" alt="แผนที่อากาศกรมอุตุนิยมวิทยา" /></a></div>
<div id="r5" class="-hidden"><a href="http://hatyaicityclimate.org/file/fl/weather/lastphoto.jpg"><img src="http://hatyaicityclimate.org/file/fl/weather/lastphoto.jpg" width="100%" height="100%" alt="ภาพถ่ายดาวเทียม" /></a></div>
<div id="r6" class="-hidden" style="height:900px;"><iframe src="http://www.sattmet.tmd.go.th/newversion/mergesat.html" height="900px" width="100%" align="middle" scrolling="no" frameborder="0" style="height:900px;"></iframe></div>
<div id="r7" class="-hidden"><img src="http://contours.hamweather.net/contours/hw_640x480/gfs/sas_qpf_day1.png" alt="" width="100%" /></div>
</div>'._NL;
	$ret.='</div>'._NL;
	$ret.='</div><!--flood__main-->'._NL;
	return $ret;
}

function __flood_cctv($rs,$repair=false) {
	$ret='<h4>'.$rs->title.' ('.$rs->station.')</h4>'._NL;
	if ($repair) {
		$ret.='<!-- <span class="repair">กำลังปรับปรุง</span>-->';
		return $ret;
	}

	$stmt='SELECT `station`,`timeRec`,`sensorName`,`value` FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="level" ORDER BY `sid` DESC LIMIT 4,1;';
	//$lastHrLevel=mydb::select($stmt,':station',$rs->station);

	$lowerBankHeight=$rs->bankheightleft>$rs->bankheightright?$rs->bankheightright:$rs->bankheightleft;
	//$ret.=$lowerBankHeight.' : '.$rs->bankheightleft.' : '.$rs->bankheightright;
	$waterBankHeight=$rs->waterlevel-$lowerBankHeight;
	//$waterBankHeight=2;

	$levelChange=$rs->waterlevel-$rs->water60min;
	if ($levelChange>0) $levelChangeStatus='up';
	else if ($levelChange<0) $levelChangeStatus='down';
	else $levelChangeStatus='stable';

	$useReservCamera=$rs->usereservcam && $rs->reservcam;
	if ($useReservCamera) {
		$last_photo=$rs->reservcam;
	} else {
		$last_photo=$rs->last_photo?_DOMAIN._URL.'/upload/sensor/'.$rs->last_photo:url('file/flood/site/photonotyetupload.png');
	}
	$ret.='<a href="'.url('flood/monitor/main/',array('basin'=>$rs->basin,'s'=>$rs->station)).'"><img class="flood-cctv-photo" src="'.$last_photo.'" width="200" alt="" /></a>'._NL;
	if (!$useReservCamera) $ret.='<p class="flood-cctv-timestamp">'.($rs->last_updated?sg_date($rs->last_updated,'ว ดด ปป H:i').' น.':'').'</p>'._NL;
	$ret.='<p class="flood-cctv-water">ระดับน้ำ '
				.'<span>'.number_format($rs->waterlevel,2).' '.$rs->levelref.' </span>'
				.'<span>'.($waterBankHeight>0?'สูง':'ต่ำ').'กว่าตลิ่ง '.number_format(abs($waterBankHeight),2).' ม. </span>'
				.'<span class="levelstatus -'.$levelChangeStatus.'">('
				.($levelChangeStatus=='up'?'+':'').number_format($levelChange,3).' ม.)</span> '
				.'<span>สถานะ'.flood_model::flag($rs->waterlevel,$rs->levelyellow,$rs->levelred,'text').'</span><br />'
				.($rs->waterupdate?' เมื่อ '.sg_date($rs->waterupdate,'ว ดด ปป H:i').' น.':'')
				.'</p>'._NL;
	$ret.=flood_model::flag($rs->waterlevel,$rs->levelyellow,$rs->levelred,'flag',$rs->manualflag)._NL;

	/*
	if ($rs->waterlevel && $rs->bankheightleft) {
		$diff=$rs->bankheightleft-$rs->waterlevel;
		$tables->rows[]=array('ค่าระดับน้ำ'.($diff>0?'ต่ำ':'สูง').'กว่าตลิ่งซ้าย','<big><font color="'.($diff>0?'':'red').'">'.number_format(abs($diff),2).'</font></big>',' ม.');
	}
	if ($rs->waterlevel && $rs->bankheightright) {
		$diff=$rs->bankheightright-$rs->waterlevel;
		$tables->rows[]=array('ค่าระดับน้ำ'.($diff>0?'ต่ำ':'สูง').'กว่าตลิ่งขวา','<big><font color="'.($diff>0?'':'red').'">'.number_format(abs($diff),2).'</font></big>',' ม.');
	}
	*/

	//$ret.=print_o($rs,'$rs');
	//$ret.=print_o($lastHrLevel,'$lastHrLevel');
	return $ret;
}

?>