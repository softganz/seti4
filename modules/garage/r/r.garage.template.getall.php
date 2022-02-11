<?php
function r_garage_template_getall($shopid,$options='{}') {
	$debug=false;
	$defaults='{value:"templatename"}';
  $options=sg_json_decode($options,$defaults);
	$result=NULL;

	$stmt='SELECT DISTINCT
					  t.*
					FROM %garage_jobtemplate% t
					WHERE `shopid` IN (0,:shopid)
					ORDER BY CONVERT(`templatename` USING tis620) ASC;
					-- {key:"templateid"}';
	$dbs=mydb::select($stmt,':shopid',$shopid);

	if ($options->value=='templatename') {
		foreach ($dbs->items as $rs) {
			$result[$rs->templateid]=$rs->templatename;
		}
	} else {
		$result=$dbs->items;
	}
	if ($debug) debugMsg($result,'$result');
	return $result;
}
?>