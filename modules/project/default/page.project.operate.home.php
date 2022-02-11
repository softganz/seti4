<?php
/**
* Project :: Follow Operate Information
* Created 2020-06-04
* Modify  2021-10-27
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/operate
*/

import('widget:project.info.appbar.php');

$debug = true;

class ProjectOperateHome extends Page {
	var $projectId;
	var $action;
	var $projectInfo;

	function __construct($projectInfo, $action = NULL) {
		$this->projectId = $projectInfo->projectId;
		$this->action = $action;
		$this->projectInfo = $projectInfo;
		$this->right = (Object) [
			'admin' => $this->projectInfo->RIGHT & _IS_ADMIN,
			'edit' => $this->projectInfo->RIGHT & _IS_EDITABLE,
		];
	}

	function build() {
		if (!$this->projectId) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

		$projectInfo = $this->projectInfo;

		$isViewOnly = $this->action == 'view';
		$isEditable = $projectInfo->info->isRight;
		$isEdit = $projectInfo->info->isRight;

		$ret .= '<h2 class="title -main">รายงานงวด</h2>';

		if ($isEdit) {
			$inlineAttr['class'] = 'sg-inline-edit ';
			$inlineAttr['data-tpid'] = $this->projectId;
			$inlineAttr['data-update-url'] = url('project/edit/tr');
			if (post('debug')) $inlineAttr['data-debug'] = 'yes';
		}
		$inlineAttr['class'] .= 'project-result';

		$ret.='<div id="project-result" '.sg_implode_attr($inlineAttr).'>'._NL;

		$ret .= '<section class="project-operate box">';
		$ret .= '<h3 class="title -sub1">รายงานการเงินประจำงวด</h3>';


		$periodInfo = project_model::get_period($this->projectId);
		$lastPeriod = 0;
		$lastPeriodLock = _PROJECT_LOCKREPORT;
		if ($periodInfo) {
			$ret.='<div class="container">';
			foreach ($periodInfo as $item) {
				if (is_null($item->flag)) break;

				$retStatus.='<div id="project-m1-status" class="row project-m1-status">'._NL;
				$retStatus.='<div class="col -md-2 -title"><a class="project-report-status -status-'.$item->flag.'" href="'.url('project/'.$this->projectId.'/operate.m1/'.$item->period).'">รายงาน<br />การเงิน<br /><b>งวดที่ '.$item->period.'</b></a></div>'._NL;

				$retStatus.='<div class="col -md-2 -status-1 -pass"><span class="status-no">1</span><span class="status-text">เริ่มทำรายงาน</span></div>'._NL;
				$retStatus.='<div class="col -md-2 -status-2'.($item->flag>=_PROJECT_COMPLETEPORT?' -pass':'').'"><span class="status-no">2</span><span class="status-text">ส่งรายงานจากพื้นที่</span></div>'._NL;
				$retStatus.='<div class="col -md-2 -status-3'.($item->flag>=_PROJECT_LOCKREPORT?' -pass':'').'"><span class="status-no">3</span><span class="status-text">ผ่านการตรวจสอบของพี่เลี้ยงโครงการ</span></div>'._NL;
				$retStatus.='<div class="col -md-2 -status-4'.($item->flag>=_PROJECT_PASS_HSMI?' -pass':'').'"><span class="status-no">4</span><span class="status-text">ผ่านการตรวจสอบของ '.cfg('project.grantpass').'</span></div>'._NL;
				$retStatus.='<div class="col -md-2 -status-5'.($item->flag>=_PROJECT_PASS_SSS?' -pass':'').'"><span class="status-no">5</span><span class="status-text">ผ่านการตรวจสอบของ '.cfg('project.grantby').'</span></div>'._NL;
				$retStatus.='<br clear="all" />';
				$retStatus.='</div><!-- row -->'._NL;

				$lastPeriod=$item->period;
				$lastPeriodLock=$item->flag;
			}
			$nextPeriod=$lastPeriod+1;
			$ret.=$retStatus;
			$ret.='</div><!-- container -->';
			//			if ($isEdit && $lastPeriodLock>=_PROJECT_LOCKREPORT) $ret.=' หรือ <a class="button" href="'.url('paper/'.$this->projectId.'/owner/m1/period/'.$nextPeriod,'action=create').'" confirm="ยืนยันการสร้างรายงาน '.$formid.' งวดที่ '.$nextPeriod.' ?">สร้างรายงาน '.$formid.' งวดที่ '.$nextPeriod.'</a>';

			if ($isEdit && $lastPeriodLock>=_PROJECT_LOCKREPORT && $nextPeriod <= count($periodInfo)) {
				$ret.='<nav class="nav -sg-text-right"><a class="sg-action btn -primary" href="'.url('project/'.$this->projectId.'/operate/m1create/'.$nextPeriod).'" data-confirm="ต้องการสร้างรายงานการเงิน งวดที่ '.$nextPeriod.' กรุณายืนยัน?"><i class="icon -addbig -white"></i><span>สร้างรายงานการเงิน งวดที่ '.$nextPeriod.'</span></a></nav>';
			}

		} else {
			$ret.='<p>คำเตือน : โครงการนี้ยังไม่มีการการกำหนดงวด <a href="'.url('project/'.$this->projectId).'">กรุณากำหนดงวด</a> '.$nextPeriod.' ของโครงการใน<a href="'.url('project/'.$this->projectId).'">รายละเอียดโครงการ</a>ก่อน !!!</p>';
		}

		$ret .= '</section><!-- project-operate -->';






		$ret .= '<section class="project-operate box">';
		$ret .= '<h3 class="title -sub1">รายงานผลงานประจำงวด</h3>';

		$currentReport=mydb::select('SELECT `period`, `flag`, COUNT(*) reportItems FROM %project_tr% tr WHERE tr.`tpid`=:tpid AND tr.`formid`=:formid GROUP BY `period`',':tpid',$this->projectId,':formid','ส.1');
		$allReport=$currentReport->_num_rows;
		//$ret .= print_o($currentReport);

		$periodInfo=project_model::get_period($this->projectId);
		$lastPeriod=0;
		$lastPeriodLock=_PROJECT_LOCKREPORT;
		$ret.='<div class="container">';
		foreach ($currentReport->items as $item) {
			if (empty($item->flag)) $item->flag = 0;


			$ret.='<div id="project-m1-status" class="row project-m1-status">'._NL;
			$ret.='<div class="col -md-2 -title"><a class="project-report-status -status-'.$item->flag.'" href="'.url('project/'.$this->projectId.'/operate.result/'.$item->period).'">รายงาน<br />ผลงาน<br /><b>งวดที่ '.$item->period.'</b></a></div>'._NL;
			//$ret.='<div class="col -md-2 -title"><a class="project-report-status -status-'.$item->flag.'" href="'.url('project/'.$this->projectId.'/operate/'.$item->period).'">รายงาน<br />ผลงาน<br /><b>งวดที่ '.$item->period.'</b></a></div>'._NL;

			$ret.='<div class="col -md-2 -status-1 -pass"><span class="status-no">1</span><span class="status-text">เริ่มทำรายงาน</span></div>'._NL;
			$ret.='<div class="col -md-2 -status-2'.($item->flag>=_PROJECT_COMPLETEPORT?' -pass':'').'"><span class="status-no">2</span><span class="status-text">ส่งรายงานจากพื้นที่</span></div>'._NL;
			$ret.='<div class="col -md-2 -status-3'.($item->flag>=_PROJECT_LOCKREPORT?' -pass':'').'"><span class="status-no">3</span><span class="status-text">ผ่านการตรวจสอบของพี่เลี้ยงโครงการ</span></div>'._NL;
			$ret.='<div class="col -md-2 -status-4'.($item->flag>=_PROJECT_PASS_HSMI?' -pass':'').'"><span class="status-no">4</span><span class="status-text">ผ่านการตรวจสอบของ '.cfg('project.grantpass').'</span></div>'._NL;
			$ret.='<div class="col -md-2 -status-5'.($item->flag>=_PROJECT_PASS_SSS?' -pass':'').'"><span class="status-no">5</span><span class="status-text">ผ่านการตรวจสอบของ '.cfg('project.grantby').'</span></div>'._NL;
			$ret.='<br clear="all" />';
			$ret.='</div><!-- row -->'._NL;

			$lastPeriod=$item->period;
			$lastPeriodLock=$item->flag;
		}
		$nextPeriod=$lastPeriod+1;
		$ret.='</div><!-- container -->';
		//			if ($isEdit && $lastPeriodLock>=_PROJECT_LOCKREPORT) $ret.=' หรือ <a class="button" href="'.url('paper/'.$this->projectId.'/owner/m1/period/'.$nextPeriod,'action=create').'" confirm="ยืนยันการสร้างรายงาน '.$formid.' งวดที่ '.$nextPeriod.' ?">สร้างรายงาน '.$formid.' งวดที่ '.$nextPeriod.'</a>';

		if ($isEdit && $nextPeriod <= count($periodInfo)) {
			$ret.='<nav class="nav -sg-text-right"><a class="sg-action btn -primary" href="'.url('project/'.$this->projectId.'/operate/createresult/'.$nextPeriod).'" data-confirm="ต้องการสร้างรายงานผลงาน งวดที่ '.$nextPeriod.' กรุณายืนยัน?"><i class="icon -addbig -white"></i><span>สร้างรายงานผลงานประจำงวด งวดที่ '.$nextPeriod.'</span></a></nav>';
		}

		$ret .= '</section><!-- project-operate -->';



		// Final Money Report
		$item = (Object) ['flag' => 0];
		$ret .= '<section class="project-operate box">';
		$ret .= '<h3 class="title -sub1">รายงานสรุปการเงินโครงการ</h3>';
		$ret.='<div class="container">';
		$ret.='<div id="project-m1-status" class="row project-m1-status">'._NL;
		$ret.='<div class="col -md-2 -title"><a class="project-report-status -status-'.$item->flag.'" href="'.url('project/'.$this->projectId.'/operate.m2').'">รายงานสรุป<br />การเงิน<br /><b>ปิดโครงการ</b></a></div>'._NL;

		$ret.='<div class="col -md-2 -status-1 -pass"><span class="status-no">1</span><span class="status-text">เริ่มทำรายงาน</span></div>'._NL;
		$ret.='<div class="col -md-2 -status-2'.($item->flag>=_PROJECT_COMPLETEPORT?' -pass':'').'"><span class="status-no">2</span><span class="status-text">ส่งรายงานจากพื้นที่</span></div>'._NL;
		$ret.='<div class="col -md-2 -status-3'.($item->flag>=_PROJECT_LOCKREPORT?' -pass':'').'"><span class="status-no">3</span><span class="status-text">ผ่านการตรวจสอบของพี่เลี้ยงโครงการ</span></div>'._NL;
		$ret.='<div class="col -md-2 -status-4'.($item->flag>=_PROJECT_PASS_HSMI?' -pass':'').'"><span class="status-no">4</span><span class="status-text">ผ่านการตรวจสอบของ '.cfg('project.grantpass').'</span></div>'._NL;
		$ret.='<div class="col -md-2 -status-5'.($item->flag>=_PROJECT_PASS_SSS?' -pass':'').'"><span class="status-no">5</span><span class="status-text">ผ่านการตรวจสอบของ '.cfg('project.grantby').'</span></div>'._NL;
		$ret.='<br clear="all" />';
		$ret.='</div><!-- row -->'._NL;
		$ret.='</div><!-- container -->'._NL;

		$ret .= '</section><!-- project-operate -->';



		// Final Report
		$item->flag = 0;
		$ret .= '<section class="project-operate box">';
		$ret .= '<h3 class="title -sub1">รายงานฉบับสมบูรณ์</h3>';
		$ret.='<div class="container">';
		$ret.='<div id="project-m1-status" class="row project-m1-status">'._NL;
		$ret.='<div class="col -md-2 -title"><a class="project-report-status -status-'.$item->flag.'" href="'.url('project/'.$this->projectId.'/finalreport').'">รายงาน<br /><b>ฉบับสมบูรณ์</b></a></div>'._NL;

		$ret.='<div class="col -md-2 -status-1 -pass"><span class="status-no">1</span><span class="status-text">เริ่มทำรายงาน</span></div>'._NL;
		$ret.='<div class="col -md-2 -status-2'.($item->flag>=_PROJECT_COMPLETEPORT?' -pass':'').'"><span class="status-no">2</span><span class="status-text">ส่งรายงานจากพื้นที่</span></div>'._NL;
		$ret.='<div class="col -md-2 -status-3'.($item->flag>=_PROJECT_LOCKREPORT?' -pass':'').'"><span class="status-no">3</span><span class="status-text">ผ่านการตรวจสอบของพี่เลี้ยงโครงการ</span></div>'._NL;
		$ret.='<div class="col -md-2 -status-4'.($item->flag>=_PROJECT_PASS_HSMI?' -pass':'').'"><span class="status-no">4</span><span class="status-text">ผ่านการตรวจสอบของ '.cfg('project.grantpass').'</span></div>'._NL;
		$ret.='<div class="col -md-2 -status-5'.($item->flag>=_PROJECT_PASS_SSS?' -pass':'').'"><span class="status-no">5</span><span class="status-text">ผ่านการตรวจสอบของ '.cfg('project.grantby').'</span></div>'._NL;
		$ret.='<br clear="all" />';
		$ret.='</div><!-- row -->'._NL;
		$ret.='</div><!-- container -->'._NL;

		$ret .= '</section><!-- project-operate -->';

		$ret.='</div><!-- project-result -->';

		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => new Widget([
				'children' => [
					$ret,
				], // children
			]), // Widget
		]);
	}
}
?>