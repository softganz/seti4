<?php
/**
* Project :: Fund Report Movement
* Created 2018-06-17
* Modify  2021-09-12
*
* @return Widget
*
* @usage project/fund/report/movement
*/

$debug = true;

class ProjectFundReportMovement extends Page {
	var $compare;
	var $rate;
	var $lowrate;
	var $year;
	var $area;
	var $changwat;
	var $ampur;
	var $order;
	var $export;

	function __construct() {
		$this->compare = SG\getFirst(post('compare'),'<');
		$this->rate = intval(SG\getFirst(post('rate'),10));
		$this->lowrate = intval(SG\getFirst(post('lowrate'),0));
		$this->year = post('year');
		$this->area = post('area');
		$this->changwat = post('changwat');
		$this->ampur = post('ampur');
		$this->order = SG\getFirst(post('o'),'CONVERT(`label` USING tis620)');
		$this->export = post('export');

		if (empty($this->year)) $this->year=date('m')>=10?date('Y')+1:date('Y');

	}
	function build() {

		$repTitle='รายงานการความเคลื่อนไหวการเงินของกองทุน';

		$data = $this->getData();
		$rateData = $this->getRateData();

		if ($this->export) {
			die(
				R::Model(
					'excel.export',
					$this->dataWidget($data),
					'รายงานความเคลื่อนไหวการเงิน'.($this->area ? ' เขต '.$this->area : '').($this->changwat ? '-'.$this->changwat : '').'-'.date('Y-m-d-H-i-s').'.xls',
					'{debug:false}'
				)
			);
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $repTitle,
				'navigator' => [
					'<a href="'.url('project/report').'"><i class="icon -material">insights</i><span>วิเคราะห์</span></a>',
					'<a href="'.url('project/fund/report/movement').'"><i class="icon -material">bar_chart</i><span>'.$repTitle.'</span></a>',
				],
			]),
			'body' => new Widget([
				'children' => [
					$this->formWidget(),
					$this->graphWidget($rateData),
					new ScrollView([
						'child' => $this->dataWidget($data),
					]),
					$this->_script(),
				], // children
			]),
		]);
	}

	function formWidget() {
		// $form='<form id="condition" class="form -report" action="'.url('project/fund/report/movement').'" method="get">';
		// 	$form.='<span></span>';

		// 	$form.='สัดส่วนการใช้เงินเทียบกับรายรับทั้งหมด ';
		// 	$form.='<select id="compare" class="form-select" name="compare"><option value="<">น้อยกว่า</option><option value=">" '.($this->compare=='>'?'selected="selected"':'').'>มากกว่า</option><option value="bt" '.($this->compare=='bt'?'selected="selected"':'').'>ระหว่าง</option></select> ';
		// 	$form.='<span id="lowrateshow" '.($this->compare=='bt' ? '' : 'style="display: none;"').'><input id="lowrate" class="form-text" type="text" name="lowrate" value="'.$this->lowrate.'" size="3" /> - </span>';
		// 	$form.='<input id="rate" class="form-text" type="text" name="rate" value="'.$this->rate.'" size="3" /> % ';
		// 	// Select year
		// 	$stmt='SELECT DISTINCT YEAR(`refdate`)+IF(MONTH(`refdate`)>=10,1,0) `budgetYear` FROM %project_gl% WHERE `glcode` IN ("40100","40200","40300") HAVING `budgetYear` ORDER BY `budgetYear` ASC';
		// 	$yearList=mydb::select($stmt);
		// 	$form.='<select id="year" class="form-select" name="yr">';
		// 	foreach ($yearList->items as $rs) {
		// 		$form.='<option value="'.$rs->budgetYear.'" '.($rs->budgetYear==$this->lowrate?'selected="selected"':'').'>พ.ศ.'.($rs->budgetYear+543).'</option>';
		// 	}
		// 	$form.='</select> ';

		// 	// Select area
		// 	$form.='<select id="area" class="form-select" name="area">';
		// 	$form.='<option value="">ทุกเขต</option>';
		// 	$areaList=mydb::select('SELECT `areaid`,`areaname` FROM %project_area% WHERE `areatype`="nhso" ORDER BY `areaid`+0 ASC');
		// 	foreach ($areaList->items as $rs) {
		// 		$form.='<option value="'.$rs->areaid.'" '.($rs->areaid==$this->area?'selected="selected"':'').'>เขต '.$rs->areaid.' '.$rs->areaname.'</option>';
		// 	}
		// 	$form.='</select> ';

		// 	// Select province
		// 	if ($this->area) {
		// 		$stmt='SELECT DISTINCT f.`changwat`, cop.`provname` FROM %project_fund% f LEFT JOIN %co_province% cop ON cop.`provid`=f.`changwat` WHERE f.`areaid`=:areaid';
		// 		$provList=mydb::select($stmt,':areaid',$this->area);
		// 		$form.='<select id="province" class="form-select" name="prov">';
		// 		$form.='<option value="">ทุกจังหวัด</option>';
		// 		foreach ($provList->items as $rs) {
		// 			$form.='<option value="'.$rs->changwat.'" '.($rs->changwat==$this->changwat?'selected="selected"':'').'>'.$rs->provname.'</option>';
		// 		}
		// 		$form.='</select> ';
		// 	}
		// 	$form.='<button class="btn -primary" name="view" value="view" type="submit"><i class="icon -material">search</i><span>ดูรายงาน</span></button> <button class="btn" name="export" value="export" type="submit"><i class="icon -material">download</i><span>Export</span></button>'._NL;
		// 	$form.='</form>'._NL;

		return new Form([
			'id' => 'condition',
			'class' => 'form-report',
			'action' => url('project/fund/report/movement'),
			'method' => 'get',
			'children' => [
				'<span title="สัดส่วนการใช้เงินเทียบกับรายรับทั้งหมด = รายจ่าย / (รายรับ+ยอดยกมา)">สัดส่วนการใช้เงิน</span>',
				'compare' => [
					'type' => 'select',
					'value' => $this->compare,
					'options' => [
						'<' => 'น้อยกว่า',
						'>' => 'มากกว่า',
						'bt' => 'ระหว่าง',
					],
				],
				'lowrate' => [
					'type' => 'text',
					'class' => '-numeric',
					'size' => 3,
					'value' => $this->lowrate,
					'posttext' => ' -',
					'container' => '{class: "'.($this->compare == 'bt' ? '' : '-hidden').'"}',
				],
				'rate' => [
					'type' => 'text',
					'class' => '-numeric',
					'size' => 3,
					'value' => $this->rate,
					'posttext' => ' %',
				],
				'year' => [
					'type' => 'select',
					'value' => $this->year,
					'options' => mydb::select('SELECT YEAR(`openbaldate`) `year`, CONCAT("พ.ศ.",YEAR(`openbaldate`)+543) `bc` FROM %project_fund% WHERE `openbaldate` IS NOT NULL ORDER BY `year` DESC; -- {resultType: "array", key: "year", value: "bc"}'),
				],
				'area' => [
					'type' => 'select',
					'value' => $this->area,
					'options' => ['' => 'ทุกเขต'] + mydb::select('SELECT `areaid`, CONCAT("เขต ", `areaid`, " ", `areaname`) `name` FROM %project_area% WHERE `areatype`="nhso" ORDER BY `areaid`+0 ASC; -- {resultType: "array", key: "areaid", value: "name"}'),
				],
				// Select province
				'changwat' => $this->area ? [
					'type' => 'select',
					'value' => $this->changwat,
					'options' => ['' => 'ทุกจังหวัด'] + mydb::select('SELECT DISTINCT f.`changwat`, cop.`provname` FROM %project_fund% f LEFT JOIN %co_province% cop ON cop.`provid`=f.`changwat` WHERE f.`areaid`=:areaid; -- {resultType: "array", key: "changwat", value: "provname"}',':areaid',$this->area),
				] : NULL,
				// Select ampur
				'ampur' => $this->changwat ? [
					'type' => 'select',
					'value' => $this->ampur,
					'options' => ['' => 'ทุกอำเภอ'] + AmpurModel::inChangwat($this->changwat),
				] : NULL,

				'go' => [
					'type' => 'button',
					'value' => '<i class="icon -material">search</i><span>ดูรายงาน</span>',
				],
				'export' => [
					'type' => 'button',
					'class' => 'btn',
					'name' => 'export',
					'value' => '<i class="icon -material">download</i><span>Export</span>',
				],
				'<script type="text/javascript">
					$("#edit-compare").change(function() {
						var $this = $(this)
						if ($this.val() == "bt") {
							if ($("#edit-lowrate").val() == "" ) $("#edit-lowrate").val($("#edit-rate").val())
							$("#form-item-edit-lowrate").show()
						} else {
							$("#form-item-edit-lowrate").hide()
						}
					})
				</script>'
			], // children
		]);
	}

	function getData() {
		// Start report query

		$label='CONCAT("เขต ",LPAD(a.areaid,2," ")," ",a.`areaname`)';
		mydb::where(NULL,':rate',$this->rate, ':lowrate', $this->lowrate);
		if ($this->year) {
			mydb::where(NULL,':startdate',($this->year-1).'-10-01',':enddate',$this->year.'-09-30',':closebalancedate',($this->year-1).'-09-30');
		} else {
			mydb::where(NULL,':startdate','2016-10-01',':enddate',date('Y').'-09-30',':closebalancedate',(date('Y')-1).'-09-30');
		}
		if ($this->area) {
		 mydb::where('f.`areaid`=:areaid',':areaid',$this->area);
		 $label='f.`namechangwat`';
		}

		if ($this->ampur) {
			mydb::where('LEFT(o.`areacode`,4) = :ampur', ':ampur', $this->ampur);
			$label = 'f.`fundName`';
		} else if ($this->changwat) {
			mydb::where('LEFT(o.`areacode`,2) = :prov', ':prov', $this->changwat);
			$label = 'f.`nameampur`';
		}

		// สัดส่วน รายจ่าย เทียบกับ รายรับ
		// if ($this->compare=="<") {
		// 	$havingCondition='`totalPaid`<=0 OR `totalIncome`<=0 OR (`totalPaid`/`totalIncome`)*100 <= :rate';
		// } else if ($this->compare=="bt") {
		// 	$havingCondition='(`totalPaid`/`totalIncome`)*100 BETWEEN :lowrate AND :rate';
		// } else {
		// 	$havingCondition='`totalPaid`>=0 AND `totalIncome`>=0 AND (`totalPaid`/`totalIncome`)*100 >= :rate';
		// }

		// สัดส่วน รายจ่าย เทียบกับ รายรับ+ยอดยกมา
		if ($this->compare=="<") {
			$havingCondition='`percentValue` <= :rate';
		} else if ($this->compare=="bt") {
			$havingCondition='`percentValue` BETWEEN :lowrate AND :rate';
		} else {
			$havingCondition='`percentValue` >= :rate';
		}

		mydb::value('$HAVING$', $havingCondition);
		mydb::value('$ORDER$', $this->order);

		$stmt = '
			SELECT
				f.`fundname` `label`
				, f.*
				, f.`openbalance` - f.`afteropen` `periodOpenBalance`
				, f.`totalNHSO` + f.`totalLocal` + f.`totalInterest` + f.`totalEtc` + f.`totalRefund` `totalIncome`
				, f.`openbalance` - f.`afteropen` + f.`totalNHSO` + f.`totalLocal` + f.`totalInterest` + f.`totalEtc` + f.`totalRefund` - `totalPaid` `balance`
				, f.`totalPaid` * 100 / (f.`openbalance` - f.`afteropen` + f.`totalNHSO` + f.`totalLocal` + f.`totalInterest` + f.`totalEtc` + f.`totalRefund`) `percentValue`
				FROM
				(
					SELECT
						  f.`orgid`
						, f.`areaid`
						, f.`changwat`
						, f.`ampur`
						, f.`namechangwat`
						, f.`nameampur`
						, o.`shortname` `fundid`
						, o.`name` `fundname`
						, o.`areacode`
						, f.`openbalance`
						, SUM(IF(gc.`gltype` IN ("4","5") AND g.`refdate` BETWEEN f.`openbaldate` AND :closebalancedate,g.`amount`,0)) `afteropen`
						, ABS(SUM(IF(g.`glcode`="40100" AND g.`refdate` BETWEEN :startdate AND :enddate,g.`amount`,0))) `totalNHSO`
						, ABS(SUM(IF(g.`glcode`="40200" AND g.`refdate` BETWEEN :startdate AND :enddate,g.`amount`,0))) `totalLocal`
						, ABS(SUM(IF(g.`glcode`="40300" AND g.`refdate` BETWEEN :startdate AND :enddate,g.`amount`,0))) `totalInterest`
						, ABS(SUM(IF(g.`glcode`="40400" AND g.`refdate` BETWEEN :startdate AND :enddate,g.`amount`,0))) `totalEtc`
						, ABS(SUM(IF(g.`glcode`="40500" AND g.`refdate` BETWEEN :startdate AND :enddate,g.`amount`,0))) `totalRefund`
						, ABS(SUM(IF(gc.`gltype`="5" AND g.`refdate` BETWEEN :startdate AND :enddate,g.`amount`,0))) `totalPaid`
					FROM %project_fund% f
						LEFT JOIN %db_org% o USING(`orgid`)
						LEFT JOIN %project_gl% g ON g.`orgid`=f.`orgid`
						LEFT JOIN %glcode% gc USING(`glcode`)
					%WHERE%
					GROUP BY `orgid`
				) f
				HAVING $HAVING$
				-- ORDER BY $ORDER$ ASC
				ORDER BY `percentValue` ASC
				;
				-- {sum:"periodOpenBalance,totalNHSO,totalLocal,totalInterest,totalEtc,totalRefund,totalPaid"}
			';

		$dbs=mydb::select($stmt);
		//$ret.='Total='.$dbs->_num_rows;
		// debugMsg('<pre>'.mydb()->_query.'</pre>');
		// debugMsg(mydb::printtable($dbs));
		// debugMsg($dbs,'$dbs');
		return $dbs;
	}

	function getRateData() {
		// Start report query

		mydb::where(NULL,':rate',$this->rate, ':lowrate', $this->lowrate);
		if ($this->year) {
			mydb::where(NULL,':startdate',($this->year-1).'-10-01',':enddate',$this->year.'-09-30',':closebalancedate',($this->year-1).'-09-30');
		} else {
			mydb::where(NULL,':startdate','2016-10-01',':enddate',date('Y').'-09-30',':closebalancedate',(date('Y')-1).'-09-30');
		}
		if ($this->area) {
		 mydb::where('f.`areaid`=:areaid',':areaid',$this->area);
		}

		if ($this->ampur) {
			mydb::where('LEFT(o.`areacode`,4) = :ampur', ':ampur', $this->ampur);
		} else if ($this->changwat) {
			mydb::where('LEFT(o.`areacode`,2) = :prov', ':prov', $this->changwat);
		}

		$stmt = '
			SELECT
				CASE
					WHEN `expense`*100/`totalIncome` < 20 THEN " <20%"
					WHEN `expense`*100/`totalIncome` < 50 THEN "20-50%"
					WHEN `expense`*100/`totalIncome` < 60 THEN "50-60%"
					WHEN `expense`*100/`totalIncome` < 70 THEN "60-70%"
					WHEN `expense`*100/`totalIncome` < 80 THEN "70-80%"
					WHEN `expense`*100/`totalIncome` < 90 THEN "80-90%"
					ELSE ">90%"
				END `percent`
				, COUNT(*) `amt`
				FROM
				(
					SELECT
						o.`name` `fundName`
						, f.`orgid`
						, o.`shortname` `fundid`
						, SUM(IF(gc.`gltype`="5" AND g.`refdate` BETWEEN :startdate AND :enddate,g.`amount`,0)) `expense`
						-- totalIncome = Open Balance - (income - expense before start date) + income between start date and end date
						, f.`openbalance`
							- SUM(IF(gc.`gltype` IN ("4","5") AND g.`refdate` BETWEEN f.`openbaldate` AND :closebalancedate,g.`amount`,0))
							+ ABS(SUM(IF(gc.`gltype`="4" AND g.`refdate` BETWEEN :startdate AND :enddate,g.`amount`,0)))
							AS `totalIncome`
					FROM %project_fund% f
						LEFT JOIN %db_org% o USING(`orgid`)
						LEFT JOIN %project_gl% g ON g.`orgid`=f.`orgid`
						LEFT JOIN %glcode% gc USING(`glcode`)
					%WHERE%
					GROUP BY `orgid`
				) f
				GROUP BY `percent`
			;
		';

		$dbs = mydb::select($stmt);
		// debugMsg('<pre>'.mydb()->_query.'</pre>');
		// debugMsg(mydb::printtable($dbs));
		return $dbs;
	}

	function graphWidget($data) {
		$rows = [
			["เปอร์เซ็นต์", "โครงการ", ['role' => 'style'],['role' => 'annotation']],
		];
		foreach ($data->items as $item) {
			$item->percent = trim($item->percent);
			$color = 'green';
			if (in_array($item->percent, ['<20%','20-50%'])) $color = '#ff0000';
			else if (in_array($item->percent, ['50-60%','60-70%'])) $color = '#FFD700';
			$rows[] = [
				$item->percent,
				$item->amt,
				$color,
				$item->amt.' กองทุน',
			];
		}

		$options = [
			'title' => 'จำนวนกองทุนที่จ่ายเงินตามระดับร้อยละการจ่ายเงิน',
			'legend' => ['position' => 'none'],
			'hAxis' => [
				'textStyle' => ['fontSize' => 10,],
			],
			'vAxis' => [
				'textStyle' => ['fontSize' => 10,],
			]
		];

		return '<div id="chart" class="sg-chart" data-chart-type="col" data-value=\''.SG\json_encode($rows).'\' data-options=\''.json_encode($options).'\' style="height: 400px;"></div>'._NL;
	}

	function dataWidget($data) {
		return new Table([
			'thead' => [
				'no'=>'ลำดับ',
				'รหัสกองทุน',
				'title -nowrap' => 'ชื่อกองทุน',
				'open -money'=>'ยอดเงินคงเหลือยกมา',
				'nhso -money'=>'สปสช.จัดสรร',
				'local -money'=>'อปท.อุดหนุน',
				'interest -money'=>'ดอกเบี้ย',
				'etc -money'=>'อื่นๆ',
				'refund -money'=>'เงินคืน',
				'recieve -money'=>'รายรับทั้งหมด',
				'expense -money'=>'รายจ่าย',
				'total -money'=>'เงินคงเหลือ',
				'percent1 -amt'=>'สัดส่วนการใช้เทียบกับรายรับ(%)',
				'percent2 -amt'=>'สัดส่วนการใช้เทียบกับเงินทั้งหมด(%)',
				/*,'totalPaid','totalIncome'*/
			],
			'children' => (function($items, &$totalRecieve, &$totalBalance) {
				$rows = [];

				$totalRecieve = $totalBalance = 0;

				foreach ($items as $rs) {
					$recieve = $rs->totalIncome;
					$balance = $rs->periodOpenBalance+$rs->totalIncome-$rs->totalPaid;
					$totalRecieve += $recieve;
					$totalBalance += $balance;
					$rows[] = [
						++$i,
						$rs->fundid,
						'<a href="'.url('project/fund/'.$rs->orgid).'">'.$rs->label.'</a>',
						number_format($rs->periodOpenBalance,2),
						number_format($rs->totalNHSO,2),
						number_format($rs->totalLocal,2),
						number_format($rs->totalInterest,2),
						number_format($rs->totalEtc,2),
						number_format($rs->totalRefund,2),
						number_format($recieve,2),
						number_format($rs->totalPaid,2),
						number_format($balance,2),
						$recieve > 0 ? number_format($rs->totalPaid*100/$recieve,2).'%' : 'n/a',
						$rs->periodOpenBalance+$recieve > 0 ? number_format($rs->totalPaid*100/($rs->periodOpenBalance+$recieve),2).'%' : 'n/a',
					];
				}
				return $rows;
			})($data->items, $totalRecieve, $totalBalance),
			'tfoot' => [
				[
					'<td></td>',
					'',
					'รวม',
					number_format($data->sum->periodOpenBalance,2),
					number_format($data->sum->totalNHSO,2),
					number_format($data->sum->totalLocal,2),
					number_format($data->sum->totalInterest,2),
					number_format($data->sum->totalEtc,2),
					number_format($data->sum->totalRefund,2),
					number_format($totalRecieve,2),
					number_format($data->sum->totalPaid,2),
					number_format($totalBalance,2),
					number_format($data->sum->totalPaid*100/$totalRecieve,2).'%',
					number_format($data->sum->totalPaid*100/($data->sum->periodOpenBalance+$totalRecieve),2).'%',
				],
			],
		]);
	}

	function _script() {
		head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');
		head('<script type="text/javascript">
		$(document).on("change","#condition select", function() {
			let $this=$(this)
			let inputName = $this.attr("name")
			console.log(inputName)
			if (inputName=="area") {
				$("#edit-changwat").val("");
				$("#edit-ampur").val("");
			}
			if (inputName=="changwat") {
				$("#edit-ampur").val("");
			}
			if (["year","area","changwat","ampur"].indexOf(inputName) != -1) {
				notify("กำลังโหลด");
				console.log($(this).attr("name"))
				$(this).closest("form").submit();
			}
		});
		</script>');
	}
}
?>