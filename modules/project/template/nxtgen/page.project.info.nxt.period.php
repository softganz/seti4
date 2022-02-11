<?php
/**
* Project Nxt :: Period
* Created 2021-11-02
* Modify  2021-11-02
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.nxtperiod
*/

$debug = true;

import('widget:project.follow.nav.php');

class ProjectInfoNxtPeriod extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		$isEdit = $this->projectInfo->info->isEdit && post('mode') != 'view';
		$isInEditMode = $isEdit;

		$inlineAttr = [];
		if ($isInEditMode) {
			$inlineAttr['data-tpid'] = $this->projectId;
			$inlineAttr['data-update-url'] = url('project/edit/tr/'.$this->projectId);
			$inlineAttr['data-refresh-url'] = url('project/'.$this->projectId.'/info.nxt.period', ['debug' => post('debug')]);
			if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
				'navigator' => new ProjectFollowNavWidget($this->projectInfo, ['showPrint' => true]),
			]),
			'body' => new Card([
				'class' => 'project-info'.($isInEditMode ? ' sg-inline-edit' : ''),
				'attribute' => $inlineAttr,
				'children' => [
					new ListTile([
						'class' => '-sg-paddingnorm',
						'title' => 'งวดรายงาน',
						'leading' => '<i class="icon -material">stars</i>',
					]),
					new Table([
						'children' => [
							cfg('project.option.argno') ? [
								'เลขที่ข้อตกลง',
								view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'agrno'
									],
									$this->projectInfo->info->agrno,
									false
								)
							] : NULL,

							[
								'รหัสโครงการ',
								view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'prid'
									],
									$this->projectInfo->info->prid,
									false
								)
							],

							[
								'วันที่อนุมัติ',
								view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'date_approve',
										'ret' => 'date:ว ดดด ปปปป',
										'value' => $this->projectInfo->info->date_approve ? sg_date($this->projectInfo->info->date_approve,'d/m/Y') : ''
									],
									$this->projectInfo->info->date_approve,
									$isInEditMode,
									'datepicker'
								)
								. ($isInEditMode?' <span class="form-required">*</span>':'')
							],

							$isAdmin ? [
								'ปีงบประมาณ',
								view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'pryear',
										'value' => $this->projectInfo->info->pryear,
									],
									$this->projectInfo->info->pryear+543,
									$isInEditMode,
									'select',
									(function() {
										$openYear = SG\getFirst($this->projectInfo->info->pryear,date('Y'));
										$pryearList = [];
										for ($i = $openYear-1; $i <= date('Y')+1; $i++) {
											$pryearList[$i] = $i + 543;
										}
										return $pryearList;
									})()
								).' (เฉพาะแอดมิน)'
							] : NULL,

							[
								'ระยะเวลาดำเนินงาน',
								view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'date_from',
										'ret' => 'date:ว ดดด ปปปป',
										'value' => $this->projectInfo->info->date_from ? sg_date($this->projectInfo->info->date_from,'d/m/Y') : ''
									],
									$this->projectInfo->info->date_from,
									$isInEditMode,
									'datepicker'
								)
								. ' - '
								. view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'date_end',
										'ret' => 'date:ว ดดด ปปปป',
										'value' => $this->projectInfo->info->date_end ? sg_date($this->projectInfo->info->date_end,'d/m/Y') : ''
									],
									$this->projectInfo->info->date_end,
									$isInEditMode,
									'datepicker'
								)
								.($isInEditMode ? ' <span class="form-required">*</span>' : '')
							],
							[
								'งบประมาณที่อนุมัติ',
								view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'budget',
										'ret' => 'money',
										'done' => 'load',
									],
									$this->projectInfo->info->budget,
									$isInEditMode,
									'money'
								)
								. ' บาท'.($isInEditMode ? ' <span class="form-required">*</span>' : ''),
							],
						], // children
					]), // Table

					R::PageWidget('project.info.period', [$this->projectInfo]),
				], // children
			]), // Card
		]);
	}
}
?>