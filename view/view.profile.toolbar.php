<?php
function view_profile_toolbar($self,$uid=NULL) {
	if (user_access('administer users','change own profile',$uid)) {
		user_menu('edit','<img src="'.cfg('img').'/edit.png" width=14 height=14 border=0 alt="Edit" />','#');
		user_menu('edit','edit_view',tr('Display profile'),url('my/change/'.$uid));
		user_menu('edit','edit_detail',tr('Edit porfile'),url('my/change/detail'));
		user_menu('edit','edit_photo',tr('Change photo'),url('my/change/photo'));
		user_menu('edit','edit_password',tr('Change password'),url('my/change/password'));
	}

	$self->theme->title='Profile';

	$ui=new Ui(NULL,'ui-nav');
	$ui->add('<a href="'.url('my').'" title="My Profile"><i class="icon -material">home</i></a>');
	if ($uid) {
		$ui->add('<a href="'.url('my/change/'.$uid).'" title="ข้อมูลส่วนตัว"><i class="icon -material">person</i></a>');
		$ui->add('<a href="'.url('paper/user/'.$uid).'" title="เอกสารของฉัน"><i class="icon -material">find_in_page</i></a>');
		$ui->add('<a href="'.url('signout').'" title="ออกจากระบบ"><i class="icon -material -gray">lock_open</i></a>');
	}

	$ret.='<nav class="nav -submodule -profile"><!-- nav of profile.nav -->';
	$ret.=$ui->build();
	$ret.='</nav><!-- submodule -->';


	$self->theme->toolbar=$ret;
	//debugMsg($self,'$tself');
}
?>