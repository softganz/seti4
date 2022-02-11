<?php
/**
* Saveup :: Report Member Who Ahve Treat
* Created 2018-05-16
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage saveup/report/havetreat
*/

$debug = true;

function saveup_report_havetreat($self) {
	$year = SG\getFirst(post('yr'),date('Y'));
	$order=SG\getFirst(post('or'),'totals');

	$yearList = explode(',',mydb::select('SELECT DISTINCT YEAR(`date`) `year` FROM %saveup_treat% ORDER BY `year` ASC')->lists->text);

	$fromyear = SG\getFirst(post('fromyear'),end($yearList));
	$toyear = SG\getFirst(post('toyear'),end($yearList));


	mydb::where('fu.`status` = "active"');
	mydb::where('YEAR(`date`) BETWEEN :fromyear AND :toyear', ':fromyear', $fromyear, ':toyear', $toyear);
	mydb::value('$ORDER', $order);

	$stmt = 'SELECT
					  fu.`mid`
					, CONCAT(firstname," ",lastname) `name`
					, SUM(`amount`) `totals`
					, COUNT(*) `amt`
		FROM %saveup_treat% ft
			LEFT JOIN %saveup_member% fu USING(`mid`)
		%WHERE%
		GROUP BY fu.`mid`
		HAVING `totals` > 0
		ORDER BY $ORDER ASC;
		-- {sum: "amt,totals"}';

	$dbs = mydb::select($stmt);

	//$ret .= print_o($dbs, '$dbs');

	$self->theme->title='รายชื่อผู้เบิกค่ารักษาพยาบาล ประจำปี '.($fromyear+543).' - '.($toyear+543);

	$ret .= '<form method="get" action="'.url('saveup/report/havetreat').'">ปี พ.ศ. ';
	$ret.='<select class="form-select" name="fromyear" />';
	foreach ($yearList as $year)
		$ret .= '<option value="'.$year.'" '.($year == $fromyear ? 'selected="selected"' : '').' />'.($year+543).'</option>';
	$ret .= '</select> - ';
	$ret .= '<select class="form-select" name="toyear" />';
	foreach ($yearList as $year)
		$ret.='<option value="'.$year.'" '.($year==$toyear ? 'selected="selected"':'').' />'.($year+543).'</option>';
	$ret .= '</select> ';

	$ret .='เรียงลำดับตาม <select class="form-select" name="order"><option value="id">รหัส</option><option value="name">ชื่อ</option><option value="total">จำนวนเงิน</option></select> ';
	$ret .= '<button class="btn -primary"><i class="icon -search -white"></i><span>ดูรายงาน</span></button>';
	$ret .= '</form>';


	$tables = new Table();
	$tables->caption = $self->theme->title;
	$tables->thead = array('รหัส','ชื่อ-นามสกุล', 'amt -amt' => 'จำนวนครั้ง', 'total -amt' => 'จำนวนเงิน');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
												$rs->mid,
												$rs->name,
												$rs->amt,
												number_format($rs->totals,2)
											);
		$total += $rs->total;
	}
	$tables->tfoot[] = array(
											'',
											'รวมทั้งสิ้น <strong>'.$dbs->_num_rows.'</strong> คน',
											number_format($dbs->sum->amt),
											number_format($dbs->sum->totals,2),
										);
	$ret .= $tables->build();
	return $ret;
}
?>