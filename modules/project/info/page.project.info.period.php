<?php
/**
* Project :: Follow Period Information
* Created 2021-05-31
* Modify  2022-01-06
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.period
*/

class ProjectInfoPeriod extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		$isEdit = $this->projectInfo->info->isEdit;
		$lockReportDate = $this->projectInfo->info->lockReportDate;
		$domId = 'project-info-period-'.$this->projectId;

		$maxPeriod = cfg('project.period.max');

		if ($maxPeriod <= 0) return  new Scaffold();

		// งวดสำหรับทำรายงาน

		$projectPeriod = project_model::get_period($this->projectId);

		if (!$isEdit && !$projectPeriod) return new Scaffold();

		$tables = new Table([
			'class' => 'project-info-period-items',
			'colgroup' => [
				'no'=>'align="center" width="5%"',
				'date fromdate'=>'align="center" width="20%"',
				'date todate'=>'align="center" width="20%"',
				'date rfromdate'=>'align="center" width="20%"',
				'date rtodate'=>'align="center" width="20%"',
				'money budget'=>'align="center" width="20%"',
				'align="center" width="5%"',
			],
			'thead' => '<thead><tr><th rowspan="2">งวด</th><th colspan="2">วันที่งวดโครงการ</th><th colspan="2">วันที่งวดรายงาน</th><th rowspan="2">งบประมาณ<br />(บาท)</th><th rowspan="2"></th></tr><tr><th>จากวันที่</th><th>ถึงวันที่</th><th>จากวันที่</th><th>ถึงวันที่</th></tr></thead>',
		]);

		$budgetPeriodSum = 0;

		foreach ($projectPeriod as $period) {
			unset($row);
			$isEditPeriod = $isEdit && (empty($period->report_from_date) || $period->report_to_date > $lockReportDate);

			$row[] = $period->period;
			$row[] = view::inlineedit(
				[
					'group' => 'info:period',
					'fld' => 'date1',
					'tr' => $period->trid,
					'ret' => 'date:j ดด ปปปป',
					'value' => $period->from_date ? sg_date($period->from_date,'d/m/Y') : ''
				],
				$period->from_date,
				$isEditPeriod,
				'datepicker'
			);

			$row[] = view::inlineedit(
				[
					'group' => 'info:period',
					'fld' => 'date2',
					'tr' => $period->trid,
					'ret' => 'date:j ดด ปปปป',
					'value' => $period->to_date ? sg_date($period->to_date,'d/m/Y') : ''
				],
				$period->to_date,
				$isEditPeriod,
				'datepicker'
			);

			$row[] = $period->report_from_date ? sg_date($period->report_from_date,'j ดด ปปปป') : '';

			$row[] = $period->report_to_date ? sg_date($period->report_to_date,'j ดด ปปปป') : '';

			$row[] = view::inlineedit(
				[
					'group' => 'info:period',
					'fld' => 'num1',
					'tr' => $period->trid,
					'ret' => 'money',
					'done' => 'load->replace:#'.$domId.':'.url('project/'.$this->projectId.'/info.period'),
					// 'callback' => 'refreshContent',
					// 'refresh-url' => url(q()), //url('project/'.$this->projectId)
				],
				$period->budget,
				$isEditPeriod,
				'money'
			);

			if ($isEdit && $period->period == count($projectPeriod)) {
				$row[] = '<a class="sg-action" href="'.url('project/'.$this->projectId.'/info/period.remove/'.$period->trid).'" data-rel="notify" data-done="load->replace:#'.$domId.':'.url('project/'.$this->projectId.'/info.period').'" data-title="ลบงวดรายงาน งวดที่ '.$period->period.'" data-confirm="ต้องการลบงวดรายงาน  งวดที่ '.$period->period.' กรุณายืนยัน?" title="ลบงวดสำหรับการทำรายงาน"><i class="icon -cancel -gray"></i></a>';
			} else {
				$row[] = '';
			}
			$tables->children[] = $row;
			$budgetPeriodSum += $period->budget;
		}
		$tables->tfoot[] = array('<td colspan="5" align="right"><strong>รวมงบประมาณ</strong></td>','<strong>'.number_format($budgetPeriodSum,2).'</strong>','');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
			]),
			'body' => new ScrollView([
				'id' => $domId,
				'children' => [

					$tables,

					$isEdit && count($projectPeriod) < $maxPeriod ? '<nav class="nav -page -sg-text-right"><a class="sg-action btn -primary" href="'.url('project/'.$this->projectId.'/info/period.add').'" data-rel="replace:#'.$domId.'" data-ret="'.url('project/'.$this->projectId.'/info.period').'" data-options=\'{"indicator": "กำลังเพิ่มงวด"}\'><i class="icon -addbig -white"></i><span>เพิ่มงวด</span></a></nav>' : NULL,

					$this->projectInfo->info->budget != $budgetPeriodSum ? '<p class="notify">คำเตือน : รวมงบประมาณของทุกงวด ('.number_format($budgetPeriodSum,2).' บาท) ไม่เท่ากับ งบประมาณโครงการ ('.number_format($this->projectInfo->info->budget,2).' บาท)</p>' : NULL,

				], // children
			]), // ScrollView
		]);
	}
}
?>