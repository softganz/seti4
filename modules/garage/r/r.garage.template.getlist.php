<?php
function r_garage_template_getlist($shopid,$templateid,$options='{}') {
	$defaults='{value:"repairname",debug:false}';
	$options=sg_json_decode($options,$defaults);
	$debug=$options->debug;
	$result=NULL;

	$stmt='SELECT DISTINCT
					  tr.*
					, r.`repairname`
					, CONCAT(r.`repairname`," (",r.`repairid`,")") `repaidcode`
					FROM %garage_jobtemplatetr% tr
						LEFT JOIN %garage_repaircode% r USING(`repairid`)
					WHERE tr.`shopid` IN (:shopid) AND tr.`templateid`=:templateid
					ORDER BY tr.`sorder` ASC,CONVERT(r.`repaircode` USING tis620) ASC;
					-- {key:"repairid"}';
	$dbs=mydb::select($stmt,':shopid',$shopid, ':templateid',$templateid);
	if ($debug) debugMsg($dbs,'$dbs');

	if ($options->value!='all') {
		foreach ($dbs->items as $rs) {
			$result[$rs->repairid]=$rs->{$options->value};
		}
	} else {
		$result=$dbs->items;
	}
	if ($debug) debugMsg($result,'$result');
	return $result;
}
?>