<?php
function r_garage_repaircode_get($id,$options='{}') {
	$defaults='{value:"repairname",debug:false}';
	$options=sg_json_decode($options,$defaults);
	$debug=$options->debug;
	
	$rs=NULL;
	
	$stmt='SELECT *
					FROM %garage_repaircode% r
					WHERE `repairid`=:id
					LIMIT 1';
	$rs=mydb::select($stmt, ':id',$id);

	if ($rs->_num_rows) {
		if (!$debug) mydb::clearprop($rs);

	}
	if ($debug) debugMsg($rs,'$rs');
	return $rs;
}
?>