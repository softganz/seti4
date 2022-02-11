<?php
/**
* Project :: Fund Report Follow Project By Organization
* Created 2017-07-17
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/report/project/org
*/

$debug = true;

function project_fund_report_project_org($self) {
	$year=post('yr');
	$area=post('area');
	$prov=post('prov');
	$ampur=post('ampur');
	$numberToShow=5;

	$repTitle='รายงานสรุปจำนวนแผนงาน/โครงการ/กิจกรรมของกองทุนฯ เปรียบเทียบกับองค์กรรับทุน';
	$supportTypeNameList=model::get_category('project:supportorg','catid');

	R::view('project.toolbar',$self,'รายงาน - '.$repTitle,'fund');

	$ui=new Ui();
	$ui->add('<a class="btn" href="'.url('project/report').'">รายงาน</a>');
	$ui->add('<a class="btn" href="'.url('project/fund/report/project/org').'">'.$repTitle.'</a>');
	$ret.='<nav class="nav -page">'.$ui->build().'</nav>';

	$form='<form id="condition" action="'.url('project/fund/report/project/org').'" method="get">';
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
	 	$numberToShow=50;
	}
	if ($ampur) {
		mydb()->where('f.`changwat`=:prov AND f.`ampur`=:ampur',':prov',$prov,':ampur',$ampur);
		$label='o.`name`';
		$numberToShow=3;
	} else if ($prov) {
		mydb()->where('f.`changwat`=:prov',':prov',$prov);
		$label='f.`nameampur`';
		$numberToShow=10;
	}

	$stmt="SELECT
					a.`areaid`
				, $label `label`
				, COUNT(p.`tpid`) `totalProject`
				, COUNT(IF(p.`supportorg`=1,1,NULL)) `totalType1`
				, COUNT(IF(p.`supportorg`=2,1,NULL)) `totalType2`
				, COUNT(IF(p.`supportorg`=3,1,NULL)) `totalType3`
				, COUNT(IF(p.`supportorg`=4,1,NULL)) `totalType4`
				, COUNT(IF(p.`supportorg`=5,1,NULL)) `totalType5`
				, COUNT(IF(p.`supportorg`=6,1,NULL)) `totalType6`
				, COUNT(IF(p.`tpid` IS NOT NULL AND (p.`supportorg`='' OR p.`supportorg` IS NULL),1,NULL)) `totalNA`
				FROM %project_area% a
					LEFT JOIN %project_fund% f USING(`areaid`)
					LEFT JOIN %db_org% o USING(`orgid`)
					LEFT JOIN %topic% t ON t.`type`='project' AND t.`orgid`=f.`orgid`
					LEFT JOIN %project% p USING(`tpid`)
				%WHERE%
				GROUP BY `label`
				ORDER BY CONVERT(`label` USING tis620) ASC
				;
				-- {sum:\"totalType1,totalType2,totalType3,totalType4,totalType5,totalType6,totalNA,totalProject\"}
				";


	$dbs=mydb::select($stmt);
	//$ret.=mydb()->_query.'<br />';


	$tables = new Table();
	$tables->thead=array('no'=>'ลำดับ','พื้นที่','amt -type1'=>'องค์กรที่ 1','amt -type2'=>'องค์กรที่ 2','amt -type3'=>'องค์กรที่ 3','amt -type4'=>'องค์กรที่ 4','amt -type5'=>'องค์กรที่ 5','amt -type6'=>'องค์กรที่ 6','amt -na'=>'ไม่ระบุ','amt -total'=>'รวม');

	$chartTable = new Table();
	foreach ($dbs->items as $rs) {
		$balance=$rs->totalOpenBalance+$rs->totalRcv+$rs->totalRet-$rs->totalPaid;
		$totalAll+=$total;

		$tables->rows[]=array(
			++$i,
			$rs->label,
			number_format($rs->totalType1),
			number_format($rs->totalType2),
			number_format($rs->totalType3),
			number_format($rs->totalType4),
			number_format($rs->totalType5),
			number_format($rs->totalType6),
			number_format($rs->totalNA),
			number_format($rs->totalProject),
		);

		$chartTable->rows[]=array(
			'string:label'=>$rs->label,
			'number:องค์กรที่ 1'=>$rs->totalType1,
			'string:องค์กรที่ 1:role'=>$rs->totalType1>=$numberToShow?number_format($rs->totalType1):'',
			'number:องค์กรที่ 2'=>$rs->totalType2,
			'string:องค์กรที่ 2:role'=>$rs->totalType2>=$numberToShow?number_format($rs->totalType2):'',
			'number:องค์กรที่ 3'=>$rs->totalType3,
			'string:องค์กรที่ 3:role'=>$rs->totalType3>=$numberToShow?number_format($rs->totalType3):'',
			'number:องค์กรที่ 4'=>$rs->totalType4,
			'string:องค์กรที่ 4:role'=>$rs->totalType4>=$numberToShow?number_format($rs->totalType4):'',
			'number:องค์กรที่ 5'=>$rs->totalType5,
			'string:องค์กรที่ 5:role'=>$rs->totalType5>=$numberToShow?number_format($rs->totalType5):'',
			'number:องค์กรที่ 6'=>$rs->totalType6,
			'string:องค์กรที่ 6:role'=>$rs->totalType6>=$numberToShow?number_format($rs->totalType6):'',
			'number:ไม่ระบุ'=>$rs->totalNA,
			'string:ไม่ระบุ:role'=>$rs->totalNA>=$numberToShow?number_format($rs->totalNA):'',
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
		number_format($dbs->sum->totalType6),
		number_format($dbs->sum->totalNA),
		number_format($dbs->sum->totalProject),
	);

	$chartPie = new Table();
	$chartPie->rows[]=array('string:label'=>'องค์กรที่ 1 '.$supportTypeNameList[1],'number:1'=>$dbs->sum->totalType1);
	$chartPie->rows[]=array('string:label'=>'องค์กรที่ 2 '.$supportTypeNameList[2],'number:2'=>$dbs->sum->totalType2);
	$chartPie->rows[]=array('string:label'=>'องค์กรที่ 3 '.$supportTypeNameList[3],'number:3'=>$dbs->sum->totalType3);
	$chartPie->rows[]=array('string:label'=>'องค์กรที่ 4 '.$supportTypeNameList[4],'number:4'=>$dbs->sum->totalType4);
	$chartPie->rows[]=array('string:label'=>'องค์กรที่ 5 '.$supportTypeNameList[5],'number:5'=>$dbs->sum->totalType5);
	$chartPie->rows[]=array('string:label'=>'องค์กรที่ 6 '.$supportTypeNameList[6],'number:6'=>$dbs->sum->totalType6);
	$chartPie->rows[]=array('string:label'=>'ไม่ระบุ','number:6'=>$dbs->sum->totalNA);

	$ret.='<div id="fund-type" class="sg-chart -type" data-chart-type="pie"><h3>แผนภูมิแสดงจำนวนแผนงาน/โครงการ/กิจกรรมของกองทุนฯ เปรียบเทียบกับองค์กรรับทุน</h3>'.$chartPie->build().'</div>'._NL;

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

	$ret.='<div id="fund-join" class="sg-chart -join" data-chart-type="col" data-options=\''.json_encode($options).'\'><h3>แผนภูมิแสดงจำนวนจำนวนแผนงาน/โครงการ/กิจกรรมของกองทุนฯ เปรียบเทียบกับองค์กรรับทุน</h3>'.$chartTable->build().'</div>'._NL;
	//$ret.=$chartTable->build();

	$ret.=$tables->build();
	//$ret.='<p>หมายเหตุ: คลิกที่ชื่อเขตเพื่อดูรายละเอียด</p>';

	$ret.='<p><strong>นิยามศัพท์</strong><br />';
	$ret.='<ul>';
	foreach ($supportTypeNameList as $k => $v) {
		$ret.='<li>องค์กรที่ '.$k.' '.$v.'</li>';
	}
	$ret.='</ul>';
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