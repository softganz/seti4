<?php
/**
* Project :: Fund Report Follow Project Target
* Created 2017-07-13
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/report/project/target
*/

$debug = true;

function project_fund_report_project_target($self) {
	$repType=SG\getFirst(post('rep'),1);
	$year=post('yr');
	$area=post('area');
	$prov=post('prov');
	$ampur=post('ampur');

	$repTitle='รายงานสรุปจำนวนแผนงาน/โครงการ/กิจกรรมของกองทุนฯ เปรียบเทียบกับกลุ่มเป้าหมาย';

	R::view('project.toolbar',$self,'รายงาน - '.$repTitle,'fund');

	$supportTypeNameList=model::get_category('project:target','catid');
	$numberToShowList=array(
		'none'=>array(1=>30000,2=>10,3=>2000000),
		'area'=>array(1=>10000,2=>20,3=>1000000),
		'prov'=>array(1=>1000,2=>3,3=>500000),
		'ampur'=>array(1=>500,2=>1,3=>300000),
	);

	$numberToShow=$numberToShowList['none'][$repType];

	$ui=new Ui();
	$ui->add('<a class="btn" href="'.url('project/report').'">รายงาน</a>');
	$ui->add('<a class="btn" href="'.url('project/fund/report/project/target').'">'.$repTitle.'</a>');
	$ret.='<nav class="nav -page">'.$ui->build().'</nav>';

	$form='<form id="condition" action="'.url('project/fund/report/project/target').'" method="get">';
	$form.='<span>ตัวเลือก </span>';

	$form.='<select id="rep" class="form-select" name="rep">';
	$form.='<option value="1" '.($repType==1?'selected="selected"':'').'>จำนวนกลุ่มเป้าหมาย</option>';
	$form.='<option value="2" '.($repType==2?'selected="selected"':'').'>จำนวนโครงการ</option>';
	$form.='<option value="3" '.($repType==3?'selected="selected"':'').'>จำนวนงบประมาณ</option>';
	foreach ($yearList->items as $rs) {
		$form.='<option value="'.$rs->budgetYear.'" '.($rs->budgetYear==$year?'selected="selected"':'').'>พ.ศ.'.($rs->budgetYear+543).'</option>';
	}
	$form.='</select> ';

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
		$numberToShow=$numberToShowList['area'][$repType];
	}
	if ($ampur) {
		mydb()->where('f.`changwat`=:prov AND f.`ampur`=:ampur',':prov',$prov,':ampur',$ampur);
		$label='o.`name`';
		$numberToShow=$numberToShowList['ampur'][$repType];
	} else if ($prov) {
		mydb()->where('f.`changwat`=:prov',':prov',$prov);
		$label='f.`nameampur`';
		$numberToShow=$numberToShowList['prov'][$repType];
	}

	$func=$repType==2?'COUNT':'SUM';
	$fld=$repType==3?'p.`budget`':'tg.`amount`';
	$digit=$repType==3?2:0;
	$stmt="SELECT
					a.`areaid`
				, $label `label`
				, $func(IF(tg.`tgtid`='1001',$fld,NULL)) `totalType1`
				, $func(IF(tg.`tgtid`='1002',$fld,NULL)) `totalType2`
				, $func(IF(tg.`tgtid`='1003',$fld,NULL)) `totalType3`
				, $func(IF(tg.`tgtid`='1004',$fld,NULL)) `totalType4`
				, $func(IF(tg.`tgtid`='2001',$fld,NULL)) `totalType5`
				, $func(IF(tg.`tgtid`='2002',$fld,NULL)) `totalType6`
				, $func(IF(tg.`tgtid`='2003',$fld,NULL)) `totalType7`
				, $func(IF(tg.`tgtid`='2004',$fld,NULL)) `totalType8`
				, $func(IF(tg.`tgtid`='2005',$fld,NULL)) `totalType9`
				, $func($fld) `totalTarget`
				FROM %project_area% a
					LEFT JOIN %project_fund% f USING(`areaid`)
					LEFT JOIN %db_org% o USING(`orgid`)
					LEFT JOIN %topic% t ON t.`type`='project' AND t.`orgid`=f.`orgid`
					LEFT JOIN %project% p USING(`tpid`)
					LEFT JOIN %project_target% tg USING(`tpid`)
				%WHERE%
				GROUP BY `label`
				ORDER BY CONVERT(`label` USING tis620) ASC
				;
				-- {sum:\"totalType1,totalType2,totalType3,totalType4,totalType5,totalType6,totalType7,totalType8,totalType9,totalTarget\"}
				";


	$dbs=mydb::select($stmt);
	//$ret.=mydb()->_query.'<br />';


	$tables = new Table();
	$tables->thead=array('no'=>'ลำดับ','พื้นที่','amt -type1'=>'กลุ่มที่ 1','amt -type2'=>'กลุ่มที่ 2','amt -type3'=>'กลุ่มที่ 3','amt -type4'=>'กลุ่มที่ 4','amt -type5'=>'กลุ่มที่ 5','amt -type6'=>'กลุ่มที่ 6','amt -type7'=>'กลุ่มที่ 7','amt -type8'=>'กลุ่มที่ 8','amt -type9'=>'กลุ่มที่ 9','amt -total'=>'รวม');

	$chartTable = new Table();
	foreach ($dbs->items as $rs) {
		$balance=$rs->totalOpenBalance+$rs->totalRcv+$rs->totalRet-$rs->totalPaid;
		$totalAll+=$total;

		$tables->rows[]=array(
			++$i,
			$rs->label,
			number_format($rs->totalType1,$digit),
			number_format($rs->totalType2,$digit),
			number_format($rs->totalType3,$digit),
			number_format($rs->totalType4,$digit),
			number_format($rs->totalType5,$digit),
			number_format($rs->totalType6,$digit),
			number_format($rs->totalType7,$digit),
			number_format($rs->totalType8,$digit),
			number_format($rs->totalType9,$digit),
			number_format($rs->totalTarget,$digit),
		);

		$chartTable->rows[]=array(
			'string:label'=>$rs->label,
			'number:กลุ่มที่ 1'=>number_format($rs->totalType1,$digit),
			'string:กลุ่มที่ 1:role'=>$rs->totalType1>=$numberToShow?number_format($rs->totalType1,$digit):'',
			'number:กลุ่มที่ 2'=>number_format($rs->totalType2,$digit),
			'string:กลุ่มที่ 2:role'=>$rs->totalType2>=$numberToShow?number_format($rs->totalType2,$digit):'',
			'number:กลุ่มที่ 3'=>number_format($rs->totalType3,$digit),
			'string:กลุ่มที่ 3:role'=>$rs->totalType3>=$numberToShow?number_format($rs->totalType3,$digit):'',
			'number:กลุ่มที่ 4'=>number_format($rs->totalType4,$digit),
			'string:กลุ่มที่ 4:role'=>$rs->totalType4>=$numberToShow?number_format($rs->totalType4,$digit):'',
			'number:กลุ่มที่ 5'=>number_format($rs->totalType5,$digit),
			'string:กลุ่มที่ 5:role'=>$rs->totalType5>=$numberToShow?number_format($rs->totalType5,$digit):'',
			'number:กลุ่มที่ 6'=>number_format($rs->totalType6,$digit),
			'string:กลุ่มที่ 6:role'=>$rs->totalType6>=$numberToShow?number_format($rs->totalType6,$digit):'',
			'number:กลุ่มที่ 7'=>number_format($rs->totalType7,$digit),
			'string:กลุ่มที่ 7:role'=>$rs->totalType7>=$numberToShow?number_format($rs->totalType7,$digit):'',
			'number:กลุ่มที่ 8'=>number_format($rs->totalType8,$digit),
			'string:กลุ่มที่ 8:role'=>$rs->totalType8>=$numberToShow?number_format($rs->totalType8,$digit):'',
			'number:กลุ่มที่ 9'=>number_format($rs->totalType9,$digit),
			'string:กลุ่มที่ 9:role'=>$rs->totalType9>=$numberToShow?number_format($rs->totalType9,$digit):'',
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
		number_format($dbs->sum->totalType7),
		number_format($dbs->sum->totalType8),
		number_format($dbs->sum->totalType9),
		number_format($dbs->sum->totalTarget),
	);

	$chartPie = new Table();
	$chartPie->rows[]=array('string:label'=>'กลุ่มที่ 1 '.$supportTypeNameList[1001],'number:1'=>$dbs->sum->totalType1);
	$chartPie->rows[]=array('string:label'=>'กลุ่มที่ 2 '.$supportTypeNameList[1002],'number:2'=>$dbs->sum->totalType2);
	$chartPie->rows[]=array('string:label'=>'กลุ่มที่ 3 '.$supportTypeNameList[1003],'number:3'=>$dbs->sum->totalType3);
	$chartPie->rows[]=array('string:label'=>'กลุ่มที่ 4 '.$supportTypeNameList[1004],'number:4'=>$dbs->sum->totalType4);
	$chartPie->rows[]=array('string:label'=>'กลุ่มที่ 5 '.$supportTypeNameList[2001],'number:5'=>$dbs->sum->totalType5);
	$chartPie->rows[]=array('string:label'=>'กลุ่มที่ 6 '.$supportTypeNameList[2002],'number:6'=>$dbs->sum->totalType6);
	$chartPie->rows[]=array('string:label'=>'กลุ่มที่ 7 '.$supportTypeNameList[2003],'number:7'=>$dbs->sum->totalType7);
	$chartPie->rows[]=array('string:label'=>'กลุ่มที่ 8 '.$supportTypeNameList[2004],'number:8'=>$dbs->sum->totalType8);
	$chartPie->rows[]=array('string:label'=>'กลุ่มที่ 9 '.$supportTypeNameList[2005],'number:9'=>$dbs->sum->totalType9);

	$ret.='<div id="fund-type" class="sg-chart -type" data-chart-type="pie"><h3>แผนภูมิแสดงจำนวนแผนงาน/โครงการ/กิจกรรมของกองทุนฯ เปรียบเทียบกับกลุ่มเป้าหมาย</h3>'.$chartPie->build().'</div>'._NL;

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

	$ret.='<div id="fund-join" class="sg-chart -join" data-chart-type="col" data-options=\''.json_encode($options).'\'><h3>แผนภูมิแสดงจำนวนแผนงาน/โครงการ/กิจกรรมของกองทุนฯ เปรียบเทียบกับกลุ่มเป้าหมาย</h3>'.$chartTable->build().'</div>'._NL;
	//$ret.=$chartTable->build();

	$ret.=$tables->build();
	//$ret.='<p>หมายเหตุ: คลิกที่ชื่อเขตเพื่อดูรายละเอียด</p>';

	$ret.='<p><strong>นิยามศัพท์</strong><br />';
	$ret.='<ul>';
	$ret.='<li>กลุ่มที่ 1 '.$supportTypeNameList[1001].'</li>';
	$ret.='<li>กลุ่มที่ 2 '.$supportTypeNameList[1002].'</li>';
	$ret.='<li>กลุ่มที่ 3 '.$supportTypeNameList[1003].'</li>';
	$ret.='<li>กลุ่มที่ 4 '.$supportTypeNameList[1004].'</li>';
	$ret.='<li>กลุ่มที่ 5 '.$supportTypeNameList[2001].'</li>';
	$ret.='<li>กลุ่มที่ 6 '.$supportTypeNameList[2002].'</li>';
	$ret.='<li>กลุ่มที่ 7 '.$supportTypeNameList[2003].'</li>';
	$ret.='<li>กลุ่มที่ 8 '.$supportTypeNameList[2004].'</li>';
	$ret.='<li>กลุ่มที่ 9 '.$supportTypeNameList[2005].'</li>';
	$ret.='</ul>';
	$ret.='</p>';

	//$ret.=print_o($dbs,'$dbs');

	head('googlegraph','<script type="text/javascript" src="https
		://www.gstatic.com/charts/loader.js"></script>');
	$ret.='<style type="text/css">
	.sg-chart {height:400px; overflow:hidden;}
	.sg-chart.-join {height:600px;}
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