<?php
/**
* Project :: Fund Estimate Main Page
* Created 2020-06-07
* Modify  2020-06-07
*
* @param Object $self
* @return String
*
* @call project/fund/estimate
*/

$debug = true;

function project_fund_eval_home($self) {
	R::view('project.toolbar',$self,'แบบประเมินกองทุน','fund');

	$ret .= '<header class="header"><h3>แบบประเมินการบริหารจัดการกองทุนหลักประกันสุขภาพในระดับท้องถิ่นหรือพื้นที่ สำนักงานหลักประกันสุขภาพแห่งชาติ</h3></header>';

	$stmt = 'SELECT
		q.*
		, qby.`value` `by`
		, o.`name` `fundname`, o.`shortname`, SUM(IF(r.`part` LIKE "RATE.%",r.`rate`,0)) `rates`
		FROM %qtmast% q
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %qttran% qby ON qby.`qtref`=q.`qtref` AND qby.`part`="HEADER.BY" 
			LEFT JOIN %qttran% r ON r.`qtref`=q.`qtref` AND r.`part` LIKE "RATE.%"
		WHERE q.`qtform`="103"
		GROUP BY q.`qtref`
		ORDER BY q.`qtref` ASC';

	$dbs = mydb::select($stmt,':orgid',$fundInfo->orgid);
	//$ret.=mydb()->_query;

	$tables = new Table();
	$tables->thead=array('no'=>'ลำดับที่','ชื่อกองทุน','ประเมินโดย','ชื่อผู้บันทึก','วันที่ประเมิน',/*'แก้ไขล่าสุด',*/'amt -hover-parent'=>'คะแนนประเมิน');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			++$no,
			$rs->fundname,
			$rs->by,
			$rs->collectname,
			sg_date($rs->created,'d/m/ปปปป'),
			//'',
			number_format($rs->rates)
			. '<nav class="nav -icons -hover"><a href="'.url('project/fund/'.$rs->orgid.'/eval/'.$rs->qtref).'"><i class="icon -viewdoc"></i></a></nav>'
		);
	}

	$ret .= $tables->build();

	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>