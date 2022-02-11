<?php
/**
* iMed :: Group My Invite List
* Created 2020-12-15
* Modify  2020-12-15
*
* @param Object $self
* @return String
*
* @usage imed/social/my/invite
*/

$debug = true;

function imed_social_my_invite($self) {
	$ret = '';

	// Check group invite
	$stmt = 'SELECT
		b.`fldref` `orgid`, b.`keyid` `uid`
		, u.`username`, u.`name`
		, o.`name` `orgName`
		, b.`flddata` `data`
		FROM %bigdata% b
			LEFT JOIN %users% u ON u.`uid` = b.`keyid`
			LEFT JOIN %db_org% o ON o.`orgid` = b.`fldref`
		WHERE b.`keyname` = "imed" AND b.`fldname` = "group.invite" AND b.`keyid` = :uid';
	$watingInvite = mydb::select($stmt, ':uid', i()->uid);
	$watingInviteCard = new Ui(NULL, 'ui-card -patient');
	foreach ($watingInvite->items as $rs) {
		$watingInviteCard->add(
			'<div class="detail">'
			. 'คำเชิญเข้ากลุ่ม <b>"'.$rs->orgName.'"</b>'
			. '<nav class="nav -card -sg-text-center"><a class="sg-action btn -link -cancel" href="'.url('imed/social/'.$rs->orgid.'/invite.reject').'" data-rel="none" data-done="remove:parent .ui-item"><i class="icon -material">cancel</i><span>ปฎิเสธ</span></a> <a class="sg-action btn -primary" href="'.url('imed/social/'.$rs->orgid.'/invite.accept').'" data-rel="none" data-done="remove:parent .ui-item"><i class="icon -material">done</i><span>ตอบรับเข้ากลุ่ม</span></a></nav>'
			//. print_o($rs,'$rs')
			. '</div>'
		);
	}
	$ret .= $watingInviteCard->build();
	//$ret .= print_o($watingInvite);

	return $ret;
}
?>