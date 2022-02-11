<?php
/**
* Project objective information
*
* @param Object $self
* @param Integer $tpid
* @param String $action
* @param Integer $tranId
* @return String
*/
function project_info_objective_view($self, $tpid = NULL, $tranId = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get', $tpid, '{initTemplate:true}');

	$tpid = $projectInfo->tpid;

	$ret.='<h4>วัตถุประสงค์</h4>';
	$ret.='<p>'.$projectInfo->objective[$tranId]->title.'</p>';
	$ret.='<h4>ตัวชี้วัดความสำเร็จ</h4>';
	$ret.='<p>'.nl2br($projectInfo->objective[$tranId]->indicatorDetail).'</p>';

	return $ret;
}
?>