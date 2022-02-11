<?php
/**
* Saveup :: Report Treat Pay For
* Created 2018-04-03
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage saveup/report/treat/payfore
*/

$debug = true;

function saveup_report_treat_payfor($self) {
	$years = explode(',',mydb::select('SELECT DISTINCT YEAR(`date`) `year` FROM %saveup_treat% ORDER BY `year` ASC')->lists->text);

	$fromyear = $_GET['fromyear']?$_GET['fromyear']:date('Y')-2;
	$toyear = $_GET['toyear']?$_GET['toyear']:end($years);
	$order = SG\getFirst(post('order'),'payfor');
	$groups = array(
		'paytype' => 'paytype',
		'payfor' => 'payfor',
		'disease' => 'disease'
	);
	$group = SG\getFirst($groups[post('g')],'paytype');

	$payType = saveup_var::$payType;

	$stmt = 'SELECT YEAR(`date`) `year`, `'.$group.'` `label`, COUNT(DISTINCT `mid`) persons, sum(amount) AS total
		FROM %saveup_treat%
		WHERE YEAR(`date`) BETWEEN :fromyear AND :toyear
		GROUP BY `'.$group.'`, YEAR(`date`)
		HAVING `total`>0
		ORDER BY '.$group.' ASC';
	$dbs=mydb::select($stmt,':fromyear',$fromyear, ':toyear',$toyear);

	$self->theme->title='ค่าสวัสดิการแยกประเภท';

	$ret .= '<form method="get" action="'.url('saveup/report/treat/payfor').'">ปี พ.ศ. ';
	$ret.='<select class="form-select" name="fromyear" />';
	foreach ($years as $year)
		$ret .= '<option value="'.$year.'" '.($year == $fromyear ? 'selected="selected"' : '').' />'.($year+543).'</option>';
	$ret .= '</select> - ';
	$ret .= '<select class="form-select" name="toyear" />';
	foreach ($years as $year)
		$ret.='<option value="'.$year.'" '.($year==$toyear ? 'selected="selected"':'').' />'.($year+543).'</option>';
	$ret .= '</select>';

	$ret .= ' จัดกลุ่ม <select class="form-select" name="g"><option value="paytype"'.($group=='paytype'?' selected="selected"':'').'>ประเภท</option><option value="payfor"'.($group=='payfor'?' selected="selected"':'').'>เพื่อเป็นค่า</option><option value="disease"'.($group=='disease'?' selected="selected"':'').'>โรค</option></select> เรียงลำดับตาม <select class="form-select" name="order"><option value="payfor">เพื่อเป็นค่า</option><option value="total">จำนวนเงิน</option></select> <button class="btn -primary"><i class="icon -search -white"></i><span>ดูรายงาน</span></button></form>';

	$tables = new Table();
	$tables->thead['no'] = '';
	$tables->thead[] = 'รายการ';
	$tables->tfoot[0]['no'] = '<td></td>';
	$tables->tfoot[0]['total'] = 'รวมทั้งสิ้น';

	foreach ($dbs->items as $rs)
		$keys[$rs->label] = $rs->label;

	foreach ($keys as $label) {
		$tables->rows[$label]['no'] = ++$no;
		if ($group == 'paytype')
			$tables->rows[$label]['payfor'] = $payType[$label];
		else
		$tables->rows[$label]['payfor'] = $label;
		for ($i=$fromyear;$i<=$toyear;$i++)
			$tables->rows[$label][$i] = 0;
	}

	foreach ($dbs->items as $rs)
		$items[$rs->year][$rs->label] = $rs->total;

	for ($i = $fromyear;$i <= $toyear; $i++)
		$tables->thead[] = $i+543;

	foreach ($items as $year => $payList) {
		foreach ($keys as $payfor) {
			$tables->rows[$payfor][$year] = $payList[$payfor] ? number_format($payList[$payfor],2) : '-';
			$tables->tfoot[0][$year] += $payList[$payfor];
		}
	}
	foreach ($tables->tfoot[0] as $key => $value) {
		if (is_numeric($value))
			$tables->tfoot[0][$key] = number_format($value,2);
	}

	$ret .= $tables->build();
	$ret .= '<style>.item td:nth-child(n+3) {text-align:center;}</style>';

	return $ret;
}
?>