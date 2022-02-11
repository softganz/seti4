<?php
/**
* Organization type of disabled
*
* @param Object $orgInfo
* @return String
*/

$debug = true;

function view_org_disabledclub_info($orgInfo = NULL) {
	$ret = '';

	$orgid = $orgInfo->orgid;

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isOfficer = $orgInfo->RIGHT & _IS_OFFICER;

	$ret .= '<h3>ยินดีต้อนรับสู่ "'.$orgInfo->name.'"</h3>';

	$ret .= '<p class="-sg-text-center"><a class="btn" href="'.url('org/disabledclub/'.$orgid.'/member',array('o'=>'id')).'"><i class="icon -people"></i>รายชื่อสมาชิก</span></a></p>';
	//$ret .= print_o($orgInfo, '$orgInfo');
	return $ret;
}
?>