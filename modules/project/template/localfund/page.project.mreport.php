<?php
/**
* Project Result
*
* @param Object $self
* @param Int $tpid
* @return String
*/

$debug = true;

function project_mreport($self, $tpid, $action = NULL, $tranId = NULL) {
	$formid='valuation';

	R::Module('project.template',$self,$tpid);

	if ($tpid) $projectInfo = R::Model('project.get',$tpid);

	R::View('project.toolbar', $self, $projectInfo->title, NULL, $projectInfo);

	if (!$projectInfo) return message('error', 'No Project');



	$valuationTr = project_model::get_tr($tpid, $formid.':title');
	$finalReportTitle = project_model::get_tr($tpid,'finalreport:title');

	$isViewOnly = $action == 'view';
	$isEditable = $projectInfo->info->isRight;
	$isEdit = $projectInfo->info->isRight && $action == 'edit';

	$ret .= '<h2 class="title -main">รายงานการเงิน</h2>';

	if ($isEdit) {
		$inlineAttr['class'] = 'sg-inline-edit ';
		$inlineAttr['data-tpid'] = $tpid;
		$inlineAttr['data-update-url'] = url('project/edit/tr');
		if (post('debug')) $inlineAttr['data-debug'] = 'yes';
	}
	$inlineAttr['class'] .= 'project-result';

	$ret.='<div id="project-result" '.sg_implode_attr($inlineAttr).'>'._NL;

	$ret .= '<section class="project-result-objective box">';
	$ret .= '<h3 class="title -sub1">รายงานการเงินประจำงวด</h3>';
	$ret .= '</section><!-- project-result-objective -->';

	$ret .= '<section class="project-result-objective box">';
	$ret .= '<h3 class="title -sub1">รายงานสรุปการเงินโครงการ</h3>';
	$ret .= '</section><!-- project-result-objective -->';

	$ret.='</div><!-- project-result -->';

	//$ret.=print_o($projectInfo,'$projectInfo');
	return $ret;
}
?>