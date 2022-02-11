<?php
/*
* resultType : item,group,select
*/
function r_project_expense_plan_get($expid=NULL,$options='{}') {
	$defaults='{debug:false}';
	$options=sg_json_decode($options,$defaults);
	$debug=$options->debug;

	$result=array();

	$stmt='SELECT
				  `tpid`
				, `trid` `expid`
				, `parent`
				, `formid`, `part`
				, `gallery` `expcode`
				, `num1` `amt`
				, `num2` `unitprice`
				, CAST(`num3` AS SIGNED) `times`
				, `num4` `total`
				, `detail1` `unitname`
				, `text1` `detail`
				FROM %project_tr%
				WHERE `trid`=:expid AND `formid`="info" AND `part`="exptr"
				LIMIT 1';

	$rs=mydb::select($stmt,':tpid',$tpid, ':expid',$expid);

	if ($rs->_num_rows) $result=$debug?$rs:mydb::clearprop($rs);

	if ($debug) {
		debugMsg($options,'$options');
		debugMsg($result,'$result');
		debugMsg($rs,'$rs');
	}
	return $result;
}
?>