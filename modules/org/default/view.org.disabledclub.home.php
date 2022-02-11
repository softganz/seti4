<?php
/**
* Module Method
*
* @param 
* @return String
*/

$debug = true;

function view_org_disabledclub_home() {
	$ret = '';
	$stmt = 'SELECT DISTINCT of.`orgid`, o.`name` FROM %org_officer% of LEFT JOIN %db_org% o USING(`orgid`) WHERE of.`uid` = :uid';
	$dbs = mydb::select($stmt, ':uid', i()->uid);

	$tables = new Table();
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array('<a href="'.url('org/disabledclub/'.$rs->orgid).'">'.$rs->name.'</a>');
	}
	$ret .= $tables->build();
	//$ret .= print_o($dbs);
	return $ret;
}
?>