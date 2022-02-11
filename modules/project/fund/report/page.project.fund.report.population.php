<?php
/**
* Project :: Population Report
* Created 2021-09-10
* Modify  2021-09-10
*
* @param Array $_REQUEST
* @return Widget
*
* @usage project/fund/report/population
*/

$debug = true;

import('package:project/fund/models/model.fund.php');

class ProjectFundReportPopulation extends Page {
	var $areaId;
	var $changwat;
	var $year;

	function __construct($arg1 = NULL) {
		$this->areaId = post('area');
		$this->changwat = post('changwat');
		$this->year = post('year');
	}

	function build() {
		if ($this->changwat) $population = FundModel::Population(['changwat' => $this->changwat, 'year' => $this->year]);

		// debugMsg($population, '$population');

		$result = new Table([
			'thead' => $this->export ? [
				'ชื่อกองทุน',
				'รหัสกองทุน',
				'อำเภอ',
				'จังหวัด',
				'money openbalance'=>'ยอดยกมา',
				'pop-year -amt' => 'จำนวนประชากร ปี '.($this->year + 543).'(คน)',
				'amt population'=>'จำนวนประชากร(คน)',
				'money -allocate'=>'ประมาณการณ์จำนวนเงินจัดสรรโดย สปสช.(บาท)',
				'money -local'=>'ประมาณการณ์เงินสมทบจากท้องถิ่น(คำนวณ)(บาท)',
				'%',
				'money -locaninput'=>'ประมาณการณ์เงินสมทบจากท้องถิ่น(บาท)',
				'%',
				'money -nshoinput'=>'จำนวนเงินรับจากสปสช.(บาท)',
				'money -localinput'=>'จำนวนเงินรับจากท้องถิ่น(บาท)',
				'amt'=>'โครงการ',
			] : [
				'name -nowrap' => 'ชื่อกองทุน',
				'pop-year -amt' => 'จำนวนประชากร ปี '.($this->year + 543).'<br />(คน)',
				'amt population'=>'จำนวนประชากร<br />(คน)',
				'money -allocate'=>'ประมาณการณ์จำนวนเงินจัดสรรโดย สปสช.<br />(บาท)',
				'money -local'=>'ประมาณการณ์เงินสมทบจากท้องถิ่น(คำนวณ)<br />(บาท)',
				'money -locaninput'=>'ประมาณการณ์เงินสมทบจากท้องถิ่น<br />(บาท)',
				'money -nshoinput'=>'จำนวนเงินรับจากสปสช.<br />(บาท)',
				'money -localinput'=>'จำนวนเงินรับจากท้องถิ่น<br />(บาท)',
				'amt'=>'โครงการ',
			],
			'children' => (function($population) {
				$rows = [];
				foreach ($population->items as $item) {
					$localPercentIndex = $getYear >= 2021 ? $item->orgincomepcnt : $item->orgsize;
					$localAddPercentAmt = $localAddPercentList[$localPercentIndex];
					$moneyAllocate = round($item->population*$factor);
					$moneyLocal = round($moneyAllocate*$localAddPercentAmt/100);
					$localBudgetPercent = $item->budgetlocal*100/$moneyAllocate;

					if ($item->budgetlocal > $moneyLocal) {
						$fontColor = 'green';
					} else if ($localAddPercentAmt-$localBudgetPercent <= 3) {
						$fontColor = 'yellow';
					} else {
						$fontColor = 'red';
					}

					$rows[] = [
					'<a href="'.url('project/fund/'.$item->orgid).'"><b>'.$item->name.'</b></a><br /><i>'.$item->shortname
					. ' อำเภอ'.$item->nameampur.'</i>',
					$item->yearPopulation ? number_format($item->yearPopulation) : '-',
					number_format($item->population),
					number_format($moneyAllocate,2),
					number_format($moneyLocal,2)
					. '<br /><span style="color:#ccc;">('.$localAddPercentAmt.'%)</span>',
					'-'.$fontColor=>number_format($item->budgetlocal,2)
					.'<br /><span style="color:#ccc;">('.number_format($localBudgetPercent).'%)</span>',
					$item->nhsoRcv ? number_format($item->nhsoRcv,2) : '-',
					$item->localRcv ? number_format($item->localRcv,2) : '-',
					$item->totalProject ? '<a href="'.url('project/fund/'.$item->orgid.'/follow').'">'.number_format($item->totalProject).'</a>' : '-',
				];
				[
						$item->shortname,
						$item->name,
						$item->yearPopulation ? number_format($item->yearPopulation) : '-',
					];
				}
				return $rows;
			})($population),
		]);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'รายงานจำนวนประชากร พ.ศ.'.($this->year + 543),
			]),
			'body' => new Widget([
				'children' => [
					new Form([
						'action' => url('project/fund/report/population'),
						'class' => '-sg-flex -justify-left',
						'method' => 'get',
						'children' => [
							'year' => [
								'type' => 'select',
								'options' => (function() {
									$options = [];
									for ($year = date('Y'); $year > 2016 ; $year--) {
										$options[$year] = 'พ.ศ.'.($year+543);
									}
									return $options;
								})(),
								'value' => $this->year,
							],
							'area' => [
								'type' => 'select',
								'options' => (function() {
									$options = ['' => '=== ทุกเขต ==='];
									foreach (mydb::select('SELECT `areaid`,`areaname` FROM %project_area% ORDER BY areaid+0 ASC')->items as $area) {
										$options[$area->areaid] = 'เขต '.$area->areaid.' - '.$area->areaname;
									}
									return $options;
								})(),
								'value' => $this->areaId,
								'attr' => ['onChange' => '$(\'#edit-changwat\').val(\'\');this.form.submit()'],
							],
							'changwat' => [
								'type' => 'select',
								'options' => (function() {
									$options = ['' => '=== เลือกจังหวัด ==='];
									if ($this->areaId) mydb::where('`areaid` = :areaId', ':areaId', $this->areaId);
									foreach (mydb::select('SELECT DISTINCT `changwat`,`namechangwat` FROM %project_fund% %WHERE% ORDER BY CONVERT(`namechangwat` USING tis620) ASC')->items as $item) {
										$options[$item->changwat] = $item->namechangwat;
									}
									return $options;
								})(),
								'value' => $this->changwat,
							],
							'go' => [
								'type' => 'button',
								'value' => '<i class="icon -material">search</i><span>ดูรายงาน</span>',
							],
							'export' => [
								'type' => 'button',
								'name' => 'export',
								'class' => 'btn',
								'value' => '<i class="icon -material">download</i><span>EXPORT</span>',
							],
						],
					]),

					new ScrollView([
						'child' => $result,
					]),
					// debugMsg(reset($population->items)),
					// print_o(post(),'post()'),
				],
			]),
		]);
	}
}
?>