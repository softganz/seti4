<?php
/**
* iMed Org
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function view_imed_org_member($orgInfo) {
	$orgId = $orgInfo->orgid;

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;


	/*
	$ui = new Ui();
	$ui->add('<a href="'.url('imed/org/'.$orgId).'"><i class="icon -home"></i><span>Home</span></a>');
	$ui->add('<a href="'.url('imed/org/'.$orgId.'/member').'"><i class="icon -people"></i><span>Member</span></a>');
	$ui->add('<a href="'.url('imed/org/'.$orgId.'/patient').'"><i class="icon -disabled-people"></i><span>Patient</span></a>');
	$ui->add('<a href="'.url('imed/org/'.$orgId.'/setting').'"><i class="icon -setting"></i><span>Setting</span></a>');
	$ret .= '<nav class="nav -icons -sg-text-center">'.$ui->build().'</nav>';
	*/

	$stmt = 'SELECT
					of.`uid`, of.`membership`, u.`username`, u.`name`, u.`datein`
					FROM %org_officer% of
						LEFT JOIN %users% u USING(`uid`)
					WHERE `orgid` = :orgid';
	$dbs = mydb::select($stmt, ':orgid', $orgId);

	$ui = new Ui(NULL,'ui-card');
	foreach ($dbs->items as $rs) {
		$cardStr = '<h3>'.$rs->name.'</h3>';
		$cardStr .= '<div>'.$rs->membership.'</div>';
		$cardStr .= '<div>'.$rs->datein.'</div>';
		$cardStr .= '<nav class="nav -icons"><ul><li><a class="btn" href="">Message</a></li><li><a class="btn" href="">Message</a></li></ul></nav>';
		$dropUi = new Ui();
		$dropUi->add('<a href="">Make Admin</a>');
		$dropUi->add('<a href="">Make Moderator</a>');
		$dropUi->add('<a href="">Remove from Group</a>');
		$dropUi->add('<a href="">Mute Member</a>');
		$cardStr .= sg_dropbox($dropUi->build(), '{class: "leftside -atright"}');
		$ui->add($cardStr);
	}
	$ret .= $ui->build();

	//$ret .= print_o($dbs);
	return $ret;
}
?>