<?php
/**
* Project Action Join Setting
* Created 2019-05-15
* Modify  2019-07-30
*
* @param Object $self
* @param Int $tpid
* @param Int $calid
* @return String
*/

function project_join_setting_distance($self, $tpid, $calid) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get', $tpid, '{initTemplate:true}');
	$tpid = $projectInfo->tpid;

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	if (!$isAdmin) return message('error', 'access denied');



	$doingInfo = R::Model('org.doing.get', array('calid' => $calid), '{data: "info"}');

	if (!$doingInfo->areacode) return message('error','ยังไม่มีการกำหนดรหัสอำเภอของสถานที่จัดงาน');

	$ret .= R::Page('code.ampur.distance', NULL, $doingInfo->areacode);
	//$ret .= print_o($doingInfo, '$doingInfo');
	//$ret .= print_o($projectInfo);
	//$ret .= print_o($doingInfo,'$doingInfo');
	return $ret;
}
?>