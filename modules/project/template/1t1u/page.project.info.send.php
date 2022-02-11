<?php
/**
* Project :: Report Send Information
* Created 2020-01-26
* Modify  2020-01-26
*
* @param Object $self
* @param Object $projectInfo
* @param Int $tranId
* @return String
*
* @usage project/{id}/info.send/{tranId}
*/

$debug = true;

function project_info_send($self, $projectInfo, $tranId = NULL) {
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$ret .= '<header class="header"><h3>Monthly Report</h3></header>';
	/*
	$qtCondition = array('qtref' => $qtRef);
	$surveyInfo = R::Model('qt.get', $qtCondition, '{debug: false}');

	if ($surveyInfo->info->qtform) {
		$formInfo = R::Model('qt.form.get', $surveyInfo->info->qtform);
		$schema = $formInfo->info->schema;
	}

	$ret .= print_o($surveyInfo, '$surveyInfo');
	$ret .= print_o($formInfo, '$formInfo');
	*/

	return $ret;
}
?>