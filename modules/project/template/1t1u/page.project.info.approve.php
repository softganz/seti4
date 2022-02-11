<?php
/**
* Project :: Report Approve Information
* Created 2020-01-26
* Modify  2020-01-26
*
* @param Object $self
* @param Object $projectInfo
* @param Int $period
* @return String
*
* @usage project/{id}/info.approve/{period}
*/

$debug = true;

function project_info_approve($self, $projectInfo, $period = NULL) {
	// Data Model
	$getChild = post('child');

	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$isEdit = $projectInfo->right->isAdmin || $projectInfo->right->isOwner;

	if (!$isEdit) return message('error', 'ขออภัย ท่านไม่สามารถใช้งานเมนูนี้ได้');

	$childInfo = R::Model('project.get', $getChild);

	if ($childInfo->info->parent != $projectInfo->projectId) return message('error', 'ขออภัย ไม่ใช่โครงการภายใต้ความรับผิดชอบของท่าน');

	$periodInfo = R::Model('project.period.get', $childInfo->projectId, $period);



	// View Model
	$ret = '<header class="header"><h3>Monthly Report Approve</h3></header>';

	$ui = new Ui(NULL, 'ui-card');

	$ui->add(
		'<div class="header">ผู้รับรองคนที่ 1</div>'
		. '<div class="detail -sg-text-right"><a class="btn -primary sg-action" href="'.url('project/'.$projectId.'/info/send.approved/'.$period, array('child' => $getChild)).'" data-rel="notify" data-done="close | load" data-title="รับรองรายงาน" data-confirm="รับรองรายงาน กรุณายืนยัน?"><i class="icon -material">done</i><span>รับรอง</span></a></div>'
	);

	$ui->add(
		'<div class="header">ผู้รับรองคนที่ 2</div>'
		. '<div class="detail -sg-text-right"><a class="btn -primary sg-action" href="'.url('project/'.$projectId.'/info/send.approved/'.$period, array('child' => $getChild)).'" data-rel="notify" data-done="close | load" data-title="รับรองรายงาน" data-confirm="รับรองรายงาน กรุณายืนยัน?"><i class="icon -material">done</i><span>รับรอง</span></a></div>'
	);

	$ui->add(
		'<div class="header">ผู้รับรองคนที่ 3</div>'
		. '<div class="detail -sg-text-right"><a class="btn -primary sg-action" href="'.url('project/'.$projectId.'/info/send.approved/'.$period, array('child' => $getChild)).'" data-rel="notify" data-done="close | load" data-title="รับรองรายงาน" data-confirm="รับรองรายงาน กรุณายืนยัน?"><i class="icon -material">done</i><span>รับรอง</span></a></div>'
	);

	$ret .= $ui->build();

	return $ret;
}
?>