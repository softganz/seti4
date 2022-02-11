<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_rehab_careplan($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.social.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isMember = $isAdmin || $orgInfo->is->socialtype;
	$isRemovePatient = $isAdmin || in_array($isMember,array('MODERATOR','CM'));
	$isCareManager = $isAdmin || $isMember == 'CM';

	if (!($isAdmin || $isMember)) return message('error','ขออภัย ท่านไม่ได้อยู่ในกลุ่มนี้');

	$ret .= '<h3>แผนการดูแลผู้ป่วยของ'.$orgInfo->name.'</h3>';

	mydb::where('c.`orgid` = :orgid', ':orgid', $orgId);

	if (post('s') == 'new') {
		mydb::value('$ORDER$','sp.`created` DESC');
	} else {
		mydb::value('$ORDER$','CONVERT(p.`name` USING tis620) ASC,  CONVERT(p.`lname` USING tis620) ASC');
	}

	$stmt = 'SELECT
					  c.`psnid`
					, p.`prename`
					, CONCAT(p.`name`," ",p.`lname`) `name`
					, p.`sex`
					, u.`name` `addByName`
					, c.`created`
					, (SELECT COUNT(*) FROM %imed_service% sv WHERE sv.`pid` = p.`psnid`) `serviceAmt`
					, COUNT(*) `planAmt`
					FROM %imed_careplan% c
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %users% u ON u.`uid` = c.`uid`
					%WHERE%
					GROUP BY c.`psnid`
					ORDER BY $ORDER$';
	$dbs = mydb::select($stmt);
	//$ret .= print_o($dbs,'$dbs');

	$ui = new Ui(NULL,'ui-card -patient -sg-flex -co-2');

	$myUid = i()->uid;
	foreach ($dbs->items as $rs) {
		$isRemoveable = $isRemovePatient || $rs->addby == $myUid;
		$cardUi = new Ui();
		$cardUi->add('<a class="btn" href="'.url('imed', ['pid' => $rs->psnid]).'" role="patient" data-pid="'.$rs->psnid.'" class="sg-tooltip" data-url="'.url('imed/patient/shortinfo/'.$rs->psnid).'"><i class="icon -doctor"></i><span>เยี่ยมบ้าน</span></a>');
		$dropUi = new Ui();
		if ($isRemoveable) {
			//$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/patient.remove/'.$rs->psnid).'" data-rel="none" data-removeparent="ul.ui-card.-patient>.ui-item" data-title="ลบผู้ป่วยออกจากกลุ่ม" data-confirm="ต้องการลบผู้ป่วยออกจากกลุ่ม กรุณายืนยัน?">Remove from Group</a>');
		}
		if ($isCareManager) {
			$cardUi->add('<a class="sg-action btn" href="'.url('imed/care/'.$rs->psnid,array('org'=>$orgId)).'" data-rel="#imed-app" data-pid="'.$rs->psnid.'" data-done="moveto:0,1"><i class="icon -material'.($rs->planAmt>0 ? '' : ' -gray').'">assignment</i><span>Care Plan</span></a>');
		}
		if ($dropUi->count()) $cardUi->add(sg_dropbox($dropUi->build()));
		$menu = '<nav class="nav -card -sg-text-right">'
				. $cardUi->build()
				. '</nav>';

		$ui->add('<div class="header -sg-clearfix">'
			. '<a class="" href="'.url('imed', ['pid' => $rs->psnid]).'" role="patient" data-pid="'.$rs->psnid.'" class="sg-tooltip" data-url="'.url('imed/patient/shortinfo/'.$rs->psnid).'">'
			. '<img class="poster-photo -sg-48" src="'.imed_model::patient_photo($rs->psnid).'" width="48" height="48" />'
			. '<span class="poster-name">'.$rs->prename.' '.$rs->name.'</span>'
			. '</a>'
			. '<span class="timestamp">Added by '.$rs->addByName.' on '.sg_date($rs->created, 'ว ดด ปปปป').'</span>'
			. '</div>'
			. '<div class="detail">เยี่ยมบ้าน '.$rs->serviceAmt.' ครั้ง'
			. ($rs->planAmt ? ' '.$rs->planAmt . ' แผนการดูแล' : '')
			. '</div>'
			. $menu
			//. print_o($rs,'$rs')
		);
	}

	if ($dbs->count() % 2) $ui->add('&nbsp;', '{class: "-empty"}');
	$ret .= $ui->build();

	//$ret .= print_o($orgInfo,'$orgInfo');

	return $ret;
}
?>