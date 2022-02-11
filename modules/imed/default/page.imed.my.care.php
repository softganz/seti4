<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_my_care($self) {
	$ret = '';

	$headerUi = new Ui();
	$dropUi = new Ui();
	$dropUi->add('<a class="sg-action" data-rel="#imed-app" href="'.url('imed/report/addqtbyme').'">รายชื่อคนพิการป้อนสอบถาม</a>');
	$headerUi->add(sg_dropbox($dropUi->build()));

	$ret .= '<header class="header -imed-pocenter"><nav class="nav -back"><a class="" href="'.url('imed').'"><i class="icon -material">arrow_back</i></a></nav><h3>สมาชิกในความดูแล</h3><nav class="nav">'.$headerUi->build().'</header>';

	// Get all my matient
	$stmt = 'SELECT DISTINCT
					  ip.`pid` `psnid`
					, p.`prename`
					, CONCAT(p.`name`," ",p.`lname`) `name`
					, ip.`service` `serviceAmt`
					FROM %imed_patient% ip
						LEFT JOIN %db_person% p ON p.`psnid` = ip.`pid`
					WHERE ip.`uid` = :uid AND ip.`service` > 0
					ORDER BY CONVERT(`name` USING tis620) ASC';
	$dbs = mydb::select($stmt,':uid',i()->uid);

	$ui = new Ui(NULL,'ui-card -patient -sg-flex -co-2');

	$myUid = i()->uid;
	foreach ($dbs->items as $rs) {
		$isRemoveable = $isRemovePatient || $rs->addby == $myUid;
		$cardUi = new Ui();
		$cardUi->add('<a class="btn" href="'.url('imed', ['pid' => $rs->psnid]).'" role="patient" data-pid="'.$rs->psnid.'"><i class="icon -doctor"></i><span>เยี่ยมบ้าน</span></a>');
		$dropUi = new Ui();
		if ($dropUi->count()) $cardUi->add(sg_dropbox($dropUi->build()));
		$menu = '<nav class="nav -card -sg-text-right">'
				. $cardUi->build()
				. '</nav>';

		$ui->add('<div class="header -sg-clearfix">'
			. '<a class="" href="'.url('imed', ['pid' => $rs->psnid]).'" role="patient" data-pid="'.$rs->psnid.'">'
			. '<img class="poster-photo -sg-48" src="'.imed_model::patient_photo($rs->psnid).'" width="48" height="48" />'
			. '<span class="poster-name">'.$rs->prename.' '.$rs->name.'</span>'
			. '</a>'
			. '<span class="timestamp">Added by '.$rs->addByName.' on '.sg_date($rs->created, 'ว ดด ปปปป').'</span>'
			. '</div>'
			. '<div class="detail">เยี่ยมบ้าน '.$rs->serviceAmt.' ครั้ง</div>'
			. $menu
			//. print_o($rs,'$rs')
		);
	}

	if ($dbs->count() % 2) $ui->add('&nbsp;', '{class: "-empty"}');
	$ret .= $ui->build();



	/*

	$ret.='<div id="myFriendPartient" class="myFriendPartient -hidden"><h3>สมาชิกในความดูแลของเพื่อน</h3>ไม่มี	</div><!-- myFriendPartient -->'._NL;

	$ret.='<div id="myGroupPartient" class="myGroupPartient -hidden"><h3>สมาชิกในความดูแลของกลุ่ม</h3>ไม่มี</div><!-- myGroupPartient -->'._NL;
	*/
	return $ret;
}
?>