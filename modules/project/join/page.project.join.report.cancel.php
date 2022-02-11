<?php
/**
* Module Method
* Created 2019-08-01
* Modify  2019-08-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_join_report_cancel($self, $projectInfo) {
	$ret = '';

	R::View('project.toolbar', $self, 'ยกเลิก - '.$projectInfo->calendarInfo->title, 'join', $projectInfo);


	$stmt = 'SELECT `joingroup`,COUNT(*) `amt`  FROM %org_dos%
		WHERE `doid` = :doid AND `isjoin`<0
		GROUP BY joingroup
		ORDER BY `amt` DESC';

	$dbs = mydb::select($stmt, ':doid', $projectInfo->doingInfo->doid);

	$tables = new Table();
	$tables->caption = 'จำนวนผู้ยกเลิกการลงทะเบียนแยกตามเครือข่าย';
	$tables->thead = array('เครือข่าย', 'amt'=>'จำนวน');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array($rs->joingroup, $rs->amt);
	}
	$ret .= $tables->build();

	//$ret .= print_o($dbs);
	//$ret .= print_o($projectInfo);
	return $ret;
}
?>