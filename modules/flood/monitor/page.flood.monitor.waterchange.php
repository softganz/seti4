<?php
function flood_monitor_waterchange($self) {
	$station=post('st');

	// Get current water level
	$stmt='SELECT * FROM %flood_station% WHERE `station`=:station LIMIT 1';
	$rs=mydb::select($stmt,':station',$station);

	$levelChange=$rs->waterlevel-$rs->water60min;

	if ($levelChange>0) $levelChangeStatus='up';
	else if ($levelChange<0) $levelChangeStatus='down';
	else $levelChangeStatus='stable';


	$ret.='<span class="levelstatus -'.$levelChangeStatus.'" style="font-size:0.8em;white-space:nowrap;">('.($levelChangeStatus=='up'?'+':'').number_format($levelChange,3).' ม.)</span>';
	return $ret;
}
?>