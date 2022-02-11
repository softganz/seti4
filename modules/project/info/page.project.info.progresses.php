<?php
/**
* Project :: Progresses Report List
* Created 2022-01-05
* Modify  2022-01-05
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.progresses
*/

import('model:project.follow.php');
import('widget:project.follow.nav.php');

class ProjectInfoProgresses extends Page {
	var $projectId;
	var $period;
	var $lastPeriodReport;
	var $right;
	var $projectInfo;

	function __construct($projectInfo) {
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

		$periodInfo = ProjectFollowModel::getProgressReport($this->projectId);

		if (!$periodInfo) return '<p>โครงการนี้ยังไม่มีการการกำหนดงวด <a href="'.url('project/'.$this->projectId).'">กรุณากำหนดงวด</a> ของโครงการใน<a href="'.url('project/'.$this->projectId).'">รายละเอียดโครงการ</a>ก่อน !!!</p>';

		// debugMsg($periodInfo, '$periodInfo');

		$this->lastPeriodReport = 0;
		foreach ($periodInfo as $period) {
			if ($period->progressTranId) $this->lastPeriodReport = $period->period;
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
				'leading' => '<i class="icon -material">task_alt</i>',
				'navigator' => new ProjectFollowNavWidget($this->projectInfo),
			]),
			'body' => new Widget([
				'children' => [
					new Container([
						'class' => 'project-operate',
						'children' => array_map(
							function ($period) {
								return new Card([
									'children' => [
										new ListTile([
											'crossAxisAlignment' => 'start',
											'title' => 'รายงานผลงานประจำงวดที่ '.$period->period,
											'leading' => '<i class="icon -material">task_alt</i>',
											'subtitle' => sg_date($period->fromDate).' - '.sg_date($period->toDate)
										]),

										// Show Period Information
										is_null($period->progressTranId) ? new Widget([
											'child' => 'ยังไม่มีการสร้างรายงานผลงานประจำงวดที่ '.$period->period,
											]) : new Widget([
											'children' => [
												new Row([
													'class' => 'project-m1-status',
													'children' => [
														'<div class="col -md-2 -status-1 -pass"><span class="status-no">1</span><span class="status-text">เริ่มทำรายงาน</span></div>',
														'<div class="col -md-2 -status-2'.($period->financialStatus>=_PROJECT_COMPLETEPORT?' -pass':'').'"><span class="status-no">2</span><span class="status-text">ส่งรายงานจากพื้นที่</span></div>',
														'<div class="col -md-2 -status-3'.($period->financialStatus>=_PROJECT_LOCKREPORT?' -pass':'').'"><span class="status-no">3</span><span class="status-text">ผ่านการตรวจสอบของพี่เลี้ยงโครงการ</span></div>',
														'<div class="col -md-2 -status-4'.($period->financialStatus>=_PROJECT_PASS_HSMI?' -pass':'').'"><span class="status-no">4</span><span class="status-text">ผ่านการตรวจสอบของ'.cfg('project.grantpass').'</span></div>',
														'<div class="col -md-2 -status-5'.($period->financialStatus>=_PROJECT_PASS_SSS?' -pass':'').'"><span class="status-no">5</span><span class="status-text">ผ่านการตรวจสอบของ'.cfg('project.grantby').'</span></div>',
													],
												]), // Row
												new Nav([
													'mainAxisAlignment' => 'end',
													'class' => '-sg-paddingnorm',
													'child' => '<a class="btn -primary" href="'.url('project/'.$this->projectId.'/info.progress/'.$period->period).'"><i class="icon -material">description</i><span>รายละเอียด</span></a>',
												]), // Nav
											], // children
										]), // Container

										// Show Create Button
										is_null($period->progressTranId) && $this->right->edit && $period->period == $this->lastPeriodReport + 1 ? new Nav([
											'mainAxisAlignment' => 'end',
											'class' => '-sg-paddingnorm',
											'child' => '<a class="sg-action btn -primary" href="'.url('project/info/api/'.$this->projectId.'/progress.create/'.$period->period).'" data-rel="notify" data-done="reload:'.url('project/'.$this->projectId.'/info.progress/'.$period->period).'" data-title="สร้างรายงานผลงาน งวดที่ '.$period->period.'" data-confirm="ต้องการสร้างรายงานผลงาน งวดที่ '.$period->period.' กรุณายืนยัน?"><i class="icon -addbig -white"></i><span>สร้างรายงานผลงาน งวดที่ '.$period->period.'</span></a>',
										]) : NULL,

										// new DebugMsg($period, '$period'),
										// new DebugMsg($this->right, '$this->right'),
									],
								]);
							},
							$periodInfo
						), // children
					]), // Widget
					$this->_script(),
				], // children
			]), // Widget
		]);
	}

	function _script() {
		head('<style type="text/css">
		.project-m1-status>.-item {flex: 0 0 calc(20% - 4px);}
		</style>');
	}
}
?>