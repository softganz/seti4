<?php
/**
* flood_monitor_realtime
*
* @param Object $self
* @return String
*/
function flood_monitor_realtime($self) {
	$basin=post('basin');
	$isAdmin=user_access('administrator floods');
	$dbs=mydb::select('SELECT * FROM %flood_station% WHERE `basin`=:basin ORDER BY `sorder` ASC',':basin',$basin);
	list($rainAvg)=flood_model::rainavg($basin);



	R::View('flood.monitor.toolbar',$self);

	$ret.='<div class="flood__realtime">';
	$ret.='<h2>ข้อมูลปัจจุบัน ณ '.sg_date('ว ดด ปปปป H:i').'</h2>';
	foreach ($dbs->items as $rs) {
		$stmt='SELECT * FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="temperature" ORDER BY `sid` DESC LIMIT 1';
		$temperature=mydb::select($stmt,':station',$rs->station);
		$stmt='SELECT * FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="relativeHumidity" ORDER BY `sid` DESC LIMIT 1';
		$humidity=mydb::select($stmt,':station',$rs->station);

		$stmt='SELECT * FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="rain" ORDER BY `sid` DESC LIMIT 10';
		$rains=mydb::select($stmt,':station',$rs->station);

		$ret.='<div class="flood__box flood__box--station -'.$rs->station.'"><h3>สถานี - '.$rs->title.'</h3>';


		$tables = new Table();
		$tables->rows[]=array('ปริมาณน้ำฝน 15 นาที',number_format($rainAvg[$rs->station]['15min'],1),'ม.ม.','');
		$tables->rows[]=array('ปริมาณน้ำฝน 1 ชั่วโมง',number_format($rainAvg[$rs->station]['1hr'],1),'ม.ม.','');
		$tables->rows[]=array('ระดับน้ำ',$rs->waterlevel?number_format($rs->waterlevel,2):'-',$rs->levelref);
		$tables->rows[]=array('อุณหภูมิ',number_format($temperature->value,2),'ํC');
		$tables->rows[]=array('ความชื้นสัมพัทธ์',number_format($humidity->value,2),'%');
		if ($isAdmin) {
			$tables->rows[]=array('แบตเตอรี่',$rs->batterylevel,'โวลท์');
		}

		$ret .= $tables->build();

		$ret.='<ul class="flood__main__cctv">';
		$ret.='<li style="width:100%;">'.__flood_cctv($rs).'</li>';
		$ret.='</ul>';
		$ret.='</div>';
	}
	$ret.='</div><!--flood__main-->';
	return $ret;
}

function __flood_cctv($rs,$repair=false) {
	$ret='<h4>'.$rs->title.' ('.$rs->station.')</h4>';
	if ($repair) {
		$ret.='<!-- <span class="repair">กำลังปรับปรุง</span>-->';
		return $ret;
	}
	$ret.='<a href="'.url('flood/monitor/main/',array('basin'=>$rs->basin,'s'=>$rs->station)).'"><img class="flood-cctv-photo" src="'.($rs->last_photo?_DOMAIN.'/upload/sensor/'.$rs->last_photo:url('file/flood/site/photonotyetupload.png')).'" width="200" alt="" /></a>';
	$ret.='<p class="flood-cctv-timestamp">'.($rs->last_updated?sg_date($rs->last_updated,'ว ดด ปปปป H:i'):'').'</p>';
	$ret.='<p class="flood-cctv-water" style="font-size:1.2em;height:20px;">ระดับน้ำ <span>'.number_format($rs->waterlevel,2).'</span> '.$rs->levelref.' สถานะ <span>'.flood_model::flag($rs->waterlevel,$rs->levelyellow,$rs->levelred,'text').'</span>'.($rs->waterupdate?'เมื่อ '.sg_date($rs->waterupdate,'ว ดด ปปปป H:i'):'').'</p>';
	$ret.=flood_model::flag($rs->waterlevel,$rs->levelyellow,$rs->levelred,'flag',$rs->manualflag);
	return $ret;
}

?>