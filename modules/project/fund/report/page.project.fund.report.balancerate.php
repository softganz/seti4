<?php
/**
* Project :: Fund Report Balance Rate
* Created 2017-07-13
* Modify  2022-01-28
*
* @return Widget
*
* @usage project/fund/report/balancecreate
*/

class ProjectFundReportBalanceRate extends Page {
	var $year;
	var $area;
	var $changwat;
	var $ampur;

	function __construct() {
		$this->year = post('year');
		$this->area = post('area');
		$this->changwat = post('changwat');
		$this->ampur = post('ampur');

		if (empty($this->year)) $this->year = date('m') >= 10 ? date('Y')+1 : date('Y');
	}

	function build() {
		$repTitle = 'รายงานสรุปเงินคงเหลือและสัดส่วนการใช้จ่ายตามพื้นที่';

		$data = $this->_getData();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $repTitle,
				'navigator' => [
					'<a href="'.url('project/report').'"><i class="icon -material">insights</i><span>วิเคราะห์</span></a>',
					'<a href="'.url('project/fund/report/balancerate').'"><i class="icon -material">bar_chart</i><span>'.$repTitle.'</span></a>',
				],
			]),
			'body' => new Widget([
				'children' => [

					$this->formWidget(),

					$data->items ? $this->graphWidget($data) : NULL,

					$data->items ? $this->dataWidget($data) : NULL,

					$this->_script(),
				],
			]),
		]);
	}

	function _getData() {
		if (!$this->year) return NULL;

		$label = 'CONCAT("เขต ",LPAD(a.areaid,2," ")," ",a.`areaname`)';

		if ($this->year) {
			mydb::where(
				NULL,
				':startdate', ($this->year-1).'-10-01',
				':enddate', $this->year.'-09-30',
				':closebalancedate', ($this->year-1).'-09-30'
			);
		} else {
			mydb::where(
				NULL,
				':startdate', 'f.`openbaldate`',
				':enddate', date('Y').'-09-30',
				':closebalancedate', 'f.`openbaldate`'
			);
		}

		if ($this->area) {
		 mydb::where('f.`areaid` = :areaid', ':areaid',$this->area);
		 $label = 'f.`namechangwat`';
		}
		if ($this->ampur) {
			mydb::where('LEFT(f.`areacode`,4) = :ampur', ':ampur', $this->ampur);
			$label = 'f.`fundName`';
		} else if ($this->changwat) {
			mydb::where('LEFT(f.`areacode`,2) = :prov', ':prov', $this->changwat);
			$label = 'f.`nameampur`';
		}

		mydb::value('$LABEL$', $label, false);
		$stmt = '
			SELECT
				  $LABEL$ `label`
				, f.*
				, SUM(`openbalance`)-SUM(`afteropen`) `totalOpenBalance`
				, SUM(`afteropen`) `totalExpenseAfter`
				, SUM(`totalNHSO`) `totalNHSO`
				, SUM(`totalLocal`) `totalLocal`
				, SUM(`totalInterest`) `totalInterest`
				, SUM(`totalEtc`) `totalEtc`
				, SUM(`totalRefund`) `totalRefund`
				, SUM(`totalPaid`) `totalPaid`
				, SUM(`openbalance`)-SUM(`afteropen`) + SUM(`totalNHSO`) + SUM(`totalLocal`) + SUM(`totalInterest`) + SUM(`totalEtc`) + SUM(`totalRefund`) `totalMoney`
				FROM
				(
					SELECT
						  f.`orgid`
						, f.`areaid`
						, f.`namechangwat`
						, f.`nameampur`
						, o.`shortname`
						, o.`name` `fundName`
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
						LEFT JOIN %project_gl% g ON g.`orgid` = f.`orgid`
						LEFT JOIN %glcode% gc USING(`glcode`)
					GROUP BY `orgid`
				) f
					RIGHT JOIN %project_area% a USING(`areaid`)
				%WHERE%
				GROUP BY `label`
				ORDER BY `totalMoney` DESC
				;
				-- {sum:"totalOpenBalance,totalNHSO,totalLocal,totalInterest,totalEtc,totalRefund,totalPaid"}
			';


		$dbs = mydb::select($stmt);
		// debugMsg('<pre>'.mydb()->_query.'</pre>');
		return $dbs;
	}

	function formWidget() {
		return new Form([
			'id' => 'condition',
			'class' => 'form-report',
			'action' => url('project/fund/report/balancerate'),
			'method' => 'get',
			'children' => [
				'ตัวเลือก',
				'year' => [
					'type' => 'select',
					'value' => $this->year,
					'options' => mydb::select(
						'SELECT YEAR(`refdate`) `year`, CONCAT("พ.ศ.",YEAR(`refdate`)+543) `bc`
						FROM %project_gl%
						WHERE YEAR(`refdate`) >= (SELECT YEAR(`openbaldate`) `openyear` FROM %project_fund% WHERE `openbaldate` IS NOT NULL GROUP BY `openyear` ORDER BY `openyear` ASC LIMIT 1)
						ORDER BY `year` DESC;
						-- {resultType: "array", key: "year", value: "bc"}'
					),
				],
				'area' => [
					'type' => 'select',
					'value' => $this->area,
					'options' => ['' => 'ทุกเขต'] + mydb::select(
						'SELECT `areaid`, CONCAT("เขต ", `areaid`, " ", `areaname`) `name`
						FROM %project_area%
						WHERE `areatype`="nhso"
						ORDER BY `areaid`+0 ASC;
						-- {resultType: "array", key: "areaid", value: "name"}'
					),
				],
				// Select province
				'changwat' => $this->area ? [
					'type' => 'select',
					'value' => $this->changwat,
					'options' => ['' => 'ทุกจังหวัด'] + mydb::select(
						'SELECT DISTINCT f.`changwat`, cop.`provname`
						FROM %project_fund% f
							LEFT JOIN %co_province% cop ON cop.`provid`=f.`changwat`
						WHERE f.`areaid`=:areaid;
						-- {resultType: "array", key: "changwat", value: "provname"}',
						[':areaid' => $this->area]
					),
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
			],
		]);
	}

	function dataWidget($data) {
		return new ScrollView([
			'child' => new Table([
				'thead' => [
					'no' => 'ลำดับ',
					'id'.($this->ampur ? '' : ' -hidden').' -nowrap' => 'รหัสกองทุน',
					'name -nowrap' => $this->ampur ? 'กองทุน' : 'พื้นที่',
					'open -money' => 'ยอดเงินคงเหลือยกมา',
					'nhso -money' => 'สปสช.จัดสรร',
					'local -money' => 'อปท.อุดหนุน',
					'interest -money' => 'ดอกเบี้ย',
					'etc -money' => 'อื่นๆ',
					'refund -money' => 'เงินคืน',
					'recieve -money' => 'รายรับทั้งหมด',
					'expense -money' => 'รายจ่าย',
					'total -money' => 'เงินคงเหลือ',
					'percent -amt -nowrap' => 'สัดส่วนการใช้<br />เทียบกับรายรับ<br />(%)',
					'percent2 -amt -nowrap' => 'สัดส่วนการใช้<br />เทียบกับเงินทั้งหมด<br />(%)',
				],
				'children' => (function($items, &$totalRecieve, &$totalBalance) {
					$rows = [];
					$totalRecieve=0;
					foreach ($items as $rs) {
						$recieve = $rs->totalNHSO+$rs->totalLocal+$rs->totalInterest+$rs->totalEtc+$rs->totalRefund;
						$balance = $rs->totalOpenBalance+$recieve-$rs->totalPaid;
						$totalRecieve += $recieve;
						$totalBalance += $balance;

						$rows[] = [
							++$i,
							$this->ampur ? $rs->shortname : '',
							$rs->label,
							number_format($rs->totalOpenBalance,2),
							number_format($rs->totalNHSO,2),
							number_format($rs->totalLocal,2),
							number_format($rs->totalInterest,2),
							number_format($rs->totalEtc,2),
							number_format($rs->totalRefund,2),
							number_format($recieve,2),
							number_format($rs->totalPaid,2),
							number_format($balance,2),
							$recieve ? number_format($rs->totalPaid*100/$recieve,2) : '0.00',
							$rs->totalOpenBalance+$recieve ? number_format($rs->totalPaid*100/($rs->totalOpenBalance+$recieve),2) : '0.00',
						];
					}
					return $rows;
				})($data->items, $totalRecieve, $totalBalance),
				'tfoot' => [
					[
						'<td></td>',
						'',
						'รวม',
						number_format($data->sum->totalOpenBalance,2),
						number_format($data->sum->totalNHSO,2),
						number_format($data->sum->totalLocal,2),
						number_format($data->sum->totalInterest,2),
						number_format($data->sum->totalEtc,2),
						number_format($data->sum->totalRefund,2),
						number_format($totalRecieve,2),
						number_format($data->sum->totalPaid,2),
						number_format($totalBalance,2),
						number_format($data->sum->totalPaid*100/$totalRecieve,2),
						number_format($data->sum->totalPaid*100/($data->sum->totalOpenBalance+$totalRecieve),2),
					],
				], // tfoot
			]), // Table
		]);
	}

	function graphWidget($data) {
		$chartTable = new Table();
		$chartIncome = new Table(['class' => '-hidden']);

		$maxValue = $maxExpense = 0;
		foreach ($data->items as $rs) {
			$recieve = $rs->totalNHSO + $rs->totalLocal + $rs->totalInterest + $rs->totalEtc + $rs->totalRefund;
			$balance=$rs->totalOpenBalance + $recieve - $rs->totalPaid;
			$totalMoney = $rs->totalOpenBalance + $recieve;

			if ($totalMoney > $maxValue) $maxValue = $totalMoney;
			if ($rs->totalPaid > $maxExpense) $maxExpense = $rs->totalPaid;

			$label = preg_replace(
				[
					'/กองทุนสุขภาพตำบล|กองทุนหลักประกันสุขภาพ|กองทุนสุขภาพ/',
					'/เทศบาลนคร/',
					'/เทศบาลเมือง/',
					'/เทศบาลตำบล/',
					'/องค์การบริหารส่วนตำบล|ตำบล/'
				],
				[
					'',
					'ทน.',
					'ทม.',
					'ทต.',
					'อบต.',
				],
				$rs->label
			);
			// $label = preg_replace('/เทศบาลนคร/', 'ทน.', $label);
			// $label = preg_replace('/เทศบาลเมือง/', 'ทม.', $label);
			// $label = preg_replace('/เทศบาลตำบล/', 'ทต.', $label);
			// $label = preg_replace('/องค์การบริหารส่วนตำบล|ตำบล/', 'อบต.', $label);

			$label = trim($label);

			$chartTable->rows[] = [
				'string:label' => $label,
				'number:รายรับ' => $recieve,
				'string:รายรับ:role' => number_format(($recieve)/1000000,1).'M',
				'number:รายจ่าย' => $rs->totalPaid,
				'string:รายจ่าย:role' => number_format($rs->totalPaid/1000000,1).'M',
				//'number:คงเหลือ' => $balance,
				//'string:รายได้อื่นๆ:role' => number_format($rs->totalOther/1000,1).'K',
			];

			$chartIncome->rows[] = [
				'string:label' => $label,
				'number:เงินทั้งหมด' => $totalMoney,
				'string:เงินทั้งหมด:role' => number_format(($rs->totalOpenBalance + $recieve)/1000000,1).'M',
				'number:รายจ่าย' => $rs->totalPaid,
				'string:รายจ่าย:role' => /*number_format($rs->totalPaid/1000000,1).'M'.*/ number_format(($rs->totalPaid*100/$totalMoney)).'%',
			];
		}

		// y: 177;
		// stroke: black;
		// stroke-width: 10;
		// opacity: 0.5;
		$options = [
			'isStacked' => true,
			'enableInteractivity' => false,
			'legend' => ['position' => 'bottom'],
			'hAxis' => [
				'textStyle' => ['fontSize' => 10,],
				'viewWindow' => [
					'max' => $maxValue+$maxExpense*1.0,
				]
			],
			'vAxis' => [
				'textStyle' => ['fontSize' => 10,],
				'viewWindow' => []
			]
		];
		// debugMsg($options,'$options');
		// return '<div id="in-out" class="sg-chart -join" data-chart-type="col" data-options=\''.json_encode($options).'\'><h3>แผนภูมิแสดงรายรับ/รายจ่ายของกองทุนฯ</h3>'.$chartTable->build().'</div>'._NL
		// return '<div id="income" class="sg-chart -join" data-chart-type="col" data-options=\''.json_encode($options).'\' data-callback="afterDraw"><h3>แผนภูมิแสดงรายรับ/รายจ่ายของกองทุนฯ</h3>'.$chartIncome->build().'</div>'._NL;
		return '<div id="income" class="sg-chart -join" data-chart-type="bar" data-options=\''.json_encode($options).'\' data-callback="afterDrawBar" style="height: '.(count($data->items)*50).'px;"><h3>แผนภูมิแสดงรายรับ/รายจ่ายของกองทุนฯ</h3>'.$chartIncome->build().'</div>'._NL;
	}

	function _script() {
		head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');

		return '<style type="text/css">
		.sg-chart {height:400px; overflow:hidden;}
		.col-money.-nhso,.col-money.-local,.col-money.-interest,.col-money.-etc,.col-money.-refund {color:#999;}
		/*
		[aria-label="A chart."] text {fill: green;}
		svg["aria-label"="A chart"] rect {opacity: 0.5;}
		g[clip-path]>g:nth-child(2)>rect {stroke: #3366CC; stroke-width: 2;}
		[aria-label="A chart."]>g:nth-child(5)>g:nth-child(2)>g:nth-child(2)>rect {stroke: #3366CC; stroke-width: 5;}
		[aria-label="A chart."]>g:nth-child(5)>g:nth-child(2)>g:nth-child(2)>rect:nth-child(n+17) {opacity: 1; stroke-width: 0;}
		*/
		/*
		[aria-label="A chart."]>g:nth-child(5)>g:nth-child(2)>g:nth-child(2)>rect:nth-child(17) {y: 230;}
		[aria-label="A chart."]>g:nth-child(5)>g:nth-child(2)>g:nth-child(2)>rect:nth-child(18) {y: 230;}
		*/
		</style>
		<script type="text/javascript">
		function afterDrawBar() {
			// clip-path
			// g:2 => box
			// g:7 => label in box
			let $chartRect = $("g[clip-path]>g:nth-child(2)>rect")
			// let $labelRect = $("g[clip-path]").closest("g").find("text")
			// let $labelRect = $(\'[aria-label="A chart."] text\')
			let $labelRect = $(\'[aria-label="A chart."]>*>g:nth-child(5)>g text\')
			let rightRectAt = $chartRect.length / 2
			let xStartAt = parseInt($($chartRect[0]).attr("x"),10)

			// console.log("xStartAt = ",xStartAt, "count =", $labelRect.length)
			// console.log($labelRect)
			// $labelRect.hide()
			$labelRect.each(function(i){
				// console.log(i, $(this))
				let $this = $(this)
				if (i + 1 >= rightRectAt && i < $chartRect.length) {
					let redRectWidth = 30;
					let $redRect = $($chartRect[i])
					if ($redRect.length) {
						// console.log($($chartRect[i]).attr("width"))
						// redRectWidth = parseInt($($chartRect[i]).attr("width"),10)
					}
					let x = xStartAt + redRectWidth - 5
					$this.css({x: x+"px", fill: "#fff", strokeWidth: 0}).attr("x", x)
					// console.log(i,$this.text(),"x = ",x,"width=",$this.attr("x"))
				}
			})

			$chartRect.each(function(i) {
				let $this = $(this)
				if (i < rightRectAt) {
					$this.css({stroke: "#3366CC", strokeWidth: "4px"})
				} else {
					$this.css("x", xStartAt+"px").attr("x", xStartAt)
				}
			})
		}

		function afterDraw() {
			// clip-path
			let $chart = $("[aria-label=\'A chart.\']")
			let chartHeight = $chart.height() - 77; //323
			console.log($chart.height())
			// $("[aria-label=\'A chart.\']>g:nth-child(5)>g:nth-child(2)>g:nth-child(2)>rect").each(function(i) {
			// 	let $this = $(this)
			// 	console.log(i,"y=",$this.attr("y"), " height=",$this.attr("height"))
			// })

			$("[aria-label=\'A chart.\']>g:nth-child(5)>g:nth-child(2)>g:nth-child(2)>rect:nth-child(n+17)").each(function(i) {
				let $this = $(this)

				// console.log("y=",$this.css("y"), " height=",$this.attr("height"))
				// let y =  parseInt($this.css("y"),10) + parseInt($this.css("height"),10)
				let y = chartHeight - $this.attr("height")
				console.log("new y = ",y)
				$this.css("y", y+"px")
				$this.hover(function() {
					$this.css("y", y+"px")
					console.log("HOVER ", i, y)
				})
			})
		}
		$("body").on("change","#condition select", function() {
			var $this=$(this);
			if ($this.attr("name")=="area") {
				$("#edit-changwat").val("");
				$("#edit-ampur").val("");
			}
			if ($this.attr("name")=="changwat") {
				$("#edit-ampur").val("");
			}
			notify("กำลังโหลด");
			console.log($(this).attr("name"))
			$(this).closest("form").submit();
		});
		</script>';
	}
}
?>