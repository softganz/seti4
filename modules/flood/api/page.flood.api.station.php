<?php
function flood_api_station($self,$station=NULL) {

	//mydb::where('s.`sensorName`=:sensorname',':sensorname',$sensorname);
	$stmt='SELECT
				  s.*
				FROM %flood_station% s
					LEFT JOIN %flood_station% t USING(`station`)
				%WHERE%;
				-- {key:"station"}';
	$dbs=mydb::select($stmt);

	$result=array();

	//$result=$dbs->items;
	foreach ($dbs->items as $key => $value) $result[$key]=$value;

	//$ret.=print_o($dbs);

	die(json_encode($result));

	return $ret;
}
?>