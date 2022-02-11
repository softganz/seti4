<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_knet_upgrade($self) {
	$getConfirm = SG\confirm();
	$ret = '<h3>Kamsai Network UPGRADE</h3>';

	$stmt = 'SELECT p.`tpid`, p.`projectset`, t.`orgid`, t.`title`
			FROM %project% p
				LEFT JOIN %topic% t USING(tpid)
			WHERE `projectset` = 5 AND t.`orgid` IS NULL';
	$dbs = mydb::select($stmt);


	$tables = new Table();

	foreach ($dbs->items as $rs) {
		$createResult = 'NOT CONFIRM';
		if ($getConfirm) {
			$ret .= R::Page('project.knet.host.create',NULL,$rs->tpid);
			$createResult = 'COMPLETED';
		}
		$tables->rows[] = array($rs->tpid,$rs->title,$createResult);
	}

	$ret .= $tables->build();


	return $ret;
}
?>