<?php
function r_garage_brand_getall($shopid,$options='{}') {
	$debug=false;
	$defaults='{value:"brandname"}';
  $options=sg_json_decode($options,$defaults);
	$result=NULL;

	$stmt='SELECT DISTINCT
					  b.*
					FROM %garage_brand% b
					WHERE `shopid` IN (0,:shopid)
					ORDER BY CONVERT(`brandname` USING tis620) ASC;
					-- {key:"brandid"}';
	$dbs=mydb::select($stmt,':shopid',$shopid);

	if ($options->value=='brandname') {
		foreach ($dbs->items as $rs) {
			$result[$rs->brandid]=$rs->brandname;
		}
	} else {
		$result=$dbs->items;
	}
	if ($debug) debugMsg($result,'$result');
	return $result;
}
?>