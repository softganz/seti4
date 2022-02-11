<?php
/**
* Project situation
*
* @param Object $self
* @return String
*/
function project_situation_eat($self) {
	project_model::set_toolbar($self,'สถานการณ์การกินอาหารและการออกกำลังกายของนักเรียน');

	$zone=post('zone');
	$province=post('province');
	$year=SG\getFirst(post('year'),'2016');
	$term=SG\getFirst(post('term'),2);
	$period=SG\getFirst(post('period'),1);

	$order=SG\getFirst(post('order'),'t.`tpid`');

	$percentDigit=2;
	$periodList=array('1'=>'ครั้งที่ 1','2'=>'ครั้งที่ 2');

	$ui=new ui();

	$zoneList=cfg('zones');
	$provSelect.='<form method="get" action="'.url('project/situation/eat').'">';
	if ($zoneList) {
		$provSelect.='<select name="zone" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"><option value="">ทุกภาค</option>';
		foreach ($zoneList as $zoneKey => $zoneItem) {
			$provSelect.='<option value="'.$zoneKey.'"'.($zoneKey==$zone?' selected="selected"':'').'>'.$zoneItem['name'].'</option>';
		}
		$provSelect.='</select> ';
	}

	$stmt='SELECT DISTINCT `changwat`, `provname`
					FROM %project% p
						LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
					'.($zone?'WHERE LEFT(p.`changwat`,1) IN ('.$zoneList[$zone]['zoneid'].')':'').'
					HAVING `provname`!=""
					ORDER BY CONVERT(`provname` USING tis620) ASC';
	$dbs=mydb::select($stmt);
	$provSelect.='<select name="province" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"><option value="">ทุกจังหวัด</option>';
	foreach ($dbs->items as $rs) {
		$provSelect.='<option value="'.$rs->changwat.'"'.($rs->changwat==$province?' selected="selected"':'').'>'.$rs->provname.'</option>';
	}
	$provSelect.='</select>&nbsp;';

	// เลือกปีการศึกษา
	$dbs=mydb::select('SELECT DISTINCT `detail1` `pryear` FROM %project_tr% WHERE `formid`="weight" AND `part`="title" HAVING `pryear` ORDER BY `pryear` ASC');
	$provSelect.='<select name="year" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"><option value="">ทุกปีการศึกษา</option>';
	foreach ($dbs->items as $rs) {
		$provSelect.='<option value="'.$rs->pryear.'"'.($rs->pryear==$year?' selected="selected"':'').'>ปีการศึกษา '.($rs->pryear+543).'</option>';
	}
	$provSelect.='</select>&nbsp;';

	// เลือกภาคการศึกษา
	$dbs=mydb::select('SELECT DISTINCT `detail2` `term` FROM %project_tr% WHERE `formid`="weight" AND `part`="title" HAVING `term` ORDER BY `term` ASC');
	$provSelect.='<select name="term" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"><option value="">ทุกภาคการศึกษา</option>';
	foreach ($dbs->items as $rs) {
		$provSelect.='<option value="'.$rs->term.'"'.($rs->term==$term?' selected="selected"':'').'>ภาคการศึกษา '.($rs->term).'</option>';
	}
	$provSelect.='</select>&nbsp;';

	// เลือกช่วงเวลา
	$dbs=mydb::select('SELECT DISTINCT `period` `period` FROM %project_tr% WHERE `formid`="weight" AND `part`="title" HAVING `period` ORDER BY `period` ASC');
	$provSelect.='<select name="period" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"><option value="">ทุกช่วงเวลา</option>';
	foreach ($dbs->items as $rs) {
		$provSelect.='<option value="'.$rs->period.'"'.($rs->period==$period?' selected="selected"':'').'>'.($periodList[$rs->period]).'</option>';
	}
	$provSelect.='</select>&nbsp;';

	$provSelect.='</form>';
	$ui->add($provSelect);
	$ret.='<div class="reportbar">'.$ui->build('ul').'</div>';

	$items=100;
	$page=post('page');
	$firstRow=$page>1 ? ($page-1)*$items : 0;

	$where=array();
	$where=sg::add_condition($where,'p.`prtype`="โครงการ" AND tr.`formid`="schooleat" AND tr.`part`="schooleat"');
	
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
		$text[]=' ช่วงเวลา '.($periodList[$period]);
	}


	$stmt='SELECT
					tr.`trid`, tr.`tpid`, tr.`sorder`, tr.`detail1` `year`, tr.`detail2` `term`, tr.`period`,
					tr.`detail3` `area`, tr.`detail4` `postby`, tr.`date1` `dateinput`
					, qt.`question`
					, qt.`qtgroup`
					, qt.`qtno`
					, COUNT(DISTINCT tr.`tpid`) totalSchool
					, SUM(tr.`num5`) `bads`
					, SUM(tr.`num6`) `fairs`
					, SUM(tr.`num7`) `goods`
					, SUM(tr.`num1`) total
					FROM %project_tr% tr
						LEFT JOIN %project% p USING(`tpid`)
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %project_tr% ti ON ti.`trid`=tr.`parent`
						LEFT JOIN %qt% qt ON tr.`part`=qt.`qtgroup` AND tr.`sorder`=qt.`qtno`
				'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					GROUP BY tr.`sorder`
					ORDER BY tr.`sorder` ASC';
	$dbs=mydb::select($stmt,$where['value']);

	$tables=new table('item -eatform');
	$tables->colgroup=array('no'=>'','','amt student'=>'','amt bad'=>'','amt badpercent'=>'','amt fair'=>'','amt fairpercent'=>'','amt good'=>'','amt goodpercent'=>'');
	$tables->thead=array('no'=>'','พฤติกรรมการกินและการออกกำลังกาย','จำนวนโรงเรียน','amt'=>'จำนวนนักเรียน<br />(คน)','<th colspan="2">ทำได้น้อย<br />(0-2 วันต่อสัปดาห์)<br />(คน)</th>','<th colspan="2">ทำได้ปานกลาง<br />(3-5 วันต่อสัปดาห์)<br />(คน)</th>','<th colspan="2">ทำได้ดี<br />(6-7 วันต่อสัปดาห์)<br />(คน)</th>');
	$rs->amt=rand(50,100);
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											$rs->qtno,
											$rs->question,
											$rs->totalSchool,
											number_format($rs->total),
											number_format($rs->bads),
											round($rs->bads*100/$rs->total,$percentDigit).'%',
											number_format($rs->fairs),
											round($rs->fairs*100/$rs->total,$percentDigit).'%',
											number_format($rs->goods),
											round($rs->goods*100/$rs->total,$percentDigit).'%',
											);
	}
	$ret.=$tables->build();

	if ($text) $self->theme->title.=' '.implode(' ',$text);

	//$ret.=print_o($dbs,'$dbs');

	$ret.='<style type="text/css">
	.item.-eatform td:nth-child(2n+3) {background:#efefef;}
	</style>';
	return $ret;
}
?>