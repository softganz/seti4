<?php
/**
* Project :: Fund Report Recieve
* Created 2017-07-13
* Modify  2021-09-11
*
* @param String $arg1
* @return Widget
*
* @usage project/fund/report/revieve
*/

$debug = true;

import('package:project/fund/widgets/widget.fund.nav');

class ProjectFundReportRecieve extends Page {
	var $year;
	var $area;
	var $changwat;
	var $ampur;
	var $fromDate;
	var $toDate;

	function __construct() {
		$this->year=post('year');
		$this->area=post('area');
		$this->changwat=post('changwat');
		$this->ampur=post('ampur');
		$this->fromDate = post('from');
		$this->toDate = post('to');

		if (empty($this->year)) $this->year = date('m') >= 10 ? date('Y')+1 : date('Y');
		if (empty($this->fromDate)) $this->fromDate = '01/10/'.($this->year-1);
		if (empty($this->toDate)) $this->toDate = '30/09/'.($this->year);
	}
	function build() {
		$repTitle='รายงานการบันทึกการรับเงินเข้ากองทุน';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $repTitle,
				'navigator' => new FundNavWidget($this->fundInfo),
			]),
			'body' => new Widget([
				'children' => [
					new Nav([
						'class' => 'nav -page',
						'child' => new Ui([
							'children' => [
								'<a class="btn" href="'.url('project/report').'">รายงาน</a>',
								'<a class="btn" href="'.url('project/fund/report/recieve').'">'.$repTitle.'</a>',
							], // children
						]), // Ui
					]), // Nav
					$this->_formWidget(),
					$this->_showWidget($this->_getData()),
					$this->_recieveNotRecord(),
					$this->_script(),
				],
			]),
		]);
	}

	function _formWidget() {
		$form = new Form([
			'id' => 'condition',
			'class' => 'form-report',
			'action' => url('project/fund/report/recieve'),
			'method' => 'get',
			'children' => [
				'<div class="form-item"><span>ตัวเลือก </span></div>',

				// Select year
				'year' => [
					'type' => 'select',
					'value' => $this->year,
					'options' => (function() {
						$options = [];
						foreach (mydb::select('SELECT DISTINCT YEAR(`refdate`)+IF(MONTH(`refdate`)>=10,1,0) `budgetYear` FROM %project_gl% WHERE `glcode` IN ("40100","40200","40300") HAVING `budgetYear` ORDER BY `budgetYear` ASC')->items as $item) {
							$options[$item->budgetYear] = 'พ.ศ.'.($item->budgetYear + 543);
						}
						return $options;
					})(),
					'attr' => ['onChange' => '$(\'#edit-from\').val(\'01/10/\'+($(\'#edit-year\').val()-1));$(\'#edit-to\').val(\'30/09/\'+($(\'#edit-year\').val()))'],
				],
				'วันที่',
				'from' => [
					'type' => 'text',
					'class' => 'sg-datepicker -date',
					'size' => 10,
					'value' => sg_date($this->fromDate, 'd/m/Y'),
					'attr' => ['data-diff' => 'edit-to' ,'data-min-date' => '01/10/'.($this->year - 1), 'data-max-date' => '30/09/'.$this->year],
				],
				'-',
				'to' => [
					'type' => 'text',
					'class' => 'sg-datepicker -date',
					'size' => 10,
					'value' => sg_date($this->toDate, 'd/m/Y'),
					'attr' => ['data-min-date' => '01/10/'.($this->year - 1), 'data-max-date' => '30/09/'.$this->year],
				],

				// Select area
				'area' => [
					'type' => 'select',
					'value' => $this->area,
					'options' => (function() {
						$options = ['' => 'ทุกเขต'];
						foreach (mydb::select('SELECT `areaid`,`areaname` FROM %project_area% WHERE `areatype`="nhso" ORDER BY `areaid`+0 ASC')->items as $item) {
							$options[$item->areaid] = 'เขต '.$item->areaid.' '.$item->areaname;
						}
						return $options;
					})(),
				],

				// Select changwat
				'changwat' => $this->area ? [
					'type' => 'select',
					'value' => $this->changwat,
					'options' => (function() {
						$options = ['' => 'ทุกจังหวัด'];
						foreach (mydb::select('SELECT DISTINCT f.`changwat` `changwatId`, cop.`provname` `changwatName` FROM %project_fund% f LEFT JOIN %co_province% cop ON cop.`provid`=f.`changwat` WHERE f.`areaid`=:areaid',':areaid',$this->area)->items as $item) {
							$options[$item->changwatId] = $item->changwatName;
						}
						return $options;
					})(),
				] : NULL,

				// Select ampur
				'ampur' => $this->changwat ? [
					'type' => 'select',
					'value' => $this->ampur,
					'options' => (function() {
						$options = ['' => 'ทุกอำเภอ'];
						foreach (mydb::select('SELECT DISTINCT `distid`, `distname` FROM  %co_district% WHERE LEFT(`distid`,2)=:prov',':prov',$this->changwat)->items as $item) {
							$options[$item->distid] = $item->distname;
						}
						return $options;
					})(),
				] : NULL,
				'go' => [
					'type' => 'button',
					'value' => '<i class="icon -material">search</i><span>ดูรายงาน</span>',
				],
			], // children
		]);

		return $form;
	}

	function _showWidget($dbs) {
		return new ScrollView([
			'child' => new Table([
				'class' => 'item -center -nowrap',
				'caption' => 'การบันทึกการรับเงินเข้ากองทุน ประจำปี '.($this->year+543).'<br />ระหว่างวันที่ '.sg_date($this->fromDate, 'ว ดด ปปปป').' - '.sg_date($this->toDate, 'ว ดด ปปปป'),
				'thead' => '<tr>'
					. '<th rowspan="2">ลำดับ</th>'
					. '<th rowspan="2">พื้นที่</th>'
					. '<th rowspan="2">กองทุน</th>'
					. '<th colspan="2">สปสช.จัดสรร</th>'
					. '<th colspan="2">อปท.อุดหนุน</th>'
					. '<th colspan="2">ดอกเบี้ย</th>'
					. '</tr>'
					. '<tr>'
					. '<th>บันทึก</th>'
					. '<th>ไม่บันทึก</th>'
					. '<th>บันทึก</th>'
					. '<th>ไม่บันทึก</th>'
					. '<th>บันทึก</th>'
					. '<th>ไม่บันทึก</th>'
					. '</tr>',
				'children' => (function($items) {
					$rows = [];
					$totalRecieve=0;
					foreach ($items as $rs) {
						$recieve=$rs->totalNHSO+$rs->totalLocal+$rs->totalInterest+$rs->totalEtc+$rs->totalRefund;
						$balance=$rs->totalOpenBalance+$recieve-$rs->totalPaid;
						$totalRecieve+=$recieve;
						$totalBalance+=$balance;

						if ($this->ampur) $link='<a class="sg-action" href="'.url('project/fund/'.$rs->orgid.'/financial').'" data-rel="box">';
						else if ($this->changwat) $link='<a href="'.url('project/fund/report/recieve',['year'=>$this->year,'area'=>$this->area,'changwat'=>$this->changwat,'ampur'=>$rs->ampur, 'from' => $this->fromDate, 'to' => $this->toDate]).'">';
						else if ($this->area) $link='<a href="'.url('project/fund/report/recieve', ['yr'=>$this->year,'area'=>$this->area,'changwat'=>$rs->changwat, 'from' => $this->fromDate, 'to' => $this->toDate]).'">';
						else $link='<a href="'.url('project/fund/report/recieve', ['year'=>$this->year,'area'=>$rs->areaid, 'from' => $this->fromDate, 'to' => $this->toDate]).'">';

						$rows[] = [
							++$i,
							'-nowrap' => $link.$rs->label,
							number_format($rs->totalFund),
							number_format($rs->totalNHSOyes),
							$rs->totalFund - $rs->totalNHSOyes,
							number_format($rs->totalLocalyes),
							$rs->totalFund - $rs->totalLocalyes,
							number_format($rs->totalInterestyes),
							$rs->totalFund - $rs->totalInterestyes,
						];
					}
					return $rows;
				})($dbs->items),
				'tfoot' => [
					[
						'<td></td>',
						'รวม',
						$dbs->sum->totalFund,
						$dbs->sum->totalNHSOyes,
						$dbs->sum->totalFund-$dbs->sum->totalNHSOyes,
						$dbs->sum->totalLocalyes,
						$dbs->sum->totalFund-$dbs->sum->totalLocalyes,
						$dbs->sum->totalInterestyes,
						$dbs->sum->totalFund-$dbs->sum->totalInterestyes,
					],
				],
			]),
		]);
	}

	function _getData() {
		$label = 'CONCAT("เขต ",LPAD(a.areaid,2," ")," ",a.`areaname`)';
		mydb::where(NULL, ':startdate', sg_date($this->fromDate,'Y-m-d'), ':enddate', sg_date($this->toDate,'Y-m-d'));
		if ($this->area) {
		 mydb::where('f.`areaid`=:areaid',':areaid',$this->area);
		 $label = 'f.`namechangwat`';
		}
		if ($this->ampur) {
			mydb::where('f.`changwat`=:prov AND f.`ampur`=:ampur',':prov',$this->changwat,':ampur',$this->ampur);
			$label = 'f.`fundname`';
		} else if ($this->changwat) {
			mydb::where('f.`changwat`=:prov',':prov',$this->changwat);
			$label = 'f.`nameampur`';
		}

		mydb::value('$LABEL$', $label, false);

		$stmt = 'SELECT
			  $LABEL$ `label`
			,	f.*
			, a.`areaid`
			, SUM(`totalNHSOyes`) `totalNHSOyes`
			, SUM(`totalFund`) `totalFund`
			, SUM(`totalLocalyes`) `totalLocalyes`
			, SUM(`totalInterestyes`) `totalInterestyes`
			FROM
			(
				SELECT
					  f.`orgid`
					, f.`areaid`
					, f.`changwat`
					, f.`ampur`
					, f.`namechangwat`
					, f.`nameampur`
					, o.`name` `fundname`
					, o.`shortname`
					, COUNT(DISTINCT f.`orgid`) `totalFund`
					, COUNT(DISTINCT IF(g.`glcode` = "40100",f.`orgid`,NULL)) `totalNHSOyes`
					, COUNT(DISTINCT IF(g.`glcode` = "40200",f.`orgid`,NULL)) `totalLocalyes`
					, COUNT(DISTINCT IF(g.`glcode` = "40300",f.`orgid`,NULL)) `totalInterestyes`
				FROM %project_fund% f
					LEFT JOIN %db_org% o USING(`orgid`)
					LEFT JOIN %project_gl% g ON g.`orgid` = f.`orgid`
					LEFT JOIN %glcode% gc USING(`glcode`)
				WHERE g.`refdate` BETWEEN :startdate AND :enddate
				GROUP BY `orgid`
			) f
				RIGHT JOIN %project_area% a USING(`areaid`)
			%WHERE%
			GROUP BY `label`
			ORDER BY CONVERT(`label` USING tis620) ASC;
			-- {sum:"totalFund,totalNHSOyes,totalLocalyes,totalInterestyes"}
		';
		return mydb::select($stmt);
	}

	function _recieveNotRecord() {
		$recieveNotRecord = '';
		if ($this->area) {
			mydb::where('g.`amount` IS NULL');
			mydb::where(NULL,':startdate',($this->year-1).'-10-01',':enddate',$this->year.'-09-30',':closebalancedate',($this->year-1).'-09-30');

			 mydb::where('f.`areaid` = :areaid',':areaid',$this->area);

			if ($this->ampur) {
				mydb::where('f.`changwat` = :prov AND f.`ampur` = :ampur',':prov',$this->changwat,':ampur',$this->ampur);
			} else if ($this->changwat) {
				mydb::where('f.`changwat` = :prov',':prov',$this->changwat);
			}

			$stmt = 'SELECT
				  f.`orgid`
				, f.`areaid`
				, f.`changwat`
				, f.`ampur`
				, f.`namechangwat`
				, f.`nameampur`
				, o.`name` `fundname`
				, o.`shortname`
				, g.`glcode`
				, g.`amount`
			FROM %project_fund% f
				LEFT JOIN %db_org% o USING(`orgid`)
				LEFT JOIN %project_gl% g ON g.`orgid` = f.`orgid` AND g.`glcode` IN ("40100") AND g.`refdate` BETWEEN :startdate AND :enddate
				LEFT JOIN %glcode% gc USING(`glcode`)
			%WHERE%
			ORDER BY `namechangwat`, `nameampur`;
			-- {reset: false}
			';

			$dbs = mydb::select($stmt);

			$recieveNotRecord .= $this->_recieveNotRecordWidget($dbs->items,'รายชื่อกองทุนที่ยังไม่บันทึก สปสช.จัดสรร ปี '.($this->year + 543))->build();


			$stmt = 'SELECT
				  f.`orgid`
				, f.`areaid`
				, f.`changwat`
				, f.`ampur`
				, f.`namechangwat`
				, f.`nameampur`
				, o.`name` `fundname`
				, o.`shortname`
				, g.`glcode`
				, g.`amount`
			FROM %project_fund% f
				LEFT JOIN %db_org% o USING(`orgid`)
				LEFT JOIN %project_gl% g ON g.`orgid` = f.`orgid` AND g.`glcode` IN ("40200") AND g.`refdate` BETWEEN :startdate AND :enddate
				LEFT JOIN %glcode% gc USING(`glcode`)
			%WHERE%
			ORDER BY `namechangwat`, `nameampur`;
			-- {reset: false}
			';

			$dbs = mydb::select($stmt);

			$recieveNotRecord .= $this->_recieveNotRecordWidget($dbs->items,'รายชื่อกองทุนที่ยังไม่บันทึก อปท.อุดหนุน ปี '.($this->year + 543))->build();

			$stmt = 'SELECT
				  f.`orgid`
				, f.`areaid`
				, f.`changwat`
				, f.`ampur`
				, f.`namechangwat`
				, f.`nameampur`
				, o.`name` `fundname`
				, o.`shortname`
				, g.`glcode`
				, g.`amount`
			FROM %project_fund% f
				LEFT JOIN %db_org% o USING(`orgid`)
				LEFT JOIN %project_gl% g ON g.`orgid` = f.`orgid` AND g.`glcode` IN ("40300") AND g.`refdate` BETWEEN :startdate AND :enddate
				LEFT JOIN %glcode% gc USING(`glcode`)
			%WHERE%
			ORDER BY `namechangwat`, `nameampur`;
			';

			$dbs = mydb::select($stmt);

			$recieveNotRecord .= $this->_recieveNotRecordWidget($dbs->items,'รายชื่อกองทุนที่ยังไม่บันทึก ดอกเบี้ย ปี '.($this->year + 543))->build();
		}
		return $recieveNotRecord;
	}

	function _recieveNotRecordWidget($items, $caption) {
		$tables = new Table();
		$tables->caption = $caption;
		$tables->thead = array('no' => 'ลำดับ', 'changwat -nowrap' => 'จังหวัด', 'ampur -nowrap' => 'อำเภอ', 'รหัสกองทุน', 'name -nowrap' => 'ชื่อกองทุน');
		foreach ($items as $rs) {
			$tables->rows[] = array(
				++$no,
				$rs->namechangwat,
				$rs->nameampur,
				$rs->shortname,
				'<a href="'.url('project/fund/'.$rs->orgid.'/financial').'" target="_blank">'.$rs->fundname.'</a>'
			);
		}
		return new ScrollView(['children' => [$tables]]);
	}

	function _script() {
		head('googlegraph','<script type="text/javascript" src="https
			://www.gstatic.com/charts/loader.js"></script>');

		return '<style type="text/css">
		.sg-chart {height:400px; overflow:hidden;}
		.col-money.-nhso,.col-money.-local,.col-money.-interest,.col-money.-etc,.col-money.-refund {color:#999;}
		</style>

		<script type="text/javascript">
		$("body").on("change","#condition select", function() {
			let $this=$(this);
			if ($this.attr("name")=="area") {
				$("#edit-changwat").val("");
				$("#edit-ampur").val("").hide();
			}
			if ($this.attr("name")=="changwat") {
				$("#edit-ampur").val("");
			}
			notify("กำลังโหลด");
			// console.log($(this).attr("name"))
			$(this).closest("form").submit();
		});
		</script>';
	}
}
?>