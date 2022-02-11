<?php
/**
* Project situation
*
* @param Object $self
* @return String
*/
function project_situation_weight_chart($self) {
	project_model::set_toolbar($self,'สถานการณ์ภาวะโภชนาการนักเรียน');

	$situation=SG\getFirst(post('situation'),1);
	$zone=post('zone');
	$province=post('province');
	$year=SG\getFirst(post('year'),2015);
	list($term,$period)=explode(':',SG\getFirst(post('term'),'1:1'));

	$order=SG\getFirst(post('order'),'t.`tpid`');

	$percentDigit=2;
	$maxStudent=0;
	$classLevelList=array(13=>'อนุบาล',23=>'ประถมศึกษาตอนต้น',26=>'ประถมศึกษาตอนปลาย',33=>'มัธยมศึกษา');
	$situationList=array(1=>'ภาวะเตี้ย',2=>'ภาวะผอม',3=>'ภาวะอ้วน',4=>'ภาวะอ้วน+เริ่มอ้วน');

	$ui=new ui();

	$zoneList=cfg('zones');
	$formText.='<form id="condition" method="get" action="'.url('project/situation/weight/chart').'">';
	$formText.='<select class="form-select" name="situation">';
	foreach ($situationList as $k => $v) {
		$formText.='<option value="'.$k.'"'.($k==$situation?' selected="selected"':'').'>'.$v.'</option>';
	}
	$formText.='</select> ';
	if ($zoneList) {
		$formText.='<select name="zone" class="form-select"><option value="">ทุกภาค</option>';
		foreach ($zoneList as $zoneKey => $zoneItem) {
			$formText.='<option value="'.$zoneKey.'"'.($zoneKey==$zone?' selected="selected"':'').'>'.$zoneItem['name'].'</option>';
		}
		$formText.='</select> ';
	}

	$stmt='SELECT DISTINCT `changwat`, `provname`
					FROM %project% p
						LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
					'.($zone?'WHERE LEFT(p.`changwat`,1) IN ('.$zoneList[$zone]['zoneid'].')':'').'
					HAVING `provname`!=""
					ORDER BY CONVERT(`provname` USING tis620) ASC';
	$dbs=mydb::select($stmt);
	$formText.='<select id="province" class="form-select" name="province"><option value="">ทุกจังหวัด</option>';
	foreach ($dbs->items as $rs) {
		$formText.='<option value="'.$rs->changwat.'"'.($rs->changwat==$province?' selected="selected"':'').'>'.$rs->provname.'</option>';
	}
	$formText.='</select>&nbsp;';

	// เลือกปีการศึกษา
	$dbs=mydb::select('SELECT DISTINCT `detail1` `pryear` FROM %project_tr% WHERE `formid`="weight" AND `part`="title" HAVING `pryear` ORDER BY `pryear` ASC');
	$formText.='<select name="year" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;">';
	foreach ($dbs->items as $rs) {
		$formText.='<option value="'.$rs->pryear.'"'.($rs->pryear==$year?' selected="selected"':'').'>ปีการศึกษา '.($rs->pryear+543).'</option>';
	}
	$formText.='</select>&nbsp;';

	// เลือกภาคการศึกษา
	$formText.='<select name="term" class="form-select">';
	for ($i=1;$i<=2;$i++) {
		for ($j=1;$j<=2;$j++) {
			$termperiod=$i.':'.$j;
			$formText.='<option value="'.$termperiod.'"'.($termperiod==$term.':'.$period?' selected="selected"':'').'>ภาคการศึกษา '.$i.' ครั้งที่ '.$j.'</option>';
		}
	}
	$formText.='</select>&nbsp;';

	$formText.='</form>';
	$ui->add($formText);
	$ret.='<div class="reportbar">'.$ui->build('ul').'</div>';









	//$ret.='<nav class="toolbar nav -graphtype"><ul class="ui-action"><li><a class="-active" href="#col">กราฟแท่ง</a></li><!-- <li><a href="#bar">กราฟแถบ</a></li><li><a href="#line">กราฟเส้น</a></li> --><li><a href="#pie">กราฟวงกลม</a></li></ul></nav>';

	$ret.='<h2>สถานการณ์ภาวะโภชนาการนักเรียน (จำแนกตามช่วงชั้น)</h2>';

	$where=array();
	if ($situation==1) {
		$where=sg::add_condition($where,'p.`prtype`="โครงการ" AND tr.`formid`="height" AND tr.`part`="height"');
	} else {
		$where=sg::add_condition($where,'p.`prtype`="โครงการ" AND tr.`formid`="weight" AND tr.`part`="weight"');
	}
	
	if ($zone) {
		$where=sg::add_condition($where,'LEFT(t.`changwat`,1) IN (:zone)','zone','SET:'.$zoneList[$zone]['zoneid']);
		$text['zone']=' '.$zoneList[$zone]['name'];
	} else $text['zone']=' ทุกภาค';
	if ($province) {
		unset($text['zone']);
		if (cfg('project.multiplearea')) {
			$where=sg::add_condition($where,'a.changwat=:changwat ','changwat',$province);
		} else {
			$where=sg::add_condition($where,'p.changwat=:changwat ','changwat',$province);
		}
		$text[]=' จังหวัด'.mydb::select('SELECT provname FROM %co_province% WHERE provid=:provid LIMIT 1',':provid',$province)->provname;
	}
	if ($year) {
		$where=sg::add_condition($where,'ti.`detail1`=:year ','year',$year);
		$text[]=' ปีการศึกษา '.($year+543);
	}


	if ($situation==1) {
		$stmt='SELECT
						  tr.`trid`, tr.`tpid`, tr.`sorder`
						, ti.`detail1` `year`, ti.`detail2` `term`, ti.`period` `times`
						, CASE
								WHEN tr.`sorder` IN (11,12,13) THEN 1
								WHEN tr.`sorder` IN (21,22,23) THEN 21
								WHEN tr.`sorder` IN (24,25,26) THEN 24
								WHEN tr.`sorder` IN (31,32,33) THEN 3
							END AS `classLevelNo`
						, CASE
								WHEN tr.`sorder` IN (11,12,13) THEN "อนุบาล"
								WHEN tr.`sorder` IN (21,22,23) THEN "ประถมศึกษาตอนต้น"
								WHEN tr.`sorder` IN (24,25,26) THEN "ประถมศึกษาตอนปลาย"
								WHEN tr.`sorder` IN (31,32,33) THEN "มัธยมศึกษา"
							END AS `classLevelName`
					--	, qt.`question`
					--	, qt.`qtgroup`
					--	, qt.`qtno`
						, COUNT(DISTINCT tr.`tpid`) totalSchool
						, SUM(tr.`num5`) `short`
						, SUM(tr.`num6`) `rathershort`
						, SUM(tr.`num7`) `standard`
						, SUM(tr.`num8`) `ratherheight`
						, SUM(tr.`num9`) `veryheight`
						, SUM(tr.`num1`) `total`
						, SUM(tr.`num2`) `getweight`
						FROM %project_tr% tr
							LEFT JOIN %project% p USING(`tpid`)
							LEFT JOIN %topic% t USING(`tpid`)
							LEFT JOIN %project_tr% ti ON ti.`trid`=tr.`parent`
						--	LEFT JOIN %qt% qt ON qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
					'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
						GROUP BY `classLevelNo`,`year`,`term`,`times`
						ORDER BY `classLevelNo`,`year` ASC,`term`,`times`';
		$dbs=mydb::select($stmt,$where['value']);
	} else {
		$stmt='SELECT
						  tr.`trid`, tr.`tpid`, tr.`sorder`
						, ti.`detail1` `year`, ti.`detail2` `term`, ti.`period` `times`
						, CASE
								WHEN tr.`sorder` IN (11,12,13) THEN 1
								WHEN tr.`sorder` IN (21,22,23) THEN 21
								WHEN tr.`sorder` IN (24,25,26) THEN 24
								WHEN tr.`sorder` IN (31,32,33) THEN 3
							END AS `classLevelNo`
						, CASE
								WHEN tr.`sorder` IN (11,12,13) THEN "อนุบาล"
								WHEN tr.`sorder` IN (21,22,23) THEN "ประถมศึกษาตอนต้น"
								WHEN tr.`sorder` IN (24,25,26) THEN "ประถมศึกษาตอนปลาย"
								WHEN tr.`sorder` IN (31,32,33) THEN "มัธยมศึกษา"
							END AS `classLevelName`
					--	, qt.`question`
					--	, qt.`qtgroup`
					--	, qt.`qtno`
						, COUNT(DISTINCT tr.`tpid`) totalSchool
						, SUM(tr.`num5`) `thin`
						, SUM(tr.`num6`) `ratherthin`
						, SUM(tr.`num7`) `willowy`
						, SUM(tr.`num8`) `plump`
						, SUM(tr.`num9`) `gettingfat`
						, SUM(tr.`num10`) `fat`
						, SUM(tr.`num1`) `total`
						, SUM(tr.`num2`) `getweight`
						FROM %project_tr% tr
							LEFT JOIN %project% p USING(`tpid`)
							LEFT JOIN %topic% t USING(`tpid`)
							LEFT JOIN %project_tr% ti ON ti.`trid`=tr.`parent`
						--	LEFT JOIN %qt% qt ON qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
					'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
						GROUP BY `classLevelName`,`year`,`term`,`times`
						ORDER BY `classLevelNo`,`year` ASC,`term`,`times`';
		$dbs=mydb::select($stmt,$where['value']);
	}

	$chartYear=new Table('item -center');
	$chartYear->thead=array('title'=>'ระดับชั้น');
	//'อนุบาล','ประถมศึกษาตอนต้น','ประถมศึกษาตอนปลาย','มัธยมศึกษา','ภาพรวมทุกชั้น');
	foreach ($dbs->items as $rs) {
		$xAxis=($rs->year+543).' '.$rs->term.'/'.$rs->times;

		if ($situation==1) $situationValue=$rs->short;
		else if ($situation==2) $situationValue=$rs->thin;
		else if ($situation==3) $situationValue=$rs->fat;
		else if ($situation==4) $situationValue=$rs->gettingfat+$rs->fat;

		$percent=number_format($situationValue*100/$rs->getweight,2);

		$chartYear->thead[$xAxis]=$xAxis.'(%)';
		$chartYear->rows['อนุบาล']['string:0']='อนุบาล';
		$chartYear->rows['ประถมศึกษา(ต้น)']['string:0']='ประถมศึกษา(ต้น)';
		$chartYear->rows['ประถมศึกษา(ปลาย)']['string:0']='ประถมศึกษา(ปลาย)';
		$chartYear->rows['มัธยมศึกษา(ต้น)']['string:0']='มัธยมศึกษา(ต้น)';
		$chartYear->rows['ประถมศึกษา(ปลาย)']['string:0']='ประถมศึกษา(ปลาย)';
		$chartYear->rows['ภาพรวมทุกชั้น']['string:0']='ภาพรวมทุกชั้น';
		if ($rs->classLevelNo==1) {
			$chartYear->rows['อนุบาล']['number:'.$xAxis]=$percent;
		} else if ($rs->classLevelNo==21) {
			$chartYear->rows['ประถมศึกษา(ต้น)']['number:'.$xAxis]=$percent;
		} else if ($rs->classLevelNo==24) {
			$chartYear->rows['ประถมศึกษา(ปลาย)']['number:'.$xAxis]=$percent;
		} else if ($rs->classLevelNo==3) {
			$chartYear->rows['มัธยมศึกษา(ต้น)']['number:'.$xAxis]=$percent;
		}
		//$chartYear->rows['ภาพรวมทุกชั้น']['number:'.$xAxis]+=$rs->fat;
		$totalPercent[$xAxis]+=$situationValue;
		$totalGetweight[$xAxis]+=$rs->getweight;
	}
	foreach ($totalPercent as $key => $value) {
		$chartYear->rows['ภาพรวมทุกชั้น']['number:'.$key]=number_format($value*100/$totalGetweight[$key],2);
	}

	$ret.='<div id="year-fat" class="sg-chart -fat" data-chart-type="col"><h3>'.$situationList[$situation].implode(' ',$text).'</h3>'.$chartYear->build().'</div>';

	head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');


	//$ret.=$chartYear->build();
	//$ret.=print_o($totalPercent,'$totalPercent');
	//$ret.=print_o($totalGetweight,'$totalGetweight');
	//$ret.=print_o($chartYear,'$chartYear');
	//$ret.=print_o($dbs,'$dbs');













	if ($term) {
		$where=sg::add_condition($where,'ti.`detail2`=:term ','term',$term);
		$text[]=' ภาคการศึกษา '.($term);
	}
	
	if ($period) {
		$where=sg::add_condition($where,'ti.`period`=:period ','period',$period);
		$text[]='ครั้งที่ '.$period;
	}

	$stmt='SELECT
					tr.`trid`, tr.`tpid`, tr.`sorder`, tr.`detail1` `year`, tr.`detail2` `term`, tr.`period`,
					tr.`detail3` `area`, tr.`detail4` `postby`, tr.`date1` `dateinput`
					, CASE
							WHEN tr.`sorder` IN (11,12,13) THEN 1
							WHEN tr.`sorder` IN (21,22,23) THEN 21
							WHEN tr.`sorder` IN (24,25,26) THEN 24
							WHEN tr.`sorder` IN (31,32,33) THEN 3
						END AS `classLevelNo`
					, CASE
							WHEN tr.`sorder` IN (11,12,13) THEN "อนุบาล"
							WHEN tr.`sorder` IN (21,22,23) THEN "ประถมศึกษาตอนต้น"
							WHEN tr.`sorder` IN (24,25,26) THEN "ประถมศึกษาตอนปลาย"
							WHEN tr.`sorder` IN (31,32,33) THEN "มัธยมศึกษา"
						END AS `classLevelName`
					, qt.`question`
					, qt.`qtgroup`
					, qt.`qtno`
					, COUNT(DISTINCT tr.`tpid`) totalSchool
					, SUM(tr.`num5`) `thin`
					, SUM(tr.`num6`) `ratherthin`
					, SUM(tr.`num7`) `willowy`
					, SUM(tr.`num8`) `plump`
					, SUM(tr.`num9`) `gettingfat`
					, SUM(tr.`num10`) `fat`
					, SUM(tr.`num1`) `total`
					, SUM(tr.`num2`) `getweight`
					FROM %project_tr% tr
						LEFT JOIN %project% p USING(`tpid`)
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %project_tr% ti ON ti.`trid`=tr.`parent`
						LEFT JOIN %qt% qt ON qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
				'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					GROUP BY tr.`sorder`
					ORDER BY tr.`sorder` ASC';
	$dbs=mydb::select($stmt,$where['value']);
	//$ret.=print_o($dbs,'$dbs');

	$whereAll=array();
	$whereAll=sg::add_condition($whereAll,'p.`prtype`="โครงการ" AND tr.`formid`="weight" AND tr.`part`="weight"');
	if ($year) {
		$whereAll=sg::add_condition($whereAll,'ti.`detail1`=:year ','year',$year);
	}
	if ($term) {
		$whereAll=sg::add_condition($whereAll,'ti.`detail2`=:term ','term',$term);
	}
	//$ret.=print_o($whereAll,'$whereAll');


	$stmt='SELECT
					tr.`trid`, tr.`tpid`
					-- , LEFT(tr.`sorder`,1) classLevel
					, CASE
							WHEN tr.`sorder` IN (11,12,13) THEN 1
							WHEN tr.`sorder` IN (21,22,23) THEN 21
							WHEN tr.`sorder` IN (24,25,26) THEN 24
							WHEN tr.`sorder` IN (31,32,33) THEN 3
						END AS `classLevelNo`
					, CASE
							WHEN tr.`sorder` IN (11,12,13) THEN "อนุบาล"
							WHEN tr.`sorder` IN (21,22,23) THEN "ประถมศึกษาตอนต้น"
							WHEN tr.`sorder` IN (24,25,26) THEN "ประถมศึกษาตอนปลาย"
							WHEN tr.`sorder` IN (31,32,33) THEN "มัธยมศึกษา"
						END AS `classLevelName`
					, tr.`detail1` `year`, tr.`detail2` `term`, tr.`period`,
					tr.`detail3` `area`, tr.`detail4` `postby`, tr.`date1` `dateinput`
					, qt.`question`
					, qt.`qtgroup`
					, qt.`qtno`
					, COUNT(DISTINCT tr.`tpid`) totalSchool
					, SUM(tr.`num5`) `thin`
					, SUM(tr.`num6`) `ratherthin`
					, SUM(tr.`num7`) `willowy`
					, SUM(tr.`num8`) `plump`
					, SUM(tr.`num9`) `gettingfat`
					, SUM(tr.`num10`) `fat`
					, SUM(tr.`num1`) `total`
					, SUM(tr.`num2`) `getweight`
					FROM %project_tr% tr
						LEFT JOIN %project% p USING(`tpid`)
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %project_tr% ti ON ti.`trid`=tr.`parent`
						LEFT JOIN %qt% qt ON qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
				'.($whereAll?'WHERE '.implode(' AND ',$whereAll['cond']):'').'
					-- GROUP BY LEFT(tr.`sorder`,1)
					GROUP BY `classLevelName`
					ORDER BY tr.`sorder` ASC';
	$dbsAll=mydb::select($stmt,$whereAll['value']);

	$totalCountry=$getweightCountry=$thinCountry=$ratherthinCountry=$willowyCountry=$plumpCountry=$gettingfatCountry=$fatCountry=0;
	foreach ($dbsAll->items as $rs) {
		$dbsAll->keys[$rs->classLevelName]=$rs;
		$totalCountry+=$rs->total;
		$getweightCountry+=$rs->getweight;
		$thinCountry+=$rs->thin;
		$ratherthinCountry+=$rs->ratherthin;
		$willowyCountry+=$rs->willowy;
		$plumpCountry+=$rs->plump;
		$gettingfatCountry+=$rs->gettingfat;
		$fatCountry+=$rs->fat;
	}
	//$ret.=print_o($dbsAll,'$dbsAll');


	// Graph
	$graph=array(
						'wc1'=>array(),'wc21'=>array(),'wc24'=>array(),'wc3'=>array(),'wcall'=>array(),
						'wa1'=>array(),'wa21'=>array(),'wc24'=>array(),'wa3'=>array(),'waall'=>array(),
						);
	$tables = new Table();
	$tables->thead=array('','อนุบาล','ประถมศึกษาตอนต้น','ประถมศึกษาตอนปลาย','มัธยมศึกษา','รวม');
	$tables->rows[]=array(
										'รวมช่วงชั้น',
										'<div id="graph-wc1" class="graph"></div>',
										'<div id="graph-wc21" class="graph"></div>',
										'<div id="graph-wc24" class="graph"></div>',
										'<div id="graph-wc3" class="graph"></div>',
										'<div id="graph-wcall" class="graph"></div>',
										);
	$tables->rows[]=array(
										'รวมช่วงชั้นทุกภาค',
										'<div id="graph-wa1" class="graph"></div>',
										'<div id="graph-wa21" class="graph"></div>',
										'<div id="graph-wa24" class="graph"></div>',
										'<div id="graph-wa3" class="graph"></div>',
										'<div id="graph-waall" class="graph"></div>',
										);
	//$ret.=$tables->build();





	// Table
	$tables=new table('item -weightform');
	$tables->caption='สถานการณ์ภาวะโภชนาการนักเรียน - น้ำหนักตามเกณฑ์ส่วนสูง';
	$tables->colgroup=array('','amt student'=>'','amt bad'=>'','amt badpercent'=>'','amt fair'=>'','amt fairpercent'=>'','amt good'=>'','amt goodpercent'=>'');
	$tables->thead='<tr><th rowspan="2">ชั้น</th><th rowspan="2">จำนวนโรงเรียน</th><th>จำนวนนักเรียนทั้งหมด</th><th colspan="2">จำนวนนักเรียนที่ชั่งน้ำหนัก/วัดส่วนสูง</th><th colspan="2">ผอม</th><th colspan="2">ค่อนข้างผอม</th><th colspan="2">สมส่วน</th><th colspan="2">ท้วม</th><th colspan="2">เริ่มอ้วน</th><th colspan="2">อ้วน</th><th colspan="2">เริ่มอ้วน+อ้วน</th></tr><tr><th>คน</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th></tr>';
	$rs->amt=rand(50,100);
	foreach ($dbs->items as $rs) {
		if (in_array($rs->qtno,array(21,24,31))) $tables->rows[]='<header>';
		if (in_array($rs->qtno,array(11,21,24,31))) {
			$classLevelName=$rs->classLevelName;
			$tables->rows[]=array('<th colspan="19"><h3>'.$classLevelName.'</h3></th>');
			$subWeightTotal=$subWeightGetweight=$subWeightThin=$subWeightRatherthin=$subWeightWillowy=$subWeightPlump=$subWeightGettingFat=$subWeightFat=0;
		}

		$tables->rows[]=array(
											$rs->question,
											$rs->totalSchool,
											number_format($rs->total),
											number_format($rs->getweight),
											round($rs->getweight*100/$rs->total,$percentDigit).'%',
											number_format($rs->thin),
											round($rs->thin*100/$rs->getweight,$percentDigit).'%',
											number_format($rs->ratherthin),
											round($rs->ratherthin*100/$rs->getweight,$percentDigit).'%',
											number_format($rs->willowy),
											round($rs->willowy*100/$rs->getweight,$percentDigit).'%',
											number_format($rs->plump),
											round($rs->plump*100/$rs->getweight,$percentDigit).'%',
											number_format($rs->gettingfat),
											round($rs->gettingfat*100/$rs->getweight,$percentDigit).'%',
											number_format($rs->fat),
											round($rs->fat*100/$rs->getweight,$percentDigit).'%',
											number_format($rs->gettingfat+$rs->fat),
											round(($rs->gettingfat+$rs->fat)*100/$rs->getweight,$percentDigit).'%',
											);
		$subWeightTotal+=$rs->total;
		$subWeightGetweight+=$rs->getweight;
		$subWeightThin+=$rs->thin;
		$subWeightRatherthin+=$rs->ratherthin;
		$subWeightWillowy+=$rs->willowy;
		$subWeightPlump+=$rs->plump;
		$subWeightGettingFat+=$rs->gettingfat;
		$subWeightFat+=$rs->fat;

		if (in_array($rs->qtno,array(13,23,26,33))) {
			$classLevelNo=$rs->classLevelNo;
			$classLevel=$rs->classLevelName; //substr($rs->qtno,0,1);
			//$ret.=$rs->qtno.' | '.substr($rs->qtno,0,1).'<br />';
			$tables->rows[]=array(
												'รวมชั้น'.$classLevelName,
												'',
												number_format($subWeightTotal),
												number_format($subWeightGetweight),
												round($subWeightGetweight*100/$subWeightTotal,$percentDigit).'%',
												number_format($subWeightThin),
												round($subWeightThin*100/$subWeightGetweight,$percentDigit).'%',
												number_format($subWeightRatherthin),
												round($subWeightRatherthin*100/$subWeightGetweight,$percentDigit).'%',
												number_format($subWeightWillowy),
												round($subWeightWillowy*100/$subWeightGetweight,$percentDigit).'%',
												number_format($subWeightPlump),
												round($subWeightPlump*100/$subWeightGetweight,$percentDigit).'%',
												number_format($subWeightGettingFat),
												round($subWeightGettingFat*100/$subWeightGetweight,$percentDigit).'%',
												number_format($subWeightFat),
												round($subWeightFat*100/$subWeightGetweight,$percentDigit).'%',
												number_format($subWeightGettingFat+$subWeightFat),
												round(($subWeightGettingFat+$subWeightFat)*100/$subWeightGetweight,$percentDigit).'%',
												'config'=>array('class'=>'subfooter')
												);
			$graph['wc'.$classLevelNo]=array(
													//array('น้ำหนักตามเกณฑ์ส่วนสูง','จำนวน'),
													array('ผอม',round($subWeightThin)),
													array('ค่อนข้างผอม',round($subWeightRatherthin)),
													array('สมส่วน',round($subWeightWillowy)),
													array('ท้วม',round($subWeightPlump)),
													array('เริ่มอ้วน',round($subWeightGettingFat)),
													array('อ้วน',round($subWeightFat)),
													);
			$graph['wcall']=array(
													array('ผอม',$graph['wcall'][0][1]+intval($subWeightThin)),
													array('ค่อนข้างผอม',$graph['wcall'][1][1]+intval($subWeightRatherthin)),
													array('สมส่วน',$graph['wcall'][2][1]+intval($subWeightWillowy)),
													array('ท้วม',$graph['wcall'][3][1]+intval($subWeightPlump)),
													array('เริ่มอ้วน',$graph['wcall'][4][1]+intval($subWeightGettingFat)),
													array('อ้วน',$graph['wcall'][5][1]+intval($subWeightFat)),
													);
			//$ret.=print_o($graph['classlevel']['all'],'$graph[classlevel][all]');

			$allData=$dbsAll->keys[$classLevel];
			//$ret.=print_o($allData,'$allData');
			$tables->rows[]=array(
												'รวมชั้น'.$classLevelName.'ทุกภาค',
												'',
												number_format($allData->total),
												number_format($allData->getweight),
												round($allData->getweight*100/$allData->total,$percentDigit).'%',
												number_format($allData->thin),
												round($allData->thin*100/$allData->getweight,$percentDigit).'%',
												number_format($allData->ratherthin),
												round($allData->ratherthin*100/$allData->getweight,$percentDigit).'%',
												number_format($allData->willowy),
												round($allData->willowy*100/$allData->getweight,$percentDigit).'%',
												number_format($allData->plump),
												round($allData->plump*100/$allData->getweight,$percentDigit).'%',
												number_format($allData->gettingfat),
												round($allData->gettingfat*100/$allData->getweight,$percentDigit).'%',
												number_format($allData->fat),
												round($allData->fat*100/$allData->getweight,$percentDigit).'%',
												number_format($allData->gettingfat+$allData->fat),
												round(($allData->gettingfat+$allData->fat)*100/$allData->getweight,$percentDigit).'%',
												'config'=>array('class'=>'subfooter -sub2')
												);
		}

		$weightTotal+=$rs->total;
		$weightGetweight+=$rs->getweight;
		$weightThin+=$rs->thin;
		$weightRatherthin+=$rs->ratherthin;
		$weightWillowy+=$rs->willowy;
		$weightPlump+=$rs->plump;
		$weightGettingfat+=$rs->gettingfat;
		$weightFat+=$rs->fat;
		if ($weightWillowy>$maxStudent) $maxStudent=$weightWillowy;
	}
	$tables->rows[]=array(
										'ภาพรวมทุกชั้น',
										'',
										number_format($weightTotal),
										number_format($weightGetweight),
										round($weightGetweight*100/$weightTotal,$percentDigit).'%',
										number_format($weightThin),
										round($weightThin*100/$weightGetweight,$percentDigit).'%',
										number_format($weightRatherthin),
										round($weightRatherthin*100/$weightGetweight,$percentDigit).'%',
										number_format($weightWillowy),
										round($weightWillowy*100/$weightGetweight,$percentDigit).'%',
										number_format($weightPlump),
										round($weightPlump*100/$weightGetweight,$percentDigit).'%',
										number_format($weightGettingfat),
										round($weightGettingfat*100/$weightGetweight,$percentDigit).'%',
										number_format($weightFat),
										round($weightFat*100/$weightGetweight,$percentDigit).'%',
										number_format($weightGettingfat+$weightFat),
										round(($weightGettingfat+$weightFat)*100/$weightGetweight,$percentDigit).'%',
										'config'=>array('class'=>'subfooter -sub3')
										);
	$tables->rows[]=array(
										'ภาพรวมทุกภาค',
										'',
										number_format($totalCountry),
										number_format($getweightCountry),
										round($getweightCountry*100/$totalCountry,$percentDigit).'%',
										number_format($thinCountry),
										round($thinCountry*100/$getweightCountry,$percentDigit).'%',
										number_format($ratherthinCountry),
										round($ratherthinCountry*100/$getweightCountry,$percentDigit).'%',
										number_format($willowyCountry),
										round($willowyCountry*100/$getweightCountry,$percentDigit).'%',
										number_format($plumpCountry),
										round($plumpCountry*100/$getweightCountry,$percentDigit).'%',
										number_format($gettingfatCountry),
										round($gettingfatCountry*100/$getweightCountry,$percentDigit).'%',
										number_format($fatCountry),
										round($fatCountry*100/$getweightCountry,$percentDigit).'%',
										number_format($gettingfatCountry+$fatCountry),
										round(($gettingfatCountry+$fatCountry)*100/$getweightCountry,$percentDigit).'%',
										'config'=>array('class'=>'subfooter -sub3')
										);

	//$ret.=print_o($graph,'$graph');
	//$ret.=print_o($graph['classlevel'],'$graph[classlevel]');
	$maxStudent=ceil($maxStudent);
	//$ret.='Max='.$maxStudent;

	$ret.=$tables->build();

	foreach ($dbsAll->items as $rs) {
		$graph['wa'.$rs->classLevelNo]=array(
												array('ผอม',round($rs->thin)),
												array('ค่อนข้างผอม',round($rs->ratherthin)),
												array('สมส่วน',round($rs->willowy)),
												array('ท้วม',round($rs->plump)),
												array('เริ่มอ้วน',round($rs->gettingfat)),
												array('อ้วน',round($rs->fat)),
												);
		$graph['waall'][0]=array('ผอม',$graph['waall'][0][1]+$rs->thin);
		$graph['waall'][1]=array('ค่อนข้างผอม',$graph['waall'][1][1]+$rs->ratherthin);
		$graph['waall'][2]=array('สมส่วน',$graph['waall'][2][1]+$rs->willowy);
		$graph['waall'][3]=array('ท้วม',$graph['waall'][3][1]+$rs->plump);
		$graph['waall'][4]=array('เริ่มอ้วน',$graph['waall'][4][1]+$rs->gettingfat);
		$graph['waall'][5]=array('อ้วน',$graph['waall'][5][1]+$rs->fat);
	}
	//$ret.=print_o($dbsAll,'$dbsAll');














	$where=array();
	$where=sg::add_condition($where,'p.`prtype`="โครงการ" AND tr.`formid`="height" AND tr.`part`="height"');
	
	if ($zone) {
		$where=sg::add_condition($where,'LEFT(t.`changwat`,1) IN (:zone)','zone','SET:'.$zoneList[$zone]['zoneid']);
	}
	if ($province) {
		if (cfg('project.multiplearea')) {
			$where=sg::add_condition($where,'a.changwat=:changwat ','changwat',$province);
		} else {
			$where=sg::add_condition($where,'p.changwat=:changwat ','changwat',$province);
		}
	}
	if ($year) {
		$where=sg::add_condition($where,'ti.`detail1`=:year ','year',$year);
	}
	if ($term) {
		$where=sg::add_condition($where,'ti.`detail2`=:term ','term',$term);
	}
	if ($period) {
		$where=sg::add_condition($where,'ti.`period`=:period ','period',$period);
	}

	$stmt='SELECT
					tr.`trid`, tr.`tpid`, tr.`sorder`, tr.`detail1` `year`, tr.`detail2` `term`, tr.`period`,
					tr.`detail3` `area`, tr.`detail4` `postby`, tr.`date1` `dateinput`
					, CASE
							WHEN tr.`sorder` IN (11,12,13) THEN 1
							WHEN tr.`sorder` IN (21,22,23) THEN 21
							WHEN tr.`sorder` IN (24,25,26) THEN 24
							WHEN tr.`sorder` IN (31,32,33) THEN 3
						END AS `classLevelNo`
					, CASE
							WHEN tr.`sorder` IN (11,12,13) THEN "อนุบาล"
							WHEN tr.`sorder` IN (21,22,23) THEN "ประถมศึกษาตอนต้น"
							WHEN tr.`sorder` IN (24,25,26) THEN "ประถมศึกษาตอนปลาย"
							WHEN tr.`sorder` IN (31,32,33) THEN "มัธยมศึกษา"
						END AS `classLevelName`
					, qt.`question`
					, qt.`qtgroup`
					, qt.`qtno`
					, COUNT(DISTINCT tr.`tpid`) totalSchool
					, SUM(tr.`num5`) `short`
					, SUM(tr.`num6`) `rathershort`
					, SUM(tr.`num7`) `standard`
					, SUM(tr.`num8`) `ratherheight`
					, SUM(tr.`num9`) `veryheight`
					, SUM(tr.`num1`) `total`
					, SUM(tr.`num2`) `getheight`
					FROM %project_tr% tr
						LEFT JOIN %project% p USING(`tpid`)
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %project_tr% ti ON ti.`trid`=tr.`parent`
						LEFT JOIN %qt% qt ON qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
				'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					GROUP BY tr.`sorder`
					ORDER BY tr.`sorder` ASC';
	$dbs=mydb::select($stmt,$where['value']);
	//$ret.=print_o($dbs,'$dbs');

	$whereAll=array();
	$whereAll=sg::add_condition($whereAll,'p.`prtype`="โครงการ" AND tr.`formid`="height" AND tr.`part`="height"');
	if ($year) {
		$whereAll=sg::add_condition($whereAll,'ti.`detail1`=:year ','year',$year);
	}
	if ($term) {
		$whereAll=sg::add_condition($whereAll,'ti.`detail2`=:term ','term',$term);
	}
	if ($period) {
		$whereAll=sg::add_condition($whereAll,'ti.`period`=:period ','period',$period);
	}

	//$ret.=print_o($where,'$where');
	//$ret.=print_o($whereAll,'$whereAll');

	$stmt='SELECT
					tr.`trid`, tr.`tpid`, LEFT(tr.`sorder`,1) `classLevel`, tr.`detail1` `year`, tr.`detail2` `term`, tr.`period`,
					tr.`detail3` `area`, tr.`detail4` `postby`, tr.`date1` `dateinput`
					, CASE
							WHEN tr.`sorder` IN (11,12,13) THEN 1
							WHEN tr.`sorder` IN (21,22,23) THEN 21
							WHEN tr.`sorder` IN (24,25,26) THEN 24
							WHEN tr.`sorder` IN (31,32,33) THEN 3
						END AS `classLevelNo`
					, CASE
							WHEN tr.`sorder` IN (11,12,13) THEN "อนุบาล"
							WHEN tr.`sorder` IN (21,22,23) THEN "ประถมศึกษาตอนต้น"
							WHEN tr.`sorder` IN (24,25,26) THEN "ประถมศึกษาตอนปลาย"
							WHEN tr.`sorder` IN (31,32,33) THEN "มัธยมศึกษา"
						END AS `classLevelName`
					, qt.`question`
					, qt.`qtgroup`
					, qt.`qtno`
					, COUNT(DISTINCT tr.`tpid`) totalSchool
					, SUM(tr.`num5`) `short`
					, SUM(tr.`num6`) `rathershort`
					, SUM(tr.`num7`) `standard`
					, SUM(tr.`num8`) `ratherheight`
					, SUM(tr.`num9`) `veryheight`
					, SUM(tr.`num1`) `total`
					, SUM(tr.`num2`) `getheight`
					FROM %project_tr% tr
						LEFT JOIN %project% p USING(`tpid`)
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %project_tr% ti ON ti.`trid`=tr.`parent`
						LEFT JOIN %qt% qt ON qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
				'.($whereAll?'WHERE '.implode(' AND ',$whereAll['cond']):'').'
					GROUP BY `classLevelNo`
					ORDER BY tr.`sorder` ASC';
	$dbsAll=mydb::select($stmt,$whereAll['value']);

	$totalCountry=$getheightCountry=$shortCountry=$rathershortCountry=$standardCountry=$ratherheightCountry=$veryheightCountry=0;
	foreach ($dbsAll->items as $rs) {
		$dbsAll->keys[$rs->classLevelName]=$rs;
		$totalCountry+=$rs->total;
		$getheightCountry+=$rs->getheight;
		$shortCountry+=$rs->short;
		$rathershortCountry+=$rs->rathershort;
		$standardCountry+=$rs->standard;
		$ratherheightCountry+=$rs->ratherheight;
		$veryheightCountry+=$rs->veryheight;
	}
	//$ret.=print_o($dbsAll,'$dbsAll');

	$tables=new table('item -weightform');
	$tables->caption='สถานการณ์ภาวะโภชนาการนักเรียน - ส่วนสูงตามเกณฑ์อายุ';
	$tables->colgroup=array('','amt student'=>'','amt bad'=>'','amt badpercent'=>'','amt fair'=>'','amt fairpercent'=>'','amt good'=>'','amt goodpercent'=>'');
	$tables->thead='<tr><th rowspan="2">ชั้น</th><th rowspan="2">จำนวนโรงเรียน</th><th>จำนวนนักเรียนทั้งหมด</th><th colspan="2">จำนวนนักเรียนที่วัดส่วนสูง</th><th colspan="2">เตี้ย</th><th colspan="2">ค่อนข้างเตี้ย</th><th colspan="2">สูงตามเกณฑ์</th><th colspan="2">ค่อนข้างสูง</th><th colspan="2">สูง</th><th colspan="2"></th></tr><tr><th>คน</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th></th><th></th></tr>';
	$rs->amt=rand(50,100);
	foreach ($dbs->items as $rs) {
		if (in_array($rs->qtno,array(21,24,31))) $tables->rows[]='<header>';
		if (in_array($rs->qtno,array(11,21,24,31))) {
			$classLevelName=$rs->classLevelName;
			$tables->rows[]=array('<th colspan="17"><h3>'.$classLevelName.'</h3></th>');
			$subHeightTotal=$subHeightGetheight=$subHeightShort=$subHeightRathershort=$subHeightStandard=$subHeightRatherheight=$subHeightVeryheight=0;
		}

		$tables->rows[]=array(
											$rs->question,
											$rs->totalSchool,
											number_format($rs->total),
											number_format($rs->getheight),
											round($rs->getheight*100/$rs->total,$percentDigit).'%',
											number_format($rs->short),
											round($rs->short*100/$rs->getheight,$percentDigit).'%',
											number_format($rs->rathershort),
											round($rs->rathershort*100/$rs->getheight,$percentDigit).'%',
											number_format($rs->standard),
											round($rs->standard*100/$rs->getheight,$percentDigit).'%',
											number_format($rs->ratherheight),
											round($rs->ratherheight*100/$rs->getheight,$percentDigit).'%',
											number_format($rs->veryheight),
											round($rs->veryheight*100/$rs->getheight,$percentDigit).'%',
											);

		$subHeightTotal+=$rs->total;
		$subHeightGetheight+=$rs->getheight;
		$subHeightShort+=$rs->short;
		$subHeightRathershort+=$rs->rathershort;
		$subHeightStandard+=$rs->standard;
		$subHeightRatherheight+=$rs->ratherheight;
		$subHeightVeryheight+=$rs->veryheight;

		if (in_array($rs->qtno,array(13,23,26,33))) {
			$classLevelNo=$rs->classLevelNo;
			$classLevel=$rs->classLevelName; //substr($rs->qtno,0,1);
			$tables->rows[]=array(
												'รวมชั้น'.$classLevelName,
												'',
												number_format($subHeightTotal),
												number_format($subHeightGetheight),
												round($subHeightGetheight*100/$subHeightTotal,$percentDigit).'%',
												number_format($subHeightShort),
												round($subHeightShort*100/$subHeightGetheight,$percentDigit).'%',
												number_format($subHeightRathershort),
												round($subHeightRathershort*100/$subHeightGetheight,$percentDigit).'%',
												number_format($subHeightStandard),
												round($subHeightStandard*100/$subHeightGetheight,$percentDigit).'%',
												number_format($subHeightRatherheight),
												round($subHeightRatherheight*100/$subHeightGetheight,$percentDigit).'%',
												number_format($subHeightVeryheight),
												round($subHeightVeryheight*100/$subHeightGetheight,$percentDigit).'%',
												'','',
												'config'=>array('class'=>'subfooter')
												);
			$allData=$dbsAll->keys[$classLevel];
			$tables->rows[]=array(
												'รวมชั้น'.$classLevelName.'ทุกภาค',
												'',
												number_format($allData->total),
												number_format($allData->getheight),
												round($allData->getheight*100/$allData->total,$percentDigit).'%',
												number_format($allData->short),
												round($allData->short*100/$allData->getheight,$percentDigit).'%',
												number_format($allData->rathershort),
												round($allData->rathershort*100/$allData->getheight,$percentDigit).'%',
												number_format($allData->standard),
												round($allData->standard*100/$allData->getheight,$percentDigit).'%',
												number_format($allData->ratherheight),
												round($allData->ratherheight*100/$allData->getheight,$percentDigit).'%',
												number_format($allData->veryheight),
												round($allData->veryheight*100/$allData->getheight,$percentDigit).'%',
												'','',
												'config'=>array('class'=>'subfooter -sub2')
												);
		}

		$heightTotal+=$rs->total;
		$heightGetheight+=$rs->getheight;
		$heightShort+=$rs->short;
		$heightRathershort+=$rs->rathershort;
		$heightStandard+=$rs->standard;
		$heightRatherheight+=$rs->ratherheight;
		$heightVeryheight+=$rs->veryheight;
	}
	//$tables->rows[]=array('รวมชั้นอนุบาล','','','','','','','','','','','','','','');
	//$tables->rows[]=array('รวมชั้นประถมศึกษาปีที่ 1-6','','','','','','','','','','','','','','');
	//$tables->rows[]=array('รวมชั้นมัธยมศึกษาปีที่ 1-3','','','','','','','','','','','','','','');
	$tables->rows[]=array(
										'ภาพรวมทุกชั้น',
										'',
										number_format($heightTotal),
										number_format($heightGetheight),
										round($heightGetheight*100/$heightTotal,$percentDigit).'%',
										number_format($heightShort),
										round($heightShort*100/$heightGetheight,$percentDigit).'%',
										number_format($heightRathershort),
										round($heightRathershort*100/$heightGetheight,$percentDigit).'%',
										number_format($heightStandard),
										round($heightStandard*100/$heightGetheight,$percentDigit).'%',
										number_format($heightRatherheight),
										round($heightRatherheight*100/$heightGetheight,$percentDigit).'%',
										number_format($heightVeryheight),
										round($heightVeryheight*100/$heightGetheight,$percentDigit).'%',
										'','',
										'config'=>array('class'=>'subfooter -sub3')
										);
	$tables->rows[]=array(
										'ภาพรวมทุกภาค',
										'',
										number_format($totalCountry),
										number_format($getheightCountry),
										round($getheightCountry*100/$getheightCountry,$percentDigit).'%',
										number_format($shortCountry),
										round($shortCountry*100/$getheightCountry,$percentDigit).'%',
										number_format($rathershortCountry),
										round($rathershortCountry*100/$getheightCountry,$percentDigit).'%',
										number_format($standardCountry),
										round($standardCountry*100/$getheightCountry,$percentDigit).'%',
										number_format($ratherheightCountry),
										round($ratherheightCountry*100/$getheightCountry,$percentDigit).'%',
										number_format($veryheightCountry),
										round($veryheightCountry*100/$getheightCountry,$percentDigit).'%',
										'','',
										'config'=>array('class'=>'subfooter -sub3')
										);

	$ret.=$tables->build();

	if ($text) $self->theme->title.=' '.implode(' ',$text);


	/*
	head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');

	$ret.='
	<script type="text/javascript">
		var chartData='.json_encode($graph).'
		var chartType="col";
		google.charts.load("current", {"packages":["corechart"]});
		google.charts.setOnLoadCallback(drawChart);

		function drawChart() {
			var options = {
											vAxis: {
												viewWindowMode: "explicit",
												viewWindow: {max: '.$maxStudent.'},
											},
										};

			if (chartType=="pie") {
				var options = {
												legend: {position: "none"},
												chartArea: {width:"100%",height:"100%"},
											};
			}
			$.each(chartData,function(i,eachChartData) {
				var data = new google.visualization.DataTable();
				data.addColumn("string", "น้ำหนักตามเกณฑ์ส่วนสูง");
				data.addColumn("number", "จำนวนคน");
				data.addRows(eachChartData);

				var chart = new google.visualization.PieChart(document.getElementById("graph-"+i));
				if (chartType=="line") {
					chart = new google.visualization.LineChart(document.getElementById("graph-"+i));
				} else if (chartType=="bar") {
					chart = new google.visualization.BarChart(document.getElementById("graph-"+i));
				} else if (chartType=="col") {
					chart = new google.visualization.ColumnChart(document.getElementById("graph-"+i));
				} else if (chartType=="pie") {
					chart = new google.visualization.PieChart(document.getElementById("graph-"+i));
				}
				chart.draw(data, options);
			});
		}
		$(document).on("click", ".toolbar.-graphtype a", function() {
			var $this=$(this);
			chartType=$this.attr("href").substring(1);
			//notify("chartType="+chartType);
			$(".toolbar.-graphtype a").removeClass("active");
			$this.addClass("active");
			drawChart();
			return false;
		});
		</script>
	';
	*/

	//$ret.=print_o($dbs,'$dbs');

	$ret.='<style type="text/css">
	.item.-weightform {margin-bottom:80px;}
	.item.-weightform caption {background:#FFAE00; color:#000; font-size:1.4em;}
	.item.-weightform td:nth-child(2n+1) {color:#999;}
	.item.-weightform td:nth-child(2n+2) {background:#efefef;}
	.item.-weightform td:nth-child(n+2) {width:50px;}
	.item.-weightform td:first-child, .item.-weightform td:nth-child(3) {color:#333;}
	.item td:nth-child(n+2) {text-align: center;}
	.item h3 {padding-left:10px;text-align:left; background:#9400FF; color:#fff;}
	.item .student {font-weight:bold;}
	.graph {width:300px;height:300px; margin:0 auto;}
	.toolbar.-graphtype {text-align: right; margin:0 0 10px 0;}
	.toolbar .active {background:#84CC00;}
	.item tr.subfooter.-sub2 td {background-color:#d0d0d0;}
	.item tr.subfooter.-sub3 td {background-color:#c0c0c0;}
	.sg-chart {height:400px;}
	</style>';

	$ret.='<script type="text/javascript">
	$("body").on("change","#condition select", function() {
		var $this=$(this);
		if ($this.attr("name")=="zone") $("#province").val("");
		notify("กำลังโหลด");
		console.log($(this).attr("name"))
		$(this).closest("form").submit();
	});
	</script>';
	return $ret;
}
?>