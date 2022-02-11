<?php
/**
* Project :: Fund Report Balance
* Created 2018-03-13
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/report/balance
*/

$debug = true;

function project_fund_report_balance($self) {
	$year=post('yr');
	$area=post('area');
	$prov=post('prov');
	$ampur=post('ampur');

	$repTitle='รายงานสรุปจำนวนเงินคงเหลือของกองทุนฯ';

	R::view('project.toolbar',$self,'รายงาน - '.$repTitle,'fund');

	$ui=new Ui();
	$ui->add('<a class="btn" href="'.url('project/report').'">รายงาน</a>');
	$ui->add('<a class="btn" href="'.url('project/fund/report/balance').'">'.$repTitle.'</a>');
	$ret.='<nav class="nav -page">'.$ui->build().'</nav>';

	$form='<form id="condition" action="'.url('project/fund/report/balance').'" method="get">';
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

	$form .= '</form>'._NL;

	$ret .= $form;

	$label = 'CONCAT("เขต ",LPAD(a.areaid,2," ")," ",a.`areaname`)';

	if ($year)
		mydb::where('YEAR(g.`refdate`)+IF(MONTH(g.`refdate`)>=10,1,0)=:year',':year',$year);

	if ($area) {
	 mydb::where('f.`areaid`=:areaid', ':areaid', $area);
	 $label = 'f.`namechangwat`';
	}

	if ($ampur) {
		mydb::where('f.`changwat`=:prov AND f.`ampur`=:ampur', ':prov', $prov, ':ampur', $ampur);
		$label = 'CONCAT(f.`fundid`," ",f.`fundname`)';
	} else if ($prov) {
		mydb::where('f.`changwat`=:prov', ':prov', $prov);
		$label = 'f.`nameampur`';
	}

	mydb::value('$label', $label, false);

	$stmt = 'SELECT
			  $label `label`
			,	f.*
			, SUM(`openbalance`) `totalOpenBalance`
			, SUM(`totalRcv`) `totalRcv`
			, SUM(`totalRet`) `totalRet`
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
					, f.`fundid`
					, o.`name` `fundname`
				  , f.`openbalance`
					, ABS(SUM(IF(gc.`gltype` = "4" AND g.`glcode` IN ("40100", "40200", "40300", "40400"), g.`amount`, 0))) `totalRcv`
				--	, ABS(SUM(IF(gc.`gltype` = "4" AND g.`glcode` != "40500", g.`amount`, 0))) `totalRcv`
				--	, 0 totalRet
					, ABS(SUM(IF(gc.`gltype` = "4" AND g.`glcode` = "40500", g.`amount`, 0))) `totalRet`
					, ABS(SUM(IF(gc.`gltype` = "5",g.`amount`, 0))) `totalPaid`
				FROM %project_fund% f
					LEFT JOIN %db_org% o USING(`orgid`)
					LEFT JOIN %project_gl% g ON g.`orgid` = f.`orgid`
					LEFT JOIN %glcode% gc USING(`glcode`)
				%WHERE%
				GROUP BY `orgid`
			) f
				RIGHT JOIN %project_area% a USING(`areaid`)
			GROUP BY `label`
			HAVING `label` IS NOT NULL
			ORDER BY CONVERT(`label` USING tis620) ASC
			;
			-- {sum:"totalOpenBalance,totalRcv,totalRet,totalPaid"}
			';


	$dbs = mydb::select($stmt);
	//$ret .= mydb()->_query.'<br />';


	$tables = new Table();
	$tables->thead=array('no'=>'ลำดับ','พื้นที่','money -nhso'=>'ยอดเงินคงเหลือยกมา','money -local'=>'รายรับ','money -interest'=>'	เหลือคืนจากโครงการ','money -other'=>'รายจ่าย','money -total'=>'คงเหลือ');

	$chartTable = new Table();
	foreach ($dbs->items as $rs) {
		$balance=$rs->totalOpenBalance+$rs->totalRcv+$rs->totalRet-$rs->totalPaid;
		$totalAll+=$total;

		$tables->rows[]=array(
			++$i,
			$rs->label,
			number_format($rs->totalOpenBalance,2),
			number_format($rs->totalRcv,2),
			number_format($rs->totalRet,2),
			number_format($rs->totalPaid,2),
			number_format($balance,2),
		);

		$chartTable->rows[]=array(
			'string:label'=>$rs->label,
			'number:รายรับ'=>$rs->totalRcv+$rs->totalRet,
			'string:รายรับ:role'=>number_format(($rs->totalRcv+$rs->totalRet)/1000000,1).'M',
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
		number_format($dbs->sum->totalRcv,2),
		number_format($dbs->sum->totalRet,2),
		number_format($dbs->sum->totalPaid,2),
		number_format($dbs->sum->totalOpenBalance+$dbs->sum->totalRcv+$dbs->sum->totalRet-$dbs->sum->totalPaid,2),
	);

	$options=array(
		"legend"=>array("position"=>"bottom"),
		"hAxis"=>array(
			"textStyle"=>array(
				"fontSize"=>12,
			)
		),
	);
	$ret.='<div id="fund-join" class="sg-chart -join" data-chart-type="col" data-options=\''.json_encode($options).'\'><h3>แผนภูมิแสดงจำนวนเงินคงเหลือของกองทุนฯ</h3>'.$chartTable->build().'</div>'._NL;
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