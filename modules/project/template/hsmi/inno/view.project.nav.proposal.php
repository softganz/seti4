<?php
/**
* Project detail
*
* @param Object $self
* @param Object $proposalInfo
* @param Object $options
* @return String
*/
function view_project_nav_proposal($proposalInfo, $options = NULL) {
	$tpid = $proposalInfo->tpid;

	$setId = SG\getFirst($proposalInfo->set, $proposalInfo->info->parent, $tpid );

	page_class('-inno');

	$ret = '';

	$isAdmin = $proposalInfo->RIGHT & _IS_ADMIN;
	$isRight = $proposalInfo->RIGHT & _IS_ACCESS;
	$isEdit = $proposalInfo->RIGHT & (_IS_ADMIN | _IS_OWNER | _IS_TRAINER);

	$ui = new Ui(NULL,'ui-nav -info -sg-text-center');
	$dropboxUi = new Ui(NULL,'ui-dropbox');
	$dropboxMember = new Ui(NULL,'ui-dropbox');

	if ($tpid) {
		if ($proposalInfo->info->thread) {
			$ui->add('<a href="'.url('project/'.$proposalInfo->info->thread).'"><i class="icon -material">arrow_back</i><span>BACK</span></a>');
		} else if ($proposalInfo->info->parent){
			$ui->add('<a href="'.url('project/set/'.$proposalInfo->info->parent).'"><i class="icon -material">arrow_back</i><span>BACK</span></a>');
		}

		$ui->add('<sep>');
		$statusList = project_base::$statusList;

		if ($isAdmin) {
			$ui->add('<a class="sg-action" href="'.url('project/proposal/'.$tpid.'/info.status').'" title="สถานะ : '.$statusList[$proposalInfo->info->status].(in_array($proposalInfo->info->status, array(1,3))?'':' (ไม่สามารถแก้ไข)').'" data-rel="box" data-width="480"><i class="icon -material">assignment_turned_in</i><span>'.SG\getFirst($statusList[$proposalInfo->info->status],'สถานะ').'</span></a>');

		} else {
			$ui->add('<a class="" href="javascript:void(0)" title="'.(in_array($proposalInfo->info->status, array(1,3))?'':'ไม่สามารถแก้ไข').'"><i class="icon -material">assignment_turned_in</i><span>'.$statusList[$proposalInfo->info->status].'</span></a>');
		}

		if (in_array($proposalInfo->info->status, array(1,3)) && $isEdit) {
			$ui->add('<a class="sg-action" href="'.url('project/proposal/'.$tpid.'/info/send',array('s'=>2)).'" title="ส่งโครงการเพื่อพิจารณา" data-title="ส่งโครงการเพื่อพิจารณา" data-confirm="ได้ดำเนินการพัฒนาโครงการเป็นที่เรียบร้อยแล้ว<br /><em>*** คำเตือน : หลังจากส่งโครงการเพื่อพิจาณาแล้ว จะไม่สามารถแก้ไขรายละเอียดของข้อเสนอโครงการจนกว่ากรรมการจะให้มีการปรับแก้ ***</em><br />ต้องการส่งโครงการเข้าสู่การพิจารณา กรุณายืนยัน?" data-rel="none" data-done="reload"><i class="icon -material">check_circle_outline</i><span>ส่งโครงการ</span></a>');
		} else if ($proposalInfo->info->status == 10) {
			$ui->add('<a class="" href="'.url('project/'.$tpid).'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ</span></a>');
		}

		$ui->add('<a href="javascript:window.print()"><i class="icon -material">print</i><span>พิมพ์</span></a>');

		if ($isAdmin && $proposalInfo->info->status == 5) {
			$dropboxUi->add('<a class="sg-action" href="'.url('project/proposal/'.$tpid.'/follow.make').'" data-rel="box" data-width="640"><i class="icon -material">assignment_turned_in</i><span>สร้างเป็นโครงการติดตาม</span></a>');
		}
		if ($isRight) {
			$dropboxUi->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.share').'" data-rel="box" data-width="640" data-class-name="-transparent"><i class="icon -material">share</i><span>แบ่งปัน</span></a>');
		}

		if ($isEdit) {
			$dropboxUi->add('<a href="'.url('project/proposal/'.$tpid.'/info.download').'"><i class="icon -material">cloud_download</i><span>ดาวน์โหลด</span></a>');
		}

		if ($isAdmin) {
			$dropboxUi->add('<a class="sg-action" href="'.url('project/proposal/'.$tpid.'/info.delete').'" data-rel="box" data-width="640"><i class="icon -material">delete</i><span>ลบพัฒนาโครงการ</span></a>');
		}
	} else if ($proposalInfo->info->parent) {
		$ui->add('<a href="'.url('project/'.$proposalInfo->info->parent.'/page').'"><i class="icon -material">home</i><span>หน้าแรก</span></a>');
	}

	$dropboxImg = i()->ok ? '<a href="javascript:void(0)"><img class="profile-photo" src="'.model::user_photo(i()->username).'" width="100%" height="100%" /></a>' : '<a class="sg-action" href="'.url('signin').'" data-rel="box" data-width="640"><i class="icon -material -sg-32">person</i></a>';


	$ret .= $ui->build()._NL;

	if (i()->ok) {
		$dropboxMember->add('<a class="sg-action" href="'.url('my').'" title="Member Profile" data-rel="box" data-width="640"><i class="icon -material -sg-16">account_circle</i><span>Account Overview</span></a>');
		$dropboxMember->add('<a class="sg-action" href="'.url('my/change/detail').'" title="Change Profile" data-rel="box" data-width="640"><i class="icon -material -sg-16">settings</i><span>Account Settings</span></a>');
		$dropboxMember->add('<a class="sg-action" href="'.url('my/change/password').'" title="Change Password" data-rel="box" data-width="640"><i class="icon -material -sg-16">vpn_key</i><span>Change Password</span></a>');
		$dropboxMember->add('<a class="sg-action" href="'.url('my/change/photo').'" title="Change Photo" data-rel="box" data-width="640"><i class="icon -material -sg-16">add_a_photo</i><span>Change Photo</span></a>');
		$dropboxMember->add('<sep>');
		$dropboxMember->add('<a class="sg-action" href="'.url('project/'.$setId.'/info.u/'.i()->uid).'" data-rel="box" data-width="640" title="นวัตกรรมของฉัน"><i class="icon -material -sg-16">assessment</i><span>นวัตกรรมของฉัน</span></a>');
		$dropboxMember->add('<a href="//hsmi2.psu.ac.th/upload/forum/manual_par.pdf" target="_blank"><i class="icon -material -sg-16">book</i><span>คู่มือการใช้งาน</span></a>');
		$dropboxMember->add('<a class="sg-action" href="?pageclass='.($_COOKIE['pageclass'] ? '' : 'icononly').'" data-rel="none" title="Icon Display" data-done="reload"><i class="icon -material -sg-16">spellcheck</i><span>'.($_COOKIE['pageclass'] ? 'แสดงไอคอนและข้อความ' : 'แสดงเฉพาะไอคอน').'</span></a>');
	}

	if ($isAdmin) {
		$dropboxMember->add('<sep>');
		$dropboxMember->add('<a href="'.url('project/admin').'" title="Admin"><i class="icon -material -sg-16">settings_application</i><span>Project Administrator</span></a>');
	}

	if (i()->ok) {
		$dropboxMember->add('<sep>');

		$dropboxMember->add('<a class="sg-action" href="'.url('signout').'" title="ออกจากระบบสมาชิก" data-title="ออกจากระบบสมาชิก" data-confirm="ต้องการออกจากระบบสมาชิก กรุณายืนยัน?"><i class="icon -material -sg-16">lock_open</i><span>ออกจากระบบสมาชิก</span></a>');
	} else {
		$dropboxMember->add('<a href="//hsmi2.psu.ac.th/upload/forum/manual_par.pdf" target="_blank"><i class="icon -material -sg-16">book</i><span>คู่มือการใช้งาน</span></a>');
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