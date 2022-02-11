<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_pocenter_setting_officer($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.pocenter.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	$ret = '';

	R::View('imed.toolbar', $self, $orgInfo->name.' @ศูนย์กายอุปกรณ์', 'pocenter', $orgInfo);

	if (!$orgInfo) return message('error', 'ไม่มีข้อมูลตามที่ระบุ');

	$isAdmin = user_access('administer imeds')
		|| $orgInfo->RIGHT & _IS_ADMIN;

	if (!$isAdmin) return message('error','access denied');

	$ret .= R::Page('org.officer', NULL, $orgId);
	return $ret;
}
?>