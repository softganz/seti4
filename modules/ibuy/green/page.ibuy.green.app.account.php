<?php
/**
* iBuy : Green App Account
* Created 2020-09-18
* Modify  2020-09-18
*
* @param Object $self
* @return String
*
* @usage ibuy/green/app/account
*/

$debug = true;

function ibuy_green_app_account($self) {
	$isAdmin = is_admin('ibuy');
	$isAccessDev = in_array(i()->username, array('softganz','momo'));

	$ui = new Ui();

	if (!i()->ok) return R::View('signform', '{time:-1}');


	$ret = '<div class="imed-patient-photo-wrapper" style="margin:0 0 16px; padding: 16px 0; position:relative; background-color: #fff">';

	$ret .= '<div id="imed-patient-photo" style="width: 196px; height: 196px; margin: 0px auto 32px; display: block; border-radius: 50%; overflow: hidden; border: 2px #eee solid;"><img src="'.model::user_photo(i()->username).'" width="100%" height="100%" /></a></div>';

	$ret .= '<form class="sg-form -upload" method="post" enctype="multipart/form-data" action="'.url('my/api/photo.change').'" data-rel="notify" data-done="load:#main:'.url('ibuy/green/app/account').'" style="position: absolute; top: 16px; right: calc(50% - 112px); padding: 0; margin: 0; background-color: transparent;"><span class="fileinput-button"><i class="icon -material">add_a_photo</i><input type="file" name="photo" class="inline-upload" accept="image/*;capture=camcorder" x-capture="capture" onchange=\'$(this).closest(form).submit(); return false;\' /></span></form>';

	$ret .= '</div>';

	$ret .= '<div class="card-item">';

	$mainUi = new Ui(NULL, 'ui-menu');

	$mainUi->add('<h3>My Account</h3>');
	$mainUi->add('<a class="sg-action" href="'.url('my/profile/info').'" data-rel="box" data-webview="Change Account Profile" data-width="480" data-max-height="80%"><i class="icon -person"></i><span>{tr:Change Your Profile Details}</span></a>');
	if (i()->username != 'demo') {
		$mainUi->add('<a class="sg-action" href="'.url('my/change/password').'" data-rel="box" data-webview="Change Password" data-width="480" data-max-height="80%"><i class="icon -invisible"></i><span>{tr:Change Password}</span></a>');
	}
	$mainUi->add('<a class="sg-action" href="'.url('signout').'" data-rel="none" data-title="ออกจากระบบสมาชิก" data-confirm="ต้องการออกจากระบบสมาชิก กรุณายืนยัน?" data-done="'.url('ibuy/green/app').'"><i class="icon -material">lock_open</i><span>{tr:Sign out}</span></a>');


	$ret .= $mainUi->build();


	$mainUi = new Ui(NULL, 'ui-menu');
	$mainUi->add('<h3>รายการของฉัน</h3>');
	$ret .= $mainUi->build();


	if ($isAccessDev) {
		$actionUi = new Ui(NULL, '-sg-flex');
		$actionUi->addConfig('nav', '{class: "nav -app-menu"}');
		$actionUi->header('<h3>สวนยางยั่งยืน</h3>');
		$actionUi->add('<a class="sg-action" href="'.url('ibuy/green/my/rubber/land').'" data-webview="แปลงสวนยาง"><i class="icon -material">nature_people</i><span>แปลงสวนยาง</span></a>');
		$actionUi->add('<a class="sg-action" href="'.url('ibuy/green/my/tree').'" data-webview="ธนาคารต้นไม้"><i class="icon -material">nature</i><span>ธนาคารต้นไม้</span></a>');
		$actionUi->add('<a class="sg-action" href="'.url('ibuy/green/my/cotree').'" data-webview="พืชร่วมยาง"><i class="icon -material">local_florist</i><span>พืชร่วมยาง</span></a>');
		$actionUi->add('<a class="sg-action" href="'.url('ibuy/green/my/samtree').'" data-webview="พืชแซมยาง"><i class="icon -material">grass</i><span>พืชแซมยาง</span></a>');
		$actionUi->add('<a class="sg-action" href="'.url('ibuy/green/my/animal').'" data-webview="ปศุสัตว์"><i class="icon -material">emoji_nature</i><span>ปศุสัตว์</span></a>');
		$actionUi->add('<a class="sg-action" href="'.url('ibuy/green/my/rubber/buy').'" data-webview="รับซื้อน้ำยาง"><i class="icon -material">money</i><span>รับซื้อน้ำยาง</span></a>');
		$actionUi->add('<a class="sg-action" href="'.url('ibuy/green/my/glorg').'" data-webview="บัญชีต้นทุน"><i class="icon -material">attach_money</i><span>บัญชีต้นทุน</span></a>');

		$ret .= $actionUi->build();
	}

	$mainUi = new Ui(NULL, 'ui-menu');

	$mainUi->add('<h3>บริการของฉัน</h3>');
	$mainUi->add('<a class="sg-action" href="'.url('ibuy/green/my/shop').'" data-webview="จัดการองค์กร/หน้าร้าน"><i class="icon -material">account_balance</i><span>จัดการองค์กร</span></a>');

	$ret .= $mainUi->build();


	if ($isAdmin) {
		$mainUi = new Ui(NULL, 'ui-menu');
		$mainUi->add('<h3>ผู้จัดการระบบ</h3>');
		$ret .= $mainUi->build();
	}

	$ret .= '</div>';

	$ret .= '<style type="text/css">
	</style>';
	return $ret;
}
?>