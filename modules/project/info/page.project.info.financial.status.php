<?php
/**
* Project :: Money Report Status
* Created 2022-01-19
* Modify  2022-01-19
*
* @param String $arg1
* @return Widget
*
* @usage project/{id}/info.sinancial.status
*/

import('model:project.follow.php');

class ProjectInfoFinancialStatus extends Page {
	var $projectId;
	var $period;
	var $right;
	var $projectInfo;

	function __construct($projectInfo, $period) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->period = $period;
		$this->right = (Object) [
			'editable' => $projectInfo->info->isRight,
			'edit' => $projectInfo->info->isRight,
		];
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		$projectId = $this->projectId;
		$projectInfo = $this->projectInfo;
		$formid = 'ง.1';
		if (empty($this->period)) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่ระบุงวด']);

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

		$periodInfo = ProjectFollowModel::getPeriod($projectId, $this->period);
		$locked = $periodInfo->flag >= _PROJECT_LOCKREPORT;

		$urlNote = 'project/'.$projectId.'/operate/m1note/'.$this->period;
		$urlStatus = 'project/info/api/'.$projectId.'/financial.period.status/'.$this->period;

		$ret.='<div id="project-report-status" class="project-m1-status">';

		$ret.='<div class="ui-item -status-1 -pass"><span class="status-no">1</span><h5>เริ่มทำรายงาน</h5>'.($isTeam?'<div class="note">'.($isOwner?'<form class="sg-form" method="post" action="'.url($urlNote).'" data-rel="notify"><input type="hidden" name="note" value="note_owner" /><textarea class="form-textarea -fill" name="msg" placeholder="เขียนบันทึกความคิดเห็น">'.$periodInfo->noteOwner.'</textarea><nav class="nav -sg-text-right"><button class="btn -primary"><i class="icon -save -white"></i><span>บันทึก</span></button></nav></form>' : sg_text2html(SG\getFirst($periodInfo->noteOwner,'ไม่มีความเห็น'))).'</div>':'').'</div>'._NL;

		$ret.='<div class="ui-item -status-2'.($periodInfo->flag>=_PROJECT_COMPLETEPORT?' -pass':'').'"><span class="status-no">2</span><h5>ส่งรายงานจากพื้นที่</h5>'.($isTeam?'<div class="note">'.($isOwner?'<form class="sg-form" method="post" action="'.url($urlNote).'" data-rel="notify"><input type="hidden" name="note" value="note_complete" /><textarea class="form-textarea -fill" name="msg" placeholder="เขียนบันทึกความคิดเห็น">'.$periodInfo->noteComplete.'</textarea><button class="btn -primary"><i class="icon -save -white"></i><span>บันทึก</span></button></form>' : sg_text2html(SG\getFirst($periodInfo->noteComplete,'ไม่มีความเห็น'))).'</div>':'').''.($isEdit && $periodInfo->flag<_PROJECT_PASS_HSMI?'<nav class="nav -sg-text-right"><a class="sg-action project-button -send" href="'.url($urlStatus, ['step'=>_PROJECT_COMPLETEPORT]).'" data-rel="notify" data-done="load | close">ส่งรายงานจากพื้นที่</a></nav>':'').'</div>'._NL;

		if (projectcfg::enable('trainer')) $ret.='<div class="ui-item -status-3'.($periodInfo->flag>=_PROJECT_LOCKREPORT?' -pass':'').'"><span class="status-no">3</span><h5>ผ่านการตรวจสอบของพี่เลี้ยงโครงการ</h5>'.($isTeam?'<div class="note">'.($isTrainer?'<form class="sg-form" method="post" action="'.url($urlNote).'" data-rel="notify"><input type="hidden" name="note" value="note_trainer" /><textarea class="form-textarea -fill" name="msg" placeholder="เขียนบันทึกความคิดเห็น">'.$periodInfo->noteTrainer.'</textarea><button class="btn -primary"><i class="icon -save -white"></i><span>บันทึก</span></button></form>':sg_text2html(SG\getFirst($periodInfo->note_trainer,'ไม่มีความเห็น'))).'</div>':'').(($isTrainer || $isAdmin) && $periodInfo->flag<_PROJECT_PASS_HSMI?'<nav class="nav -sg-text-right"><a class="sg-action project-button -pass" href="'.url($urlStatus,array('step'=>_PROJECT_LOCKREPORT)).'" data-rel="refresh" data-done="close">ผ่านการตรวจสอบ</a>':'').'</nav></div>'._NL;

		$ret.='<div class="ui-item -status-4'.($periodInfo->flag>=_PROJECT_PASS_HSMI?' -pass':'').'"><span class="status-no">4</span><h5>ผ่านการตรวจสอบของผู้จัดการโครงการ</h5>'.($isTeam?'<div class="note">'.($isAdmin?'<form class="sg-form" method="post" action="'.url($urlNote).'" data-rel="notify"><input type="hidden" name="note" value="note_hsmi" /><textarea class="form-textarea -fill" name="msg" placeholder="เขียนบันทึกความคิดเห็น">'.$periodInfo->noteManager.'</textarea><button class="btn -primary"><i class="icon -save -white"></i><span>บันทึก</span></button></form>':sg_text2html($periodInfo->noteManager)).'</div>':'').($isAdmin?'<nav class="nav -sg-text-right"><a class="sg-action project-button -pass" href="'.url($urlStatus,array('step'=>_PROJECT_PASS_HSMI)).'" data-rel="refresh" data-done="close">ผ่านการตรวจสอบ</a><a class="sg-action project-button -reject" href="'.url($urlStatus,array('step'=>_PROJECT_DRAFTREPORT)).'" data-rel="refresh" data-done="close">แก้ไข</a></nav>':'').'</div>'._NL;

		$ret.='<div class="ui-item -status-5'.($periodInfo->flag>=_PROJECT_PASS_SSS?' -pass':'').'"><span class="status-no">5</span><h5>ผ่านการตรวจสอบของผู้ให้ทุน</h5>'.($isTeam?'<div class="note">'.($isAdmin?'<form class="sg-form" method="post" action="'.url($urlNote).'" data-rel="notify"><input type="hidden" name="note" value="note_sss" /><textarea class="form-textarea -fill" name="msg" placeholder="เขียนบันทึกความคิดเห็น">'.$periodInfo->noteGranter.'</textarea><button class="btn -primary"><i class="icon -save -white"></i><span>บันทึก</span></button></form>':sg_text2html($periodInfo->noteGranter)).'</div>':'').($isAdmin?'<nav class="nav -sg-text-right"><a class="sg-action project-button -pass" href="'.url($urlStatus,array('step'=>_PROJECT_PASS_SSS)).'" data-rel="refresh" data-done="close">ผ่านการตรวจสอบ</a><a class="sg-action project-button -reject" href="'.url($urlStatus,array('step'=>_PROJECT_DRAFTREPORT)).'" data-rel="refresh" data-done="close">แก้ไข</a></nav>':'').'</div>'._NL;

		$ret.='</div><!-- container -->';
		$ret .= '<style type="text/css">
		.project-m1-status .nav {margin:16px;}
		</style>';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สถานะรายงานการเงินประจำงวด '.$this->period,
				'boxHeader' => true,
			]), // AppBar
			'body' => new Widget([
				'children' => [
					$ret
				], // children
			]), // Widget
		]);
	}
}
?>