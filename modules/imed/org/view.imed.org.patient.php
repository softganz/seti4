<?php
/**
* iMed Org
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function view_imed_org_patient($orgInfo) {
	$orgId = $orgInfo->orgid;

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;



	$stmt = 'SELECT
					om.*
					, CONCAT(p.`name`, " ", p.`lname`) `fullname`
					FROM %org_member% om
						LEFT JOIN %db_person% p USING(`psnid`)
					WHERE `orgid` = :orgid';
	$dbs = mydb::select($stmt, ':orgid', $orgId);

	$ui = new Ui(NULL,'ui-card');
	foreach ($dbs->items as $rs) {
		$cardStr = '<h3>'.$rs->fullname.'</h3>';
		$cardStr .= '<div>'.$rs->mtype.'</div>';
		$cardStr .= '<div>'.sg_date(SG\getFirst($rs->datein,$rs->created),'ว ดด ปปปป').'</div>';
		$cardStr .= sg_dropbox('MENU', '{class: "leftside -atright"}');
		//$cardStr .= print_o($rs,'$rs');
		$ui->add($cardStr);
	}
	$ret .= $ui->build();

	//$ret .= print_o($dbs);
	return $ret;
}
?>