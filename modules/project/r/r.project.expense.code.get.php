<?php
/*
* resultType : item,group,select
*/
function r_project_expense_code_get($expid=NULL,$groupId=NULL,$options='{}') {
	$defaults='{resultType:"item",debug:false}';
	$options=sg_json_decode($options,$defaults);
	$debug=$options->debug;

	$result=array();

	$stmt='SELECT
					  c.`tid`, c.`taggroup`, c.`catid`, c.`name`
					, c.`catparent` `groupId`, g.`name` `groupName`
					, c.`description`
					, c.`weight`
					FROM %tag% c
						LEFT JOIN %tag% g ON g.`taggroup`="project:expgr" AND g.`catid`=c.`catparent`
					WHERE c.`taggroup`="project:expcode" AND c.`name` != "" '.($groupId?'AND c.`catparent`=:groupId':'').'
					ORDER BY c.`catparent` ASC, c.`catid` ASC;
					-- {key:"catid"}';
	$dbs=mydb::select($stmt,':expid',$expid, ':groupId',$groupId);

	if ($expid) {
		$result=$dbs->items[$expid];
	} else if ($options->resultType=='select') {
		foreach ($dbs->items as $rs) {
			$result[$rs->groupName][$rs->catid]=$rs->name;
		}
	} else if ($options->resultType=='group') {
		foreach ($dbs->items as $rs) {
			$result[$rs->groupId][$rs->catid]=$rs;
		}
	} else {
		$result=$dbs->items;
	}

	if ($debug) {
		debugMsg($options,'$options');
		debugMsg($result,'$result');
		debugMsg($dbs,'$dbs');
	}
	return $result;
}
?>