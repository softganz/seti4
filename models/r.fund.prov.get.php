<?php
function r_fund_prov_get($prov,$options='{}') {
	$defaults='{clearProp:true, getAllRecord:false, debug:false}';
	$options=sg_json_decode($options,$defaults);
	$result=new stdClass();

	$stmt='SELECT DISTINCT
					  `changwat` `provid`,`namechangwat` `name`
					FROM %project_fund% f
					'.($prov?'WHERE `prov`=:prov AND ':'').'
					ORDER BY CONVERT(`namechangwat` USING tis620) ASC
					'.($options->getAllRecord?'; -- {key:"provid"}':'LIMIT 1');
	$rs=mydb::select($stmt,':prov',$prov);

	if ($rs->_num_rows) {
		if ($options->getAllRecord) $result=$rs->items;
		else $result=$options->clearProp?mydb::clearprop($rs):$rs;
	} else $result=null;

	if ($options->debug) {
		debugMsg(mydb()->_query);
		debugMsg($options,'$options');
		debugMsg($rs,'$rs');
	}
	return $result;
}
?>