<?php
/**
* Module Method
* Created 2019-09-01
* Modify  2019-09-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_info_status($self, $projectInfo) {
	$status = $projectInfo->info->project_status;

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo);

	$ret .= '<header class="header"><h3>สถานะโครงการ : '.$status.'</h3></header>';

	$tpid = $projectInfo->tpid;
	if (!$tpid) return message('error', 'PROCESS ERROR');

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN; 
	$isMember = $isAdmin || $projectInfo->info->membershipType;


	//'กำลังดำเนินโครงการ','ดำเนินการเสร็จสิ้น','ยุติโครงการ','ระงับโครงการ'

	// ยุติโครงการ โดย admin / owner
	$isCancle = $isAdmin;

	// ระงับโครงการ โดย Admin
	$isStop = $isAdmin;

	// ปิดโครงการ (ดำเนินการเสร็จสิ้น) โดย Admin
	$isClose = $isAdmin;

	// กำลังดำเนินโครงการ โดย Admin
	$isOpen = $isAdmin;

	$statusTextList = array(
		'กำลังดำเนินโครงการ' => 'โครงการกำลังอยู่ในระหว่างดำเนินงาน',
		'ดำเนินการเสร็จสิ้น' => 'โครงการได้ดำเนินการเรียบร้อยแล้ว พร้อมทั้งส่งรายงานการเงินและผลการดำเนินงานเสร็จสมบูรณ์',
		'ยุติโครงการ' => 'เจ้าของโครงการไม่สามารถดำเนินงานต่อไปได้ เจ้าของโครงการจึงขอหยุดการดำเนินงาน',
		'ระงับโครงการ' => 'โครงการมีปัญหาในการดำเนินงาน ผู้ให้ทุนโครงการจึงขอระงับไม่ให้ดำเนินงานต่อ',
	);

	$ui = new Ui('div', 'ui-card project-status');

	if ($isAdmin) {
		if ($status == 'กำลังดำเนินโครงการ' && $isClose) {
			$ui->add(
				'<div class="header"><h4>ปิดโครงการ</h4></div><div class="detail">'.$statusTextList['ดำเนินการเสร็จสิ้น'].'</div><nav class="nav -card -sg-text-right"><a class="sg-action btn -primary" href="'.url('project/'.$tpid.'/info/status/close').'" data-rel="notify" data-done="close | reload"><i class="icon -material">done_all</i><span>ปิดโครงการ</span></a></nav>',
				'{class: "-to-close"}'
			);

			$ui->add(
				'<div class="header"><h4>ยุติโครงการ</h4></div><div class="detail">'.$statusTextList['ยุติโครงการ'].'</div><nav class="nav -card -sg-text-right"><a class="sg-action btn -primary" href="'.url('project/'.$tpid.'/info/status/stop').'" data-rel="notify" data-done="close | reload"><i class="icon -material">cancel</i><span>ยุติโครงการ</span></a></nav>',
				'{class: "-to-stop"}'
			);

			$ui->add(
				'<div class="header"><h4>ระงับโครงการ</h4></div><div class="detail">'.$statusTextList['ระงับโครงการ'].'</div><nav class="nav -card -sg-text-right"><a class="sg-action btn -primary" href="'.url('project/'.$tpid.'/info/status/suspend').'" data-rel="notify" data-done="close | reload"><i class="icon -material">block</i><span>ระงับโครงการ</span></a></nav>',
				'{class: "-to-suspend"}'
			);

		} else {
			$ui->add(
				'<div class="header"><h4>กำลังดำเนินโครงการ</h4></div><div class="detail">'.$statusTextList['กำลังดำเนินโครงการ'].'</div><nav class="nav -card -sg-text-right"><a class="sg-action btn -primary" href="'.url('project/'.$tpid.'/info/status/open').'" data-rel="notify" data-done="close | reload"><i class="icon -material">done</i><span>กำลังดำเนินโครงการ</span></a></nav>',
				'{class: "-to-open"}'
			);
		}
	} else {
		$ui->add(
			'<div class="header"><h4>'.$status.'</h4></div><div class="detail">'.$statusTextList[$status].'</div>',
			'{class: "-to-status"}'
		);

	}

	$ret .= $ui->build();

	$ret .= '<style type="text/css">
	.project-status .nav.-card {padding-right: 8px;}
	.project-status .ui-item.-to-close {background-color: #d9ffc1;}
	.project-status .ui-item.-to-stop {background-color: #fffdc1;}
	.project-status .ui-item.-to-suspend {background-color: #ffcfcf;}
	.project-status .ui-item.-to-open {background-color: #d9ffc1;}
	</style>';
	return $ret;
}
?>