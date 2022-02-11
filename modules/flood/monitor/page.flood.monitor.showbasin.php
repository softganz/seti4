<?php
function flood_monitor_showbasin($self) {
	$basin=post('basin');
	$panel=post('panel');
	$isAdmin=user_access('administrator floods');
	$basinRs=mydb::select('SELECT * FROM %flood_basin% WHERE `basin` = :basin LIMIT 1',':basin',$basin);
	if ($basinRs->_empty) return;

	$dbs=mydb::select('SELECT * FROM %flood_station% WHERE `basin` = :basin AND `active` > 0 ORDER BY `sorder` ASC',':basin',$basin);

	list($rainAvg)=flood_model::rainavg($basin);

	$ret.='<h3>'.$basinRs->name.'</h3><ul class="flood-monitor-center-expand"><li><a class="btn" href="'.url('flood/monitor/center',array('basin'=>$basin)).'" target="_blank"><i class="icon -material">search</i></a></li></ul>'._NL;
	$ret.='<ul class="flood__center--cctv -items-'.$dbs->_num_rows.'">'._NL;
	foreach ($dbs->items as $rs) {
		$useReservCamera=$rs->usereservcam && $rs->reservcam;
		$photoTime='';
		if ($useReservCamera) {
			$photo=$rs->reservcam;
		} else {
			$photo=$rs->last_photo?_DOMAIN._URL.'/upload/sensor/'.$rs->last_photo:url('file/flood/site/photonotyetupload.png');
			$photoTime=$rs->last_updated?'<span class="time__date">'.sg_date($rs->last_updated,'d').'</span>'.(sg_date($rs->last_updated,'Y-m-d')!=date('Y-m-d') || cfg('flood.center.showmonth')?'<span class="time__month">'.sg_date($rs->last_updated,'ดด ปป').'</span>':'').'<span class="time__hrmin">'.sg_date($rs->last_updated,'H:i').'<span class="time__timeunit"> น.</span>':'';
		}
		//$photo=$rs->last_photo?_URL.'upload/sensor/'.$rs->last_photo:url('file/flood/site/photonotyetupload.png');


		$levelTime=sg_date($rs->waterupdate,'Y-m-d')==date('Y-m-d') ? sg_date($rs->waterupdate,'H:i') : sg_date($rs->waterupdate,'ว ดด ปป H:i');

		$ret.='<li>'._NL;
		$ret.='<h4>'.$rs->title.' - '.$rs->station.'</h4>'._NL;
		$ret.='<a class="sg-action imglink" href="'.$photo.'" data-rel="img"><img class="cctv" src="'.$photo.'" width="320" height="180" /></a>'._NL;
		//$ret.=print_o($rs,'$rs');
		$ret.='<div class="time flood__phototime">'.($rs->last_updated?$photoTime:'-').'</div>'._NL;
		if (user_access('administrator floods,operator floods')) {
			$ret.='<a class="sg-action" href="'.url('flood/monitor/center',array('action'=>'flag','id'=>$rs->station)).'" data-rel="box" data-width="320">'.flood_model::flag($rs->waterlevel,$rs->levelyellow,$rs->levelred,'flag',$rs->manualflag).'</a>'._NL;
		} else {
			$ret.=flood_model::flag($rs->waterlevel,$rs->levelyellow,$rs->levelred,'flag',$rs->manualflag);
		}

		/*
		$stmt='SELECT * FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="temperature" ORDER BY `sid` DESC LIMIT 1';
		$stmt='SELECT * FROM %flood_sensor% WHERE `sid`=(SELECT MAX(`sid`) FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="temperature") LIMIT 1';
		//$temperature=mydb::select($stmt,':station',$rs->station);

		$stmt='SELECT * FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="relativeHumidity" ORDER BY `sid` DESC LIMIT 1';
		$humidity=mydb::select($stmt,':station',$rs->station);

		$stmt='SELECT * FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="rain" ORDER BY `sid` DESC LIMIT 10';
		$rains=mydb::select($stmt,':station',$rs->station);

		$stmt='SELECT `station`,`timeRec`,`sensorName`,`value` FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="level" ORDER BY `sid` DESC LIMIT 4,1;';
		$lastHrLevel=mydb::select($stmt,':station',$rs->station);
		*/

		$levelChange=$rs->waterlevel-$rs->water60min;
		if ($levelChange>0) $levelChangeStatus='up';
		else if ($levelChange<0) $levelChangeStatus='down';
		else $levelChangeStatus='stable';

		$waterChangeLevel='<span class="levelstatus -'.$levelChangeStatus.'" style="font-size:0.8em;white-space:nowrap;">('.($levelChangeStatus=='up'?'+':'').number_format($levelChange,3).' ม.)</span>';


		$tables = new Table();
		$tables->caption='ข้อมูลตรวจวัด เมื่อ <span class="time">'.$levelTime.' น.</span>';
		$tables->rows[]=array('<td class="header" colspan="3">ระดับน้ำ</td>');
		$tables->rows[]=array(
			'ปัจจุบัน',
			($rs->waterlevel?number_format($rs->waterlevel,2):'-').'<br /><div">'.$waterChangeLevel.'</div>',
			$rs->levelref
		);

		if ($rs->waterlevel && $rs->bankheightleft) {
			$diff=$rs->bankheightleft-$rs->waterlevel;
			$tables->rows[]=array(($diff>0?'ต่ำ':'สูง').'กว่าตลิ่งซ้าย','<strong><font color="'.($diff>0?'':'red').'">'.number_format(abs($diff),2).'</font></strong>',' ม.<span class="sg-info" data-tooltip="ระดับตลิ่งซ้าย '.number_format($rs->bankheightleft,2).' ม.">?</span>');
		}
		if ($rs->waterlevel && $rs->bankheightright) {
			$diff=$rs->bankheightright-$rs->waterlevel;
			$tables->rows[]=array(($diff>0?'ต่ำ':'สูง').'กว่าตลิ่งขวา','<strong><font color="'.($diff>0?'':'red').'">'.number_format(abs($diff),2).'</font></strong>',' ม.<span class="sg-info" data-tooltip="ระดับตลิ่งขวา '.number_format($rs->bankheightright,2).' ม.">?</span>');
		}
		$tables->rows[]=array('<td class="header" colspan="3">ปริมาณน้ำฝน</td>');
		$tables->rows[]=array('15 นาที',number_format($rainAvg[$rs->station]['15min'],1),'ม.ม.','');
		$tables->rows[]=array('1 ชั่วโมง',number_format($rainAvg[$rs->station]['1hr'],1),'ม.ม.','');
		$tables->rows[]=array('3 ชั่วโมง',number_format($rainAvg[$rs->station]['3hr'],1),'ม.ม.','');
		$tables->rows[]=array('<span title="ปริมาณน้ำฝนรวมวันนี้ คือ ปริมาณน้ำฝนรวมตั้งแต่ 0:00 น. ของวันนี้ จนถึงปัจจุบัน">รวมวันนี้',number_format($rainAvg[$rs->station]['today'],1),'ม.ม.');
		$tables->rows[]=array('<span title="ปริมาณน้ำฝนรวมเมื่อวาน คือ ปริมาณน้ำฝนรวมตั้งแต่ 0:00 น. จนถึง 24:00 น. เมื่อวานนี้">เมื่อวาน</span>',number_format($rainAvg[$rs->station]['yesterday'],1),'ม.ม.');
		$tables->rows[]=array('<td class="header" colspan="3">ข้อมูลอื่น ๆ</td>');
		$tables->rows[]=array('อุณหภูมิ',number_format($rs->temperature,1),'ํC');
		$tables->rows[]=array('ความชื้นสัมพัทธ์',number_format($rs->humidity,0),'%');
		if ($isAdmin) {
			$tables->rows[]=array('แบตเตอรี่',number_format($rs->batterylevel,1),'โวลท์');
		}

		$ret .= $tables->build();

		$ret.='</li>'._NL;
	}
	$ret.='</ul>'._NL;

	return $ret;
}
?>