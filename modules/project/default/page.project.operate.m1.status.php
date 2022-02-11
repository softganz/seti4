<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_operate_m1_status($self, $tpid, $period = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get', $tpid, '{initTemplate:true}');

	$tpid = $projectInfo->tpid;

	$formid='ง.1';

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo,'{showPrint: true}');

	if (empty($period)) return message('error', 'ไม่ระบุงวด');

	$statusText = [
		_PROJECT_DRAFTREPORT=>'เริ่มทำรายงาน',
		_PROJECT_COMPLETEPORT=>'ส่งรายงานจากพื้นที่',
		_PROJECT_LOCKREPORT=>'ผ่านการตรวจสอบของพี่เลี้ยงโครงการ',
		_PROJECT_PASS_HSMI=>'ผ่านการตรวจสอบของผู้จัดการโครงการ',
		_PROJECT_PASS_SSS=>'ผ่านการตรวจสอบของผู้ให้ทุน'
	];

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isOwner = $projectInfo->RIGHT & _IS_OWNER;
	$isTrainer = $projectInfo->RIGHT & _IS_TRAINER;
	$isTeam = $isAdmin || $isOwner || $isTrainer;
	$isEdit = $projectInfo->info->project_statuscode == 1 && $isTeam;

	$periodInfo=project_model::get_period($tpid,$period);
	$locked=$periodInfo->flag>=_PROJECT_LOCKREPORT;

	$ret = '';

	$ret .= '<h3 class="title -box">สถานะรายงาน</h3>';

	$url = 'project/'.$tpid.'/operate/m1note/'.$period;
	$passUrl = 'project/info/api/'.$tpid.'/financial.period.status/'.$period;

	$ret.='<div id="project-report-status" class="project-m1-status">';

	$ret.='<div class="ui-item -status-1 -pass"><span class="status-no">1</span><h5>เริ่มทำรายงาน</h5>'.($isTeam?'<div class="note">'.($isOwner?'<form class="sg-form" method="post" action="'.url($url).'" data-rel="notify"><input type="hidden" name="note" value="note_owner" /><textarea class="form-textarea -fill" name="msg" placeholder="เขียนบันทึกความคิดเห็น">'.$periodInfo->note_owner.'</textarea><nav class="nav -sg-text-right"><button class="btn -primary"><i class="icon -save -white"></i><span>บันทึก</span></button></nav></form>' : sg_text2html(SG\getFirst($periodInfo->note_owner,'ไม่มีความเห็น'))).'</div>':'').'</div>'._NL;

	$ret.='<div class="ui-item -status-2'.($periodInfo->flag>=_PROJECT_COMPLETEPORT?' -pass':'').'"><span class="status-no">2</span><h5>ส่งรายงานจากพื้นที่</h5>'.($isTeam?'<div class="note">'.($isOwner?'<form class="sg-form" method="post" action="'.url($url).'" data-rel="notify"><input type="hidden" name="note" value="note_complete" /><textarea class="form-textarea -fill" name="msg" placeholder="เขียนบันทึกความคิดเห็น">'.$periodInfo->note_complete.'</textarea><button class="btn -primary"><i class="icon -save -white"></i><span>บันทึก</span></button></form>' : sg_text2html(SG\getFirst($periodInfo->note_complete,'ไม่มีความเห็น'))).'</div>':'').''.($isEdit && $periodInfo->flag<_PROJECT_PASS_HSMI?'<nav class="nav -sg-text-right"><a class="sg-action project-button -send" href="'.url($passUrl, ['step'=>_PROJECT_COMPLETEPORT]).'" data-rel="notify" data-done="load | close">ส่งรายงานจากพื้นที่</a></nav>':'').'</div>'._NL;

	if (projectcfg::enable('trainer')) $ret.='<div class="ui-item -status-3'.($periodInfo->flag>=_PROJECT_LOCKREPORT?' -pass':'').'"><span class="status-no">3</span><h5>ผ่านการตรวจสอบของพี่เลี้ยงโครงการ</h5>'.($isTeam?'<div class="note">'.($isTrainer?'<form class="sg-form" method="post" action="'.url($url).'" data-rel="notify"><input type="hidden" name="note" value="note_trainer" /><textarea class="form-textarea -fill" name="msg" placeholder="เขียนบันทึกความคิดเห็น">'.$periodInfo->note_trainer.'</textarea><button class="btn -primary"><i class="icon -save -white"></i><span>บันทึก</span></button></form>':sg_text2html(SG\getFirst($periodInfo->note_trainer,'ไม่มีความเห็น'))).'</div>':'').(($isTrainer || $isAdmin) && $periodInfo->flag<_PROJECT_PASS_HSMI?'<nav class="nav -sg-text-right"><a class="sg-action project-button -pass" href="'.url($passUrl,array('step'=>_PROJECT_LOCKREPORT)).'" data-rel="refresh" data-done="close">ผ่านการตรวจสอบ</a>':'').'</nav></div>'._NL;

	$ret.='<div class="ui-item -status-4'.($periodInfo->flag>=_PROJECT_PASS_HSMI?' -pass':'').'"><span class="status-no">4</span><h5>ผ่านการตรวจสอบของผู้จัดการโครงการ</h5>'.($isTeam?'<div class="note">'.($isAdmin?'<form class="sg-form" method="post" action="'.url($url).'" data-rel="notify"><input type="hidden" name="note" value="note_hsmi" /><textarea class="form-textarea -fill" name="msg" placeholder="เขียนบันทึกความคิดเห็น">'.$periodInfo->note_hsmi.'</textarea><button class="btn -primary"><i class="icon -save -white"></i><span>บันทึก</span></button></form>':sg_text2html($periodInfo->note_hsmi)).'</div>':'').($isAdmin?'<nav class="nav -sg-text-right"><a class="sg-action project-button -pass" href="'.url($passUrl,array('step'=>_PROJECT_PASS_HSMI)).'" data-rel="refresh" data-done="close">ผ่านการตรวจสอบ</a><a class="sg-action project-button -reject" href="'.url($passUrl,array('step'=>_PROJECT_DRAFTREPORT)).'" data-rel="refresh" data-done="close">แก้ไข</a></nav>':'').'</div>'._NL;

	$ret.='<div class="ui-item -status-5'.($periodInfo->flag>=_PROJECT_PASS_SSS?' -pass':'').'"><span class="status-no">5</span><h5>ผ่านการตรวจสอบของผู้ให้ทุน</h5>'.($isTeam?'<div class="note">'.($isAdmin?'<form class="sg-form" method="post" action="'.url($url).'" data-rel="notify"><input type="hidden" name="note" value="note_sss" /><textarea class="form-textarea -fill" name="msg" placeholder="เขียนบันทึกความคิดเห็น">'.$periodInfo->note_sss.'</textarea><button class="btn -primary"><i class="icon -save -white"></i><span>บันทึก</span></button></form>':sg_text2html($periodInfo->note_sss)).'</div>':'').($isAdmin?'<nav class="nav -sg-text-right"><a class="sg-action project-button -pass" href="'.url($passUrl,array('step'=>_PROJECT_PASS_SSS)).'" data-rel="refresh" data-done="close">ผ่านการตรวจสอบ</a><a class="sg-action project-button -reject" href="'.url($passUrl,array('step'=>_PROJECT_DRAFTREPORT)).'" data-rel="refresh" data-done="close">แก้ไข</a></nav>':'').'</div>'._NL;

	$ret.='</div><!-- container -->';
	$ret .= '<style type="text/css">
	.project-m1-status .nav {margin:16px;}
	</style>';
	return $ret;
}
?>