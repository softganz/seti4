<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_pocenter_setting($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.pocenter.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	R::View('imed.toolbar', $self, $orgInfo->name.' @ศูนย์กายอุปกรณ์', 'pocenter', $orgInfo);

	if (!$orgInfo) return message('error', 'ไม่มีข้อมูลตามที่ระบุ');

	$isAdmin = user_access('administer imeds')
		|| $orgInfo->RIGHT & _IS_ADMIN;


	$ret = '';

	if (!$isAdmin) {
		return $ret;
	}


	$ui = new Ui(NULL,'ui-nav -sg-text-center');

	//$ui->add('<a class="btn sg-action" href="'.url('imed/pocenter/'.$orgId.'/setting.officer').'" data-rel="box" data-width="512"><i class="icon -material">supervisor_account</i><span>เจ้าหน้าที่</span></a>');

	$ui->add('<a class="btn" href="'.url('imed/pocenter/'.$orgId.'/setting.officer').'"><i class="icon -material">supervisor_account</i><span>เจ้าหน้าที่</span></a>');

	$ret .= '<nav class="nav -page -icons">'.$ui->build().'</nav>';
	//$ret .= print_o($orgInfo,'$orgInfo');

	$ret .= '<style type="text/css">
	.nav.-icons .ui-item>a.btn {padding: 16px;}
	</style>';
	return $ret;
}
?>