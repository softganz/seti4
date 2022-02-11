<?php
/**
* Project :: Repair Person AreaCode
* Created 2021-04-11
* Modify  2021-04-11
*
* @param Object $self
* @return String
*
* @usage project/admin/repair/person/areacode
*/

$debug = true;

function project_admin_repair_person_areacode($self) {
	// Data Model


	// View Model
	new Toolbar($self, 'Repair Valuation');

	$ret .= '<nav class="nav -page -sg-text-center"><a class="sg-action btn -primary" href="'.url('project/admin/repair/person/areacode').'" data-rel="#main" data-title="ซ่อมแซม" data-confirm="กรุณายืนยัน?">START REPAIR PERSON AREACODE</a></nav>';

	if (SG\confirm()) {
		mydb::query(
			'UPDATE %db_person%
			SET
			`areacode` = CONCAT(`areacode`,LPAD(`village`,2,"0"))
			, `village` = ""
			WHERE LENGTH(`areacode`) = 6 AND `village` != ""'
		);

		$ret .= mydb()->_query.'<br />';
	}


	$stmt = 'SELECT p.`name`, p.`lname`, p.`areacode`, p.`village`
		FROM %db_person% p
			WHERE LENGTH(p.`areacode`) = 6 AND p.`village` != ""
		';

	$dbs = mydb::select($stmt);

	//$ret .= mydb()->_query;

	$ret .= mydb::printtable($dbs);

	return $ret;
}
?>