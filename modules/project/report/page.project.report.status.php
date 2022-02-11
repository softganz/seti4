<?php
/**
* Project situation
*
* @param Object $self
* @return String
*/
function project_report_status($self) {
	R::View('project.toolbar', $self, 'รายงานจำแนกตามกลุ่มเป้าหมาย', 'report');

	$zone=post('zone');
	$province=post('province');
	$year=post('year');

	$order=SG\getFirst(post('order'),'t.`tpid`');

	$percentDigit=2;
	$periodList=array('1'=>'ก่อนทำโครงการ','2'=>'ระหว่างทำโครงการ','3'=>'หลังทำโครงการ');


	$ui=new ui();

	$zoneList=cfg('zones');
	$provSelect.='<form method="get" action="'.url('project/report/status').'">';
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
	$dbs=mydb::select('SELECT DISTINCT `pryear` FROM %project% HAVING `pryear` ORDER BY `pryear` ASC');
	$provSelect.='<select name="year" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"><option value="">ทุกปี</option>';
	foreach ($dbs->items as $rs) {
		$provSelect.='<option value="'.$rs->pryear.'"'.($rs->pryear==$year?' selected="selected"':'').'>พ.ศ.'.($rs->pryear+543).'</option>';
	}
	$provSelect.='</select>&nbsp;';

	$provSelect.='</form>';
	$ui->add($provSelect);
	$ret.='<div class="reportbar">'.$ui->build('ul').'</div>';

	$items=100;
	$page=post('page');
	$firstRow=$page>1 ? ($page-1)*$items : 0;

	$where=array();
	$where=sg::add_condition($where,'p.`prtype`="โครงการ"');
	
	if (!$zone) {
		$label='CASE ';
		foreach ($zoneList as $item) $label.='WHEN LEFT(t.`changwat`,1) IN ('.$item['zoneid'].') THEN "'.$item['name'].'" ';
		$label.='END';
	} else if ($zone) {
		$label='cop.`provname`';
	}

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
		$where=sg::add_condition($where,'p.`pryear`=:year ','year',$year);
		$text[]=' ปี '.($year+543);
	}

	$whereCond=$where?'WHERE '.implode(' AND ',$where['cond']):'';
	$stmt="SELECT
					  $label `label`
					, COUNT(*) `total`
					, SUM(p.`studentjoin`) `studentjoin`
					, SUM(p.`teacherjoin`) `teacherjoin`
					, SUM(p.`parentjoin`) `parentjoin`
					, SUM(p.`clubjoin`) `clubjoin`
					, SUM(p.`localorgjoin`) `localorgjoin`
					, SUM(p.`govjoin`) `govjoin`
					, SUM(p.`otherjoin`) `otherjoin`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %co_province% cop ON cop.`provid`=t.`changwat`
					$whereCond
					GROUP BY `label`
					ORDER BY CONVERT(`label` USING tis620) ASC";
	$dbs=mydb::select($stmt,$where['value']);

	$projectTargetList=cfg('project.target');
	$projectSupportList=cfg('project.support');

	$tables=new table('item -report -project-status');
	$tables->thead='<thead><tr><th rowspan="2">พื้นที่</th><th rowspan="2">จำนวนโครงการ</th><th colspan="'.count($projectTargetList).'">กลุ่มเป้าหมายหลัก</th><th colspan="'.count($projectSupportList).'">ผู้มีส่วนร่วม/ผู้สนับสนุน</th></tr>';
	$tables->thead.='<tr>';
	foreach ($projectTargetList as $key => $value) {
		$tables->thead.='<th>'.$value.'</th>';
	}
	foreach ($projectSupportList as $key => $value) {
		$tables->thead.='<th>'.$value.'</th>';
	}
	$tables->thead.='</tr></thead>';

	$total['str']='รวม';
	$total['total']=0;
	foreach ($projectTargetList as $key => $value) {$total[$key]=0;}
	foreach ($projectSupportList as $key => $value) {$total[$key]=0;}

	foreach ($dbs->items as $rs) {
		unset($row);
		$row[]=$rs->label;
		$row[]=number_format($rs->total);
		foreach ($projectTargetList as $key => $value) {
			$row[]=number_format($rs->{$key});
			$total[$key]+=$rs->{$key};
		}
		foreach ($projectSupportList as $key => $value) {
			$row[]=number_format($rs->{$key});
			$total[$key]+=$rs->{$key};
		}
		$tables->rows[]=$row;
		$total['total']+=$rs->total;
	}
	foreach ($total as $key => $value) {
		if (is_numeric($value)) $total[$key]=number_format($value);
	}
	$tables->tfoot[]=$total;
	$ret.=$tables->build();

	if ($text) $self->theme->title.=' '.implode(' ',$text);

	//$ret.=print_o($dbs,'$dbs');

	$ret.='<style type="text/css">
	.item.-project-status td:nth-child(n+1) {text-align:center; width:10%;}
	</style>';
	return $ret;
}
?>