<?php
/**
* My Profile
* Created 2019-11-24
* Modify  2019-11-24
*
* @param Object $self
* @return String
*/

$debug = true;

function garage_my_profile($self) {
	if (!i()->ok) {
		// Show Login Page
		R::View('toolbar',$self,'@Secure Log in','none');
		$ret = R::View('signform', '{time:-1}');
		$ret .= '<style type="text/css">
		.toolbar.-main h2 {text-align: center;}
		.form.signform .form-item {margin-bottom: 16px; position: relative;}
		.form.signform label {position: absolute; left: 8px; color: #666; font-style: italic; font-size: 0.9em; font-weight: normal;}
		.form.signform .form-text, .form.signform .form-password {padding-top: 24px;}
		.module-ibuy.-softganz-app .form-item.-edit-cookielength {display: none;}
		.login.-normal h3 {display: none;}
		</styel>';
		return $ret;
	}

	R::View('toolbar',$self,'@'.i()->name,'none');

	$ret .= '<div class="imed-patient-photo-wrapper" style="margin:0 0 16px; padding: 16px 0; position:relative; background-color: #fff">';
	$ret .= '<div id="imed-patient-photo" style="width: 196px; height: 196px; margin: 0px auto 32px; display: block; border-radius: 50%; overflow: hidden; border: 2px #eee solid;"><img src="'.model::user_photo(i()->username).'" width="100%" height="100%" /></a></div>';

	$ret .= '<form class="sg-form -upload" method="post" enctype="multipart/form-data" action="'.url('my/api/photo.change').'" data-rel="notify" data-done="load" style="position: absolute; top: 16px; right: calc(50% - 112px); padding: 0; margin: 0; background-color: transparent;"><span class="fileinput-button"><i class="icon -material">add_a_photo</i><input type="file" name="photo" class="inline-upload" accept="image/*;capture=camcorder" x-capture="capture" onchange=\'$(this).closest(form).submit(); return false;\' /></span></form>';

	$ret .= '</div>';


	$ui = new Ui(NULL, 'ui-menu');
	$ui->add('<a href="{url:signout}"><i class="icon -material">lock_open</i><span>ออกจากระบบ</span></a>');

	$ret .= $ui->build();
	return $ret;
}
?>