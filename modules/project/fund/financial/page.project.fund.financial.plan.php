<?php
/**
* Project Fund :: Financial Plan
* Created 2021-09-10
* Modify  2021-09-14
*
* @param Object $fundInfo
* @param Int $budgetYear
* @param String $action
* @param Mixed $tranId
* @return Widget
*
* @usage project/fund/{id}/financial.plan
*/

$debug = true;

import('model:project.planning.php');
import('package:project/fund/models/model.fund.php');
import('package:project/fund/widgets/widget.fund.trailing.php');
import('package:project/fund/widgets/widget.fund.nav.php');

class ProjectFundFinancialPlan extends Page {
	var $orgId;
	var $budgetYear;
	var $action;
	var $fundInfo;
	var $right;
	var $tranId;

	function __construct($fundInfo, $budgetYear = NULL, $action = NULL, $tranId = NULL) {
		$this->orgId = $fundInfo->orgId;
		$this->fundInfo = $fundInfo;
		$this->budgetYear = $budgetYear;
		$this->right = $this->fundInfo->right;
		$this->action = $action;
		$this->tranId = $tranId;
	}

	function build() {
		// If return new Widget, AppBar not show
		if ($this->budgetYear) {
			return (new ProjectFundFinancialPlanView($this->fundInfo, $this->budgetYear, $this->action, $this->tranId))->build();
		} else {
			return (new ProjectFundFinancialPlanList($this->fundInfo))->build();
		}

		// return new Widget([
		// 	'child' => (function() {
		// 		if ($this->budgetYear) {
		// 			return new ProjectFundFinancialPlanView($this->fundInfo, $this->budgetYear, $this->action, $this->tranId);
		// 		} else {
		// 			return new ProjectFundFinancialPlanList($this->fundInfo);
		// 		}
		// 	})(),
		// ]);

		//  return new Scaffold([
		// 	'appBar' => new AppBar([
		// 		'title' => 'แผนการเงิน'.($this->budgetYear ? ' ปีงบประมาณ '.($this->budgetYear+543) : ''),
		// 		'trailing' => new FundTrailingWidget($this->fundInfo),
		// 		'navigator' => new FundNavWidget($this->fundInfo),
		// 	]),
		// 	'body' => $this->budgetYear ? new ProjectFundFinancialPlanView($this->fundInfo, $this->budgetYear, $this->action, $this->tranId) : new ProjectFundFinancialPlanList($this->fundInfo),
		// ]);
	}
}

class ProjectFundFinancialPlanView extends Page {
	var $orgId;
	var $budgetYear;
	var $action;
	var $tranId;
	var $fundInfo;
	var $right;

	function __construct($fundInfo, $budgetYear = NULL, $action = NULL, $tranId = NULL) {
		$this->orgId = $fundInfo->orgId;
		$this->fundInfo = $fundInfo;
		$this->budgetYear = $budgetYear;
		$this->right = $this->fundInfo->right;
		$this->planInfo = FundModel::getMoneyPlan($this->orgId, $this->budgetYear);
		$this->action = $action;
		$this->tranId = $tranId;
	}

