<?php
/**
* Project :: Fund Evaluate Listing
* Created 2020-10-23
* Modify  2020-10-23
*
* @param Object $self
* @param Objectnt $fundInfo
* @return String
*
* @call project/fund/$orgId/eval
*/

$debug = true;

function project_fund_eval_list($self, $fundInfo = NULL) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	$isEdit = $fundInfo->right->edit;

	R::view('project.toolbar',$self,'แบบประเมิน - '.$fundInfo->name,'fund',$fundInfo);

	$ret = '<header class="header"><h3>แบบประเมิน การบริหารจัดการกองทุนหลักประกันสุขภาพในระดับท้องถิ่นหรือพื้นที่</h3>'
		. ($isEdit ? '<nav class="nav"><ul><li><a class="btn -floating -circle32" href="'.url('project/fund/'.$orgId.'/estimate.form').'" title="บันทึกแบบประเมินประจำปี"><i class="icon -material -white">add</i></a></li></ul></nav>' : '')
		. '</header>';

	/*
	if ($fundInfo->right->edit) {
		$ret.='<div class="btn-floating -right-bottom">';
		$ret.='<a class="btn -floating -circle48" href="'.url('project/fund/'.$orgId.'/estimate.form').'" title="บันทึกแบบประเมินประจำปี"><i class="icon -material -white">add</i></a>';
		$ret.='</div>';
	}
	*/

	$stmt = 'SELECT
		q.*
		, qby.`value` `by`
		, o.`name` `fundname`
		, SUM(IF(r.`part` LIKE "RATE.%",r.`rate`,0)) `rates`
		FROM %qtmast% q
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %qttran% qby ON qby.`qtref` = q.`qtref` AND qby.`part` = "HEADER.BY" 
			LEFT JOIN %qttran% r ON r.`qtref` = q.`qtref` AND r.`part` LIKE "RATE.%"
		WHERE q.`qtform` = "103" AND q.`orgid` = :orgid
		GROUP BY q.`qtref`
		ORDER BY q.`qtref` ASC';

	$dbs = mydb::select($stmt,':orgid',$orgId);

	//$ret.=mydb()->_query;

	$tables = new Table();
	$tables->thead = array(
		'no'=>'ลำดับ',
		'ชื่อกองทุน',
		'ประเมินโดย',
		'ชื่อผู้บันทึก',
		'date -date' => 'วันที่ประเมิน',
		'amt -hover-parent'=>'คะแนนประเมิน',
	);

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			++$no,
			$rs->fundname,
			$rs->by,
			$rs->collectname,
			sg_date($rs->created,'d/m/ปปปป'),
			//'',
			number_format($rs->rates)
			. '<nav class="nav -icons -hover"><a href="'.url('project/fund/'.$orgId.'/estimate/'.$rs->qtref).'"><i class="icon -material">find_in_page</i></a></nav>'
		);
	}

	$ret .= $tables->build();



	$stmt = 'SELECT
		q.*
		, qby.`value` `by`
		, o.`name` `fundname`
		, SUM(IF(r.`part` LIKE "RATE.%",r.`rate`,0)) `rates`
		FROM %qtmast% q
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %qttran% qby ON qby.`qtref` = q.`qtref` AND qby.`part` = "HEADER.BY" 
			LEFT JOIN %qttran% r ON r.`qtref` = q.`qtref` AND r.`part` LIKE "RATE.%"
		WHERE q.`qtform` = "106" AND q.`orgid` = :orgid
		GROUP BY q.`qtref`
		ORDER BY q.`qtref` ASC';

	$dbs = mydb::select($stmt,':orgid',$orgId);

	$ret .= '<header class="header"><h3>แบบประเมิน
กองทุนหลักประกันสุขภาพระดับท้องถิ่นหรือพื้นที่</h3>'
		. ($isEdit ? '<nav class="nav"><ul><li><a class="btn -floating -circle32" href="'.url('project/fund/'.$orgId.'/eval.operate.form').'" title="บันทึกแบบประเมินประจำปี"><i class="icon -material -white">add</i></a></nav>' : '')
		. '</header>';

	$tables = new Table();
	$tables->thead = array(
		'no'=>'ลำดับ',
		'ชื่อกองทุน',
		'ประเมินโดย',
		'ชื่อผู้บันทึก',
		'date -date' => 'วันที่ประเมิน',
		'amt -hover-parent'=>'คะแนนประเมิน',
	);

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			++$no,
			$rs->fundname,
			$rs->by,
			$rs->collectname,
			sg_date($rs->created,'d/m/ปปปป'),
			//'',
			number_format($rs->rates)
			. '<nav class="nav -icons -hover"><a href="'.url('project/fund/'.$orgId.'/eval.operate/'.$rs->qtref).'"><i class="icon -material">find_in_page</i></a></li></ul></nav>'
		);
	}

	$ret .= $tables->build();


	$stmt = 'SELECT
		q.*
		, qby.`value` `by`
		, o.`name` `fundname`
		, SUM(IF(r.`part` LIKE "RATE.%",r.`rate`,0)) `rates`
		FROM %qtmast% q
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %qttran% qby ON qby.`qtref` = q.`qtref` AND qby.`part` = "HEADER.BY" 
			LEFT JOIN %qttran% r ON r.`qtref` = q.`qtref` AND r.`part` LIKE "RATE.%"
		WHERE q.`qtform` = "107" AND q.`orgid` = :orgid
		GROUP BY q.`qtref`
		ORDER BY q.`qtref` ASC';

	$dbs = mydb::select($stmt,':orgid',$orgId);

	$ret .= '<header class="header"><h3>แบบประเมิน กองทุนผู้สูงอายุที่มีภาวะพึ่งพิงและบุคคลอื่นที่มีภาวะพึ่งพิง (LTC)</h3>'
		. ($isEdit ? '<nav class="nav"><ul><li><a class="btn -floating -circle32" href="'.url('project/fund/'.$orgId.'/eval.ltc.form').'" title="บันทึกแบบประเมินประจำปี"><i class="icon -material -white">add</i></a></nav>' : '')
		. '</header>';

	$tables = new Table();
	$tables->thead = array(
		'no'=>'ลำดับ',
		'ชื่อกองทุน',
		'ประเมินโดย',
		'ชื่อผู้บันทึก',
		'date -date' => 'วันที่ประเมิน',
		'amt -hover-parent'=>'คะแนนประเมิน',
	);

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			++$no,
			$rs->fundname,
			$rs->by,
			$rs->collectname,
			sg_date($rs->created,'d/m/ปปปป'),
			//'',
			number_format($rs->rates)
			. '<nav class="nav -icons -hover"><a href="'.url('project/fund/'.$orgId.'/eval.ltc/'.$rs->qtref).'"><i class="icon -material">find_in_page</i></a></li></ul></nav>'
		);
	}

	$ret .= $tables->build();

	return $ret;
}
?>