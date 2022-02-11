<?php
/**
* Project Localfund : Fix db_org tambon,ampur,changwat from project_fund
*
* @param Object $self
* @return String
*/

$debug = true;

function project_admin_fix_orgtambonformfund($self) {
	$ret = '';
	// -- UPDATE tambon code
	$stmt = 'UPDATE %db_org% o
					LEFT JOIN sgz_project_fund f USING(`orgid`)
					SET o.`tambon`=f.`tambon`, o.`ampur`=f.`ampur`, o.`changwat`=f.`changwat`
					WHERE o.`tambon` IS NULL';
	mydb::query($stmt);

	return $ret;
}
?>

