<?php
/**
* Project :: Bank Check Report
* Created 2021-02-26
* Modify  2021-02-26
*
* @param Object $self
* @return String
*
* @usage project/report/bank/check
*/

$debug = true;

function project_report_bank_check($self) {
	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>ยืนยันบัญชีธนาคาร</h3></header>';

	$bankUpdate = mydb::select(
		'SELECT tp.`tpid`, tp.`parent`, tp.`title`, COUNT(*) `total`
		FROM %bigdata% c
			LEFT JOIN %topic% t ON t.`tpid` = c.`keyid`
			LEFT JOIN %topic% tp ON tp.`tpid` = t.`parent`
		WHERE `keyname` = "project.info" AND `fldname` = "bankcheck"
		GROUP BY tp.`tpid`
		ORDER BY CONVERT(t.`title` USING tis620) ASC
		-- {sum: "total"}'
	);

	$tables = new Table();
	$tables->thead = array('ตำบล', 'total -amt' => 'จำนวน');
	foreach ($bankUpdate->items as $rs) {
		$tables->rows[] = array('<a class="sg-action" href="'.url('project/'.$rs->parent.'/info.child.bank', array('child' => $rs->tpid)).'" data-rel="box">'.$rs->title.'</a>', $rs->total);
	}
	$tables->tfoot[] = array('รวม', number_format($bankUpdate->sum->total));

	$ret .= $tables->build();
	//$ret .= print_o($bankUpdate);

	return $ret;
}
?>