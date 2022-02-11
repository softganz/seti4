<?php
/**
* Module Method
* Created 2019-09-01
* Modify  2019-09-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function saveup_treat_year($self, $memberId, $year = NULL) {
	$memberInfo = is_object($memberId) ? $memberId : R::Model('saveup.member.get',$memberId);
	$memberId = $memberInfo->mid;

	$ret = '';

	$payTypeList = saveup_var::$payType;
	$payRate = sg_json_decode(cfg('saveup.treat.rate'));
	$payRight = sg_json_decode(cfg('saveup.treat.right'));

	$registerAge = $memberInfo->info->date_regist;

	$dateFrom =  new DateTime($memberInfo->info->date_regist);
	$dateTo = new DateTime();
	$yearDiff = $dateTo->diff($dateFrom)->format("%y");
	$dateDiff = $dateTo->diff($dateFrom)->format("%a");

	//$ret .= 'Diff day = '.$dateDiff.' Year = '.$yearDiff.'<br />';

	if ($yearDiff>=6) $maxPayRate = $payRight->{"6"};
	else if ($yearDiff >= 5) $maxPayRate = $payRight->{"5"};
	else if ($yearDiff >= 4) $maxPayRate = $payRight->{"4"};
	else if ($yearDiff >= 3) $maxPayRate = $payRight->{"3"};
	else if ($yearDiff >= 2) $maxPayRate = $payRight->{"2"};
	else if ($yearDiff >= 1) $maxPayRate = $payRight->{"1"};
	else $maxPayRate = 0;

	//$ret .= 'maxPayRate = '.$maxPayRate;

	//$ret .= print_o($payRate, '$payRate');
	//$ret .= print_o(cfg(),'cfg()');

	foreach ($payRate as $key => $value) {
		if ($value > $maxPayRate) $payRate->{$key} = $maxPayRate;
	}
	$stmt = 'SELECT * FROM %saveup_treat% WHERE `mid` = :mid ORDER BY `date` DESC;
		-- {sum: "amount"}';
	$dbs = mydb::select($stmt, ':mid', $memberId);

	$currentYear = date('Y');
	$currentYearTotal = 0;

	$payTypeTable = new Table();
	$payTypeTable->caption = 'วงเงินสวัสดิการปี '.($currentYear+543);
	$payTypeTable->thead = array('ประเภท','total -money'=>'วงเงินที่เบิกได้', 'ok -money' => 'เบิกแล้ว', 'balance -money' => 'คงเหลือ');
	$payTypeTable->tfoot[0] = array('รวม',number_format($maxPayRate,2), '','');
	foreach ($payTypeList as $key => $value) {
		$payTypeTable->rows[$key] = array('key'=>$value,1=>$payRate->{$key},2=>0,3=>$payRate->{$key});
	}

	$tables = new Table();
	$tables->thead = array('paydate -date' => 'วันที่', 'ค่าสวัสดิการ', 'amt -money' => 'จำนวนเงิน');
	foreach ($dbs->items as $rs) {
		if (sg_date($rs->date, 'Y') == $currentYear) {
			$payTypeTable->rows[$rs->paytype][2] += $rs->amount;
			$currentYearTotal += $rs->amount;
			if ($payTypeTable->rows[$rs->paytype][1]) {
				$payTypeTable->rows[$rs->paytype][3] = $payTypeTable->rows[$rs->paytype][1] - $payTypeTable->rows[$rs->paytype][2];
			}
		}
		$tables->rows[] = array(
			sg_date($rs->date, 'ว ดด ปปปป'),
			$payTypeList[$rs->paytype].' ('.$rs->payfor.')',
			number_format($rs->amount,2),
		);
	}

	$payTypeTable->tfoot[0][2] = number_format($currentYearTotal,2);
	$payTypeTable->tfoot[0][3] = number_format($maxPayRate - $currentYearTotal,2);

	$ret .= $payTypeTable->build();

	$ret .= $tables->build();


	$ret .= '<p>';
	$ret .= 'วันที่เริ่มเป็นสมาชิก '.sg_date($registerAge,'ว ดด ปปปป').'<br />';
	$ret .= 'จำนวนอายุการเป็นสมาชิก '.$yearDiff.' ปี<br />';
	$ret .= '</p>';

	return $ret;
}
?>