<?php
/**
* iMed :: Social Group Member List
* Created 2019-04-29
* Modify  2020-12-15
*
* @param Object $self
* @param Int $orgId
* @return String
*
* @usage imed/social/{id}/member
*/

$debug = true;

function imed_social_member($self, $orgId = NULL) {
	// Data Model
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.social.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isMember = $orgInfo->is->socialtype;


	$groupMemberShipType = array(
		'ADMIN'=>'ADMIN',
		'MODERATOR'=>'MODERATOR',
		'CM'=>'CARE MANAGER',
		'CG'=>'CARE GIVER',
		'PHYSIOTHERAPIST'=>'นักกายภาพบำบัด',
		'PHYSIOTHERAPIST ASS'=>'ผู้ช่วยนักกายภาพบำบัด',
		'VHVOLUNTEER EXPERT'=>'อสม.เชี่ยวชาญ',
		'VHVOLUNTEER'=>'อสม.',
		'อพม.' => 'อพม.',
		'อบต.' => 'อบต.',
		'รพ.สต.' => 'รพ.สต.',
		'ชมรมผู้สูงอายุ' => 'ชมรมผู้สูงอายุ',
		'อาสาสมัครบริบาล' => 'อาสาสมัครบริบาล',
		'VOLUNTEER'=>'จิตอาสา',
		'REGULAR MEMBER'=>'REGULAR MEMBER',
	);

	$groupMemberShipOption = '';
	foreach ($groupMemberShipType as $key => $value) {
		$groupMemberShipOption .= '<option value="'.$key.'"'.($key =='REGULAR MEMBER' ? ' selected="selected"' : '').'>'.$value.'</option>';
	}

	$stmt = 'SELECT
			sm.*, u.`name`, u.`username`, ua.`name` `addByName`
		FROM %imed_socialmember% sm
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %users% ua ON ua.`uid` = sm.`addby`
		WHERE `orgid` = :orgid
		ORDER BY
			CASE
				WHEN `membership` = "ADMIN" THEN 1
				WHEN `membership` = "MODERATOR" THEN 2
				ELSE 3
			END
		, CONVERT(u.`name` USING tis620) ASC
		';

	$memberDbs = mydb::select($stmt, ':orgid', $orgId);

	$stmt = 'SELECT b.`fldref` `orgid`, b.`keyid` `uid`, u.`username`, u.`name`, b.`flddata` `data`
		FROM %bigdata% b
			LEFT JOIN %users% u ON u.`uid` = b.`keyid`
		WHERE `keyname` = "imed" AND `fldname` = "group.invite" AND `fldref` = :orgid';
	$watingInvite = mydb::select($stmt, ':orgid', $orgId);



	// View Model
	$ret = '<section id="imed-social-member" data-url="'.url('imed/social/'.$orgId.'/member').'" style="flex: 1;"><!-- Start of imed-social-member -->';

	$ret .= '<header class="header -box"><h3>'.$memberDbs->count().' Members in Group</h3></header>';

	if ($isAdmin) {
		$form = new Form(NULL, url('imed/social/'.$orgId.'/invite.add'),'add-member', 'sg-form -inlineitem imed-social-patient-form');
		$form->addData('checkValid', true);
		$form->addData('rel','none');
		$form->addData('done', 'load->replace:#imed-social-member');
		//$form->addData('width', 512);

		$form->addField('uid',array('type' => 'hidden', 'name' => 'uid', 'id' => 'uid'));

		$form->addField(
			'name',
			array(
				'type' => 'text',
				'label' => tr('INVITE MEMBERS'),
				'class' => 'sg-autocomplete -fill',
				'require' => true,
				'placeholder' => '+ ชื่อจริง หรือ อีเมล์ ของสมาชิกที่ต้องการเชิญเข้ากลุ่ม',
				'posttext' => '<div class="input-append"><span><select class="form-select" name="membership">'.$groupMemberShipOption.'</select></span><span><button class="btn -primary"><i class="icon -material">add</i></button></span></div>',
				'container' => '{class: "-group -label-in"}',
				'attr' => array(
					'data-query'=>url('api/user'),
					'data-altfld' => 'uid',
				),
			)
		);

		/*
		$form->addField(
			'membership',
			array(
				'type' => 'select',
				'options' => $groupMemberShipType,
				'value' => 'REGULAR MEMBER',
			)
		);

		$form->addField(
			'button',
			array(
				'type' => 'button',
				'value' => '<i class="icon -addbig -white"></i>',
			)
		);
		*/

		$ret .= $form->build();
	}


	$memberCard = new Ui('div','ui-card -member -sg-flex -co-2');

	foreach ($memberDbs->items as $rs) {
		if ($isAdmin) {
			$headerUi = new Ui();
			$dropUi = new Ui();
			if ($rs->membership == 'ADMIN') {
				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.type/'.$rs->uid, array('ty'=>'MODERATOR')).'" data-rel="none" data-done="load->replace:#imed-social-member">Change to Moderator</a>');
				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.type/'.$rs->uid, array('ty'=>'REGULAR MEMBER')).'" data-rel="none" data-done="load->replace:#imed-social-member">Remove as Admin</a>');
				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.remove/'.$rs->uid).'" data-rel="none" data-removeparent="ui-card.-member>.ui-item" data-title="ลบสมาชิกออกจากกลุ่ม" data-confirm="ต้องการลบสมาชิกออกจากกลุ่ม กรุณายืนยัน?">Leave Group</a>');
			} else {
				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.type/'.$rs->uid, array('ty'=>'ADMIN')).'" data-rel="none" data-done="load->replace:#imed-social-member">Make Admin</a>');
				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.type/'.$rs->uid, array('ty'=>'MODERATOR')).'" data-rel="none" data-done="load->replace:#imed-social-member">Make Moderator</a>');
				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.type/'.$rs->uid, array('ty'=>'CM')).'" data-rel="none" data-done="load->replace:#imed-social-member">Make '.$groupMemberShipType['CM'].'</a>');
				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.type/'.$rs->uid, array('ty'=>'CG')).'" data-rel="none" data-done="load->replace:#imed-social-member">Make '.$groupMemberShipType['CG'].'</a>');

				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.type/'.$rs->uid, array('ty'=>'PHYSIOTHERAPIST')).'" data-rel="none" data-done="load->replace:#imed-social-member">Make นักกายภาพบำบัด</a>');
				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.type/'.$rs->uid, array('ty'=>'PHYSIOTHERAPIST ASS')).'" data-rel="none" data-done="load->replace:#imed-social-member">Make ผู้ช่วยนักกายภาพบำบัด</a>');

				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.type/'.$rs->uid, array('ty'=>'VHVOLUNTEER EXPERT')).'" data-rel="none" data-done="load->replace:#imed-social-member">Make อสม.เชี่ยวชาญ</a>');
				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.type/'.$rs->uid, array('ty'=>'VHVOLUNTEER')).'" data-rel="none" data-done="load->replace:#imed-social-member">Make อสม.</a>');

				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.type/'.$rs->uid, array('ty'=>'อพม.')).'" data-rel="none" data-done="load->replace:#imed-social-member">Make อพม.</a>');
				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.type/'.$rs->uid, array('ty'=>'อบต.')).'" data-rel="none" data-done="load->replace:#imed-social-member">Make อบต.</a>');
				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.type/'.$rs->uid, array('ty'=>'รพ.สต.')).'" data-rel="none" data-done="load->replace:#imed-social-member">Make รพ.สต.</a>');
				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.type/'.$rs->uid, array('ty'=>'ชมรมผู้สูงอายุ')).'" data-rel="none" data-done="load->replace:#imed-social-member">Make ชมรมผู้สูงอายุ</a>');
				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.type/'.$rs->uid, array('ty'=>'อาสาสมัครบริบาล')).'" data-rel="none" data-done="load->replace:#imed-social-member">Make อาสาสมัครบริบาล</a>');



				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.type/'.$rs->uid, array('ty'=>'VOLUNTEER')).'" data-rel="none" data-done="load->replace:#imed-social-member">Make จิตอาสา</a>');

				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.type/'.$rs->uid, array('ty'=>'REGULAR MEMBER')).'" data-rel="none" data-done="load->replace:#imed-social-member">Make Normal Member</a>');
				$dropUi->add('<sep>');
				if ($rs->status == -1) {
					$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.unmute/'.$rs->uid).'" data-rel="none" data-done="load->replace:#imed-social-member">Unmute Member</a>');
				} else {
					$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.mute/'.$rs->uid).'" data-rel="none" data-done="load->replace:#imed-social-member">Mute Member</a>');
				}
				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member.remove/'.$rs->uid).'" data-rel="none" data-removeparent=".ui-card.-member>.ui-item" data-title="ลบสมาชิกออกจากกลุ่ม" data-confirm="ต้องการลบสมาชิกออกจากกลุ่ม กรุณายืนยัน?">Remove from Group</a>');
			}

			if ($dropUi->count()) $headerUi->add(sg_dropbox($dropUi->build()));
			$headerMenu = '<nav class="nav -header -sg-text-right">'.$headerUi->build().'</nav>';
		}

		$memberCard->add(
			'<div class="header -sg-clearfix">'
			. '<span class="profile">'
			. '<a class="sg-action" href="'.url('imed/u/'.$rs->uid).'" data-rel="box" data-width="400" data-height="400">'
			. '<img class="poster-photo -sg-48" src="'.model::user_photo($rs->username).'" width="48" height="48" />'
			. '<span class="poster-name">'.$rs->name.'</span>'
			. '</a>'
			. '<span class="timestamp"><i class="icon -material">'.($rs->membership == 'ADMIN' ? 'star' : 'person_outline').'</i>'.$groupMemberShipType[$rs->membership]
			. ($orgInfo->uid == $rs->uid ? ' (Owner)' : '')
			.'</span>'
			. '<span class="timestamp">Added by '.$rs->addByName.' on '.sg_date($rs->created, 'ว ดด ปปปป').'</span>'
			. '</span>'
			. $headerMenu
			. '</div>'
			//. print_o($rs,'$rs')
		);
	}
	if ($memberDbs->count() % 2) $memberCard->add('&nbsp;', '{class: "-empty"}');

	$ret .= $memberCard->build();


	// Show waiting invite
	if ($watingInvite->count()) {
		$ret .= '<header class="header"><h4>สมาชิกรอตอบรับเข้ากลุ่ม</h4></header>';
		$waitingCard = new Ui('div','ui-card -member -invite -sg-flex -co-2');
		foreach ($watingInvite->items as $rs) {
			$data = SG\json_decode($rs->data);

			$waitingHeaderMenu = new Ui();
			$waitingHeaderMenu->config('container', '{tag: "nav", class: "nav -header -sg-text-right"}');
			if ($isAdmin) {
				$dropUi = new Ui();
				$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/invite.remove/'.$rs->uid).'" data-rel="none" data-done="remove:parent .ui-card.-member.-invite>.ui-item" data-title="ยกเลิกการเชิญเข้ากลุ่ม" data-confirm="ต้องการยกเลิกการเชิญสมาชิกเข้ากลุ่ม กรุณายืนยัน?"><i class="icon -material">cancel</i><span>ยกเลิกการเชิญเข้ากลุ่ม</span></a>');
				if ($dropUi->count()) $waitingHeaderMenu->add(sg_dropbox($dropUi->build()));
			}

			$waitingCard->add(
				'<div class="header -sg-clearfix">'
				. '<span class="profile">'
				. '<a class="sg-action" href="'.url('imed/u/'.$rs->uid).'" data-rel="box" data-width="400" data-height="400">'
				. '<img class="poster-photo -sg-48" src="'.model::user_photo($rs->username).'" width="48" height="48" />'
				. '<span class="poster-name">'.$rs->name.'</span>'
				. '</a>'
				. '<span class="timestamp"><i class="icon -material">'.($data->membership == 'ADMIN' ? 'star' : 'person_outline').'</i>'.$groupMemberShipType[$data->membership]
				.'</span>'
				. '<span class="timestamp">Invite by '.$data->inviteByName.' on '.sg_date($rs->created, 'ว ดด ปปปป').'</span>'
				. '</span>'
				. $waitingHeaderMenu->build()
				. '</div>'
				//. print_o($rs,'$rs')
			);

		}
		$ret .= $waitingCard->build();
	}

	//$ret .= print_o($watingInvite);
	//$ret .= print_o($orgInfo,'$orgInfo');
	$ret .= '<style>
	#add-member .title {}
	#edit-membership {width:60px;}</style>';

	$ret .= '<!-- End of imed-social-member --></section>';

	return $ret;
}
?>