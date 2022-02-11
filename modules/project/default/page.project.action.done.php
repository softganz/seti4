<?php
function project_action_done($self,$tpid=NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid, '{initTemplate: true}');
	$tpid = $projectInfo->tpid;


	$ret = '';

	if (empty($projectInfo)) return message('error','ERROR : No Project');

	R::View('project.toolbar',$self,$projectInfo->title,NULL,$projectInfo);

	$actionList = R::Model('project.action.get',$tpid, '{order:"`actionDate` DESC, `actionId` DESC", includePhoto: false}');

	foreach ($actionList as $rs) {
		$ret.=R::View('project.action.render',$projectInfo,$rs);
	}
	//$ret.=print_o($actionList,'$actionList');
	return $ret;
}
?>