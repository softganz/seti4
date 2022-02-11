<?php
/**
* Project :: Monthly Send Report
* Created 2021-01-23
* Modify  2021-01-23
*
* @param Object $self
* @param Int $projectId
* @return String
*
* @usage project/app/month/pki
*/

$debug = true;

function project_app_month_send($self, $projectId = NULL) {
	// Data Model
	if (empty($projectId)) {
		$selectProject = R::View('project.select', '{rel: "box->clear", retUrl: "'.url('project/app/month/kpi/$id').'"}');
		//debugMsg($selectProject, '$selectProject');
		if ($selectProject->error) {
			return message('error', 'ขออภัย '.$selectProject->error);
		} else if ($selectProject->projectId) {
			$projectId = $selectProject->projectId;
		} else {
			return '<header class="header -box -hidden">'._HEADER_BACK.'<h3>เลือกโครงการ</h3></header>'
				. $selectProject->build();
		}
	}

	$projectInfo = R::Model('project.get', $projectId);
	if (!$projectInfo->info->membershipType) {
		return message('error', 'ขออภัย - โครงการนี้ท่านไม่สามารถเขียนบันทึกกิจกรรมได้');
	}


	// View Model
	$ret = '<header class="header">'._HEADER_BACK.'<h3>ส่งรายงานประจำเดือน</h3></header>';

	$ret .= R::Page('project.info.send.form', NULL, $projectInfo);

	//$ret .= print_o($projectInfo, '$projectInfo');

	return $ret;
}
?>