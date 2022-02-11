<?php
/**
* Project detail
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_project_nav_money($rs=NULL,$options=NULL) {
	$ret='';

	unset($self->theme->moduleNav);

	$ui=new Ui(NULL,'ui-nav -info');
	$ui->add('<a href="'.url('paper/'.$rs->tpid).'" title="หน้าหลัก"><i class="icon -home"></i><span class="-hidden">หน้าหลัก</span></a>');
	$ui->add('<a href="'.url('project/money/'.$rs->tpid).'" title="หน้าหลัก"><i class="icon -home"></i><span class="-hidden">หน้าหลัก</span></a>');
	$ui->add('<a href="'.url('project/money/'.$rs->tpid.'/member').'" title="ผู้เข้าร่วมกิจกรรม"><i class="icon -person"></i><span class="-hidden">ผู้เข้าร่วมกิจกรรม</span></a>');
	$ui->add('<a href="'.url('project/money/'.$rs->tpid.'/qrcode').'" title="QR Code"><i class="icon -module"></i><span class="x-hidden">QR Code</span></a>');
	$ui->add('<a class="" href="javascript:window.print()"><i class="icon -print"></i><span class="-hidden">พิมพ์</span></a>');
	/*
	$dropboxUi=new Ui(NULL,'ui-dropbox');
	$dropboxUi->add('<a href="'.url('project/my/action/*').'" title="กิจกรรมล่าสุด"><i class="icon -person"></i><span>กิจกรรมล่าสุด</span></a>');
	$dropboxUi->add('<a href="'.url('project/my/setting').'" title="ตั้งค่า"><i class="icon -setting"></i><span>ตั้งค่า</span></a>');
	$ui->add(sg_dropbox($dropboxUi->build('ul'),'{class:"leftside -atright"}'));
	*/
	$ret.=$ui->build();
	return $ret;
}
?>