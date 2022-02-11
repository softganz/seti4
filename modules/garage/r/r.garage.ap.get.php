<?php
function r_garage_ap_get($shopid,$id,$options='{}') {
	$defaults='{value:"repairname",debug:false}';
	$options=sg_json_decode($options,$defaults);
	$debug=$options->debug;
	
	$rs=NULL;
	
	$stmt='SELECT *
					FROM %garage_ap% c
					WHERE `shopid`=:shopid AND `apid`=:id
					LIMIT 1';
	$rs=mydb::select($stmt, ':shopid',$shopid, ':id',$id);

	if ($rs->_num_rows) {
		if (!$debug) mydb::clearprop($rs);
	}
	if ($debug) debugMsg($rs,'$rs');
	return $rs;
}
?>