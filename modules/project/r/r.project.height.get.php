<?php
function r_project_height_get($tpid) {
	$formid='weight';
	$debug=false;

	$stmt='SELECT
					tr.`trid`, tr.`tpid`, tr.`sorder`, tr.`detail1` `year`, tr.`detail2` `term`, tr.`period`,
					tr.`detail3` `area`, tr.`detail4` `postby`, tr.`date1` `dateinput`
					, COUNT(DISTINCT tr.`tpid`) totalSchool
					, SUM(q.`num5`) `short`
					, SUM(q.`num6`) `rathershort`
					, SUM(q.`num7`) `standard`
					, SUM(q.`num8`) `ratherheight`
					, SUM(q.`num9`) `veryheight`
					, SUM(q.`num1`) `total`
					, SUM(q.`num2`) `getheight`
					FROM %project_tr% tr
						LEFT JOIN %project_tr% q ON q.`parent`=tr.`trid` AND q.`part`="height"
					WHERE tr.`tpid`=:tpid AND tr.`formid`=:formid AND tr.`part`="title"
					GROUP BY tr.`sorder`
					ORDER BY `year` ASC,`term` ASC, `period` ASC';

	$dbs=mydb::select($stmt,':tpid',$tpid,':formid',$formid);

	if ($debug) debugMsg($dbs,'$dbs');
	return $dbs->items;
}
?>