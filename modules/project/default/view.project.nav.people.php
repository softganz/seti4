<?php
/**
* Project detail
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_project_nav_people($projectInfo, $options = '{}') {
	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;

	$ret = '';

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isRight = $projectInfo->RIGHT & _IS_ACCESS;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;
	$isMember = $projectInfo->info->membershipType || $projectInfo->info->orgmembership;
	$isExport = $isAdmin || $projectInfo->info->membershipType == 'OWNER';
	$isOwner = $isAdmin || $projectInfo->info->membershipType == 'OWNER';

	//debugMsg($projectInfo,'$projectInfo');

	$ui = new Ui(NULL,'ui-nav -info -sg-text-center');
	$dropUi = new Ui();

	$ui->add('<a href="'.url('project/'.$projectInfo->tpid).'" title="รายละเอียดโครงการ"><i class="icon -material">home</i><span class="" title="รายละเอียดโครงการ">โครงการ</span></a>');
	$ui->add('<sep>');

	if ($isMember && $calId) {
		$ui->add('<a href="'.url('project/'.$tpid.'/info.join/'.$calId).'" title="รายละเอียดกิจกรรม"><i class="icon -material">info</i><span class="">กิจกรรม</span></a>');
		$ui->add('<a href="'.url('project/'.$tpid.'/info.join.invite/'.$calId).'" title="รายชื่อผู้ถูกเข้าร่วม"><i class="icon -material">person_add</i><span class="">เชิญเข้าร่วม</span></a>');
		$ui->add('<a href="'.url('project/'.$tpid.'/info.join.joined/'.$calId).'" title="รายชื่อผู้เข้าร่วม"><i class="icon -material">people</i><span class="">ผู้เข้าร่วม</span></a>');
		$ui->add('<a href="'.url('project/'.$tpid.'/info.join.regform/'.$calId).'" title="ใบลงทะเบียน"><i class="icon -material">ballot</i><span class="">ใบลงทะเบียน</span></a>');
		$ui->add('<a href="'.url('project/'.$tpid.'/join/'.$calId.'/money').'" title="การเงิน"><i class="icon -material">monetization_on</i><span class="">การเงิน</span></a>');
//http://localhost/hsmi/hsmi.psu.ac.th/scac/org/1/meeting.info/93/invite
	}


/*

	if (($isRight || $isMember) && $calId) {
		$ui->add('<a href="'.url('project/join/'.$tpid.'/'.$calId).'" title="รายชื่อผู้ลงทะเบียน"><i class="icon -people"></i><span class="">รายชื่อ</span></a>');
		$ui->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/distance').'" title="ประมาณค่าเดินทาง"><i class="icon -car"></i><span class="">ค่าเดินทาง</span></a>');
	} else if ($calId) {
		$ui->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/checkname').'" title="รายชื่อผู้ลงทะเบียน"><i class="icon -people"></i><span class="">รายชื่อ</span></a>');
	}
	if ($isEdit && $calId) {
		$ui->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/money').'" title="ใบสำคัญรับเงิน"><i class="icon -money"></i><span class="">ใบสำคัญรับเงิน</span></a>');

		$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/distance').'" title="ประมาณค่าเดินทาง"><i class="icon -car"></i><span>ประมาณค่าเดินทาง</span></a>');
		$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/eat').'" title="สรุปอาหาร"><i class="icon -blank"></i><span>สรุปอาหาร</span></a>');
		$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/money').'" title="ใบสำคัญรับเงิน"><i class="icon -money"></i><span>ใบสำคัญรับเงิน</span></a>');
		$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/paid').'" title="ใบสำคัญรับเงิน"><i class="icon -money"></i><span>ใบสำคัญรับเงิน - สร้างแล้ว</span></a>');
	}
*/
	$ui->add('<sep>');
	$ui->add('<a href="javascript:window.print()" title="พิมพ์"><i class="icon -print"></i><span>พิมพ์</span></a>');

	$ret .= $ui->build()._NL;

	if ($isEdit) {
		//$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/mate').'"><i class="icon -people"></i><span>รายชื่อผู้พักคู่</span></a>');
		//$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/printregister').'"><i class="icon -print"></i><span>พิมพ์ใบลงทะเบียน</span></a>');
	}

	if ($isAdmin || $isOwner) {
		$dropUi->add('<sep>');
		$dropUi->add('<a href="'.url('project/'.$tpid.'/info.join.download/'.$calId).'" title="ดาวน์โหลด"><i class="icon -material">cloud_download</i><span class="">ดาวน์โหลด</span></a>');
		//$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/duplicatecheck').'"><i class="icon -save"></i><span>ตรวจสอบข้อมูลลงทะเบียนซ้ำ</span></a>');
		$dropUi->add('<a href="'.url('project/'.$tpid.'/info.join/'.$calId.'/setting').'"><i class="icon -setting"></i><span>กำหนดค่า</span></a>');
	}
	if ($dropUi->count())
		$ret .= sg_dropbox($dropUi->build(),'{class: "leftside -atright"}');

	//$ret .= print_o($projectInfo, '$projectInfo');
	return $ret;
}
?>