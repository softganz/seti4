<?php
function project_action_notdone($self,$tpid=NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get', $tpid, '{initTemplate:true}');

	$tpid = $projectInfo->tpid;

	if (empty($projectInfo)) return message('error','ERROR : No Project');

	R::View('project.toolbar',$self, $projectInfo->title, NULL, $projectInfo);

	$ret.='List of not done activity';
	return $ret;
}
?>