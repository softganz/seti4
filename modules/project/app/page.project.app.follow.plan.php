<?php
/**
* Project :: App follow activity plan
* Created 2021-01-27
* Modify  2021-01-27
*
* @param Object $self
* @param Object $projectInfo
* @return String
*
* @usage project/app/follow/{id}/plan
*/

$debug = true;

function project_app_follow_plan($self, $projectInfo) {
	// Data model
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$isEdit = $projectInfo->right->isEdit;



	// View model
	$toolbar = new Toolbar($self, 'แผนกิจกรรม');
	$toolbarNav = new Ui();
	if ($isEdit) {
		//$toolbarNav->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info.plan.form').'" data-rel="box" data-width="640"><i class="icon -material">playlist_add</i><span>เพิ่มกลุ่มกิจกรรม</span></a>');
	}
	$toolbar->addNav('main', $toolbarNav);

	return R::PageWidget('project.info.plan.card', [$projectInfo]);
}
?>