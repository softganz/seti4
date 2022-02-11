<?php
/**
* Project Join Navigator
* Created 2019-05-20
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @param Object $options
* @return String
*/
function view_project_nav_join($projectInfo, $options = '{}') {
	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;
	$ret = '';

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isRight = $projectInfo->RIGHT & _IS_ACCESS;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;
	$isMember = $projectInfo->info->membershipType;
	$isExport = $isAdmin || $projectInfo->info->membershipType == 'OWNER';
	$isOwner = $isAdmin || $projectInfo->info->membershipType == 'OWNER';

	$ui = new Ui(NULL,'ui-nav -info -sg-text-center');
	$dropUi = new Ui();

	$ui->add('<a href="'.url('project/'.$projectInfo->tpid).'" title="รายละเอียดโครงการ"><i class="icon -material">home</i><span class="" title="รายละเอียดโครงการ">โครงการ</span></a>');
	$ui->add('<sep>');


	if ($isOwner && $calId) {
		$ui->add('<a href="'.url('project/'.$tpid.'/info.join/'.$calId).'" title="รายละเอียดกิจกรรม"><i class="icon -material">info</i><span class="">กิจกรรม</span></a>');
		//$ui->add('<a href="'.url('project/join/'.$tpid).'"><i class="icon -assignment" title="กิจกรรมโครงการ"></i><span class="">กิจกรรม</span></a>');
		$ui->add('<sep>');
	}

	if (($isRight || $isMember) && $calId) {
		$ui->add('<a href="'.url('project/join/'.$tpid.'/'.$calId).'" title="รายชื่อผู้ลงทะเบียน"><i class="icon -material">person_add</i><span class="">ลงทะเบียน</span></a>');
	} else if ($calId) {
		$ui->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/checkname').'" title="รายชื่อผู้ลงทะเบียน"><i class="icon -material">people</i><span class="">รายชื่อ</span></a>');
	}
	if ($isEdit && $calId) {
		$ui->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/money', array('o' => 'name', 's' => 'a')).'" title="ใบสำคัญรับเงิน"><i class="icon -material">monetization_on</i><span class="">การเงิน</span></a>');
	}

	if (($isAdmin || $isMember) && $calId) {
		$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/distance').'" title="ประมาณค่าใช้จ่าย"><i class="icon -material">directions_car</i><span>ประมาณค่าใช้จ่าย</span></a>');
		$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/eat').'" title="สรุปอาหาร"><i class="icon -material">fastfood</i><span>สรุปอาหาร</span></a>');
		$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/mate').'"><i class="icon -material">people</i><span>รายชื่อผู้พักโรงแรม</span></a>');
		$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/printregister').'"><i class="icon -material">print</i><span>พิมพ์ใบลงทะเบียน</span></a>');
	}

	if ($isEdit && $calId) {
		$dropUi->add('<sep>');
		$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/money', array('o' => 'name', 's' => 'a')).'" title="ใบสำคัญรับเงิน"><i class="icon -material">monetization_on</i><span>ใบสำคัญรับเงิน</span></a>');
		$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/money', array('group' => 'all', 'o' => 'name', 's' => 'a')).'" title="ใบสำคัญรับเงิน - สร้างแล้ว"><i class="icon -material">monetization_on</i><span>ใบสำคัญรับเงิน - สร้างแล้ว</span></a>');
		$dropUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.create').'" data-rel="box" data-width="640" title="สร้างใบสำคัญรับเงิน"><i class="icon -material">monetization_on</i><span>สร้างใบสำคัญรับเงินโดยไม่ลงทะเบียน</span></a>');
		$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/topaid', array('o' => 'name', 's' => 'a')).'" title="รายการรับเงิน"><i class="icon -material">monetization_on</i><span>รายการรับเงิน</span></a>');
	}

	$ui->add('<sep>');
	$ui->add('<a href="javascript:window.print()" title="พิมพ์"><i class="icon -material">print</i><span>พิมพ์</span></a>');

	$ret .= $ui->build()._NL;

	if ($isOwner) {
		$dropUi->add('<sep>');
		$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/paidsum').'"><i class="icon -report"></i><span>รายงานสรุปค่าใช้จ่าย</span></a>');
		$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/report.join').'"><i class="icon -report"></i><span>รายงานสรุปใบลงทะเบียน</span></a>');
	}

	if ($isAdmin || $isExport) {
		$dropUi->add('<sep>');
		$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/export').'"><i class="icon -material">cloud_download</i><span>ดาวน์โหลด</span></a>');
		$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/duplicatecheck').'"><i class="icon -save"></i><span>ตรวจสอบข้อมูลลงทะเบียนซ้ำ</span></a>');
		$dropUi->add('<a href="'.url('project/'.$tpid.'/join.setting/'.$calId).'"><i class="icon -setting"></i><span>กำหนดค่า</span></a>');
	}

	if ($dropUi->count()) {
		$ret .= sg_dropbox($dropUi->build(),'{class: "leftside -atright"}');
	}

	//$ret .= print_o($projectInfo, '$projectInfo');
	return $ret;
}
?>