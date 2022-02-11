<?php
/**
* Project :: Fund Report Money Used Rate
* Created 2017-08-11
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/report/moneyuserate
*/

$debug = true;

function project_fund_report_moneyuserate($self,$fundid) {
	$year=post('yr');
	$area=post('area');
	$prov=post('prov');
	$ampur=post('ampur');

	$repTitle='รายงานสัดส่วนการใช้จ่ายของกองทุนตำบล';

	R::view('project.toolbar',$self,'รายงาน - '.$repTitle,'fund');

	$ui=new Ui();
	$ui->add('<a class="btn" href="'.url('project/report').'">รายงาน</a>');
	$ui->add('<a class="btn" href="'.url('project/fund/report/balancerate').'">'.$repTitle.'</a>');
	$ret.='<nav class="nav -page">'.$ui->build().'</nav>';

	$form='<form id="condition" action="'.url('project/fund/report/balancerate').'" method="get">';
	$form.='<span>ตัวเลือก </span>';

	// Select year
	$stmt='SELECT DISTINCT YEAR(`refdate`)+IF(MONTH(`refdate`)>=10,1,0) `budgetYear` FROM %project_gl% HAVING `budgetYear` ORDER BY `budgetYear` ASC';
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
		$stmt='SELECT DISTINCT f.`changwat`, cop.`provname` FROM %project_fund% f LEFT JOIN %co_province% cop ON cop.`provid`=f.`changwat` WHERE f.`areaid`=:areaid';
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
	if ($year) {
		mydb::where(NULL,':startdate',($year-1).'-10-01',':enddate',$year.'-09-30',':closebalancedate',($year-1).'-09-30');
	} else {
		mydb::where(NULL,':startdate','2016-10-01',':enddate',date('Y').'-09-30',':closebalancedate',(date('Y')-1).'-09-30');
	}
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

	$stmt="
		SELECT
			  $label `label`
			,	f.*
			, SUM(`openbalance`)-SUM(`afteropen`) `totalOpenBalance`
			, SUM(`afteropen`) `totalExpenseAfter`
			, SUM(`totalNHSO`) `totalNHSO`
			, SUM(`totalLocal`) `totalLocal`
			, SUM(`totalInterest`) `totalInterest`
			, SUM(`totalEtc`) `totalEtc`
			, SUM(`totalRefund`) `totalRefund`
			, SUM(`totalPaid`) `totalPaid`
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
					, f.`openbalance`
					, SUM(IF(gc.`gltype` IN ('4','5') AND g.`refdate` BETWEEN f.`openbaldate` AND :closebalancedate,g.`amount`,0)) `afteropen`
					, ABS(SUM(IF(g.`glcode`='40100' AND g.`refdate` BETWEEN :startdate AND :enddate,g.`amount`,0))) `totalNHSO`
					, ABS(SUM(IF(g.`glcode`='40200' AND g.`refdate` BETWEEN :startdate AND :enddate,g.`amount`,0))) `totalLocal`
					, ABS(SUM(IF(g.`glcode`='40300' AND g.`refdate` BETWEEN :startdate AND :enddate,g.`amount`,0))) `totalInterest`
					, ABS(SUM(IF(g.`glcode`='40400' AND g.`refdate` BETWEEN :startdate AND :enddate,g.`amount`,0))) `totalEtc`
					, ABS(SUM(IF(g.`glcode`='40500' AND g.`refdate` BETWEEN :startdate AND :enddate,g.`amount`,0))) `totalRefund`
					, ABS(SUM(IF(gc.`gltype`='5' AND g.`refdate` BETWEEN :startdate AND :enddate,g.`amount`,0))) `totalPaid`
				FROM %project_fund% f
					LEFT JOIN %db_org% o USING(`orgid`)
					LEFT JOIN %project_gl% g ON g.`orgid`=f.`orgid`
					LEFT JOIN %glcode% gc USING(`glcode`)
				GROUP BY `orgid`
			) f
				RIGHT JOIN %project_area% a USING(`areaid`)
			%WHERE%
			GROUP BY `label`
			ORDER BY CONVERT(`label` USING tis620) ASC
			;
			-- {sum:\"totalOpenBalance,totalNHSO,totalLocal,totalInterest,totalEtc,totalRefund,totalPaid\"}
			";


	$dbs=mydb::select($stmt);
	//$ret.='<pre>'.mydb()->_query.'</pre><br />';
	//$ret.=mydb::printtable($dbs);


	$tables = new Table();
	$tables->thead=array('no'=>'ลำดับ','พื้นที่','money -open'=>'ยอดเงินคงเหลือยกมา','money -nhso'=>'สปสช.จัดสรร','money -local'=>'อปท.อุดหนุน','money -interest'=>'ดอกเบี้ย','money -etc'=>'อื่นๆ','money -refund'=>'เงินคืน','money -recieve'=>'รายรับทั้งหมด','money -expense'=>'รายจ่าย','money -total'=>'เงินคงเหลือ','amt -percent'=>'สัดส่วนการใช้เทียบกับรายรับ(%)','amt percent2'=>'สัดส่วนการใช้เทียบกับเงินทั้งหมด(%)');

	$totalRecieve=0;
	$chartTable = new Table();
	foreach ($dbs->items as $rs) {
		$recieve=$rs->totalNHSO+$rs->totalLocal+$rs->totalInterest+$rs->totalEtc+$rs->totalRefund;
		$balance=$rs->totalOpenBalance+$recieve-$rs->totalPaid;
		$totalRecieve+=$recieve;
		$totalBalance+=$balance;

		$tables->rows[]=array(
			++$i,
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
			number_format($rs->totalPaid*100/$recieve,2),
			number_format($rs->totalPaid*100/($rs->totalOpenBalance+$recieve),2),
		);

		$chartTable->rows[]=array(
			'string:label'=>$rs->label,
			'number:รายรับ'=>$recieve,
			'string:รายรับ:role'=>number_format(($recieve)/1000000,1).'M',
			'number:รายจ่าย'=>$rs->totalPaid,
			'string:รายจ่าย:role'=>number_format($rs->totalPaid/1000000,1).'M',
			//'number:คงเหลือ'=>$balance,
			//'string:รายได้อื่นๆ:role'=>number_format($rs->totalOther/1000,1).'K',
		);
	}

	$tables->tfoot[]=array(
		'<td></td>',
		'รวม',
		number_format($dbs->sum->totalOpenBalance,2),
		number_format($dbs->sum->totalNHSO,2),
		number_format($dbs->sum->totalLocal,2),
		number_format($dbs->sum->totalInterest,2),
		number_format($dbs->sum->totalEtc,2),
		number_format($dbs->sum->totalRefund,2),
		number_format($totalRecieve,2),
		number_format($dbs->sum->totalPaid,2),
		number_format($totalBalance,2),
		number_format($dbs->sum->totalPaid*100/$totalRecieve,2),
		number_format($dbs->sum->totalPaid*100/($dbs->sum->totalOpenBalance+$totalRecieve),2),
	);

	$options=array(
		"legend"=>array("position"=>"bottom"),
		"hAxis"=>array(
			"textStyle"=>array(
				"fontSize"=>12,
			)
		),
	);

	$ret.='<div id="fund-join" class="sg-chart -join" data-chart-type="col" data-options=\''.json_encode($options).'\'><h3>แผนภูมิแสดงรายรับ/รายจ่ายของกองทุนฯ</h3>'.$chartTable->build().'</div>'._NL;

	//$ret.=$chartTable->build();

	$ret.=$tables->build();
	//$ret.='<p>หมายเหตุ: คลิกที่ชื่อเขตเพื่อดูรายละเอียด</p>';

	$ret.='<h3>รายงานโครงการ จำแนกตามประเภท</h3>';
	$ret.='<h3>จำนวนเงินที่โครงการทั้งหมด/ปีนี้/งบ/เบิก/ความเร็วของการเบิกจ่าย</h3>';
	$ret.='<h3>คงเหลือเมื่อเทียบกับค่ามาตรฐานไม่ควรเกิน 10% ของยอดคงเหลือทั้งหมด</h3>';
	//$ret.=print_o($dbs,'$dbs');

	head('googlegraph','<script type="text/javascript" src="https
		://www.gstatic.com/charts/loader.js"></script>');
	$ret.='<style type="text/css">
	.sg-chart {height:400px; overflow:hidden;}
	.col-money.-nhso,.col-money.-local,.col-money.-interest,.col-money.-etc,.col-money.-refund {color:#999;}
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