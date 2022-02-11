<?php
/**
* Project :: Fund Report Budget
* Created 2018-03-13
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/report/budget
*/

$debug = true;

function project_fund_report_budget($self) {
	$year=post('yr');
	$area=post('area');
	$prov=post('prov');
	$ampur=post('ampur');

	$repTitle='รายงานสรุปจำนวนเงินงบประมาณของกองทุนฯ';

	R::view('project.toolbar',$self,'รายงาน - '.$repTitle,'fund');

	$ui=new Ui();
	$ui->add('<a class="btn" href="'.url('project/report').'">รายงาน</a>');
	$ui->add('<a class="btn" href="'.url('project/fund/report/budget').'">'.$repTitle.'</a>');
	$ret.='<nav class="nav -page">'.$ui->build().'</nav>';

	$form='<form id="condition" action="'.url('project/fund/report/budget').'" method="get">';
	$form.='<span>ตัวเลือก </span>';

	// Select year
	$stmt='SELECT DISTINCT YEAR(`refdate`)+IF(MONTH(`refdate`)>=10,1,0) `budgetYear` FROM %project_gl% ORDER BY `budgetYear` ASC';
	$yearList=mydb::select($stmt);

	$form.='<select id="year" class="form-select" name="yr">';
	$form.='<option value="">ทุกปีงบประมาณ</option>';
	foreach ($yearList->items as $rs) {
		$form.='<option value="'.$rs->budgetYear.'" '.($rs->budgetYear==$year?'selected="selected"':'').'>พ.ศ.'.($rs->budgetYear+543).'</option>';
	}
	$form.='</select> ';

	// Select area
	$form.='<select id="area" class="form-select" name="area">';
	$form.='<option value="">ทุกเขต</option>';
	$areaList=mydb::select('SELECT `areaid`,`areaname` FROM %project_area% WHERE `areatype`="nhso" ORDER BY `areaid`+0 ASC');
	foreach ($areaList->items as $rs) {
		$form.='<option value="'.$rs->areaid.'" '.($rs->areaid==$area?'selected="selected"':'').'>เขต '.$rs->areaid.' '.$rs->areaname.'</option>';
	}
	$form.='</select> ';

	// Select province
	if ($area) {
		$stmt='SELECT DISTINCT f.`changwat`, cop.`provname` FROM %project_fund% f LEFT JOIN %co_province% cop ON cop.`provid`=f.`changwat` WHERE f.`areaid`=:areaid HAVING `provname` != "" ';
		$provList=mydb::select($stmt,':areaid',$area);
		$form.='<select id="province" class="form-select" name="prov">';
		$form.='<option value="">ทุกจังหวัด</option>';
		foreach ($provList->items as $rs) {
			$form.='<option value="'.$rs->changwat.'" '.($rs->changwat==$prov?'selected="selected"':'').'>'.$rs->provname.'</option>';
		}
		$form.='</select> ';
	}

	// Select province
	if ($prov) {
		$stmt='SELECT DISTINCT `distid`, `distname` FROM  %co_district% WHERE LEFT(`distid`,2)=:prov';
		$ampurList=mydb::select($stmt,':prov',$prov);
		$form.='<select id="ampur" class="form-select" name="ampur">';
		$form.='<option value="">ทุกอำเภอ</option>';
		foreach ($ampurList->items as $rs) {
			$form.='<option value="'.substr($rs->distid,2).'" '.(substr($rs->distid,2)==$ampur?'selected="selected"':'').'>'.$rs->distname.'</option>';
		}
		$form.='</select> ';
	}

	$form.='</form>'._NL;

	$ret.=$form;

	$label='CONCAT("เขต ",LPAD(a.areaid,2," ")," ",a.`areaname`)';

	if ($year)
		mydb::where('YEAR(g.`refdate`)+IF(MONTH(g.`refdate`)>=10,1,0)=:year',':year',$year);

	if ($area) {
	 mydb::where('f.`areaid`=:areaid',':areaid',$area);
	 $label='f.`namechangwat`';
	}

	if ($ampur) {
		mydb::where('f.`changwat`=:prov AND f.`ampur`=:ampur',':prov',$prov,':ampur',$ampur);
		$label='f.`fundname`';
	} else if ($prov) {
		mydb::where('f.`changwat`=:prov',':prov',$prov);
		$label='f.`nameampur`';
	}

	mydb::value('$label', $label, false);

	$stmt='SELECT
					a.`areaid`
				, $label `label`
				, g.`glcode`, g.`amount`
				, ABS(SUM(IF(g.`glcode` = "40100", g.`amount`, 0))) `totalNHSO`
				, ABS(SUM(IF(g.`glcode` = "40200", g.`amount`, 0))) `totalLocal`
				, ABS(SUM(IF(g.`glcode` = "40300", g.`amount`, 0))) `totalInterest`
				, ABS(SUM(IF(g.`glcode` = "40400", g.`amount`, 0))) `totalOther`
			--	, COUNT(f.`areaid`) `totalFund`
			--	, COUNT(IF(f.`openbalance`>0,1,NULL)) `totalJoin`
				FROM %project_area% a
					LEFT JOIN %project_fund% f USING(`areaid`)
					LEFT JOIN %project_gl% g ON g.`orgid`=f.`orgid` AND LEFT(g.`glcode`,1)=4
				%WHERE%
				GROUP BY `label`
				ORDER BY CONVERT(`label` USING tis620) ASC
				;
				-- {sum:"totalNHSO,totalLocal,totalInterest,totalOther"}
				';

	$dbs=mydb::select($stmt);
	//$ret.=mydb()->_query.'<br />';


	$tables = new Table();
	$tables->thead=array('no'=>'ลำดับ','พื้นที่','money -nhso'=>'ค่าบริการจากสปสช.','money -local'=>'เงินอุดหนุนจากอปท.','money -interest'=>'	ดอกเบี้ย','money -other'=>'รายได้อื่นๆ','money -total'=>'รวม');

	$chartTable = new Table();
	foreach ($dbs->items as $rs) {
		$total=$rs->totalNHSO+$rs->totalLocal+$rs->totalInterest+$rs->totalOther;
		$totalAll+=$total;

		$tables->rows[]=array(
			++$i,
			$rs->label,
			number_format($rs->totalNHSO,2),
			number_format($rs->totalLocal,2),
			number_format($rs->totalInterest,2),
			number_format($rs->totalOther,2),
			number_format($total,2),
		);

		$chartTable->rows[]=array(
			'string:label'=>$rs->label,
			'number:ค่าบริการจาก สปสช'=>$rs->totalNHSO,
			'string:ค่าบริการจาก สปสช:role'=>number_format($rs->totalNHSO/1000000,1).'M',
			'number:เงินอุดหนุนจาก อปท.'=>$rs->totalLocal,
			'string:เงินอุดหนุนจาก อปท.:role'=>number_format($rs->totalLocal/1000000,1).'M',
			'number:ดอกเบี้ย'=>$rs->totalInterest,
			//'string:ดอกเบี้ย:role'=>number_format($rs->totalInterest/1000000,1).'M',
			'number:รายได้อื่นๆ'=>$rs->totalOther,
			//'string:รายได้อื่นๆ:role'=>number_format($rs->totalOther/1000,1).'K',
		);
	}

	$tables->tfoot[]=array(
		'<td></td>',
		'รวม',
		number_format($dbs->sum->totalNHSO,2),
		number_format($dbs->sum->totalLocal,2),
		number_format($dbs->sum->totalInterest,2),
		number_format($dbs->sum->totalOther,2),
		number_format($totalAll,2),
	);

	$options=array(
		"isStacked"=>true,
		"legend"=>array("position"=>"bottom"),
		"hAxis"=>array(
			"textStyle"=>array(
				"fontSize"=>12,
			)
		),
	);

	$ret.='<div id="fund-join" class="sg-chart -join" data-chart-type="col" data-options=\''.json_encode($options).'\'><h3>แผนภูมิแสดงจำนวนเงินงบประมาณของกองทุนฯ</h3>'.$chartTable->build().'</div>'._NL;
	//$ret.=$chartTable->build();

	$ret.=$tables->build();
	//$ret.='<p>หมายเหตุ: คลิกที่ชื่อเขตเพื่อดูรายละเอียด</p>';

	//$ret.=print_o($dbs,'$dbs');

	head('googlegraph','<script type="text/javascript" src="https
		://www.gstatic.com/charts/loader.js"></script>');
	$ret.='<style type="text/css">
	.sg-chart {height:400px; overflow:hidden;}
	</style>';

	$ret.='<script type="text/javascript">
	$("body").on("change","#condition select", function() {
		var $this=$(this);
		if ($this.attr("name")=="area") {
			$("#province").val("");
			$("#ampur").val("");
		}
		if ($this.attr("name")=="prov") {
			$("#ampur").val("");
		}
		notify("กำลังโหลด");
		console.log($(this).attr("name"))
		$(this).closest("form").submit();
	});
	</script>';

	return $ret;
}
?>