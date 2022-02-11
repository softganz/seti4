<?php
/**
* Project :: Repair Space
* Created 2021-02-08
* Modify  2021-02-08
*
* @param Object $self
* @return String
*
* @usage project/admin/repair/space
*/

$debug = true;

function project_admin_repair_space($self) {
	$ret = '<header class="header"><h3>Repair Space In Title</h3></header>';

	$ret .= '<nav class="nav -page"><a class="sg-action btn -primary" href="'.url('project/admin/repair/space').'" data-rel="#main" data-title="ซ่อมแซมช่องว่าง" data-confirm="กรุณายืนยัน?">START REPAIR SPACE</a></nav>';

	if (SG\confirm()) {
		$stmt = 'UPDATE %topic% SET `title` = REPLACE(`title`,"  "," ") WHERE INSTR(`title`, "  ") > 0';

		$dbs = mydb::query($stmt);

		//$ret .= mydb()->_query.'<br />';
	}

	$stmt = 'SELECT `tpid`, `title`, `type` FROM %topic% WHERE INSTR(`title`, "  ") > 0';

	$dbs = mydb::select($stmt);

	$ret .= mydb::printtable($dbs);

	return $ret;
}
?>