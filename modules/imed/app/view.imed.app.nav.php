<?php
/**
* Project detail
*
* @param Object $self
* @param Object $psnInfo
* @param Object $para
* @return String
*/
function view_imed_app_nav($psnInfo,$options) {
	$submenu = q(2);

	$ret = '';

	$isAdmin = $psnInfo->RIGHT & IS_ADMIN;
	$isRight = $psnInfo->RIGHT & _IS_ACCESS;
	$isEdit = $psnInfo->RIGHT & IS_EDITABLE;


	$ui = new Ui(NULL,'ui-nav -info');

	$ui->add('<a class="" href="'.url('imed/app').'"><i class="icon -material">home</i><span class="-hidden">Home</span></a>');

	if (i()->ok) {
		$ui->add('<a class="sg-action" href="'.url('imed/app/my/care').'" data-webview="ดูแล"><i class="icon -material">accessible</i><span class="-hidden">ดูแล</span></a>');
		$ui->add('<a class="sg-action" href="'.url('imed/app/need').'" data-webview="ความต้องการ"><i class="icon -material">how_to_reg</i><span class="-hidden">Needs</span></a>');
		$ui->add('<a class="sg-action" href="'.url('imed/app/social').'" data-webview="@ Social Groups"><i class="icon -material">group</i><span class="-hidden">Groups</span></a>');
		$ui->add('<a class="" href="'.url('imed/app/menu').'"><i class="icon -material">menu</i><span class="-hidden">Menu</span></a>');
	}

	$ret .= $ui->build();

	return $ret;
}
?>