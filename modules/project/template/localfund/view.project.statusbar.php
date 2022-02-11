<?php
/**
* Module Method
* Created 2020-08-01
* Modify  2020-08-01
*
* @param 
* @return String
*/

$debug = true;

function view_project_statusbar($projectInfo) {
	$tpid = $projectInfo->tpid;

	$paidDocs = R::Model('project.paiddoc.get', $tpid, NULL, NULL, '{getAllRecord: true, debug: false}');
	$torInfo = R::Model('project.tor.get',$tpid);
	$finalReportTitle = project_model::get_tr($tpid,'finalreport:title');

	$ui = new Ui(NULL,'ui-statusbar');
	$ui->addConfig('nav', '{class: "nav project-view-statusbar -no-print"}');
	$ui->add('<a class="status -s1 -active" href="javascript:void(0)" data-tooltip="ติดตามโครงการ"><i class="icon -material">directions_run</i></a>');
	$ui->add('<a class="status -s2'.($torInfo->torId ? ' -active' : '').'" href="'.url('project/'.$tpid.'/info.tor').'" data-tooltip="TOR"><i class="icon -material">beenhere</i></a>');
	$ui->add('<a class="status -s3'.($paidDocs ? ' -active' : '').'" href="'.url('project/'.$tpid.'/info.paiddoc').'" data-tooltip="เบิกเงิน"><i class="icon -material">attach_money</i></a>');
	$ui->add('<a class="status -s4'.($projectInfo->info->performance ? ' -active' : '').'" href="'.url('project/'.$tpid.'/info.summary').'" data-tooltip="สรุปโครงการ"><i class="icon -material">assessment</i></a>');
	$ui->add('<a class="sg-action status -s5'.($projectInfo->info->project_status != 'กำลังดำเนินโครงการ' ? ' -active' : '').'" href="'.url('project/'.$tpid.'/info.status').'" data-rel="box" data-width="480" data-tooltip="ปิดโครงการ"><i class="icon -material">lock</i></a>');

	return $ui;
}
?>