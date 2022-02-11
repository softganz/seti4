<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_app_social_menu($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.social.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isMember = $orgInfo->is->socialtype;
	$isGroupAdmin = $isAdmin  || in_array($isMember,array('ADMIN','MODERATOR'));

	if (!($isAdmin || $isMember)) return message('error','ขออภัย ท่านไม่ได้อยู่ในกลุ่มนี้');

	R::View('imed.toolbar', $self, $orgInfo->name, 'app.social', $orgInfo);

	$mainUi = new Ui(NULL, 'ui-menu -report');

	$mainUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/careplan/list').'" data-rel="#main" data-webview="Care Plan List"><i class="icon -material">view_list</i><span>{tr:Care Plan List}</span></a>');

	$mainUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/child',array('ref'=>'app')).'" data-webview="หน่วยงานในสังกัด"><i class="icon -material">assessment</i><span>หน่วยงานในสังกัด</span></a>');

	if ($isGroupAdmin) {
		$mainUi->add('<sep>');
		$mainUi->add('<h3>รายงาน</h3>');
		$mainUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/report.visit.summary',array('ref'=>'app')).'" data-webview="รายงานสรุปผลปฎิบัติงานประจำเดือน"><i class="icon -material">assessment</i><span>รายงานสรุปผลปฎิบัติงานประจำเดือน</span></a>');
		$mainUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/report.visit.month',array('ref'=>'app')).'" data-webview="รายงานรายละเอียดผลปฎิบัติงาน"><i class="icon -material">assessment</i><span>รายงานรายละเอียดผลปฎิบัติงาน</span></a>');

		$mainUi->add('<sep>');

		$mainUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/setting').'" data-rel="#main" data-webview="Settings"><i class="icon -material">settings</i><span>{tr:Settings}</span></a>');
	}



	$ret .= $mainUi->build();

	$ret .= '<style type="text/css">
	.ui-menu.-report {flex: 1 0 100%;}
	</style>';

	return $ret;
}
?>