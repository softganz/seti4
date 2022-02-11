<?php
/**
* Project :: Fund Information
* Created 2021-06-28
* Modify  2021-06-28
*
* @param Object $fundInfo
* @return Widget
*
* @usage project/fund/{id}
*/

$debug = true;

import('model:project.fund.finance');
import('package:project/fund/widgets/widget.fund.trailing');
import('package:project/fund/widgets/widget.fund.nav');

class ProjectFundInfoView extends Page {
	var $orgId;
	var $fundInfo;
	var $showWidget;
	var $right;
	var $followType;
	var $budgetYear;

	function __construct($fundInfo) {
		$this->orgId = $fundInfo->orgId;
		$this->followType = post('follow');
		$this->showWidget = post('show');
		$this->fundInfo = $fundInfo;
		$this->budgetYear = post('year');
	}

	function build() {
		if (!$this->orgId) return message('error', 'PROCESS ERROR:NO FUND');

		$this->right = $right = $this->fundInfo->right;


		if ($right->edit && (empty($this->fundInfo->info->tambon) || empty($this->fundInfo->info->ampur) || empty($this->fundInfo->info->changwat))) {
			return '<p class="notify">ข้อมูลที่อยู่กองทุนยังไม่สมบูรณ์ กรุณาแก้ไขข้อมูลที่อยู่ของกองทุน<br /><br /><a class="sg-action btn -primary" href="'.url('project/fund/'.$this->orgId.'/info.address').'" data-rel="box" data-width="480">แก้ไขที่อยู่กองทุน</a></p>';
		}

		if ($this->followType) return $this->_showProject($this->followType);
		else if ($this->showWidget) return $this->_showWidget($this->showWidget);

		$this->isInPopulationPeriod = in_array(date('m'),explode(',',cfg('project.localfund.population.month')));
		$this->nextPopulationYear = date('Y')+1;
		$this->isReadyPopulation = mydb::select('SELECT `trid` FROM %project_tr% WHERE `orgid` = :orgid AND `refid` = :year AND `formid` = "population" LIMIT 1', ':orgid', $this->orgId, ':year', $this->nextPopulationYear)->trid;

		$currentBudgetYear = sg_budget_year(SG\getFirst($this->budgetYear, date('Y-m-d')));

		$stmt = 'SELECT
			  COUNT(DISTINCT IF(p.`project_status` = "กำลังดำเนินโครงการ",p.`tpid`,NULL)) `activeProject`
			, COUNT(DISTINCT p.`tpid`) `totalProject`
			, SUM(pd.`amount`) `paidBudget`
			, SUM(IF(p.`project_status` = "กำลังดำเนินโครงการ",p.`budget`,0)) `activeBudget`
			, SUM(p.`budget`) `totalBudget`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %project_paiddoc% pd ON pd.`tpid` = p.`tpid`
			WHERE p.`prtype` = "โครงการ" AND t.`orgid` = :orgid AND p.`pryear` = :currentBudgetYear
			LIMIT 1';

		$projectRs = mydb::select($stmt,':orgid',$this->orgId, ':currentBudgetYear', $currentBudgetYear);

		// 'กำลังดำเนินโครงการ','ดำเนินการเสร็จสิ้น','ยุติโครงการ','ระงับโครงการ'
		// debugMsg($projectRs);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->fundInfo->name,
				'trailing' => new FundTrailingWidget($this->fundInfo),
				'navigator' => new FundNavWidget($this->fundInfo),
			]),
			'body' => new Widget([
				'children' => [
					$right->edit && $right->addPopulation && $this->isInPopulationPeriod && !$this->isReadyPopulation ? $this->_inputPopulation() : NULL,
					$right->edit && date('m') >= 10 && date('m') <= 12 ? $this->_inputEvalManage() : NULL,
					$this->_showDeleteFundButton($projectRs),
					$this->_showGuage($projectRs),
					// $this->_showGraph($projectRs),
					// new ListTile([
					// 	'class' => 'project-card -summary',
					// 	'title' => 'Graph',
					// 	'leading' => '<i class="icon -material">info</i>',
					// 	// 'trailing' => '<a class="btn -link" href=").'"><span>รายละเอียด</span><i class="icon -material">read_more</i></a>',
					// ]),
					$this->_showProject(),
					// Fund Info Menu
					new Card([
						'class' => 'project-card -info',
						'children' => [
							new ListTile([
								'title' => 'รายละเอียดกองทุน',
								'leading' => '<i class="icon -material">info</i>',
								'trailing' => '<a class="sg-action btn -link" href="'.url('project/fund/'.$this->orgId,['show' => 'info']).'" data-rel="box" data-width="full"><span>รายละเอียด</span><i class="icon -material">read_more</i></a>',
							]),
							new ListTile([
								'title' => 'กองทุน LTC',
								'leading' => '<i class="icon -material">accessible</i>',
								'trailing' => '<a class="sg-action btn -link" href="'.url('project/fund/'.$this->orgId,['show' => 'ltc']).'" data-rel="box" data-width="full"><span>รายละเอียด</span><i class="icon -material">read_more</i></a>',
							]),
							new ListTile([
								'title' => 'คณะกรรมการ',
								'leading' => '<i class="icon -material">group</i>',
								'trailing' => '<a class="btn -link" href="'.url('project/fund/'.$this->orgId.'/board').'"><span>รายละเอียด</span><i class="icon -material">read_more</i></a>',
							]),
							new ListTile([
								'title' => 'เจ้าหน้าที่กองทุน',
								'leading' => '<i class="icon -material">account_circle</i>',
								'trailing' => '<a class="sg-action btn -link" href="'.url('project/fund/'.$this->orgId,['show' => 'officer']).'" data-rel="box" data-width="full"><span>รายละเอียด</span><i class="icon -material">read_more</i></a>',
							]),
							new ListTile([
								'title' => 'ประเมิน',
								'leading' => '<i class="icon -material">bar_chart</i>',
								'trailing' => '<a class="sg-action btn -link" href="'.url('project/fund/'.$this->orgId,['show' => 'eval']).'" data-rel="box" data-width="full"><span>รายละเอียด</span><i class="icon -material">read_more</i></a>',
							]),
						], // children
					]), // Card
					// $this->_showInfo(),
					// $this->_showLtc(),
					// $this->_showAccount(),
					// $this->_showBoard(),
					$this->_showPopulation(),
					// $this->_showOfficer(),
					// $this->_showEval(),
					$this->_script(),
					head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>'),

				], // children
			]), // body
		]);
	}

	function _showWidget($widgetName) {
		switch ($widgetName) {
			case 'info': $ret = $this->_showInfo(); break;
			case 'ltc': $ret = $this->_showLtc(); break;
			case 'board': $ret = $this->_showBoard(); break;
			case 'population': $ret = $this->_showPopulation($this->isInPopulationPeriod); break;
			case 'officer': $ret = $this->_showOfficer(); break;
			case 'eval': $ret = $this->_showEval(); break;
			case 'popdocs': $ret = $this->_showPopulationDocs(); break;
		}
		return $ret;
	}

	function _inputPopulation() {
		$propStr = 'บันทึกข้อมูลประชากรตามทะเบียนราษฎร์ 1 เมษายน '.(date('Y')+543);

		return '<div style="margin-bottom:32px;background:#FAD163;">'
			. '<p class="notify">กรุณาบันทึกข้อมูลประชากรตามทะเบียนราษฎร์ เพื่อให้การจัดสรรงบประมาณเข้ากองทุนในปีงบประมาณหน้าจะได้ดำเนินการเสร็จสิ้นอย่างรวดเร็ว โดยการคลิกที่ปุ่ม "<b>'.$propStr.'</b>" ด้านล่าง <img src="/library/img/hot.1.gif" /></p>'
			. '<div style="text-align:center;"><a class="sg-action btn -primary" href="'.url('project/fund/'.$this->orgId.'/population.form').'" data-rel="box" data-width="full" style="display:inline-block;margin:0 auto 32px; padding:16px;"><i class="icon -addbig -white"></i><span>'.$propStr.'</span></a></div>'
			. '</div>';
	}

	function _inputEvalManage() {
		$stmt = 'SELECT * FROM %qtmast% WHERE `qtform` = 108 AND `orgid` = :orgId AND YEAR(`qtdate`) = :year LIMIT 1';
		$evalManageReady = mydb::select($stmt, ':orgId', $this->orgId, ':year', date('Y'));
		if (!$evalManageReady->count()) {
			$ret .= '<div class="notify">กองทุนยังไม่ได้ทำแบบประเมินการบริหารจัดการกองทุนหลักประกันสุขภาพ ประจำปี '.(date('Y')+543).' กรุณาทำแบบประเมินให้เรียบร้อยภายในเดือนธันวาคมนะคะ<br /><a class="btn -primary" href="'.url('project/fund/'.$this->orgId.'/eval.manage/new').'"><i class="icon -material">checklist</i><span>ทำแบบประเมิน</span></a><a class="sg-action btn -link" href="#" data-rel="none" data-done="remove:parent div" style="position: absolute; top: 4px; right: 4px;"><i class="icon -material">close</i></a></div>';
		}
		return $ret;
	}

	function _showDeleteFundButton($projectRs) {
		if ($projectRs->totalProject==0 && $fundInfo->right->admin) {
			$ret .= '<div class="iconset" style="text-align:right; margin:20px 0; background-color: transparent;"><a class="sg-action btn -danger" href="'.url('project/fund/'.$this->orgId.'/info/delete').'" data-rel="none" data-done="reload:'.url('project/fund').'" data-title="ลบกองทุน!!!" data-confirm="คำเตือน : การลบกองทุนจะทำการลบข้อมูลกองทุนและข้อมูลอื่น ๆ ที่เกี่ยวข้องกับกองทุน รวมทั้งข้อมูลการเงิน , ข้อมูลโครงการและกิจกรรม.<br /><b>ท่านต้องการลบกองทุนจริงหรือไม่? กรุณายืนยัน?</b>"><i class="icon -material">delete</i><span>ลบกองทุน !!!</span></a></div>';
		}
		return $ret;
	}

	function _showProject($process = 'notwithdraw') {
		// Get all follow not close
		$stmt = 'SELECT
				  p.`tpid`, p.`pryear`, t.`title`,p.`budget`
				, SUM(pd.`amount`) `totalPaid`
				, (SELECT SUM(rt.`num1`) FROM %project_tr% rt WHERE rt.`tpid` = p.`tpid` AND rt.`formid` = "info" AND `part` = "moneyback") `totalMoneyBack`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %project_paiddoc% pd USING(`tpid`)
			WHERE p.`prtype` = "โครงการ" AND t.`orgid` = :orgid AND p.`project_status` = 1
			GROUP BY `tpid`
			ORDER BY `tpid` DESC;
			-- {sum:"budget,totalPaid,totalMoneyBack"}';

		$dbs = mydb::select($stmt, ':orgid', $this->orgId);

		return new Container([
			'id' => 'project-card',
			'class' => 'project-card -project',
			'children' => [
				'<h3>โครงการ/กิจกรรม</h3>',
				new Row([
					'class' => 'nav -icons',
					'style' => 'padding: 8px',
					'children' => [
						'<a class="sg-action btn'.($process == 'notwithdraw' ? ' -active' : '').'" href="'.url('project/fund/'.$this->orgId,['follow' => 'notwithdraw']).'" data-rel="replace:#project-card"><i class="icon -material">money_off</i><span>รอเบิก</span></a>', // ยังมีเงินคงเหลือ
						'<a class="sg-action btn'.($process == 'withdraw' ? ' -active' : '').'" href="'.url('project/fund/'.$this->orgId,['follow' => 'withdraw']).'" data-rel="replace:#project-card"><i class="icon -material">price_check</i><span>เบิกบางส่วน</span></a>', // เบิกจ่ายครบ/ปิดโครงการแล้ว
						'<a class="sg-action btn'.($process == 'notclose' ? ' -active' : '').'" href="'.url('project/fund/'.$this->orgId,['follow' => 'notclose']).'" data-rel="replace:#project-card"><i class="icon -material">paid</i><span>รอปิด</span></a>', // เบิกจ่ายครบ/ยังไม่ปิดโครงการ
					],
				]),
				new Table([
					'style' => 'margin : 0;',
					'thead' => ['amt -year'=>'ปี','ชื่อโครงการ','amt -budget'=>'งบประมาณ','amt -balance'=>'งบคงเหลือ'],
					'children' => (function($items, $process) {
						$rows = [];
						$totalFollow = $totalBudget = $totalBalance = 0;
						foreach ($items as $item) {
							$budget = $item->budget;
							$balance = $budget - $item->totalPaid + $item->totalMoneyBack;
							$row = [
								$item->pryear+543,
								'<a href="'.url('project/'.$item->tpid).'">'.SG\getFirst($item->title,'===ยังไม่ระบุชื่อโครงการ===').'</a>',
								number_format($budget,2),
								number_format($balance,2),
							];
							if ($process == 'notwithdraw' && $balance > 0 && $balance == $budget) {
								$rows[] = $row;
								$totalBudget += $budget;
								$totalBalance += $balance;
								$totalFollow++;
							} else if ($process == 'withdraw' && $balance > 0 && $balance != $budget) {
								$rows[] = $row;
								$totalBudget += $budget;
								$totalBalance += $balance;
								$totalFollow++;
							} else if ($process == 'notclose' && $balance == 0) {
								$rows[] = $row;
								$totalBudget += $budget;
								$totalBalance += $balance;
								$totalFollow++;
							}
						}
						$rows[] = [
							'',
							'รวม '.$totalFollow.' โครงการ',
							number_format($totalBudget,2),
							number_format($totalBalance,2),
							'config' => ['class' => 'subfooter'],
						];
						return $rows;
					})($dbs->items, $process),
					// 'tfoot' => [
					// 	[
					// 		'',
					// 		'รวม',
					// 		number_format($dbs->sum->budget,2),
					// 		number_format($dbs->sum->budget-$dbs->sum->totalPaid+$dbs->sum->totalMoneyBack,2),
					// 	],
					// ],
				]),
				// '<p>จำนวน '.$dbs->_num_rows.' โครงการ <a class="btn" href="'.url('project/fund/'.$orgId.'/follow/all').'"><i class="icon -list"></i><span>โครงการทั้งหมด</span></a></p>',
			],
		]);
		$ret = '<div class="project-card -project">';
		$ret .= '<h3>โครงการ/กิจกรรม</h3>';

		$stmt = 'SELECT
				  p.`tpid`, p.`pryear`, t.`title`,p.`budget`
				, SUM(pd.`amount`) `totalPaid`
				, (SELECT SUM(rt.`num1`) FROM %project_tr% rt WHERE rt.`tpid`=p.`tpid` AND rt.`formid`="info" AND `part`="moneyback") `totalMoneyBack`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %project_paiddoc% pd USING(`tpid`)
			WHERE p.`prtype`="โครงการ" AND t.`orgid`=:orgid AND p.`project_status`=1
			GROUP BY `tpid`
			ORDER BY `tpid` DESC;
			-- {sum:"budget,totalPaid,totalMoneyBack"}';

		$dbs = mydb::select($stmt,':orgid',$this->orgId);
		//$ret .= mydb()->_query;

		$tables = new Table();
		$tables->thead=array('amt -year'=>'ปี','ชื่อโครงการ','amt -budget'=>'งบประมาณ','amt -balance'=>'งบคงเหลือ');
		$no=0;
		foreach ($dbs->items as $rs) {
			$tables->rows[] = array(
				$rs->pryear+543,
				'<a href="'.url('project/'.$rs->tpid).'">'.SG\getFirst($rs->title,'===ยังไม่ระบุชื่อโครงการ===').'</a>',
				number_format($rs->budget,2),
				number_format($rs->budget-$rs->totalPaid+$rs->totalMoneyBack,2),
			);
		}

		$tables->tfoot[] = array(
			'',
			'รวม',
			number_format($dbs->sum->budget,2),
			number_format($dbs->sum->budget-$dbs->sum->totalPaid+$dbs->sum->totalMoneyBack,2)
		);

		$ret .= $tables->build();
		$ret .= '<p>จำนวน '.$dbs->_num_rows.' โครงการ ';

		$ret .= '<a class="btn" href="'.url('project/fund/'.$orgId.'/follow/all').'"><i class="icon -list"></i><span>โครงการทั้งหมด</span></a></p>';
		$ret .= '</div>';
		return $ret;
	}

	// Customize guage css :: https://jsfiddle.net/dzhankhotov/vxdzyj79/
	function _showGuage($projectRs) {
		$currentBudgetYear = SG\getFirst($this->budgetYear, sg_budget_year(date('Y-m-d')));

		$finance = FundFinanceModel::yearRecieveExpense($this->orgId, $currentBudgetYear.'-09-30');

		$moneyIn = $finance->currentYear->openBalance + $finance->currentYear->recieve;
		$moneyOut = $finance->currentYear->expense;


		// $ret .= '<div class="project-card -summary">';
		// $tables = new Table();
		// $tables->rows[]=array('โครงการ',number_format($projectRs->activeProject).'/'.number_format($projectRs->totalProject).' โครงการ');
		// $tables->rows[]=array('งบประมาณ',number_format($projectRs->activeBudget,2).'/'.number_format($projectRs->totalBudget,2).' บาท');
		// //$tables->rows[]=array('งบคงเหลือ','xxx,xxx/xxxxx,xxx บาท');

		$stmt = 'SELECT
			p.`supporttype`, IFNULL(st.`name`,"ไม่ระบุ") `supporttypeName`
			, COUNT(*) `totalProject`, SUM(`budget`) `totalBudget`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %db_org% o USING(`orgid`)
				LEFT JOIN %tag% st ON st.`taggroup`="project:supporttype" AND st.`catid`=p.`supporttype`
			WHERE t.`orgid` = :orgId AND p.`prtype` = "โครงการ" AND p.`pryear` = :year
			GROUP BY `supporttypeName`
			ORDER BY p.`supporttype`;
			-- {sum:"totalProject,totalBudget"}';
		$type10Dbs = mydb::select($stmt, ':orgId', $this->orgId, ':year',$currentBudgetYear);

		$stmt = 'SELECT
			tgn.`catid`, tgn.`name` `targetName`
			, a.*
			FROM %tag% tgn
				LEFT JOIN (SELECT
				o.`orgId`, t.`tpid`, t.`title`, p.`pryear`
				, COUNT(DISTINCT t.`tpid`) `totalProject`
				, SUM(tg.`amount`) `totalTarget`
				, SUM(p.`budget`) `totalBudget`
				, tg.`tgtid`
				FROM %db_org% o
					LEFT JOIN %topic% t ON t.`type` = "project" AND t.`orgid` = o.`orgid`
					LEFT JOIN %project% p ON p.`tpid` = t.`tpid`
					LEFT JOIN %project_target% tg ON tg.`tpid` = t.`tpid` AND tg.`tagname` = "info"
				WHERE t.`orgid` = :orgId AND  p.`pryear` = :year AND p.`prtype` = "โครงการ" AND `tgtid` != "2005"
				GROUP BY `tgtid`
			) a ON tgn.`taggroup` = "project:target" AND a.`tgtid` = tgn.`catid`
			WHERE tgn.`taggroup` = "project:target" AND tgn.`catparent` IS NOT NULL AND tgn.`catid` != "2005"
			ORDER BY tgn.`catid` ASC;
			-- {sum:"totalProject,totalTarget,totalBudget"}
		';
		$costDbs = mydb::select($stmt, ':orgId', $this->orgId, ':year',$currentBudgetYear);
		// debugMsg($costDbs, '$costDbs');

		return new Card([
			'class' => 'project-card -guage',
			'style' => 'width: 100%; margin: 0 0 32px;',
			'children' => [
				new Form([
					'class' => 'sg-form -sg-flex',
					'action' => url('project/fund/'.$this->orgId),
					'method' => 'get',
					'rel' => '#main',
					'children' => [
						'year' => [
							'type' => 'select',
							'options' => (function() {
								$options = [];
								$startYear = SG\getFirst('2016');
								for ($year = sg_budget_year(date('Y-m-d')); $year >= $startYear; $year--) {
									$options[$year] = 'ปีงบประมาณ '.($year+543);
								}
								return $options;
							})(),
							'value' => $this->budgetYear,
							'attr' => ['onChange' => 'this.form.submit()'],
						],
					], // children
				]),
				new Row([
					'style' => 'flex-wrap: wrap;',
					'children' => [
						// รายจ่ายเทียบกับยอดยกมา+รายรับ = จ่าย/(ยกมา+รับ)
						// ร้อยละการเบิกเงินเทียบเงินทั้งหมด ปี 2564
						new Widget([
							'children' => [
								'<h6>ร้อยละการเบิกเงินเทียบเงินทั้งหมด ปี '.($currentBudgetYear+543).'</h6>',
								new Container([
									'id' => 'guage-remain',
									'class' => 'sg-chart -guage',
									'attribute' => ['data-chart-type' => 'guage',],
									'child' => new Table([
										'class' => '-hidden',
										'children' => [
											[
												'string:Label' => 'จ่าย '.round((($moneyOut)*100/$moneyIn)).'%',
												'Value' => $moneyOut,
												'max' => $moneyIn,
												'redFrom' => 0,
												'redTo' => $moneyIn*0.4,
												'yellowFrom' => $moneyIn*0.4,
												'yellowTo' => $moneyIn*0.7,
												'greenFrom' => $moneyIn*0.7,
												'greenTo' => $moneyIn,
											]
										], // children
									]), // Table
								]), // Container
							], // children
						]), // Container

						// เบิกงบแล้ว
						// ร้อยการเบิกจ่ายงบประมาณของโครงการ ปี 2564
						new Widget([
							'children' => [
								'<h6>ร้อยการเบิกจ่ายงบของโครงการ ปี '.($currentBudgetYear+543).'</h6>',
								new Container([
									'id' => 'budget-guage',
									'class' => 'sg-chart -guage',
									'attribute' => ['data-chart-type' => 'guage',],
									'child' => new Table([
										'class' => '-hidden',
										'children' => [
											[
												'string:Label' => 'เบิก '.($projectRs->totalBudget ? round((($projectRs->paidBudget)*100/$projectRs->totalBudget)) : '0').'%',
												'Value' => $projectRs->paidBudget,
												'max' => $projectRs->totalBudget,
												// 'redFrom' => 0,
												// 'redTo' => $projectRs->totalBudget*0.40,
												// 'yellowFrom' => $projectRs->totalBudget*0.40,
												// 'yellowTo' => $projectRs->totalBudget*0.70,
												// 'greenFrom' => $projectRs->totalBudget*.70,
												'greenFrom' => 0,
												'greenTo' => $projectRs->paidBudget,
												'redFrom' => $projectRs->paidBudget,
												'redTo' => $projectRs->totalBudget,
												'string:redColor' => '#ccc',
											]
										], // children
									]), // Table
								]), // Container
							], // children
						]), // Container

						// โครงการปิดแล้ว
						// จำนวนโครงการที่ปิด
						new Widget([
							'children' => [
								'<h6>โครงการที่ดำเนินการเรียบร้อย ปี '.($currentBudgetYear+543).'</h6>',
								new Container([
									'id' => 'project-guage',
									'class' => 'sg-chart -guage',
									'attribute' => ['data-chart-type' => 'guage',],
									'child' => new Table([
										'class' => '-hidden',
										'children' => [
											[
												'string:Label' => 'ปิด '.($projectRs->totalProject ? round(($projectRs->totalProject - $projectRs->activeProject)*100/$projectRs->totalProject) : '0').'%',
												'Value' => $projectRs->totalProject - $projectRs->activeProject,
												'max' => $projectRs->totalProject,
												'greenFrom' => 0,
												'greenTo' => $projectRs->totalProject - $projectRs->activeProject,
												'redFrom' => $projectRs->totalProject - $projectRs->activeProject,
												'redTo' => $projectRs->totalProject,
												'string:redColor' => '#ccc',
											]
										], // children
									]), // Table
								]), // Container
							], // children
						]), // Container

						// โครงการแต่ละประเภท
						// ร้อยละจำนวนโครงการแยกตามประเภท
						new Widget([
							'children' => [
								'<h6>ร้อยละจำนวนโครงการแยกตามประเภท ปี '.($currentBudgetYear+543).'</h6>',
								new Container([
									'id' => 'chart-project-org',
									'class' => 'sg-chart -pie -project-org',
									'attribute' => ['data-chart-type' => 'pie', 'data-options' => '{pieHole: 0.4}'],
									'children' => [
										new Table([
											'class' => '-hidden',
											'children' => (function($items) {
												$rows = [];
												foreach ($items as $item) {
													$rows[] = [
														'string:Label' => '10('.$item->supporttype.')',
														'number:Value' => $item->totalProject,
													];
												}
												return $rows;
											})($type10Dbs->items),
										]), // Table
									], // children
								]), // Container
							], // children
						]), // Container

						// งบประมาณแต่ละประเภท
						// ร้อยละงบประมาณแยกตามประเภท
						new Widget([
							'children' => [
								'<h6>ร้อยละงบประมาณแยกตามประเภท ปี '.($currentBudgetYear+543).'</h6>',
								new Container([
									'id' => 'chart-expense-org',
									'class' => 'sg-chart -pie -expense-org',
									'attribute' => ['data-chart-type' => 'pie', 'data-options' => '{pieHole: 0.4}'],
									'children' => [
										new Table([
											'class' => '-hidden',
											'children' => (function($items) {
												$rows = [];
												foreach ($items as $item) {
													$rows[] = [
														'string:Label' => '10('.$item->supporttype.')',
														'number:Value' => $item->totalBudget,
													];
												}
												return $rows;
											})($type10Dbs->items),
										]), // Table
									], // children
								]), // Container
							], // children
						]), // Container

						// โครงการ/งบทุกปี
						new Widget([
							'children' => [
								'<h6>จำนวนโครงการ/งบประมาณแต่ละปี</h6>',
								new Container([
									'id' => 'year-project',
									'class' => 'sg-chart -col -project',
									'attribute' => ['data-chart-type' => 'col', 'data-series' => 2],
									'children' => [
										new Table([
											'class' => '-hidden',
											'children' => (function() {
												$rows = [];
												$stmt = 'SELECT
													  p.`pryear`
													, COUNT(*) `totalProject`
													, SUM(p.`budget`) `totalBudget`
													FROM %project% p
														LEFT JOIN %topic% t USING(`tpid`)
													WHERE t.`orgid`=:orgid AND p.`prtype`="โครงการ"
													GROUP BY `pryear`
													ORDER BY `pryear` ASC';

												$dbs = mydb::select($stmt,':orgid',$this->orgId);

												foreach ($dbs->items as $rs) {
													$rows[] = [
														'string:Year'=>$rs->pryear+543,
														'number:Project'=>$rs->totalProject,
														'number:Budget'=>$rs->totalBudget,
													];
												}
												return $rows;
											})(),
										]), // Table
									], // children
								]), // Container
							], // children
						]), // Container

					], // children
				]), // Row

				// ความคุ้มทุน
				new Container([
					'class' => '-value-base',
					'children' => [
						new Container([
							'class' => '-value-card',
							'children' => (function($costDbs) {
								$widgets = ['<h5>งบประมาณตามกลุ่มเป้าหมาย</h5>'];
								$no = 0;
								$icons = [
									'1001' => 'child_care',
									'1002' => 'accessibility_new',
									'1003' => 'badge',
									'1004' => 'elderly',
									'2001' => 'pregnant_woman',
									'2002' => 'heart_broken',
									'2003' => 'accessible',
									'2004' => 'groups',
								];
								foreach ($costDbs->items as $item) {
									$widgets[] = new ListTile([
										'class' => '-type-'.$item->catid,
										'crossAxisAlignment' => 'start',
										'leading' => '<i class="icon -material">'.$icons[$item->catid].'</i>',
										'title' => ++$no.'. '.$item->targetName,
										'subtitle' => '<em>'.number_format($item->totalProject).'</em> โครงการ <em>'.number_format($item->totalTarget).'</em> คน<br /><em>'.number_format($item->totalBudget,2).'</em> บาท',
									]);
								}
								return $widgets;
							})($costDbs),
						]), // Container
						new Container([
							'class' => '-value-table',
							'child' => new Table([
								'class' => 'item',
								'thead' => [
									'กลุ่มเป้าหมาย',
									'project -amt' => 'โครงการ',
									'target -amt' => 'คน',
									'budget -money -nowrap' => 'งบประมาณ<br />(บาท)',
									'percent -amt -nowrap' => 'สัดส่วนงบ<br />(%)',
									'avg -amt -nowrap' => 'เฉลี่ย<br />(บาท/คน)'],
								'children' => (function($costDbs) {
									$rows = [];
									foreach ($costDbs->items as $item) {
										$rows[] = [
											$item->targetName,
											number_format($item->totalProject),
											number_format($item->totalTarget),
											number_format($item->totalBudget,2),
											$costDbs->sum->totalBudget ? number_format(($item->totalBudget*100)/$costDbs->sum->totalBudget,1).'%' : '-',
											$item->totalTarget ? number_format($item->totalBudget/$item->totalTarget,2) : '-',
										];
									}
									return $rows;
								})($costDbs),
							]), // Table
						]), // Container
					], // children
				]), // Container

			], // children
		]);
	}

	function _showGraph() {
		$graphYear = new Table();
		$graphYearProject = new Table();

		$stmt = 'SELECT
			  p.`pryear`
			, COUNT(*) `totalProject`
			, SUM(p.`budget`) `totalBudget`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
			WHERE t.`orgid`=:orgid AND p.`prtype`="โครงการ"
			GROUP BY `pryear`
			ORDER BY `pryear` ASC';

		$dbs = mydb::select($stmt,':orgid',$this->orgId);
		//$ret .= print_o($dbs,'$dbs');

		foreach ($dbs->items as $rs) {
			$graphYear->rows[]=array('string:Year'=>$rs->pryear+543,'number:Project'=>$rs->totalProject,'number:Budget'=>$rs->totalBudget);
			$graphYearProject->rows[]=array('string:Year'=>$rs->pryear+543,'number:Project'=>$rs->totalProject);
		}
		//$ret .= print_o($graphYear,'$graphYear');
		$ret .= '<div id="year-project-2" class="sg-chart -project" data-chart-type="col" data-series="2"><h3>จำนวนโครงการ/งบประมาณแต่ละปี</h3>'._NL.$graphYear->build().'</div>';
		//$ret .= '<div id="year-budget" class="sg-chart -project" data-chart-type="col"><h3>จำนวนโครงการ/งบประมาณแต่ละปี</h3>'._NL.$graphYearProject->build().'</div>';
		$ret .= '</div><!-- project-card -->';
		return $ret;
	}

	function _showInfo() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->fundInfo->name,
				'leading' => _HEADER_BACK,
				'boxHeader' => 'true',
				'trailing' => new Row([
					'children' => [
						$this->right->edit ? '<a class="sg-action btn -link" href="'.url('project/fund/'.$this->orgId.'/info.area').'" data-rel="box" data-width="full"><i class="icon -material">edit</i></a>' : NULL,
					],
				]),
			]),
			'body' => new Container([
				'class' => '-sg-paddingmore',
				'children' => [
					new Card([
						'children' => [
							new ListTile([
								'title' => 'รายละเอียดกองทุน',
								'leading' => '<i class="icon -material">info</i>',
								'trailing' => new Row([
									'children' => [
										$this->right->edit ? '<a class="sg-action btn -link" href="'.url('project/fund/'.$this->orgId.'/info.area').'" data-rel="box" data-width="full"><i class="icon -material">edit</i></a>' : NULL,
									], // children
								]), // Row
							]), // ListTile
							new Container([
								'class' => '-sg-padding-more',
								'children' => [
									'<p>ชื่อกองทุน : <b>'.$this->fundInfo->name.'</b></p>'
									. '<p><b>เขต '.$this->fundInfo->info->areaid.' '.$this->fundInfo->info->namearea
									. ' จังหวัด'.$this->fundInfo->info->changwatName
									. ' อำเภอ'.$this->fundInfo->info->ampurName
									. ' ตำบล'.$this->fundInfo->info->tambonName
									. '</b></p>'
									. '<p>ที่อยู่ : <b>'.$this->fundInfo->info->address.' '.$this->fundInfo->info->orgzip.'</b></p>'
									. '<p>โทรศัพท์ : <b>'.$this->fundInfo->info->orgphone.($this->fundInfo->info->orgfax?' โทรสาร: '.$this->fundInfo->info->orgfax:'').'</b></p>'
									. '<p>อีเมล์ : <b>'.$this->fundInfo->info->orgemail.'</b></p>'
									. '<p>ประเภท : <b>'.$this->fundInfo->info->orgSizeName.'</b></p>',
								], // children
							]), // Container
						], // children
					]), // Card
					new Card([
						'children' => [
						new ListTile([
								'title' => 'พื้นที่ดำเนินงาน',
								'leading' => '<i class="icon -material">push_pin</i>',
								'trailing' => new Row([
									'children' => [
										$this->right->edit ? '<a class="sg-action btn -link" href="'.url('project/fund/'.$this->orgId.'/info.area').'" data-rel="box" data-width="full"><i class="icon -material">edit</i></a>' : NULL,
									], // children
								]), // Row
							]), // ListTile
							new Container([
								'class' => '-sg-padding-more',
								'children' => [
									'<p>พื้นที่รับผิดชอบ : <b>'.number_format($this->fundInfo->info->tambonnum).'</b> ตำบล <b>'.number_format($this->fundInfo->info->moonum).'</b> ชุมชน/หมู่บ้าน</p>'
									. '<p>จัดตั้งเมื่อ : <b>พ.ศ. '.($this->fundInfo->info->openyear+543).'</b></p>'
									. '<p>จำนวนประชากร : <b>'.number_format($this->fundInfo->info->population).'</b> คน เมื่อ<b>วันที่ 1 กรกฎาคม 2559</b></p>'
									. '<p>พิกัด : <b>'.$this->fundInfo->info->location.'</b> <a class="sg-action" href="'.url('org/'.$this->orgId.'/info.map').'" data-rel="box" data-width="600"><i class="icon -material">room</i></a></p>',
								], // children
							]), // Container
						], // children
					]), // Card
					new Card([
						'children' => [
						new ListTile([
								'title' => 'บัญชี-การเงิน',
								'leading' => '<i class="icon -material">account_balance</i>',
								'trailing' => new Row([
									'children' => [
										$this->right->edit ? '<a class="sg-action btn -link" href="'.url('project/fund/'.$this->orgId.'/info.finance').'" data-rel="box" data-width="full"><i class="icon -material">edit</i></a>' : NULL,
									], // children
								]), // Row
							]), // ListTile
							new Container([
								'class' => '-sg-padding-more',
								'children' => [
									'<p>บัญชีธนาคาร : <b>'.$this->fundInfo->info->accbank.'</b><br />'
									. 'ชื่อบัญชี : <b>'.$this->fundInfo->info->accname.'</b><br />'
									. 'เลขที่บัญชี : <b>'.($this->right->edit ? $this->fundInfo->info->accno : 'XXX-X-XXXXX-X').'</b><br />'
									. 'ยอดเงินคงเหลือยกมา : <b>'.number_format($this->fundInfo->info->openbalance,2).'</b> บาท  เมื่อ<b>วันที่ '.($this->fundInfo->info->openbaldate ? sg_date($this->fundInfo->info->openbaldate, 'ว ดดด ปปปป') : '').'</b></p>',
								], // children
							]), // Container
						], // children
					]), // Card
				],
			]),
		]);
		return $ret;
	}

	function _showLtc() {
		$stmt = 'SELECT * FROM %bigdata% WHERE `keyname` = "project.ltc" AND `keyid` = :orgId AND `fldname` = "info.contact" LIMIT 1';
		$rs = mydb::select($stmt, ':orgId', $this->orgId);
		$ltcInfo = sg_json_decode($rs->flddata);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'รายละเอียดกองทุน LTC',
				'leading' => _HEADER_BACK,
				'boxHeader' => 'true',
				'trailing' => new Row([
					'children' => [
						$this->right->edit ? '<a class="sg-action btn -link" href="'.url('project/ltc/'.$this->orgId.'/info.form').'" data-rel="box" data-width="full"><i class="icon -material">edit</i></a>' : NULL,
					],
				]),
			]),
			'body' => new Container([
				'class' => '-sg-paddingmore',
				'children' => [
					$rs->count() ?
						'<p>ชื่อผู้ประสานงาน : <b>'.$ltcInfo->contactname.'</b><br />'
						. 'โทรศัพท์ : <b>'.$ltcInfo->orgphone.'</b> โทรสาร: <b>'.$ltcInfo->orgfax.'</b><br />'
						. 'อีเมล์ : <b>'.$ltcInfo->orgemail.'</b><br />'
						. 'จัดตั้งเมื่อ พ.ศ. <b>'.($ltcInfo->openyear ? $ltcInfo->openyear+543 : '').'</b><br />'
						. '</p>'
					:
						'<p class="-sg-text-center" style="padding: 32px;">ยังไม่มีข้อมูลกองทุน LTC'
						.($this->right->edit ? '<br /><br />หากกองทุนสุขภาพตำบลมีการจัดตั้งกองทุน LTC กรุณาบันทึกข้อมูลรายละเอียดการติดต่อของกองทุน LTC ด้วยค่ะ<br /><br /><br /><a class="sg-action btn" href="'.url('project/ltc/'.$this->orgId.'/info.form').'" data-rel="box" data-width="640"><i class="icon -material">add</i><span>บันทึกรายละเอียดกองทุน LTC</span></a></p>' : NULL)
						. '</p>',
				],
			]),
		]);

		$stmt = 'SELECT * FROM %bigdata% WHERE `keyname` = "project.ltc" AND `keyid` = :orgId AND `fldname` = "info.contact" LIMIT 1';
		$rs = mydb::select($stmt, ':orgId', $this->orgId);
		$ltcInfo = sg_json_decode($rs->flddata);

		$ret .= '<div class="project-card -ltc">';
		$ui = new ui();
		if ($this->right->edit) {
			$ui->add('<a class="sg-action btn -link" href="'.url('project/ltc/'.$this->orgId.'/info.form').'" data-rel="box" data-width="640"><i class="icon -material">edit</i></a>');
		}
		$ret .= '<header class="header"><h3>รายละเอียดกองทุน LTC</h3><nav class="nav">'.$ui->build().'</nav></header>';
		if ($rs->count()) {
			$ret .= '<p>ชื่อผู้ประสานงาน : <b>'.$ltcInfo->contactname.'</b><br />';
			$ret .= 'โทรศัพท์ : <b>'.$ltcInfo->orgphone.'</b> โทรสาร: <b>'.$ltcInfo->orgfax.'</b><br />';
			$ret .= 'อีเมล์ : <b>'.$ltcInfo->orgemail.'</b><br />';
			$ret .= 'จัดตั้งเมื่อ พ.ศ. <b>'.($ltcInfo->openyear ? $ltcInfo->openyear+543 : '').'</b><br />';
			$ret .= '</p>';
		} else {
			$ret .= '<p class="-sg-text-center" style="padding: 32px;">ยังไม่มีข้อมูลกองทุน LTC';
			if ($this->right->edit) {
				$ret .= '<br /><br />หากกองทุนสุขภาพตำบลมีการจัดตั้งกองทุน LTC กรุณาบันทึกข้อมูลรายละเอียดการติดต่อของกองทุน LTC ด้วยค่ะ<br /><br /><br /><a class="sg-action btn" href="'.url('project/ltc/'.$this->orgId.'/info.form').'" data-rel="box" data-width="640"><i class="icon -material">add</i><span>บันทึกรายละเอียดกองทุน LTC</span></a></p>';
			}
			$ret .= '</p>';
		}
		$ret .= '</div>';
		return $ret;
	}

	function _showBoard() {
		// return new Scaffold([
		// 	'appBar' => new AppBar([
		// 		'title' => 'คณะกรรมการ',
		// 		'leading' => _HEADER_BACK,
		// 		'boxHeader' => 'true',
		// 		'trailing' => new Row([
		// 			'children' => [
		// 				$this->right->edit ? '<a class="sg-action btn -link" href="'.url('project/fund/'.$this->orgId.'/info.area').'" data-rel="box" data-width="full"><i class="icon -material">edit</i></a>' : NULL,
		// 			],
		// 		]),
		// 	]),
		// 	'body' => new Container([
		// 		'class' => '-sg-paddingmore',
		// 		'children' => [
		// 		],
		// 	]),
		// ]);

		// $ret .= '<div class="project-card -board">';
		// $ui = new Ui();
		// $ui->add('<a class="btn -link" href="'.url('project/fund/'.$this->orgId.'/board').'"><i class="icon -people"></i></a>');
		// if ($this->right->edit) {
		// 	$ui->add('<a class="btn -link" href="'.url('project/fund/'.$this->orgId.'/board').'"><i class="icon -edit"></i></a>');
		// }
		// $ret .= '<header class="header"><h3>คณะกรรมการ</h3><nav class="nav">'.$ui->build().'</nav></header>';

		// $stmt = 'SELECT b.*, bp.`name` `boardName`
		// 	FROM %org_board% b
		// 		LEFT JOIN %tag% bp ON bp.`catid` = b.`boardposition` AND bp.`taggroup` = "project:board"
		// 	WHERE b.`orgid` = :orgid AND b.`status` = 1
		// 	ORDER BY IF(`boardposition` = 2,0,1),`boardposition`, `position`
		// 	LIMIT 5';

		// $dbs = mydb::select($stmt,':orgid',$this->orgId);

		// $tables=new table('item -board');
		// $tables->thead=array('ชื่อ-นามสกุล', 'pos -center' => 'ตำแหน่ง');
		// foreach ($dbs->items as $rs) {
		// 	$tables->rows[]=array(
		// 		$rs->name,
		// 		$rs->boardName,
		// 	);
		// }
		// $ret .= $tables->build();
		// $ret .= '</div>';
		// return $ret;
	}

	function _showPopulation() {
		//TODO: Bug มีการเปลี่ยนแปลงรหัสกองทุนจาก 5xxxx เป็น Lxxxx แต่ใน project_tr ยังคงบันทึกค่ารหัสกองทุนเก่า => ให้เปลี่ยนเป็น รหัสองค์กร แทน
		$stmt = 'SELECT tr.`trid`, YEAR(tr.`date1`) `recordYear`, tr.`num2` `population`
			FROM %project_tr% tr
			WHERE tr.`formid` = "population" AND tr.`part` = :fundid
			ORDER BY tr.`trid` DESC
			LIMIT 2';

		$dbs = mydb::select($stmt,':fundid',$this->fundInfo->fundid);

		return new Card([
			'class' => 'project-card -population',
			'children' => [
				new ListTile([
					'style' => 'padding: 8px;',
					'title' => 'ประชากร',
					'leading' => '<i class="icon -material">groups</i>',
					'trailing' => $this->right->addPopulation ? new Row([
						'children' => [
							'<a class="sg-action btn -link" href="'.url('project/fund/'.$this->orgId,['show' => 'popdocs']).'" data-rel="#population-docs"><i class="icon -material">view_list</i><span>เอกสาร</span></a>',
							'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('project/fund/'.$this->orgId.'/upload',array('tagname'=>'population')).'" data-rel="#population-photo" data-prepend="li"><span class="btn btn-success fileinput-button"><i class="icon -upload"></i><span>อัพโหลด</span><input type="file" name="photo[]" multiple="true" class="inline-upload" /></span><input class="-hidden" type="submit" value="upload" /></form>',
						]
					]) : NULL,
				]),
				// Population table
				new Table([
					'thead' => ['พ.ศ.','amt -hover-parent' => 'คน', 'pop -hover-parent' => ''],
					'children' => (function($items) {
						$rows = [];

						$maxPopulation = 0;
						foreach ($items as $rs) {
							if ($maxPopulation < $rs->population) $maxPopulation = $rs->population;
						}
						foreach ($items as $rs) {
							$menu = new Ui();
							if ($this->right->addPopulation && $this->isInPopulationPeriod && $this->nextPopulationYear == $rs->recordYear) {
								$menu->add('<a class="sg-action" href="'.url('project/fund/'.$this->orgId.'/population.form',array('year' => $rs->recordYear)).'" data-rel="box" data-width="full"><i class="icon -material">edit</i></a>');
								$menu->add('<a class="sg-action" href="'.url('project/fund/'.$this->orgId.'/info/population.remove/'.$rs->trid, array('year' => $rs->recordYear)).'" data-rel="notify" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-done="remove:parent tr"><i class="icon -material -gray">cancel</i></a>');
							}

							$graphValue = ($rs->population/$maxPopulation)*10;

							$rows[] = array(
								$rs->recordYear+543,
								number_format($rs->population),
								str_repeat('<i class="icon -material -pop">person</i>',$graphValue)
									. ($menu->count() ? '<nav class="nav -icons -hover">'.$menu->build().'</nav>' : ''),
							);
						}
						return $rows;
					})($dbs->items),
				]),

				'<div id="population-docs"><ul id="population-photo" class="ui-album photocard -loapp -no-print"></ul></div>',
				// $this->right->addPopulation ? '<div style="margin:20px 0;"><form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('project/fund/'.$this->orgId.'/upload',array('tagname'=>'population')).'" data-rel="#population-photo" data-prepend="li"><span class="btn btn-success fileinput-button"><i class="icon -upload"></i><span>อัพโหลดเอกสารประชากรประจำปี</span><input type="file" name="photo[]" multiple="true" class="inline-upload" /></span><input class="-hidden" type="submit" value="upload" /></form></div>'._NL : NULL,
			],
		]);
	}

	function _showPopulationDocs() {
		$ret = '<ul id="population-photo" class="ui-album photocard -loapp -no-print">'._NL;
		// Get photo from database
		$photos=mydb::select('SELECT f.`fid`, f.`type`, f.`file`, f.`title`, f.`timestamp` `created` FROM %topic_files% f WHERE f.`orgid`=:orgid AND `tagname`="population" ORDER BY `fid` DESC', ':orgid',$this->orgId);
		// debugMsg($photos,'$photos');

		// Show photos
		foreach ($photos->items as $rs) {
			if ($rs->type=='photo') {
				$photo=model::get_photo_property($rs->file);
				$photo_alt=$rs->title;
				$ret .= '<li class="ui-item -hover-parent" style="width: 120px; height: 120px;">';
				$ret .= '<a class="sg-action" data-group="photo" href="'.$photo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
				$ret .= '<img class="photoitem -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" ';
				$ret .= ' />';
				$ret .= '</a>';
				$photomenu=array();
				$ui = new Ui('span');
				if ($this->right->addPopulation) {
					$ui->add('<a class="sg-action" href="'.url('project/edit/delphoto/'.$rs->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="none" data-removeparent="li"><i class="icon -material">cancel</i></a>','{class:"-hover-parent"}');
				}
				$ret .= $ui->build();
				/*
				if ($this->right->edit) {
					$ret .= view::inlineedit(array('group'=>'photo','fld'=>'title','tr'=>$rs->fid),$rs->title,$this->right->edit,'text');
				} else {
					$ret .= '<span>'.$rs->title.'</span>';
				}
				*/
				$ret .= '</li>'._NL;
			} else {
				$uploadUrl=cfg('paper.upload.document.url').$rs->file;
				$ret .= '<li class="-hover-parent"><a href="'.$uploadUrl.'"><img src="//img.softganz.com/icon/pdf-icon.png" /><br />';
				$ret .= $rs->title.'<br />@'.sg_date($rs->created,'d-m-ปปปป');
				$ret .= '</a>';
				$ui=new Ui('span');
				if ($this->right->addPopulation) {
					$ui->add('<a class="sg-action" href="'.url('project/edit/delphoto/'.$rs->fid).'" title="ลบไฟล์นี้" data-confirm="ยืนยันว่าจะลบไฟล์นี้จริง?" data-rel="none" data-removeparent="li"><i class="icon -delete"></i></a>');
				}
				$ret .= '<nav class="nav iconset -hover">'.$ui->build().'</nav>';
				$ret .= '</li>';
			}
		}
		$ret .= '</ul><!-- loapp-photo -->';
		return $ret;
	}

	function _showPopulationV1() {
		$ret = '';
		//TODO: Bug มีการเปลี่ยนแปลงรหัสกองทุนจาก 5xxxx เป็น Lxxxx แต่ใน project_tr ยังคงบันทึกค่ารหัสกองทุนเก่า => ให้เปลี่ยนเป็น รหัสองค์กร แทน
		$stmt = 'SELECT tr.`trid`, YEAR(tr.`date1`) `recordYear`, tr.`num2` `population`
			FROM %project_tr% tr
			WHERE tr.`formid` = "population" AND tr.`part` = :fundid
			ORDER BY tr.`trid` DESC';

		$dbs = mydb::select($stmt,':fundid',$this->fundInfo->fundid);

		$tables = new Table();
		$graphData = new Table();
		$tables->addClass('-center');
		$tables->thead = array('ปี พ.ศ.','amt -hover-parent' => 'ประชากร(คน)');
		foreach ($dbs->items as $rs) {
			$menu = new Ui();
			$dropMenu = new Ui();
			if ($this->right->addPopulation && $this->isInPopulationPeriod) {
				if ($this->nextPopulationYear == $rs->recordYear) {
					$menu->add('<a class="sg-action" href="'.url('project/fund/'.$this->orgId.'/population.form',array('year' => $rs->recordYear)).'" data-rel="box" data-width="800"><i class="icon -material">edit</i></a>');
					$dropMenu->add('<a class="sg-action" href="'.url('project/fund/'.$this->orgId.'/info/population.remove/'.$rs->trid, array('year' => $rs->recordYear)).'" data-rel="notify" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-done="remove:parent tr"><i class="icon -material">cancel</i><span>ลบรายการ</span></a>');
				}
				if ($dropMenu->count()) $menu->add(sg_dropbox($dropMenu->build()));
			}

			$tables->rows[] = array(
				$rs->recordYear+543,
				number_format($rs->population)
					. ($menu->count() ? '<nav class="nav -icons -hover">'.$menu->build().'</nav>' : ''),
			);
			$graphData->rows[] = array(
				'string:Year' => $rs->recordYear+543,
				'number:Population' => number_format($rs->population),
			);
		}
		$ret .= $tables->build();

		$graphData->rows = array_reverse($graphData->rows);

		$ret .= '<div id="year-population" class="sg-chart -population" style="height:200px;"><h3>จำนวนประชากรแต่ละปี</h3>'.$graphData->build().'</div>';

		if ($this->right->addPopulation) {
			$ret .= '<div style="margin:20px 0;"><form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('project/fund/'.$this->orgId.'/upload',array('tagname'=>'population')).'" data-rel="#population-photo" data-prepend="li"><span class="btn btn-success fileinput-button"><i class="icon -upload"></i><span>อัพโหลดเอกสารประชากรประจำปี</span><input type="file" name="photo[]" multiple="true" class="inline-upload" /></span><input class="-hidden" type="submit" value="upload" /></form></div>'._NL;
		}



		$ret .= '<ul id="population-photo" class="ui-album photocard -loapp -no-print">'._NL;
		// Get photo from database
		$photos=mydb::select('SELECT f.`fid`, f.`type`, f.`file`, f.`title`, f.`timestamp` `created` FROM %topic_files% f WHERE f.`orgid`=:orgid AND `tagname`="population" ORDER BY `fid` DESC', ':orgid',$this->orgId);
		//$ret .= print_o($photos,'$photos');

		// Show photos
		foreach ($photos->items as $rs) {
			list($photoid,$photo)=explode('|',$rs);
			if ($rs->type=='photo') {
				$photo=model::get_photo_property($rs->file);
				$photo_alt=$rs->title;
				$ret .= '<li class="ui-item -hover-parent" style="width: 120px; height: 120px;">';
				$ret .= '<a class="sg-action" data-group="photo" href="'.$photo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
				$ret .= '<img class="photoitem -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" ';
				$ret .= ' />';
				$ret .= '</a>';
				$photomenu=array();
				$ui = new Ui('span');
				if ($this->right->addPopulation) {
					$ui->add('<a class="sg-action" href="'.url('project/edit/delphoto/'.$rs->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="none" data-removeparent="li"><i class="icon -material">cancel</i></a>','{class:"-hover-parent"}');
				}
				$ret .= $ui->build();
				/*
				if ($this->right->edit) {
					$ret .= view::inlineedit(array('group'=>'photo','fld'=>'title','tr'=>$rs->fid),$rs->title,$this->right->edit,'text');
				} else {
					$ret .= '<span>'.$rs->title.'</span>';
				}
				*/
				$ret .= '</li>'._NL;
			} else {
				$uploadUrl=cfg('paper.upload.document.url').$rs->file;
				$ret .= '<li class="-hover-parent"><a href="'.$uploadUrl.'"><img src="//img.softganz.com/icon/pdf-icon.png" /><br />';
				$ret .= $rs->title.'<br />@'.sg_date($rs->created,'d-m-ปปปป');
				$ret .= '</a>';
				$ui=new Ui('span');
				if ($this->right->addPopulation) {
					$ui->add('<a class="sg-action" href="'.url('project/edit/delphoto/'.$rs->fid).'" title="ลบไฟล์นี้" data-confirm="ยืนยันว่าจะลบไฟล์นี้จริง?" data-rel="none" data-removeparent="li"><i class="icon -delete"></i></a>');
				}
				$ret .= '<nav class="nav iconset -hover">'.$ui->build().'</nav>';
				$ret .= '</li>';
			}
		}
		$ret .= '</ul><!-- loapp-photo -->';
		// $ui = new Ui();
		// if ($this->right->addPopulation) {
		// 	$ui->add('<a class="sg-action btn -link" href="'.url('project/fund/'.$this->orgId.'/population.form').'" data-rel="box" data-width="800"><i class="icon -add"></i></a>');
		// }
		// $ret .= '<header class="header"><h3>ประชากร</h3><nav class="nav">'.$ui->build().'</nav></header>';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ประชากร',
				'leading' => _HEADER_BACK,
				'boxHeader' => 'true',
				'trailing' => new Row([
					'children' => [
						$this->right->addPopulation ? '<a class="sg-action btn -link" href="'.url('project/fund/'.$this->orgId.'/population.form').'" data-rel="box" data-width="full"><i class="icon -material">edit</i></a>' : NULL,
					],
				]),
			]),
			'body' => new Container([
				'class' => '-sg-paddingmore',
				'children' => [
					$ret,
				],
			]),
		]);
	}

	function _showOfficer() {
		$isCreateMember = $this->fundInfo->right->createMember;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'เจ้าหน้าที่กองทุน',
				'leading' => _HEADER_BACK,
				'boxHeader' => 'true',
				'trailing' => new Row([
					'children' => [
						$isCreateMember ? '<a class="sg-action btn" href="'.url('project/fund/'.$this->orgId.'/info.officer/add').'" data-rel="load:#officer"><i class="icon -material">add_circle_outline</i><span>พี่เลี้ยง</span></a>' : NULL,
						$isCreateMember ? '<a class="btn -primary" href="'.url('project/fund/'.$this->orgId.'/info.member').'"><i class="icon -people -white"></i>จัดการสมาชิกกองทุน</a>' : NULL,
					],
				]),
			]),
			'body' => new Container([
				'id' => 'officer',
				'class' => '',
				'children' => [
					R::Page('project.fund.info.officer', NULL, $this->fundInfo),
				],
			]),
		]);
		return $ret;
	}

	function _showEval() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ประเมิน',
				'leading' => _HEADER_BACK,
				'boxHeader' => 'true',
				'trailing' => new Row([
					'children' => [
						$this->right->edit ? '<a class="btn" href="'.url('project/fund/'.$this->orgId.'/eval').'"><i class="icon -material">add</i><span>บันทึกแบบประเมิน</span></a>' : NULL,
					],
				]),
			]),
			'body' => new Table([
				'thead' => ['วันที่ประเมิน','amt -hover-parent'=>'คะแนนประเมิน'],
				'children' => (function() {
					$stmt = 'SELECT
						q.*
						, qby.`value` `by`
						, o.`name` `fundname`, SUM(IF(r.`part` LIKE "RATE.%",r.`rate`,0)) `rates`
						FROM %qtmast% q
							LEFT JOIN %db_org% o USING(`orgid`)
							LEFT JOIN %qttran% qby ON qby.`qtref`=q.`qtref` AND qby.`part`="HEADER.BY"
							LEFT JOIN %qttran% r ON r.`qtref`=q.`qtref` AND r.`part` LIKE "RATE.%"
						WHERE q.`qtform` IN (103, 106, 107, 108) AND q.`orgid` = :orgid
						GROUP BY q.`qtref`
						ORDER BY q.`qtref` ASC';

					$dbs = mydb::select($stmt,':orgid',$this->orgId);

					$rows = [];
					foreach ($dbs->items as $rs) {
						switch ($rs->qtform) {
							case 103:
								$evalUrl = url('project/fund/'.$rs->orgid.'/eval.manage/'.$rs->qtref);
								break;
							case 108:
								$evalUrl = url('project/fund/'.$rs->orgid.'/eval.manage/'.$rs->qtref);
								break;
							case 106:
								$evalUrl = url('project/fund/'.$rs->orgid.'/eval.operate/'.$rs->qtref);
								break;
							case 107:
								$evalUrl = url('project/fund/'.$rs->orgid.'/eval.ltc/'.$rs->qtref);
								break;
						}
						$rows[] = [
							sg_date($rs->created,'d/m/ปปปป'),
							number_format($rs->rates)
							. '<nav class="nav -icons -hover"><a href="'.$evalUrl.'"><i class="icon -viewdoc"></i></a></nav>'
						];
					}
					return $rows;
				})(),
			]),
		]);
	}

	function _script() {
		return '<style type="text/css">
			/* Outer-ring */
			.sg-chart.-guage circle:nth-child(1) {
				stroke-width: 5;
				stroke: #999;
				fill: #bbb;
				display:none;
			}
			/* Main background */
			.sg-chart.-guage circle:nth-child(2) {
				fill: black;
				stroke: #ddd;
				stroke-width: 5;
				display:none;
			}
			/* Circle of the pointer */
			.sg-chart.-guage circle:nth-child(3) {}

			/* Main text of the gauge */
			.sg-chart.-guage text {}

			/* Current value text */
			.sg-chart.-guage text:nth-child(1) {}

			/* Ticks */
			.sg-chart.-guage path {}

			/* Circle : เข็มชี้ */
			.sg-chart.-guage path:nth-child(2) {}

			/* Warning area */
			.sg-chart.-guage path:nth-child(3) {}

			/* Danger area */
			.sg-chart.-guage path:nth-child(4) {}
		</style>';
	}
}
?>