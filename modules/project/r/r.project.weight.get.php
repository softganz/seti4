<?php
function r_project_weight_get($tpid) {
	$formid='weight';
	$debug=false;

	$stmt='SELECT
				tr.`trid`, tr.`tpid`, tr.`sorder`, tr.`detail1` `year`, tr.`detail2` `term`, tr.`period`,
				tr.`detail3` `area`, tr.`detail4` `postby`, tr.`date1` `dateinput`,
				SUM(q.`num5`) `thin`,
				SUM(q.`num6`) `ratherthin`,
				SUM(q.`num7`) `willowy`,
				SUM(q.`num8`) `plump`,
				SUM(q.`num9`) `gettingfat`,
				SUM(q.`num10`) `fat`,
				SUM(q.`num1`) `total`,
				SUM(q.`num2`) `getweight`
				FROM %project_tr% tr
					LEFT JOIN %project_tr% q ON q.`parent`=tr.`trid` AND q.`part`="weight"
				WHERE tr.`tpid`=:tpid AND tr.`formid`=:formid AND tr.`part`="title"
				GROUP BY tr.`sorder`
				ORDER BY `year` ASC,`term` ASC, `period` ASC';

	$dbs=mydb::select($stmt,':tpid',$tpid,':formid',$formid);

	if ($debug) debugMsg($dbs,'$dbs');
	return $dbs->items;
}
?>