	function build() {
		// debugMsg($this,'$this');
		// debugMsg($this->fundInfo,'$fundInfo');
		// print_o(new FundNavWidget($this->fundInfo),'nav');

		// return new Scaffold([
		// 	'appBar' => new AppBar([
		// 		'title' => 'แผนงาน - '.$this->fundInfo->name,
		// 		'navigator' => new FundNavWidget($this->fundInfo),
		// 	]),
		// 	'body' => new Widget([
		// 		'children' => [
		// 			'<p class="notify">ไม่มีแผนการเงินของปี '.($this->budgetYear + 543).'</p>',
		// 			// new ProjectFundFinancialPlanList($this->fundInfo),
		// 		], // children
		// 	]),
		// ]);

		if (!$this->planInfo) return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แผนการเงิน',
				'trailing' => new FundTrailingWidget($this->fundInfo),
				'navigator' => new FundNavWidget($this->fundInfo),
			]),
			'body' => new Widget([
				'children' => [
					'<p class="notify -sg-paddingmore"><i class="icon -material">warning</i><span>ไม่มีแผนการเงินของปี '.($this->budgetYear + 543).'</span></p>',
					new ProjectFundFinancialPlanList($this->fundInfo),
				], // children
			]),
		]);

		// debugMsg($this->planInfo, '$planInfo');

		if ($this->action) {
			switch ($this->action) {
				case 'project.form': return $this->_projectForm(); break;
				case 'income.form': return $this->_incomeForm(); break;
				case 'expense.form': return $this->_expenseForm(); break;
			}
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'class' => '-no-print',
				'title' => 'แผนการเงิน',
				'trailing' => new FundTrailingWidget($this->fundInfo),
				'navigator' => new FundNavWidget($this->fundInfo),
			]),
			'body' => new Container([
				'class' => $class,
				'attribute' => $inlineAttr,
				'children' => [
					'<h3 class="-sg-text-center">'.'แผนการเงินประจำปีงบประมาณ '.($this->budgetYear+543).'<br />'.$this->fundInfo->name.'</h3>',
					new ListTile([
						'class' => '-no-print',
						'title' => 'แผนการรับเงิน',
						'leading' => '<i class="icon -material">attach_money</i>',
						'trailing' => $this->right->edit ? new Row(['children'=>['<a class="sg-action btn -link -no-print" href="'.url('project/fund/'.$this->orgId.'/financial.plan/'.$this->budgetYear.'/income.form').'" data-rel="box" data-width="480"><i class="icon -material">edit</i></a>'],]) : NULL,
						'class' => '-sg-paddingmore',
						'style' => 'background-color: #94e493',
					]),
					new ScrollView([
						'child' => new Table([
							'colgroup' => ['', 'title -fill' => '','','amt -center' => '',''],
							'children' => [
								[
									'1.',
									'เงินคงเหลือยกมา',
									'จำนวน',
									number_format($this->planInfo->info->openBalance,2),
									'บาท'
								],
								['2.', 'เงินโอนจาก สปสช.', 'จำนวน', number_format($this->planInfo->info->incomeNhso,2), 'บาท'],
								['3.', 'เงินสมทบจาก อปท.', 'จำนวน', number_format($this->planInfo->info->incomeLocal,2), 'บาท'],
								['4.', 'รายได้อื่น ๆ', 'จำนวน', number_format($this->planInfo->info->incomeOther,2), 'บาท'],
							], // children
							'tfoot' => [
								['', 'รวมเงิน', 'จำนวน', number_format($this->planInfo->info->incomeTotal,2), 'บาท'],
							], // tfoot
						]), // Table
					]),
					new ListTile([
						'title' => 'แผนการจ่ายเงิน',
						'leading' => '<i class="icon -material">money_off</i>',
						'trailing' => $this->right->edit ? new Row(['children'=>['<a class="sg-action btn -link -no-print" href="'.url('project/fund/'.$this->orgId.'/financial.plan/'.$this->budgetYear.'/expense.form').'" data-rel="box" data-width="480"><i class="icon -material">edit</i></a>'],]) : NULL,
						'class' => '-sg-paddingmore',
						'style' => 'background-color: #ffd4d4',
					]),
					new ScrollView([
						'child' => new Table([
							'class' => '-center',
							'thead' => ['10(1)', '10(2)', '10(3)', '10(4)', '10(5)', 'รวมเงิน'],
							'children' => [
								[
									number_format($this->planInfo->info->budget10_1,2),
									number_format($this->planInfo->info->budget10_2,2),
									number_format($this->planInfo->info->budget10_3,2),
									number_format($this->planInfo->info->budget10_4,2),
									number_format($this->planInfo->info->budget10_5,2),
									number_format($this->planInfo->info->budgetTotal,2),
								],
							], // children
						]), // Table
					]),
					'หมายเหตุ การคำนวณ 10(4) ให้คิดจาก ข้อ 2-4 ไม่ร่วมเงินคงเหลือยกมา<br /><br /><br />',
					'<div class="-hidden -to-print">เรียนคณะกรรมการ เพื่อโปรดพิจารณา<br /><br /><br />'
					. 'ลงชื่อ .................................. เลขานุการ<br /><br />'
					. '    ( ........................................ )<br /><br /><br />'
					. 'เห็นชอบตามมติการประชุมคณะกรรมการครั้งที่ ............. เมื่อวันที่ ............................<br /><br />'
					. 'ลงชื่อ ................................... ประธานคณะกรรมการ<br /><br />'
					. '    (...........................................)</div>',
					'<hr class="pagebreak" />',

					'<h3 class="-sg-text-center">'.'แผนโครงการประจำปีงบประมาณ '.($this->budgetYear+543).'<br />'.$this->fundInfo->name.'<br />งบประมาณตามแผนการเงินประจำปี</h3>',

					new Table([
							'class' => '-center',
							'thead' => ['10(1)', '10(2)', '10(3)', '10(4)', '10(5)', 'รวมเงิน'],
							'children' => [
								[
									number_format($this->planInfo->info->budget10_1,2),
									number_format($this->planInfo->info->budget10_2,2),
									number_format($this->planInfo->info->budget10_3,2),
									number_format($this->planInfo->info->budget10_4,2),
									number_format($this->planInfo->info->budget10_5,2),
									number_format($this->planInfo->info->budgetTotal,2),
								],
							], // children
						]), // Table
					'</div>',
					$this->typeWidget(10,1),
					$this->typeWidget(10,2),
					$this->typeWidget(10,3),
					$this->typeWidget(10,4),
					$this->typeWidget(10,5),
					'<div class="-hidden -to-print">เรียนคณะกรรมการ เพื่อโปรดพิจารณา<br /><br /><br />'
					. 'ลงชื่อ .................................. เลขานุการ<br /><br />'
					. '    ( ........................................ )<br /><br /><br />'
					. 'เห็นชอบตามมติการประชุมคณะกรรมการครั้งที่ ............. เมื่อวันที่ ............................<br /><br />'
					. 'ลงชื่อ ................................... ประธานคณะกรรมการ<br /><br />'
					. '    (...........................................)</div>',
				],
			]),
		]);
	}

	function typeWidget($type, $subType) {
		$subTypeItems = $this->planInfo->supportType[$subType];
		return new Card([
			'children' => [
				new ListTile([
					'class' => '-sg-paddingmore',
					'title' => 'โครงการตามแผนสำหรับ '.$type.'('.$subType.')',
					'leading' => '<i class="icon -material">fact_check</i>',
				]),
				$subTypeItems ? new Table([
					'thead' => $subTypeItems ? ['no' => '', 'โครงการ', 'หน่วยงาน', 'budget -money -hover-parent' => 'งบประมาณ'] : NULL,
					'children' => (function($items, &$total = 0) {
						$rows = [];
						$no = 0;
						foreach ($items as $item) {
							$rows[] = [
								++$no,
								$item->projectTitle,
								$item->orgNameDo,
								number_format($item->budget,2)
								. (new Nav([
									'class' => 'nav -icons -hover -no-print',
									'children' => [
										$item->proposalId ? '<a href="'.url('project/develop/'.$item->proposalId).'" title="พัฒนาโครงการ" target="_blank"><i class="icon -material">nature_people</i></a>' : '<a class="-disabled" title="ไม่มีพัฒนาโครงการ"><i class="icon -material -gray">nature_people</i></a>',
										$item->planningId ? '<a href="'.url('project/planning/'.$item->planningId).'" title="แผนงาน" target="_blank"><i class="icon -material">device_hub</i></a>' : '<a class="-disabled" title="ไม่มีแผนงาน"><i class="icon -material -gray">device_hub</i></a>',
										$this->right->edit ? '<a class="sg-action" href="'.url('project/fund/'.$this->orgId.'/financial.plan/'.$this->budgetYear.'/project.form/'.$item->projectDoId).'" data-rel="box" data-width="480" title="แก้ไข"><i class="icon -material">edit</i></a>' : NULL,
										$this->right->edit ? ($item->proposalId ? '<a class="-disabled" title="ลบไม่ได้"><i class="icon -material -gray">cancel</i></a>' : '<a class="sg-action" href="'.url('project/fund/api/'.$this->orgId.'/moneyplan.project.remove/'.$item->projectDoId).'" data-rel="notify" data-done="remove:parent tr" data-title="ลบโครงการตามแผน" data-confirm="ต้องการลบโครงการตามแผน กรุณายืนยัน?" title="ลบ"><i class="icon -material">cancel</i></a>') : NULL,
									],
								]))->build(),
							];
							$total += $item->budget;
						}
						return $rows;
					})($subTypeItems, $total),
					'tfoot' => $total ? [['<td></td>','','',number_format($total,2)]] : NULL,
				]) : '<p class="-sg-text-center">ยังไม่มีโครงการตามแผนสำหรับ 10('.$subType.')!!!</p>',
				$this->right->edit ? new Nav([
					'class' => '-sg-paddingnorm',
					'mainAxisAlignment' => 'end',
					'child' => '<a class="sg-action btn -primary -no-print" href="'.url('project/fund/'.$this->orgId.'/financial.plan/'.$this->budgetYear.'/project.form', ['supportType' => $subType]).'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>เพิ่มโครงการตามแผน 10('.$subType.')</span></a>',
				]) : NULL,
			], // children
		]);
	}

	function _incomeForm() {
		return new Form([
			'class' => 'sg-form',
			'action' => url('project/fund/api/'.$this->orgId.'/moneyplan.income.save/'.$this->budgetYear),
			'rel' => 'notify',
			'done' => 'load | close',
			'checkValid' => true,
			'children' => [
				'<header class="header">'._HEADER_BACK.'<h3>แผนการรับเงิน</h3></header>',
				'openBalance' => [
					'type' => 'text',
					'class' => '-fill',
					'label' => 'เงินคงเหลือยกมา (บาท)',
					'require' => true,
					'value' => number_format($this->planInfo->info->openBalance,2),
					'placeholder' => '0.00',
				],
				'incomeNhso' => [
					'type' => 'text',
					'class' => '-fill',
					'label' => 'เงินโอนจาก สปสช. (บาท)',
					'require' => true,
					'value' => number_format($this->planInfo->info->incomeNhso,2),
					'placeholder' => '0.00',
				],
				'incomeLocal' => [
					'type' => 'text',
					'class' => '-fill',
					'label' => 'เงินสมทบจาก อปท. (บาท)',
					'require' => true,
					'value' => number_format($this->planInfo->info->incomeLocal,2),
					'placeholder' => '0.00',
				],
				'incomeOther' => [
					'type' => 'text',
					'class' => '-fill',
					'label' => 'รายได้อื่น ๆ (บาท)',
					'require' => true,
					'value' => number_format($this->planInfo->info->incomeOther,2),
					'placeholder' => '0.00',
				],
				'save' => [
					'type' => 'button',
					'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}',
					'container' => '{class: "-sg-text-right"}',
				],
			], // children
		]);
	}

	function _expenseForm() {
		return new Form([
			'class' => 'sg-form',
			'action' => url('project/fund/api/'.$this->orgId.'/moneyplan.expense.save/'.$this->budgetYear),
			'rel' => 'notify',
			'done' => 'load | close',
			'checkValid' => true,
			'children' => [
				'<header class="header">'._HEADER_BACK.'<h3>แผนการจ่าย</h3></header>',
				'budget10_1' => [
					'type' => 'text',
					'class' => '-fill',
					'label' => '10(1) (บาท)',
					'require' => true,
					'value' => number_format($this->planInfo->info->budget10_1,2),
					'placeholder' => '0.00',
				],
				'budget10_2' => [
					'type' => 'text',
					'class' => '-fill',
					'label' => '10(2) (บาท)',
					'require' => true,
					'value' => number_format($this->planInfo->info->budget10_2,2),
					'placeholder' => '0.00',
				],
				'budget10_3' => [
					'type' => 'text',
					'class' => '-fill',
					'label' => '10(3) (บาท)',
					'require' => true,
					'value' => number_format($this->planInfo->info->budget10_3,2),
					'placeholder' => '0.00',
				],
				'budget10_4' => [
					'type' => 'text',
					'class' => '-fill',
					'label' => '10(4) (บาท)',
					'require' => true,
					'value' => number_format($this->planInfo->info->budget10_4,2),
					'placeholder' => '0.00',
				],
				'budget10_5' => [
					'type' => 'text',
					'class' => '-fill',
					'label' => '10(5) (บาท)',
					'require' => true,
					'value' => number_format($this->planInfo->info->budget10_5,2),
					'placeholder' => '0.00',
				],
				'save' => [
					'type' => 'button',
					'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}',
					'container' => '{class: "-sg-text-right"}',
				],
			], // children
		]);
	}

	function _projectForm() {
		$data = (Object) [];
		if ($this->tranId) {
			foreach ($this->planInfo->supportType as $typeItems) {
				// debugMsg($typeItems,'$typeItems');
				if (array_key_exists($this->tranId, $typeItems)) {
					$data = (Object) $typeItems[$this->tranId];
					break;
				}
			}
		}

		return new Form([
			'class' => 'sg-form',
			'action' => url('project/fund/api/'.$this->orgId.'/moneyplan.project.save'),
			'rel' => 'none',
			'done' => 'load | close',
			'checkValid' => true,
			'children' => [
				'<header class="header">'._HEADER_BACK.'<h3>โครงการที่ควรดำเนินการ</h3></header>',
				'projectDoId' => $data->projectDoId ? ['type' => 'hidden', 'value' => $data->projectDoId] : NULL,
				'projectTitle' => [
					'type' => 'text',
					'class' => '-fill',
					'label' => 'ชื่อโครงการที่ควรดำเนินการ',
					'require' => true,
					'value' => $data->projectTitle,
					'placeholder' => 'ระบุชื่อโครงการที่ควรดำเนินการ',
					'description' => 'โครงการที่ควรดำเนินการจะถูกเพิ่มเข้าไปในแผนงานโดยอัตโนมัติ',
				],
				'supportType' => [
					'type' => 'radio',
					'label' => 'ประเภทการสนับสนุน',
					'require' => true,
					'options' => model::get_category('project:supporttype','catid'),
					'value' => $data->supportType,
				],
				'planningId' => [
					'type' => 'select',
					'label' => 'แผนงาน',
					'class' => '-fill',
					'require' => true,
					'value' => $data->planningId,
					'options' => (function() {
						$options = ['' => '=== เลือกแผนงาน ==='];
						foreach (ProjectPlanningModel::items(['orgId' => $this->orgId, 'year' => $this->budgetYear]) as $item) {
							$options[$item->planningId] = $item->title;
						}
						return $options;
					})(),
				],
				'orgNameDo' => [
					'type' => 'text',
					'label' => 'หน่วยงาน/บุคคลผู้รับผิดชอบ',
					'class' => '-fill',
					'value' => $data->orgNameDo,
					'placeholder' => 'ระบุชื่อหน่วยงาน/บุคคลผู้รับผิดชอบ'
				],
				'budget' => [
					'type' => 'text',
					'label' => 'งบประมาณที่ตั้งไว้(บาท)',
					'class' => '-fill',
					'require' => true,
					'value' => number_format($data->budget,2),
					'placeholder' => '0.00',
				],
				'save' => [
					'type' => 'button',
					'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}',
					'container' => '{class: "-sg-text-right"}',
				],
			], // children
		]);
	}
}

