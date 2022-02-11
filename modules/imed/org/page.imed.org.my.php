<?php
/**
* My iMed Org
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_org_my($self) {
	$uid = i()->uid;

	if (empty($uid)) return R::View('signform');
	$ret = 'My Org';

	$stmt = 'SELECT of.*, o.`name` FROM %org_officer% of LEFT JOIN %db_org% o USING(`orgid`) WHERE of.`uid` = :uid';
	$dbs = mydb::select($stmt, ':uid', $uid);

	$ui = new Ui(NULL, 'ui-card');
	foreach ($dbs->items as $rs) {
		$cardStr = '<h3>'.$rs->name.'</h3>';
		$cardStr .= '<nav class="nav -icons"><ul><li><a class="btn" href="'.url('imed/org/'.$rs->orgid).'"><i class="icon -home"></i><span>View</span></a></li></ul></nav>';
		$ui->add($cardStr);
	}

	$ret .= $ui->build();

	//$ret .= print_o($dbs);
	return $ret;
}
?>