<?php
/**
* green :: Manage Group Member
* Created 2020-12-22
* Modify  2020-12-22
*
* @param Object $self
* @return String
*
* @usage green/my/manage/member
*/

$debug = true;

import('model:org.php');

function green_my_manage_member($self) {
	// Data Model
	$shopId = ($shopInfo = R::Model('green.shop.get', 'my')) ? $shopInfo->shopId : location('green/my/shop');

	$isAdmin = is_admin('green') || $shopInfo->RIGHT & _IS_ADMIN;
	$isEdit = $isAdmin || $shopInfo->RIGHT & _IS_EDITABLE;

	$officers = OrgModel::officers($shopId);

	$groupMemberShipType = array(
		'ADMIN'=>'แอดมิน',
		'MANAGER'=>'ผู้จัดการกลุ่ม',
		'OFFICER'=>'เจ้าหน้าที่',
		'NETWORK'=>'เครือข่าย',
		'MEMBER'=>'สมาชิกทั่วไป',
	);

	$groupMemberShipOption = '';
	foreach ($groupMemberShipType as $key => $value) {
		$groupMemberShipOption .= '<option value="'.$key.'"'.($key =='NETWORK' ? ' selected="selected"' : '').'>'.$value.'</option>';
	}



	// View Model
	$ret = '<section id="imed-social-member" data-url="'.url('green/my/manage/member').'" style="flex: 1;"><!-- Start of imed-social-member -->';
	//$ret .= '<header class="header"><h3>สมาชิกกลุ่ม</h3></header>';


	if ($isAdmin) {
		$form = new Form(NULL, url('green/my/info/officer.add/'.$shopId),'add-member', 'sg-form green-group-member-form');
		$form->addData('checkValid', true);
		$form->addData('rel','notify');
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

		$ret .= $form->build();
	}


	$memberCard = new Ui('div','ui-card -member');

	foreach ($officers->items as $rs) {
		if ($isAdmin) {
			$headerUi = new Ui();
			$dropUi = new Ui();
			$dropUi->add('<a class="sg-action" href="'.url('green/my/info/officer.remove/'.$shopId,array('uid'=>$rs->uid)).'" data-rel="none" data-done="remove:parent .ui-card.-member>.ui-item" data-title="ลบสมาชิกออกจากกลุ่ม" data-confirm="ต้องการลบสมาชิกออกจากกลุ่ม กรุณายืนยัน?"><i class="icon -material">delete</i><span>ลบออกจากกลุ่ม</span></a>');

			if ($dropUi->count()) $headerUi->add(sg_dropbox($dropUi->build()));
			$headerMenu = '<nav class="nav -header -sg-text-right">'.$headerUi->build().'</nav>';
		}

		$memberCard->add(
			'<div class="header -sg-clearfix">'
			. '<span class="profile">'
			. '<img class="poster-photo -sg-48" src="'.model::user_photo($rs->username).'" width="48" height="48" />'
			. '<span class="poster-name">'.$rs->name.'</span>'
			//. '</a>'
			. '<span class="timestamp"><i class="icon -material">'.($rs->membership == 'ADMIN' ? 'star' : 'person_outline').'</i>'.$groupMemberShipType[$rs->membership]
			. ($orgInfo->uid == $rs->uid ? ' (Owner)' : '')
			.'</span>'
			//. '<span class="timestamp">Added by '.$rs->addByName.' on '.sg_date($rs->created, 'ว ดด ปปปป').'</span>'
			. '</span>'
			. $headerMenu
			. '</div>'
			//. print_o($rs,'$rs')
		);
	}

	$ret .= $memberCard->build();

	//$ret .= print_o($officers,'$officers');

	/*
	$tables = new Table();
	$tables->thead = array('ชื่อ-นามสกุล', 'center' => 'สมาชิก', 'date -hover-parent' => 'วันที่');
	if ($officers) {
		foreach ($officers->items as $rs) {
			$ui = new Ui();
			if ($isAdmin) {
				$ui->add('<a class="sg-action" href="'.url('green/my/info/officer.remove/'.$shopId,array('uid'=>$rs->uid)).'" data-rel="notify" data-removeparent="tr" data-confirm="ต้องการลบเจ้าหน้าที่ออกจากองค์กร?"><i class="icon -cancel"></i></a>');
			}

			$menu = '<nav class="nav iconset -hover">'.$ui->build().'</nav>';

			$tables->rows[] = array(
				$rs->name,
				$rs->membership,
				sg_date($rs->datein,'ว ดด ปปปป')
				.$menu
			);
		}
	}
	*/

	/*
	if ($isAdmin) {
		$ret .= '<form class="sg-form" action="'.url('green/my/info/officer.add/'.$shopId).'" method="post" data-rel="notify" data-done="load:#main"><input id="officer-uid" type="hidden" name="uid" value="" />';
		$tables->rows[] = array(
			'<input type="text" name="orgname" class="sg-autocomplete form-text -fill" data-query="'.url('api/user').'" data-altfld="officer-uid" placeholder="ป้อนชื่อสมาชิก" data-select="label" />',
			'<select name="membership" class="form-select -fill"><option value="ADMIN">Admin</option><option value="MANAGER">Manager</option><option value="OFFICER">Officer</option><option value="NETWORK">Network</option><option value="MEMBER" selected="selected">Regular Member</option></select>',
			'<button class="btn"><i class="icon -add"></i><span>เพิ่มเจ้าหน้าที่</span></button>'
		);
	}
	*/

	//$ret .= $tables->build();

	//if ($isAdmin) $ret.='</form>';
	$ret .= '</section>';


	//$ret .= print_o($shopInfo, '$shopInfo');

	//$ret .= '<nav class="nav -page -sg-text-right"><a class="sg-action btn" href="'.url('green/shop/'.$shopId.'/field.add').'" data-rel="box" data-width="640"><i class="icon -material">add_circle_outline</i><span>เพิ่มสินค้า</span></a></nav>';

	return $ret;
}
?>