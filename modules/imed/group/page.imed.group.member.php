<?php
/**
* iMed :: Group Member
* Created 2021-08-17
* Modify  2021-08-17
*
* @param Object $orgInfo
* @return Widget
*
* @usage imed/group/{id}/member
*/

$debug = true;

class ImedGroupMember extends Page {
	var $refApp;
	var $orgId;
	var $orgInfo;
	var $urlView = 'imed/group/';
	var $urlPatientView;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
		parent::__construct();
	}

	function build() {
		$defaults = '{debug:false, showEdit: true, page: "web"}';
		$options = SG\json_decode($options,$defaults);
		$debug = $options->debug;

		$orgInfo = $this->orgInfo;
		$orgId = $orgInfo->orgid;

		if (!$orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

		$this->isAdmin = $isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
		$isMember = $isAdmin || $orgInfo->is->socialtype;
		$isRemovePatient = $isAdmin || in_array($orgInfo->is->socialtype,array('MODERATOR','CM'));
		$this->isCareManager = $isAdmin || in_array($isMember,array('CM','MODERATOR','PHYSIOTHERAPIST'));

		if (!($isAdmin || $isMember)) return message('error','ขออภัย ท่านไม่ได้อยู่ในกลุ่มนี้');

		$groupMemberShipType = [
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
		];

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

		$memberDbs = mydb::select($stmt, ':orgid', $this->orgId);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => '@'.$this->orgInfo->name,
			]), // AppBar
			'body' => new Container([
				'tagName' => 'section',
				'id' => 'imed-group-member',
				'dataUrl' => url($this->urlView.$orgId.'/member'),
				'children' => [
					new Card([
						'children' => [
							new ListTile([
								'title' => ' Members - '.$memberDbs->count().' in Group',
								'leading' => '<i class="icon -material">group</i>',
							]), //
						], // children
					]), // Card

					$isAdmin ? $this->_addMemberForm($groupMemberShipType) : NULL,
					$this->_members($memberDbs, $groupMemberShipType),
					$isAdmin ? $this->_waitingMembers($groupMemberShipType) : NULL,
					// R::Page('imed.social.member',NULL, $this->orgInfo),
				],
			]),
		]);
	}

	function _addMemberForm($groupMemberShipType) {
		$groupMemberShipOption = '';
		foreach ($groupMemberShipType as $key => $value) {
			$groupMemberShipOption .= '<option value="'.$key.'"'.($key =='REGULAR MEMBER' ? ' selected="selected"' : '').'>'.$value.'</option>';
		}
		return new Form([
			'action' => url('imed/social/'.$this->orgId.'/invite.add'),
			'id' => 'add-member',
			'class' => 'sg-form -inlineitem imed-social-patient-form',
			'checkValid' => true,
			'rel' => 'none',
			'done' => 'load->replace:#imed-group-member',
			'children' => [
				'uid' => ['type' => 'hidden', 'name' => 'uid', 'id' => 'uid'],
				'name' => [
					'type' => 'text',
					'label' => tr('INVITE MEMBERS'),
					'class' => 'sg-autocomplete -fill',
					'require' => true,
					'placeholder' => '+ ชื่อจริง หรือ อีเมล์ ของสมาชิกที่ต้องการเชิญเข้ากลุ่ม',
					'posttext' => '<div class="input-append"><span><select class="form-select" name="membership">'.$groupMemberShipOption.'</select></span><span><button class="btn -primary"><i class="icon -material">add</i></button></span></div>',
					'container' => '{class: "-group -label-in"}',
					'attr' => [
						'data-query'=>url('api/user'),
						'data-altfld' => 'uid',
					],
				],
			],
		]);
	}

	function _members($memberDbs, $groupMemberShipType) {
		return new Widget([
			'children' => (function($members, $groupMemberShipType) {
				$widgets = [];
					$doneChangeType = 'load->replace:#imed-group-member';
					foreach ($members as $rs) {
						$dropUi = new DropBox();
						if ($this->isAdmin) {
							if ($rs->membership == 'ADMIN') {
								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.type/'.$rs->uid, array('ty'=>'MODERATOR')).'" data-rel="none" data-done="'.$doneChangeType.'">Change to Moderator</a>');
								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.type/'.$rs->uid, array('ty'=>'REGULAR MEMBER')).'" data-rel="none" data-done="'.$doneChangeType.'">Remove as Admin</a>');
								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.remove/'.$rs->uid).'" data-rel="none" data-removeparent="ui-card.-member>.ui-item" data-title="ลบสมาชิกออกจากกลุ่ม" data-confirm="ต้องการลบสมาชิกออกจากกลุ่ม กรุณายืนยัน?">Leave Group</a>');
							} else {
								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.type/'.$rs->uid, array('ty'=>'ADMIN')).'" data-rel="none" data-done="'.$doneChangeType.'">Make Admin</a>');
								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.type/'.$rs->uid, array('ty'=>'MODERATOR')).'" data-rel="none" data-done="'.$doneChangeType.'">Make Moderator</a>');
								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.type/'.$rs->uid, array('ty'=>'CM')).'" data-rel="none" data-done="'.$doneChangeType.'">Make '.$groupMemberShipType['CM'].'</a>');
								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.type/'.$rs->uid, array('ty'=>'CG')).'" data-rel="none" data-done="'.$doneChangeType.'">Make '.$groupMemberShipType['CG'].'</a>');

								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.type/'.$rs->uid, array('ty'=>'PHYSIOTHERAPIST')).'" data-rel="none" data-done="'.$doneChangeType.'">Make นักกายภาพบำบัด</a>');
								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.type/'.$rs->uid, array('ty'=>'PHYSIOTHERAPIST ASS')).'" data-rel="none" data-done="'.$doneChangeType.'">Make ผู้ช่วยนักกายภาพบำบัด</a>');

								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.type/'.$rs->uid, array('ty'=>'VHVOLUNTEER EXPERT')).'" data-rel="none" data-done="'.$doneChangeType.'">Make อสม.เชี่ยวชาญ</a>');
								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.type/'.$rs->uid, array('ty'=>'VHVOLUNTEER')).'" data-rel="none" data-done="'.$doneChangeType.'">Make อสม.</a>');

								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.type/'.$rs->uid, array('ty'=>'อพม.')).'" data-rel="none" data-done="'.$doneChangeType.'">Make อพม.</a>');
								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.type/'.$rs->uid, array('ty'=>'อบต.')).'" data-rel="none" data-done="'.$doneChangeType.'">Make อบต.</a>');
								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.type/'.$rs->uid, array('ty'=>'รพ.สต.')).'" data-rel="none" data-done="'.$doneChangeType.'">Make รพ.สต.</a>');
								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.type/'.$rs->uid, array('ty'=>'ชมรมผู้สูงอายุ')).'" data-rel="none" data-done="'.$doneChangeType.'">Make ชมรมผู้สูงอายุ</a>');
								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.type/'.$rs->uid, array('ty'=>'อาสาสมัครบริบาล')).'" data-rel="none" data-done="'.$doneChangeType.'">Make อาสาสมัครบริบาล</a>');



								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.type/'.$rs->uid, array('ty'=>'VOLUNTEER')).'" data-rel="none" data-done="'.$doneChangeType.'">Make จิตอาสา</a>');

								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.type/'.$rs->uid, array('ty'=>'REGULAR MEMBER')).'" data-rel="none" data-done="'.$doneChangeType.'">Make Normal Member</a>');
								$dropUi->add('<sep>');
								if ($rs->status == -1) {
									$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.unmute/'.$rs->uid).'" data-rel="none" data-done="'.$doneChangeType.'">Unmute Member</a>');
								} else {
									$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.mute/'.$rs->uid).'" data-rel="none" data-done="'.$doneChangeType.'">Mute Member</a>');
								}
								$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member.remove/'.$rs->uid).'" data-rel="none" data-removeparent=".ui-card.-member>.ui-item" data-title="ลบสมาชิกออกจากกลุ่ม" data-confirm="ต้องการลบสมาชิกออกจากกลุ่ม กรุณายืนยัน?">Remove from Group</a>');
							}
						}

						$memberLink = '<a class="sg-action" href="'.url('imed/u/'.$rs->uid, ['ref' => $this->refApp]).'" data-rel="box" data-width="full">';

						$widgets[] = new ListTile([
							'crossAxisAlignment' => 'start',
							'style' => 'margin-bottom: 8px;',
							'title' => $memberLink.$rs->name.'</a>',
							'subtitle' => '<i class="icon -material">'.($rs->membership == 'ADMIN' ? 'star' : 'person_outline').'</i>'.$groupMemberShipType[$rs->membership]
							. ($this->orgInfo->uid == $rs->uid ? ' (Owner)' : '')
							. '<br />Added by '.$rs->addByName.' on '.sg_date($rs->created, 'ว ดด ปปปป'),
							'leading' => $memberLink.'<img class="profile-photo -sg-48" src="'.model::user_photo($rs->username).'" width="48" height="48" /></a>',
							'trailing' => $dropUi,
						]);
					}
							'<div class="header -sg-clearfix">'
							. '<span class="profile">'
							. '<img class="poster-photo -sg-48" src="'.model::user_photo($rs->username).'" width="48" height="48" />'
							. '<span class="poster-name">'.$rs->name.'</span>'
							. '</a>'
							. '<span class="timestamp"><i class="icon -material">'.($rs->membership == 'ADMIN' ? 'star' : 'person_outline').'</i>'.$groupMemberShipType[$rs->membership]
							. ($this->orgInfo->uid == $rs->uid ? ' (Owner)' : '')
							.'</span>'
							. '<span class="timestamp">Added by '.$rs->addByName.' on '.sg_date($rs->created, 'ว ดด ปปปป').'</span>'
							. '</span>'
							. $headerMenu
							. '</div>';
				return $widgets;
			})($memberDbs->items, $groupMemberShipType),
		]);
		$memberCard = new Ui('div','ui-card -member -sg-flex -co-2');


		if ($memberDbs->count() % 2) $memberCard->add('&nbsp;', '{class: "-empty"}');

		return $memberCard;
	}

	// Show waiting invite
	function _waitingMembers($groupMemberShipType) {
		$stmt = 'SELECT b.`fldref` `orgid`, b.`keyid` `uid`, u.`username`, u.`name`, b.`flddata` `data`
			FROM %bigdata% b
				LEFT JOIN %users% u ON u.`uid` = b.`keyid`
			WHERE `keyname` = "imed" AND `fldname` = "group.invite" AND `fldref` = :orgid';
		$watingInvite = mydb::select($stmt, ':orgid', $this->orgId);

		if (!$watingInvite->count()) return;

		return new Container([
			'children' => [
				new ListTile([
					'style' => 'margin-bottom: 8px;',
					'title' => 'สมาชิกรอตอบรับเข้ากลุ่ม',
					'leading' => '<i class="icon -material">pending_actions</i>',
				]),
				new Widget([
					'children' => (function($watings, $groupMemberShipType) {
						$widgets = [];
						foreach ($watings as $rs) {
							$data = SG\json_decode($rs->data);

							$memberLink = '<a class="sg-action" href="'.url('imed/u/'.$rs->uid).'" data-rel="box" data-width="full">';
							$widgets[] = new ListTile([
								'crossAxisAlignment' => 'start',
								'style' => 'margin-bottom: 8px;',
								'title' => $memberLink.$rs->name.'</a>',
								'subtitle' => '<i class="icon -material">'.($data->membership == 'ADMIN' ? 'star' : 'person_outline').'</i>'.$groupMemberShipType[$data->membership].'<br />Invite by '.$data->inviteByName.' on '.sg_date($rs->created, 'ว ดด ปปปป'),
								'leading' => $memberLink.'<img class="profile-photo -sg-32" src="'.model::user_photo($rs->username).'" /></a>',
								'trailing' => $this->isAdmin ? new DropBox([
									'children' => [
										'<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/invite.remove/'.$rs->uid).'" data-rel="none" data-done="remove:parent .widget-listtile" data-title="ยกเลิกการเชิญเข้ากลุ่ม" data-confirm="ต้องการยกเลิกการเชิญสมาชิกเข้ากลุ่ม กรุณายืนยัน?"><i class="icon -material">cancel</i><span>ยกเลิกการเชิญเข้ากลุ่ม</span></a>',
									],
								]) : NULL,
							]);
						}
						return $widgets;
					})($watingInvite->items, $groupMemberShipType),
				]), // Widget
			], // children
		]);
	}
}
?>