<?php
/**
* Project : Follow Dashboard for App
* Created 2021-02-26
* Modify  2021-02-26
*
* @param Object $self
* @param Object $projectInfo
* @return String
*
* @usage project/{id}/info.dashboard_app
*/

$debug = true;

function project_info_dashboard_app($self, $projectInfo) {
	// Data Model
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$isAdmin = $projectInfo->right->isAdmin;
	$isEdit = $isAdmin || $projectInfo->right->isOwner;

	if (!$isEdit) return message('error', 'Access Denied');

	// View Model
	$toolbar = new Toolbar($self, $projectInfo->title);

	$mainUi = new Ui();
	$mainUi->addConfig('nav', '{class: "nav -app-menu"}');

	$mainUi->header('<h3>ทั่วไป</h3>');

	$ret .= $mainUi->build();

	$reportUi = new Ui();
	$reportUi->addConfig('nav', '{class: "nav -app-menu"}');

	$reportUi->header('<h3>รายงาน</h3>');

	$ret .= $reportUi->build();

	$otherUi = new Ui();
	$otherUi->addConfig('nav', '{class: "nav -app-menu"}');

	$otherUi->header('<h3>อื่นๆ</h3>');
	if ($projectInfo->info->ownertype == _PROJECT_OWNERTYPE_UNIVERSITY) {
		$otherUi->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info.employee.withdraw.report').'" data-webview="จ่ายค่าจ้างประจำเดือน"><i class="icon -material">local_atm</i><span>จ่ายค่าจ้างประจำเดือน</span></a>');
		$otherUi->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info.child.bank').'" data-webview="เลขบัญชีผู้รับจ้าง"><i class="icon -material">price_check</i><span>เลขบัญชีผู้รับจ้าง</span></a>');
	}


	if ($isAdmin) {
		$otherUi->add('<a href="'.url('project/'.$projectId).'"><i class="icon -material">find_in_page</i><span>รายละเอียดโครงการ</span></a>');
	}
	$ret .= $otherUi->build();

	return $ret;
}
?>