class ProjectFundFinancialPlanList extends Page {
	var $orgId;
	var $fundInfo;
	var $right;

	function __construct($fundInfo) {
		$this->orgId = $fundInfo->orgId;
		$this->fundInfo = $fundInfo;
		$this->right = $this->fundInfo->right;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แผนการเงิน',
				'trailing' => new FundTrailingWidget($this->fundInfo),
				'navigator' => new FundNavWidget($this->fundInfo),
			]),
			'body' => new Widget([
				'children' => [
					new Container([
						'children' => (function(&$lastPlanYear) {
							$widgets = [];
							foreach (FundModel::moneyPlans(['orgId' => $this->orgId]) as $item) {
								// Check Last Year Money Plan For Create Button
								if ($lastPlanYear < $item->budgetYear) $lastPlanYear = $item->budgetYear;
								$widgets[] = new Card([
									'children' => [
										new ListTile([
											'class' => '-sg-paddingnorm',
											'title' => 'แผนการเงิน ปี '.($item->budgetYear + 543),
											'leading' => '<i class="icon -material">paid</i>',
											'trailing' => '<a class="btn" href="'.url('project/fund/'.$this->orgId.'/financial.plan/'.$item->budgetYear).'"><span>รายละเอียด</span><i class="icon -material">navigate_next</i></a>',
										]), // ListTile
									], // children
								]);
							}
							// if (empty($lastPlanYear)) $lastPlanYear = sg_budget_year(date('Y'));
							// debugMsg($lastPlanYear);
							// debugMsg(sg_budget_year('2021-10-01')+543);

							return $widgets;
						})($lastPlanYear), // children
					]), // Container

					// Show create button when month between 10 and 12 and last plan is not equal to current budget year
					$this->right->edit && date('m') >= 10 && $lastPlanYear != ($currentBudgetYear = sg_budget_year(date('Y-m-d'))) ? new FloatingActionButton([
						'children' => [
							'<a class="sg-action btn -floating" href="'.url('project/fund/api/'.$this->orgId.'/financial.plan.create/'.$currentBudgetYear).'" data-rel="none" data-done="reload:'.url('project/fund/'.$this->orgId.'/financial.plan/'.$currentBudgetYear).'" data-title="สร้างแผนการเงิน" data-confirm="ต้องการสร้างแผนการเงินสำหรับปีงบประมาณ '.($currentBudgetYear+543).' กรุณายืนยัน?"><i class="icon -material">add</i><span>สร้างแผนการเงิน ปี '.($currentBudgetYear + 543).'</span></a>',
						],
					]) : NULL, // FloatingActionButton

				], // children
			]), // Container
		]);
	}
}

?>