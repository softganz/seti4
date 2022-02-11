<?php
/**
* Project :: Fund Report Follow Project Budget
* Created 2017-07-13
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/report/project/budget
*/

$debug = true;

function project_fund_report_project_budget($self) {
	$year=post('yr');
	$area=post('area');
	$prov=post('prov');
	$ampur=post('ampur');
	$numberToShow=5;

	$repTitle='รายงานสรุปจำนวนการสนับสนุนงบประมาณตามแผนงาน/โครงการ/กิจกรรมของกองทุนฯ';

	R::view('project.toolbar',$self,'รายงาน - '.$repTitle,'fund');

	$ui=new Ui();
	$ui->add('<a class="btn" href="'.url('project/report').'">รายงาน</a>');
	$ui->add('<a class="btn" href="'.url('project/fund/report/project/budget').'">'.$repTitle.'</a>');
	$ret.='<nav class="nav -page">'.$ui->build().'</nav>';

	$form='<form id="condition" action="'.url('project/fund/report/project/budget').'" method="get">';
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
		$stmt='SELECT DISTINCT `distid`, `distname` FROM  %co_district% WHERE LEFT(`distid`,2)=:prov ORDER BY CONVERT(`distname` USING tis620) ASC';
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
		mydb()->where('p.`pryear`=:year',':year',$year);
	}
	if ($area) {
	 mydb()->where('f.`areaid`=:areaid',':areaid',$area);
	 $label='f.`namechangwat`';
	 	$numberToShow=2000000;
	}
	if ($ampur) {
		mydb()->where('f.`changwat`=:prov AND f.`ampur`=:ampur',':prov',$prov,':ampur',$ampur);
		$label='o.`name`';
		$numberToShow=50000;
	} else if ($prov) {
		mydb()->where('f.`changwat`=:prov',':prov',$prov);
		$label='f.`nameampur`';
		$numberToShow=200000;
	}

	$stmt="SELECT
					a.`areaid`
				, $label `label`
				, SUM(p.`budget`) `totalProject`
				, SUM(IF(p.`supporttype`=1,p.`budget`,NULL)) `totalType1`
				, SUM(IF(p.`supporttype`=2,p.`budget`,NULL)) `totalType2`
				, SUM(IF(p.`supporttype`=3,p.`budget`,NULL)) `totalType3`
				, SUM(IF(p.`supporttype`=4,p.`budget`,NULL)) `totalType4`
				, SUM(IF(p.`supporttype`=5,p.`budget`,NULL)) `totalType5`
				, SUM(IF(p.`tpid` IS NOT NULL AND (p.`supporttype`='' OR p.`supporttype` IS NULL),p.`budget`,NULL)) `totalNA`
				FROM %project_area% a
					LEFT JOIN %project_fund% f USING(`areaid`)
					LEFT JOIN %db_org% o USING(`orgid`)
					LEFT JOIN %topic% t ON t.`type`='project' AND t.`orgid`=f.`orgid`
					LEFT JOIN %project% p USING(`tpid`)
				%WHERE%
				GROUP BY `label`
				ORDER BY CONVERT(`label` USING tis620) ASC
				;
				-- {sum:\"totalType1,totalType2,totalType3,totalType4,totalType5,totalNA,totalProject\"}
				";


	$dbs=mydb::select($stmt);
	//$ret.=mydb()->_query.'<br />';


	$tables = new Table();
	$tables->thead=array('no'=>'ลำดับ','พื้นที่','amt -type1'=>'ประเภทที่ 1','amt -type2'=>'ประเภทที่ 2','amt -type3'=>'ประเภทที่ 3','amt -type4'=>'ประเภทที่ 4','amt -type5'=>'ประเภทที่ 5','amt -na'=>'ไม่ระบุ','amt -total'=>'รวม');

	$chartTable = new Table();
	foreach ($dbs->items as $rs) {
		$balance=$rs->totalOpenBalance+$rs->totalRcv+$rs->totalRet-$rs->totalPaid;
		$totalAll+=$total;

		$tables->rows[]=array(
			++$i,
			$rs->label,
			number_format($rs->totalType1,2),
			number_format($rs->totalType2,2),
			number_format($rs->totalType3,2),
			number_format($rs->totalType4,2),
			number_format($rs->totalType5,2),
			number_format($rs->totalNA,2),
			number_format($rs->totalProject,2),
		);

		$chartTable->rows[]=array(
			'string:label'=>$rs->label,
			'number:ประเภทที่ 1'=>number_format($rs->totalType1,2),
			'string:ประเภทที่ 1:role'=>$rs->totalType1>=$numberToShow?number_format($rs->totalType1/1000000,1).'M':'',
			'number:ประเภทที่ 2'=>number_format($rs->totalType2,2),
			'string:ประเภทที่ 2:role'=>$rs->totalType2>=$numberToShow?number_format($rs->totalType2/1000000,1).'M':'',
			'number:ประเภทที่ 3'=>number_format($rs->totalType3,2),
			'string:ประเภทที่ 3:role'=>$rs->totalType3>=$numberToShow?number_format($rs->totalType3/1000000,1).'M':'',
			'number:ประเภทที่ 4'=>number_format($rs->totalType40,2),
			'string:ประเภทที่ 4:role'=>$rs->totalType4>=$numberToShow?number_format($rs->totalType4/1000000,1).'M':'',
			'number:ประเภทที่ 5'=>number_format($rs->totalType5,2),
			'string:ประเภทที่ 5:role'=>$rs->totalType5>=$numberToShow?number_format($rs->totalType5/1000000,1).'M':'',
			'number:ไม่ระบุ'=>number_format($rs->totalNA,2),
			'string:ไม่ระบุ:role'=>$rs->totalNA>=$numberToShow?number_format($rs->totalNA/1000000,1).'M':'',
		);
	}

	$tables->tfoot[]=array(
		'<td></td>',
		'รวม',
		number_format($dbs->sum->totalType1),
		number_format($dbs->sum->totalType2),
		number_format($dbs->sum->totalType3),
		number_format($dbs->sum->totalType4),
		number_format($dbs->sum->totalType5),
		number_format($dbs->sum->totalNA),
		number_format($dbs->sum->totalProject),
	);

	$chartPie = new Table();
	$chartPie->rows[]=array('string:label'=>'ประเภทที่ 1','number:1'=>$dbs->sum->totalType1);
	$chartPie->rows[]=array('string:label'=>'ประเภทที่ 2','number:2'=>$dbs->sum->totalType2);
	$chartPie->rows[]=array('string:label'=>'ประเภทที่ 3','number:3'=>$dbs->sum->totalType3);
	$chartPie->rows[]=array('string:label'=>'ประเภทที่ 4','number:4'=>$dbs->sum->totalType4);
	$chartPie->rows[]=array('string:label'=>'ประเภทที่ 5','number:5'=>$dbs->sum->totalType5);
	$chartPie->rows[]=array('string:label'=>'ไม่ระบุ','number:6'=>$dbs->sum->totalNA);

	$ret.='<div id="fund-type" class="sg-chart -type" data-chart-type="pie"><h3>แผนภูมิแสดงการสนับสนุนงบประมาณตามแผนงาน/โครงการ/กิจกรรมของกองทุนฯ</h3>'.$chartPie->build().'</div>'._NL;

	$options=array(
		"isStacked"=>true,
		"legend"=>array("position"=>"bottom"),
		"hAxis"=>array(
			"textStyle"=>array(
				"fontSize"=>12,
			)
		),
		"annotations"=>array(
			"textStyle"=>array(
				"fontSize"=>8,
			),
		),
	);
	$ret.='<div id="fund-join" class="sg-chart -join" data-chart-type="col" data-options=\''.json_encode($options).'\'><h3>แผนภูมิแสดงการสนับสนุนงบประมาณตามแผนงาน/โครงการ/กิจกรรมของกองทุนฯ</h3>'.$chartTable->build().'</div>'._NL;
	//$ret.=$chartTable->build();

	$ret.=$tables->build();
	//$ret.='<p>หมายเหตุ: คลิกที่ชื่อเขตเพื่อดูรายละเอียด</p>';

	$supportTypeNameList=model::get_category('project:supporttype','catid');
	$ret.='<p><strong>นิยามศัพท์</strong><br />';
	$ret.='<ul><li>'.implode('</li><li>', $supportTypeNameList).'</li></ul>';
	$ret.='</p>';
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