<?php
function flood_api_sensor($self,$sensorname="level") {

	mydb::where('s.`sensorName`=:sensorname',':sensorname',$sensorname);
	mydb::where('s.`timeRec` >= now() - INTERVAL 1 DAY AND MINUTE(s.`timeRec`)=0');
	$stmt='SELECT s.`station`, s.`timerec`, s.`value`
				FROM %flood_sensor% s
				%WHERE%
				ORDER BY `sid` ASC';
	$dbs=mydb::select($stmt);

	$result=array();

	$result=$dbs->items;

	//$ret.=print_o($dbs);

	die(json_encode($result));

	return $ret;
}
?>