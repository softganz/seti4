<?php
/**
* Project detail
*
* @param Object $self
* @param Object $projectInfo
* @param Object $options
* @return String
*/
function view_project_nav_default($projectInfo, $options = NULL) {
	$tpid = $projectInfo->tpid;
	$setId = SG\getFirst($projectInfo->info->parent, $projectInfo->tpid);

	page_class('-uokr');

	$ret = '';

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isRight = $projectInfo->RIGHT & _IS_ACCESS;
	$isEdit = $projectInfo->RIGHT & (_IS_ADMIN | _IS_OWNER | _IS_TRAINER);
	$isAddScaleUp = $isEdit || $projectInfo->info->membershipType;

	$ui = new Ui(NULL,'ui-nav -info -sg-text-center');
	$dropboxUi = new Ui(NULL,'ui-dropbox');
	$dropboxMember = new Ui(NULL,'ui-dropbox');

	if ($projectInfo->submodule == 'set') {
		$homeUrl = 'uokr';
		$ui->add('<a href="'.url($homeUrl).'"><i class="icon -material">home</i><span class="">หน้าแรก</span></a>');
		//$ui->add('<a href="'.url('project/set/'.$setId,array('mode'=>'default')).'"><i class="icon -material">view_list</i><span class="">รายชื่อ{tr:โครงการ}</span></a>');
		//$ui->add('<a href="'.url('project/set/'.$setId.'/map').'"><i class="icon -material">place</i><span class="">แผนที่</span></a>');
		//$ui->add('<sep>');
		//$ui->add('<a class="sg-action" href="'.url('project/create/'.$setId, array('abtest'=>'nav','rel'=>'box','startyear'=>date('Y')-5, 'signret' => i()->ok ? NULL : 'project/set/'.$setId)).'" data-rel="box" data-width="640"><i class="icon -material">add_circle</i><span class="">เพิ่มข้อมูลโครงการเดิม</span></a>', '{class: "-add-new"}');
	} else if ($tpid) {
		$homeUrl = 'uokr';
		$ui->add('<a href="'.url($homeUrl).'"><i class="icon -material">home</i><span class="">หน้าแรก</span></a>');
		if ($projectInfo->info->parent) {
			//$ui->add('<a href="'.url('project/set/'.$projectInfo->info->parent).'"><i class="icon -material">view_list</i><span class="">รายชื่อ{tr:โครงการ}</span></a>');
		}
		//$ui->add('<a href="'.url('project/my/project').'"><i class="icon -person"></i><span class="">ของฉัน</span></a>');

		$ui->add('<sep>');
		if ($options->showPrint) {
			$ui->add('<a href="javascript:window.print()"><i class="icon -material">print</i><span>พิมพ์</span></a>');
		}

		if ($isAddScaleUp) {
			$ui->add('<sep>');
			//$ui->add('<a class="sg-action" href="'.url('project/proposal/new/'.$projectInfo->info->parent, array('previd'=>$tpid)).'" data-rel="box" data-width="640" title="การขอทุนขยายผลโครงการ"><i class="icon -material">add_circle_outline</i><span>การขอทุนขยายผลโครงการ</span></a>', '{class: "-add-new"}');
		}

		if ($projectInfo->info->proposalId) {
			$dropboxUi->add('<a href="'.url('project/proposal/'.$tpid).'" title="พัฒนาโครงการ"><i class="icon -material">pageview</i><span>พัฒนาโครงการ</span></a>');
		}
		if ($isRight) {
			$dropboxUi->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.share').'" data-rel="box" data-width="640" data-class-name="-transparent"><i class="icon -material">share</i><span>แบ่งปัน</span></a>');
			$dropboxUi->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.delete').'" data-rel="box" data-width="640"><i class="icon -material">delete</i><span>ลบ{tr:โครงการ}</span></a>');
			$dropboxUi->add('<sep>');
			$dropboxUi->add('<a href="'.url('project/'.$tpid.'/info.dashboard').'"><i class="icon -material">dashboard</i><span>แผงควบคุม</span></a>');
		}

	}

	$dropboxImg = i()->ok ? '<a href="javascript:void(0)"><img class="profile-photo" src="'.model::user_photo(i()->username).'" width="100%" height="100%" /></a>' : '<a class="sg-action" href="'.url('signin',array('signret'=>'project/set/'.$setId)).'" data-rel="box" data-width="640"><i class="icon -material -sg-32">person</i></a>';

	/*
	$ui->add(
		(i()->ok ? '<a class="sg-action" href="'.url('my').'" data-rel="box" data-width="640"><img class="profile-photo" src="'.model::user_photo(i()->username).'" width="32" height="32" /></a>' : '<a class="sg-action" href="'.url('signin').'" data-rel="box" data-width="640"><i class="icon -material -sg-32">person</i>'),
			'{class: "-member"}'
		);
		*/

	$ret .= $ui->build()._NL;

	if (i()->ok) {
		$dropboxMember->add('<a class="sg-action" href="'.url('my').'" title="Member Profile" data-rel="box" data-width="640"><i class="icon -material -sg-16">account_circle</i><span>Account Overview</span></a>');
		$dropboxMember->add('<a class="sg-action" href="'.url('my/change/detail').'" title="Change Profile" data-rel="box" data-width="640"><i class="icon -material -sg-16">settings</i><span>Account Settings</span></a>');
		$dropboxMember->add('<a class="sg-action" href="'.url('my/change/password').'" title="Change Password" data-rel="box" data-width="640"><i class="icon -material -sg-16">vpn_key</i><span>Change Password</span></a>');
		$dropboxMember->add('<a class="sg-action" href="'.url('my/change/photo').'" title="Change Photo" data-rel="box" data-width="640"><i class="icon -material -sg-16">add_a_photo</i><span>Change Photo</span></a>');
		$dropboxMember->add('<sep>');
		$dropboxMember->add('<a class="sg-action" href="'.url('project/'.SG\getFirst($tpid,$projectInfo->set).'/info.u/'.i()->uid).'" data-rel="box" data-width="640" title="{tr:โครงการ}ของฉัน"><i class="icon -material -sg-16">assessment</i><span>{tr:โครงการ}ของฉัน</span></a>');
	}

	if ($isAdmin) {
		$dropboxMember->add('<sep>');
		$dropboxMember->add('<a href="'.url('project/admin').'" title="Admin"><i class="icon -material -sg-16">settings_application</i><span>Project Administrator</span></a>');
	}

	if (i()->ok) {
		$dropboxMember->add('<sep>');

		$dropboxMember->add('<a class="sg-action" href="'.url('signout').'" title="ออกจากระบบสมาชิก" data-title="ออกจากระบบสมาชิก" data-confirm="ต้องการออกจากระบบสมาชิก กรุณายืนยัน?"><i class="icon -material -sg-16">exit_to_app</i><span>ออกจากระบบสมาชิก</span></a>');
	} else {
		$dropboxMember->add('<a href="//hsmi2.psu.ac.th/upload/forum/manual_par.pdf" target="_blank"><i class="icon -material -sg-16">menu_book</i><span>คู่มือการใช้งาน</span></a>');
	}

	if ($dropboxUi->count()) {
		$ret .= sg_dropbox($dropboxUi->build(), array('class' => 'leftside -atright'));
	}

	$ret .= sg_dropbox(
		$dropboxMember->build(),
		array('class' => 'leftside -atright -member', 'link' => $dropboxImg)
	);

	return $ret;
}
?>