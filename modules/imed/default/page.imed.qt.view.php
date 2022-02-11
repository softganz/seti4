<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_qt_view($self,$pid) {
	$ret = '<header class="header -box"><h3>แบบสอบถามของ '.$pid.'</h3></header>';
	$stmt = 'SELECT
		*
		, uc.`name` `createBy`
		, um.`name` `modifyBy`
		FROM %imed_qt% qt
			LEFT JOIN %users% uc ON uc.`uid` = qt.`ucreated`
			LEFT JOIN %users% um ON um.`uid` = qt.`umodify`
		WHERE `pid` = :pid
		ORDER BY `part` ASC';

	$dbs = mydb::select($stmt,':pid',$pid);

	$tables = new Table();
	$tables->thead = array(
		'no'=>'',
		'Part',
		'Value',
		'date -create'=>'Created',
		'namecreate -nowrap' => 'By',
		'date -modify'=>'Modified',
		'namemodify -nowrap' => 'By'
	);

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			++$no,
			$rs->part,
			$rs->value,
			sg_date($rs->dcreated,'Y-m-d H:i:s'),
			$rs->createBy,
			$rs->dmodify?sg_date($rs->dmodify,'Y-m-d H:i:s'):'',
			$rs->modifyBy,
		);
	}
	$ret .= $tables->build();
	return $ret;
}
?>