<?php
/**
* Project :: Fund Planning
* Created 2020-06-07
* Modify  2021-09-13
*
* @param Object $fundInfo
* @return Widget
*
* @usage project/fund/$orgId/planning
*/

$debug = true;

import('package:project/fund/widgets/widget.fund.nav');

class ProjectFundPlanning extends Page {
	var $orgId;
	var $right;
	var $fundInfo;

	function __construct($fundInfo) {
		$this->orgId = $fundInfo->orgId;
		$this->fundInfo = $fundInfo;
		$this->right = (Object) [
			'edit' => $fundInfo->right->edit || $fundInfo->right->trainer || user_access('administer projects')
		];
	}

	function build() {
		if (!$this->orgId) return message('error', 'PROCESS ERROR:NO FUND');

		$currentYear = date('Y') + (date('m') >= 4 ? 1 : 0);
		$yearCanCreate = $currentYear - 3;


		$moneyPlan = mydb::select(
			'SELECT `orgId`, `budgetYear`
			FROM %project_fundmoneyplan%
			WHERE `orgId` = :orgId
			ORDER BY `budgetYear` ASC;
			-- {key: "budgetYear"}',
			[':orgId' => $this->orgId]
		)->items;

		mydb::where('p.`prtype` = "แผนงาน"');
		mydb::where('t.`orgid` = :orgid', ':orgid', $this->orgId);

		$planning = mydb::select(
			'SELECT
			p.`tpid`, p.`pryear`
			, t.`title`, t.`orgid`
			, o.`shortname`, o.`name` `orgName`
			, t.`approve`
			, pt.`refid` `plangroup`
			, t.`created`
			, u.`name` `posterName`
			, (SELECT COUNT(*) FROM %project% cp WHERE cp.`projectset` = p.`tpid`) `childcount`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %users% u USING(`uid`)
				LEFT JOIN %db_org% o USING(`orgid`)
				LEFT JOIN %project_tr% pt ON pt.`tpid` = p.`tpid` AND pt.`formid` = "info" AND pt.`part` = "title"
			%WHERE%
			HAVING `plangroup` IS NOT NULL
			ORDER BY `pryear` DESC, `tpid` DESC;
			-- {group: "pryear"}'
		)->items;

		for ($i = $yearCanCreate; $i <= $currentYear; $i++) {
			if (!array_key_exists($i, $planning)) {
				$planning[$i] = [];
			}
		}

		krsort($planning);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แผนงาน - '.$this->fundInfo->name,
				'navigator' => new FundNavWidget($this->fundInfo),
			]),
			'body' => new ScrollView([
				'children' => [
					new Table([
						'thead' => [
							'center -year' => 'ปีงบประมาณ',
							'title' => 'ชื่อแผนงาน',
							'icons -c1' => '<i class="icon -material -gray">verified</i>',
							'org -hover-parent' => 'หน่วยงาน',
						],
						'showHeader' => false,
						'children' => (function($planning) use($moneyPlan, $yearCanCreate) {
							$rows = [];
							foreach ($planning as $yearPlan => $yearPlanItems) {
								$rows[] = [
									'<td colspan="4">'.(new ListTile(['title' => '<h3>แผนงานปีงบประมาณ '.($yearPlan+543).'</h3>','leading'=>'<i class="icon -material">device_hub</i>']))->build().'</td>',
									'config' => ['class' => 'subheader']
								];

								$rows[] = '<header>';

								$hasPlanOfYear = false;
								foreach ($yearPlanItems as $rs) {
									$hasPlanOfYear = true;
									$isDeleteable = $this->right->edit && $rs->childcount == 0;

									$ui = new Ui('span');
									$ui->addConfig('nav', '{class: "nav -icons -hover"}');
									$ui->add('<a href="'.url('project/planning/'.$rs->tpid).'"><i class="icon -viewdoc"></i></a>');

									$rows[] = [
										$rs->pryear + 543,
										'<a href="'.url('project/planning/'.$rs->tpid).'">'.SG\getFirst($rs->title,'ไม่ระบุ').'</a><br /><span class="timestamp">'.$rs->posterName.' @'.$rs->created.'</span>',
										'<i class="icon -material -approve-'.strtolower($rs->approve).'">'.['MASTER' => 'verified', 'USE' => 'recommend', 'LEARN' => 'flaky'][$rs->approve].'</i>',
										'<a href="'.url('project/fund/'.$rs->orgid).'">'.$rs->orgName.'</a>'
										. $ui->build()
									];

								}
								if (!$hasPlanOfYear)
									$rows[] = ['<td colspan="4" align="center">ไม่มีแผนงานโครงการปีงบประมาณ '.($yearPlan+543).'</td>'];

								$hasMoneyPlan = array_key_exists($yearPlan, $moneyPlan);

								if ($hasMoneyPlan) {
									$moneyPlanBtn = '<a class="btn" href="'.url('project/fund/'.$this->orgId.'/financial.plan/'.$yearPlan).'"><i class="icon -material">local_atm</i><span>แผนการเงินปี '.($yearPlan+543).'</span></a>';
								} else if ($this->right->edit && $yearCanCreate <= $yearPlan) {
									$moneyPlanBtn = '<a class="sg-action btn -floating" href="'.url('project/fund/api/'.$this->orgId.'/financial.plan.create/'.$yearPlan).'" data-rel="none" data-done="reload:'.url('project/fund/'.$this->orgId.'/financial.plan/'.$yearPlan).'" data-title="สร้างแผนการเงิน ปี '.($yearPlan+543).'" data-confirm="ต้องการสร้างแผนการเงินสำหรับปีงบประมาณ '.($yearPlan+543).' กรุณายืนยัน?"><i class="icon -material">add</i><span>สร้างแผนการเงิน ปี '.($yearPlan + 543).'</span></a>';
								} else {
									$moneyPlanBtn = NULL;
								}

								$nav = new Nav([
									'class' => '-sg-paddingmore',
									'children' => [
										$moneyPlanBtn,
										$this->right->edit && $yearCanCreate <= $yearPlan ? '<a class="btn -primary" href="'.url('project/planning/year/'.$this->orgId.'/'.$yearPlan).'" data-rel="#detail"><i class="icon -addbig -white -circle"></i><span>สร้างแผนงานโครงการปีงบประมาณ '.($yearPlan+543).'</span></a>' : NULL,
										$hasPlanOfYear ? '<a class="btn -block" href="'.url('project/planning/print/'.$this->orgId.'/'.$yearPlan).'"><i class="icon -print"></i><span>พิมพ์แผนงานปีงบประมาณ '.($yearPlan+543).'</span></a>' : NULL,
									], // children
								]);
								$rows[] = ['<td colspan="4">'.$nav->build().'</td>'];
							}
							return $rows;
						})($planning),

					]),
				],
			]),
		]);
	}
}
?>