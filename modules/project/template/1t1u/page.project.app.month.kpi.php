<?php
/**
* Project :: Monthly KPI
* Created 2021-01-21
* Modify  2021-01-21
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage project/app/month/pki
*/

$debug = true;

function project_app_month_kpi($self, $projectId = NULL, $qtRef = NULL) {
	$ret = '';

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

	//$ret .= '<header class="header">'._HEADER_BACK.'<h3>Tambon System Integrator</h3></header>';

	$ret .= R::Page('project.month.kpi', NULL, $projectInfo, $qtRef);

	//$ret .= print_o($projectInfo, '$projectInfo');

	return $ret;
}
?>