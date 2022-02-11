<?php
/**
* On Calendar Event
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function on_project_calendar_isadd($para) {
	R::Manifest('project');
	$tpid = $para->tpid;

	$projectInfo = R::Model('project.get',$tpid,'{data: "info"}');

	$rs = mydb::select('SELECT t.`uid`,p.`project_status` FROM %topic% t LEFT JOIN %project% p USING(`tpid`) WHERE t.`tpid`=:tpid LIMIT 1',':tpid',$tpid);

	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE && $projectInfo->info->project_status == 'กำลังดำเนินโครงการ';

	//debugMsg('IS EDIT '.($isEdit?'Yes':'No'));
	//debugMsg($projectInfo,'$projectInfo');
	//debugMsg($para,'$para');
	return $isEdit;
}
?>