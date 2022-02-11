<?php
/**
* Project situation
*
* @param Object $self
* @return String
*/
function project_report_heightbyschool($self) {
	project_model::set_toolbar($self,'สรุปข้อมูลภาวะโภชนาการ (ส่วนสูงตามเกณฑ์อายุ)');

	$zone=post('zone');
	$province=post('province');
	$year=SG\getFirst(post('year'),2015);
	list($term,$period)=explode(':',SG\getFirst(post('term'),'1:1'));

	$order=SG\getFirst(post('order'),'t.`tpid`');

	$percentDigit=2;
	$maxStudent=0;
	$periodList=array('1'=>'ก่อนทำโครงการ','2'=>'ระหว่างทำโครงการ','3'=>'หลังทำโครงการ');
	$classLevelList=array(13=>'อนุบาล',23=>'ประถมศึกษาตอนต้น',26=>'ประถมศึกษาตอนปลาย',33=>'มัธยมศึกษา');

	$ui=new ui();

	$zoneList=cfg('zones');
	$formText.='<form method="get" action="'.url('project/report/heightbyschool').'">';
	if ($zoneList) {
		$formText.='<select name="zone" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"><option value="">ทุกภาค</option>';
		foreach ($zoneList as $zoneKey => $zoneItem) {
			$formText.='<option value="'.$zoneKey.'"'.($zoneKey==$zone?' selected="selected"':'').'>'.$zoneItem['name'].'</option>';
		}
		$formText.='</select> ';
	}

	// เลือกปีการศึกษา
	$dbs=mydb::select('SELECT DISTINCT `detail1` `pryear` FROM %project_tr% WHERE `formid`="weight" AND `part`="title" HAVING `pryear` ORDER BY `pryear` ASC');
	$formText.='<select name="year" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;">';
	foreach ($dbs->items as $rs) {
		$formText.='<option value="'.$rs->pryear.'"'.($rs->pryear==$year?' selected="selected"':'').'>ปีการศึกษา '.($rs->pryear+543).'</option>';
	}
	$formText.='</select>&nbsp;';

	// เลือกภาคการศึกษา
	$formText.='<select name="term" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;">';
	for ($i=1;$i<=2;$i++) {
		for ($j=1;$j<=2;$j++) {
			$termperiod=$i.':'.$j;
			$formText.='<option value="'.$termperiod.'"'.($termperiod==$term.':'.$period?' selected="selected"':'').'>ภาคการศึกษา '.$i.' ครั้งที่ '.$j.'</option>';
		}
	}
	$formText.='</select>&nbsp;';

	$formText.='</form>';
	$ret.=$formText;






	$where=array();
	$where=sg::add_condition($where,'p.`prtype`="โครงการ" AND tr.`formid`="height" AND tr.`part`="height"');
	
	if ($zone) {
		$where=sg::add_condition($where,'LEFT(t.`changwat`,1) IN (:zone)','zone','SET:'.$zoneList[$zone]['zoneid']);
		$text[]=' พื้นที่ '.$zoneList[$zone]['name'];
	}
	if ($province) {
		if (cfg('project.multiplearea')) {
			$where=sg::add_condition($where,'a.changwat=:changwat ','changwat',$province);
		} else {
			$where=sg::add_condition($where,'p.changwat=:changwat ','changwat',$province);
		}
		$text[]='จังหวัด'.mydb::select('SELECT provname FROM %co_province% WHERE provid=:provid LIMIT 1',':provid',$province)->provname;
	}
	if ($year) {
		$where=sg::add_condition($where,'ti.`detail1`=:year ','year',$year);
		$text[]=' ปีการศึกษา '.($year+543);
	}
	if ($term) {
		$where=sg::add_condition($where,'ti.`detail2`=:term ','term',$term);
		$text[]=' ภาคการศึกษา '.($term);
	}
	if ($period) {
		$where=sg::add_condition($where,'ti.`period`=:period ','period',$period);
		$text[]='ครั้งที่ '.$period;
	}

	$titleText=implode(' ',$text);

	$stmt='SELECT
					  tr.`tpid`
					, ti.`trid`
					, t.`title`
					, t.`changwat`
					, cop.`provname` `changwatName`
					, ti.`detail1` `year`
					, ti.`detail2` `term`
					, ti.`period`
					,	ti.`detail3` `area`
					, ti.`detail4` `postby`
					, ti.`date1` `dateinput`
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
						LEFT JOIN %co_province% cop ON cop.`provid`=t.`changwat`
						LEFT JOIN %project_tr% ti ON ti.`trid`=tr.`parent`
						LEFT JOIN %qt% qt ON qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
				'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					GROUP BY `tpid`
					ORDER BY `changwat` ASC, CONVERT(`title` USING tis620) ASC';
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
	//$ret.=print_o($whereAll,'$whereAll');
	$stmt='SELECT
					  SUM(tr.`num5`) `short`
					, SUM(tr.`num6`) `rathershort`
					, SUM(tr.`num7`) `standard`
					, SUM(tr.`num8`) `ratherheight`
					, SUM(tr.`num9`) `veryheight`
					, SUM(tr.`num1`) `total`
					, SUM(tr.`num2`) `getheight`
					, COUNT(Distinct tr.`tpid`) `schools`
					FROM %project_tr% tr
						LEFT JOIN %project% p USING(`tpid`)
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %project_tr% ti ON ti.`trid`=tr.`parent`
						LEFT JOIN %qt% qt ON qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
				'.($whereAll?'WHERE '.implode(' AND ',$whereAll['cond']):'').'
					LIMIT 1';
	$allArea=mydb::select($stmt,$whereAll['value']);
	//$ret.=print_o($allArea,'$allArea');





	// Table
	$tables=new table('item -weightform');
	$tables->caption='สรุปข้อมูลภาวะโภชนาการ (ส่วนสูงตามเกณฑ์อายุ) '.$titleText;
	$tables->colgroup=array('','','amt student'=>'','amt bad'=>'','amt badpercent'=>'','amt fair'=>'','amt fairpercent'=>'','amt good'=>'','amt goodpercent'=>'');
	$tables->thead='<tr><th rowspan="2">โรงเรียน</th><th rowspan="2">จังหวัด</th><th>จำนวนนักเรียนทั้งหมด</th><th colspan="2">จำนวนนักเรียนที่วัดส่วนสูง</th><th colspan="2">เตี้ย</th><th colspan="2">ค่อนข้างเตี้ย</th><th colspan="2">สูงตามเกณฑ์</th><th colspan="2">ค่อนข้างสูง</th><th colspan="2">สูง</th><th colspan="2"></th></tr><tr><th>คน</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th></tr>';
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											'<a href="'.url('project/'.$rs->tpid.'/info.weight/view/'.$rs->trid).'" target="_blank">'.$rs->title.'</a>',
											$rs->changwatName,
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
		$heightTotal+=$rs->total;
		$heightGetheight+=$rs->getheight;
		$heightShort+=$rs->short;
		$heightRathershort+=$rs->rathershort;
		$heightStandard+=$rs->standard;
		$heightRatherheight+=$rs->ratherheight;
		$heightVeryheight+=$rs->veryheight;
	}

	// Show school total
	$tables->rows[]=array(
										'<td colspan="2">ภาพรวม '.$dbs->_num_rows.' โรงเรียน</td>',
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
										'config'=>array('class'=>'subfooter -sub3')
										);

	// Show  all area total
	$tables->rows[]=array(
										'<td colspan="2">ภาพรวมทุกภาค '.$allArea->schools.' โรงเรียน</td>',
										number_format($allArea->total),
										number_format($allArea->getheight),
										round($allArea->getheight*100/$allArea->total,$percentDigit).'%',
										number_format($allArea->short),
										round($allArea->short*100/$allArea->getheight,$percentDigit).'%',
										number_format($allArea->rathershort),
										round($allArea->rathershort*100/$allArea->getheight,$percentDigit).'%',
										number_format($allArea->standard),
										round($allArea->standard*100/$allArea->getheight,$percentDigit).'%',
										number_format($allArea->ratherheight),
										round($allArea->ratherheight*100/$allArea->getheight,$percentDigit).'%',
										number_format($allArea->veryheight),
										round($allArea->veryheight*100/$allArea->getheight,$percentDigit).'%',
										'config'=>array('class'=>'subfooter -sub3')
										);

	$ret.=$tables->build();
	//$ret.=print_o($allArea,'$allArea');



	$ret.='<style type="text/css">
	.item.-weightform {margin-bottom:80px;}
	.item.-weightform caption {background:#FFAE00; color:#000; font-size:1.2em; font-weight: normal;}
	.item.-weightform td:nth-child(2n+1) {color:#999;}
	.item.-weightform td:nth-child(2n+2) {background:#efefef;}
	.item.-weightform td:nth-child(n+3) {width:50px;}
	.item.-weightform td:first-child, .item.-weightform td:nth-child(3) {color:#333;}
	.item td:nth-child(n+2) {text-align: center;}
	.item h3 {padding-left:10px;text-align:left; background:#9400FF; color:#fff; font-weight:normal;}
	.item .student {font-weight:bold;}
	.graph {width:300px;height:300px; margin:0 auto;}
	.toolbar.-graphtype {text-align: right; margin:0 0 10px 0;}
	.toolbar .active {background:#84CC00;}
	.item tr.subfooter.-sub2 td {background-color:#d0d0d0;}
	.item tr.subfooter.-sub3 td {background-color:#c0c0c0;}
	</style>';
	return $ret;
}
?>