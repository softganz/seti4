<?php
/**
* iMed Org
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function view_imed_org_view($orgInfo) {
	$orgId = $orgInfo->orgid;

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;


	$ret .= '<h3>บริการของกลุ่ม</h3>';

	$ui = new Ui();
	$ui->add('<a class="btn" href="">บริการกายอุปกรณ์</a>');
	$ui->add('<a class="btn" href="">บริการกายอุปกรณ์</a>');

	$ret .= $ui->build();

	//$ret .= '$orgId = '.$orgId.print_o($orgInfo, '$orgInfo');

	/*

	$stmt = 'SELECT of.*, o.`name` FROM %org_officer% of LEFT JOIN %db_org% o USING(`orgid`) WHERE of.`uid` = :uid';
	$dbs = mydb::select($stmt, ':uid', $uid);

	$ui = new Ui();
	foreach ($dbs->items as $rs) {
		$ui->add('<a href="'.url('imed/org/'.$rs->orgid).'">'.$rs->name.'</a>');
	}

	$ret .= $ui->build();

	$ret .= print_o($dbs);
	*/
	return $ret;
}
?>