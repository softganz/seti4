<?php
/**
* flood_monitor_station
*
* @param Object $self
* @return String
*/
function flood_monitor_cctv($self) {
	$station=post('s');
	$date=SG\getFirst(post('d'),date('d/m/Y'));

	$ret.='<div class="toolbar main">';
	$ret.='<form method="get" action="'.url('flood/monitor/cctv').'"><input type="hidden" name="basin" value="'.$basin.'" /><input type="hidden" name="s" value="'.$station.'" /><label>วันที่</label> <input type="text" name="d" class="form-text sg-datepicker" size="10" value="'.$date.'" /> <input type="submit" class="button" value=" ดู " /></form>';
	$ret.='</div>';

	$where=array();
	$where=sg::add_condition($where,'`station`=:station AND `sensorName`="camera"','station',$station);
	$where=sg::add_condition($where,'`timeRec` BETWEEN :fromdate AND :todate','fromdate',sg_date($date,'Y-m-d 00:00:00'),'todate',sg_date($date,'Y-m-d 23:59:59'));
	$stmt='SELECT `sid`, `timeRec`, `value`
				FROM %flood_sensor% s
				WHERE '.implode(' AND ',$where['cond']).'
				ORDER BY `sid` DESC';
	$dbs=mydb::select($stmt,$where['value']);

	$ret.='<ul class="sensor-photo-thumbnail">';
	foreach ($dbs->items as $rs) {
		$imgSrc='/upload/sensor/'.$rs->value;
		$ret.='<li><a class="sg-action" href="'.$imgSrc.'" data-rel="img"><img src="'.$imgSrc.'" width="200" /></a><p>'.sg_date($rs->timeRec,'d/m/ปป H:i').' น.</li>';
	}
	$ret.='</ul>';
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>