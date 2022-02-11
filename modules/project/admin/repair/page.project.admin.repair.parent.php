<?php
/**
* Repair Real Join Target Person Amount From Table project_target
* Created 2019-12-07
* Modify  2019-12-07
*
* @param Object $self
* @return String
*/

$debug = true;

function project_admin_repair_parent($self) {
	$ret = '';

	$ret .= '<nav class="nav -page"><a class="sg-action btn -primary" href="'.url('project/admin/repair/parent').'" data-rel="#main" data-title="ซ่อมแซม" data-confirm="กรุณายืนยัน?">START REPAIR</a></nav>';

	if (!SG\confirm()) {
		$stmt = 'UPDATE `sgz_project` p SET p.`jointarget` =
			(SELECT SUM(IFNULL(`joinamt`,0)) `joinTotal`
			FROM `sgz_project_target` 
			WHERE `tpid` = p.`tpid` AND `tagname` = "info")';

		$stmt = 'SELECT t.`tpid`,p.`tpid`,t.`parent`,p.`projectset`, t.`title`
			FROM %topic% t
			LEFT JOIN %project% p USING(`tpid`)
			WHERE t.`type`="project"
			AND (
				(p.`projectset` IS NOT NULL AND t.`parent` IS NULL)
				OR (p.`projectset` IS NULL AND t.`parent` IS NOT NULL)
			)';

		$dbs = mydb::select($stmt);

		//$ret .= mydb()->_query.'<br />';

		$ret .= mydb::printtable($dbs);
	} else {
		$stmt = 'UPDATE %topic% t
			LEFT JOIN %project% p USING(`tpid`)
			SET t.`parent` = p.`projectset`
			WHERE t.`type` = "project" AND (p.`projectset` IS NOT NULL AND t.`parent` IS NULL)';

		mydb::query($stmt);

		$ret .= mydb()->_query.'<br /><br />';

		$stmt = 'UPDATE %topic% t
			LEFT JOIN %project% p USING(`tpid`)
			SET p.`projectset` = t.`parent`
			WHERE t.`type` = "project" AND (p.`projectset` IS NULL AND t.`parent` IS NOT NULL)';

		mydb::query($stmt);

		$ret .= mydb()->_query;

	}

	//$ret .= 

	//$stmt = 'UPDATE `sgz_project` SET `jointarget` = NULL WHERE `jointarget` = 0';

	//mydb::query($stmt);

	//$ret .= mydb()->_query.'<br />';
	return $ret;
}
?>