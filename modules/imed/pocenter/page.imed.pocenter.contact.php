<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_pocenter_contact($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.pocenter.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	R::View('imed.toolbar', $self, 'ศูนย์กายอุปกรณ์ @'.$orgInfo->name, 'pocenter', $orgInfo);

	if (!$orgInfo) return message('error', 'ไม่มีข้อมูลตามที่ระบุ');
	$ret = '';

	$isAdmin = user_access('administer imeds')
					|| $orgInfo->RIGHT & _IS_ADMIN;
	$isEditable = $isAdmin || $orgInfo->is->officer;

	$ret .= '<section id="imed-pocenter-info" class="box">';
	$ret .= '<h3>'.$orgInfo->info->name.'</h3>';
	$ret .= 'ที่อยู่ '.$orgInfo->info->address.'<br />';
	$ret .= 'โทรศัพท์ '.$orgInfo->info->phone.'<br />';
	$ret .= 'แฟกซ์ '.$orgInfo->info->fax.'<br />';
	$ret .= 'อีเมล์ '.$orgInfo->info->email.'<br />';
	$ret .= 'เว็บ '.$orgInfo->info->website.'<br />';
	$ret .= 'เฟซบุ๊ค '.$orgInfo->info->facebook.'<br />';
	$ret .= '</section>';

	//$ret .= print_o($orgInfo,'$orgInfo');

	return $ret;
}
?>