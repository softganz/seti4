<?php
/**
* flood_monitor_rainavg
*
* @param Object $self
* @return String
*/
function flood_monitor_rainavg($self) {
	$basin=post('basin');

	$isAdmin=user_access('administrator floods');
	$basinDbs=mydb::select('SELECT * FROM %flood_basin%');
	$date=SG\getFirst(post('d'),date('Y-m-d H:i:s'));

	R::View('flood.monitor.toolbar',$self);


	$ret.='<div class="flood__avg">';
	$ret.='<h2>ปริมาณน้ำฝนเฉลี่ย ณ '.sg_date($date,'ว ดด ปปปป H:i').'</h2>';
	foreach ($basinDbs->items as $basinRs) {
		$basin=$basinRs->basin;
		list($rainBasin,$data)=flood_model::rainavg($basin,$date);
		foreach ($rainBasin as $key => $rs) $rains[$key]=$rs;
	}
	$dbs=mydb::select('SELECT * FROM %flood_factor%');
	foreach ($dbs->items as $rs) $factors[$rs->station][$rs->substation]=(array)$rs;

	foreach ($basinDbs->items as $basinRs) {
		$basin=$basinRs->basin;
		$dbs=mydb::select('SELECT * FROM %flood_station% WHERE `basin`=:basin ORDER BY `sorder` ASC',':basin',$basin);
		$ret.='<div class="flood__box flood__box--station">';
		unset($tables);
		$ret.='<h3>ลุ่มน้ำ'.$basinRs->name.'</h3>';

		$tables = new Table();
//		<h3>ปริมาณน้ำฝนเฉลี่ยนของลุ่มน้ำเหนือสถานี - '.$rs->title.'</h3>
		foreach ($dbs->items as $rs) {
			$station=$rs->station;
			//$ret.=$station.'='._rain_avg($station,'today',$rains,$factors[$station]).'<br />';
			$avg1hr=_rain_avg($station,'1hr',$rains,$factors[$station]);
			$avg3hr=_rain_avg($station,'3hr',$rains,$factors[$station]);
			$avgToday=_rain_avg($station,'today',$rains,$factors[$station]);
			$avgYesterday=_rain_avg($station,'yesterday',$rains,$factors[$station]);
			$tables->rows[]='<tr><th colspan="3">ปริมาณน้ำฝนเฉลี่ยของลุ่มน้ำเหนือสถานี'.$rs->title.'</th></tr>';
			$tables->rows[]=array('ปริมาณน้ำฝนเฉลี่ย 1 ชั่วโมง',number_format($avg1hr,2),'ม.ม.');
			$tables->rows[]=array('ปริมาณน้ำฝนเฉลี่ย 3 ชั่วโมง',number_format($avg3hr,2),'ม.ม.');
			$tables->rows[]=array('ปริมาณน้ำฝนเฉลี่ย วันนี้',number_format($avgToday,2),'ม.ม.');
			$tables->rows[]=array('ปริมาณน้ำฝนเฉลี่ย เมื่อวานนี้',number_format($avgYesterday,2),'ม.ม.');
		}

		$ret .= $tables->build();

		//$ret.=print_o($rainBasin[$rs->station],'$rainBasin');
		$ret.='</div>';
		//$ret.=print_o($data['MBT2'],'$data');
	}
	$ret.='</div><!--flood__main-->';
	//$ret.=print_o($rains,'$rains');
	//$ret.=print_o($factors,'$factors');
	return $ret;
}
function _rain_avg($station,$period,$rains,$factors) {
	$avg=0;
	//print_o($factors,'$factors',1);
	foreach ($factors as $factor) {
		//echo $station.','.$factor['factor'].','.$rains[$factor['substation']][$period].'<br />';
		$avg+=$factor['factor']*$rains[$factor['substation']][$period];
	}
	//echo '='.$avg.'<br />';
	return $avg;
}
?>