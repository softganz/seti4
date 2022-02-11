<?php
/**
* Project :: Repair Location
* Created 2021-02-08
* Modify  2021-02-08
*
* @param Object $self
* @return String
*
* @usage project/admin/repair/location
*/

$debug = true;

function project_admin_repair_location($self) {
	$ret = '<header class="header"><h3>Repair Location GIS</h3></header>';

	$ret .= '<nav class="nav -page"><a class="sg-action btn -primary" href="'.url('project/admin/repair/location').'" data-rel="#main" data-title="ซ่อมแซมพิกัด" data-confirm="กรุณายืนยัน?">START REPAIR LOCATION</a></nav>';

	if (SG\confirm()) {
		$stmt = 'UPDATE %project% p
			LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
			LEFT JOIN %co_subdistloc% l ON l.`subdistid` = LEFT(t.`areacode`,6)
			SET p.`location` = POINT(l.`LAT`, l.`LNG`)
			WHERE p.`location` IS NULL
			';

		$dbs = mydb::query($stmt);

		$ret .= mydb()->_query.'<br />';
	}

	$stmt = 'SELECT `tpid`, `title` FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE p.`location` IS NULL';

	$dbs = mydb::select($stmt);

	$tables = new Table();

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array($rs->tpid, '<a class="sg-action" href="'.url('project/'.$rs->tpid.'/info.map').'" data-rel="box" data-width="640">'.$rs->title.'</a>');
	}

	$ret .= $tables->build();

	return $ret;
}
?>