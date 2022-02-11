<?php
/**
* Project :: Financials Report List
* Created 2022-01-05
* Modify  2022-01-19
*
* @param Object $projectInfo
* @param Int $period
* @return Widget
*
* @usage project/{id}/info.financials
*/

import('model:project.follow.php');
import('widget:project.follow.nav.php');

class ProjectInfoFinancials extends Page {
	var $projectId;
	var $right;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->right = (Object) [
			'editable' => $projectInfo->info->isRight,
			'edit' => $projectInfo->info->isRight,
		];
	}

	function build() {
		if (!$this->projectId) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

		$periodInfo = ProjectFollowModel::getPeriod($this->projectId);

		if (!$periodInfo) return '<p>โครงการนี้ยังไม่มีการการกำหนดงวด <a href="'.url('project/'.$this->projectId).'">กรุณากำหนดงวด</a> ของโครงการใน<a href="'.url('project/'.$this->projectId).'">รายละเอียดโครงการ</a>ก่อน !!!</p>';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
				'leading' => '<i class="icon -material">attach_money</i>',
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
											'title' => 'รายงานการเงินประจำงวดที่ '.$period->period,
											'leading' => '<i class="icon -material">attach_money</i>',
											'subtitle' => sg_date($period->fromDate).' - '.sg_date($period->toDate)
										]),

										// Show Period Information
										is_null($period->financialStatus) ? new Widget([
											'child' => 'ยังไม่มีการสร้างรายงานการเงินประจำงวดที่ '.$period->period
											]) : new Widget([
											'children' => [
												new Row([
													'mainAxisAlignment' => 'spacearound',
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
													'child' => '<a class="btn -primary" href="'.url('project/'.$this->projectId.'/info.financial/'.$period->period).'"><i class="icon -material">description</i><span>รายละเอียด</span></a>',
												]), // Nav
											], // children
										]), // Container

										// Show Create Button
										is_null($period->financialStatus) && $this->right->edit ? new Nav([
											'mainAxisAlignment' => 'end',
											'class' => '-sg-paddingnorm',
											'child' => '<a class="sg-action btn -primary" href="'.url('project/info/api/'.$this->projectId.'/financial.period.create/'.$period->period).'" data-rel="notify" data-done="reload:'.url('project/'.$this->projectId.'/info.financial/'.$period->period).'" data-title="สร้างรายงานการเงิน งวดที่ '.$period->period.'" data-confirm="ต้องการสร้างรายงานการเงิน งวดที่ '.$period->period.' กรุณายืนยัน?"><i class="icon -addbig -white"></i><span>สร้างรายงานการเงิน งวดที่ '.$period->period.'</span></a>',
										]) : NULL,

										// new DebugMsg($period, '$period'),
										// new DebugMsg($this->right, '$this->right'),
									],
								]);
							},
							$periodInfo
						), // children
					]), // Container
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