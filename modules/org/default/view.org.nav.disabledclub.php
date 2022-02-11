<?php
/**
* Disabled Club Navigator
*
* @param Object $orgInfo
* @param Object $options
* @return String
*/
function view_org_nav_disabledclub($orgInfo = NULL, $options = NULL) {
	$ui = new Ui(NULL,'ui-nav -info');
	if ($orgInfo->orgid) {
		$ui->add('<a href="'.url('org/disabledclub/'.$orgInfo->orgid).'"><i class="icon -home"></i><span class="-hidden">หน้าแรก</span></a>');
		$ui->add('<a href="'.url('org/disabledclub/'.$orgInfo->orgid.'/member').'"><i class="icon -people"></i><span class="-hidden">สมาชิก</span></a>');
	} else {
		$ui->add('<a href="'.url('org/disabledclub').'"><i class="icon -home"></i><span class="-hidden">หน้าแรก</span></a>');
	}
	$ret.=$ui->build();

	return $ret;
}
?>