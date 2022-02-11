<?php
/**
* Project Proposal Menu
*
* @param Object $proposalInfo
* @param Object $options
* @return String
*/

function view_project_nav_proposal($proposalInfo = NULL, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$tpid = $proposalInfo->tpid;
	$ret = '';

	$isAdmin = $proposalInfo->RIGHT & _IS_ADMIN;
	$isEdit = $proposalInfo->RIGHT & _IS_EDITABLE;
	$isCreateFollow = R::Model('project.right.develop.createfollow',$proposalInfo);
	$isDeleteDevelop = R::Model('project.right.develop.delete',$proposalInfo);

	$ui = new Ui(NULL,'ui-nav -info -sg-text-center');
	$dboxUi = new Ui(NULL,'ui-dropbox');

	$ui->add('<a class="" href="'.url('project/develop').'" title="Project Development"><i class="icon -material">home</i><span class="">หน้าหลัก</span></a>');
	$ui->add('<a class="" href="'.url('project/my/all').'" title="Project Development"><i class="icon -material">person</i><span class="">ของฉัน</span></a>');

	$ui->add('<sep>');

	//$dboxUi->add('<a class="" href="'.url('project/develop').' " title="Project Development"><i class="icon -list"></i><span>รวมพัฒนาโครงการ</span></a>');

	if ($tpid) {
		// Nav Bar Menu
		$ui->add('<a class="" href="'.url('project/develop/'.$tpid).'"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>');
		if ($proposalInfo->info->followId) {
			$ui->add('<a class="" href="'.url('project/'.$tpid).'"><i class="icon -walk"></i><span>ติดตาม</span></a>');
		}
		$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.share').'" data-rel="box" data-width="640" data-class-name="-transparent"><i class="icon -material">share</i><span>แบ่งปัน</span></a>');

		$ui->add('<sep>');
		$status=array(1=>'กำลังพัฒนา',2=>'พิจารณา',3=>'ปรับแก้',5=>'ผ่าน',8=>'ไม่ผ่าน',9=>'ยกเลิก','10'=>'ดำเนินการ');

		if ($isAdmin) {
			$ui->add('<a class="sg-action" href="'.url('project/proposal/'.$tpid.'/info.status').'" title="'.(in_array($proposalInfo->info->status, array(1,3))?'':'ไม่สามารถแก้ไข').'" data-rel="box" data-width="480"><i class="icon -material">assignment_turned_in</i><span>'.$status[$proposalInfo->info->status].'</span></a>');

		} else {
			$ui->add('<a class="" href="javascript:void(0)" title="'.(in_array($proposalInfo->info->status, array(1,3))?'':'ไม่สามารถแก้ไข').'"><i class="icon -material">assignment_turned_in</i><span>'.$status[$proposalInfo->info->status].'</span></a>');
		}

		if (in_array($proposalInfo->info->status, array(1,3)) && $isEdit) {
			$ui->add('<a class="sg-action" href="'.url('project/proposal/'.$tpid.'/info.send',array('s'=>2)).'" title="ส่งโครงการเพื่อพิจารณา" data-title="ส่งโครงการเพื่อพิจารณา" data-confirm="ได้ดำเนินการพัฒนาโครงการเป็นที่เรียบร้อยแล้ว<br /><em>*** คำเตือน : หลังจากส่งโครงการเพื่อพิจาณาแล้ว จะไม่สามารถแก้ไขรายละเอียดของข้อเสนอโครงการจนกว่ากรรมการจะให้มีการปรับแก้ ***</em><br />ต้องการส่งโครงการเข้าสู่การพิจารณา กรุณายืนยัน?" data-rel="none" data-callback="'.url('project/develop/'.$tpid).'"><i class="icon -material">check_circle_outline</i><span>ส่งโครงการ</span></a>');
		}


		// Dropbox Menu
		$dboxUi->add('<a href="'.url('project/develop/'.$tpid,array('o'=>'word','a'=>'download')).'"><i class="icon -download"></i><span>ดาวน์โหลด</span></a>');
		$dboxUi->add('<a href="'.url('project/develop/'.$tpid.'/proposal').'"><i class="icon -viewdoc"></i><span>แบบฟอร์มเสนอโครงการ</span></a>');

		$dboxUi->add('<sep>');

		if (!empty($proposalInfo->info->followId))
			$dboxUi->add('<a class="" href="'.url('project/'.$tpid).'"><i class="icon -walk"></i><span>ติดตามโครงการ</span></a>');

		if ($isCreateFollow && empty($proposalInfo->info->followId) && $proposalInfo->info->status == 5)
			$dboxUi->add('<a class="" href="'.url('project/develop/'.$tpid.'/createproject').'"><i class="icon -adddoc"></i><span>สร้างเป็นติดตามโครงการ</span></a>');


		if ($proposalInfo->info->thread)
			$dboxUi->add('<a href="'.url('project/'.$proposalInfo->info->thread.'/eval.valuation').'">ประเมินผลโครงการเดิม</a>');

		if ((user_access('administer projects')
			|| i()->uid==$proposalInfo->uid)
			&& in_array($proposalInfo->info->status, array(8,9)))
			$dboxUi->add('<a href="'.url('project/develop/'.$tpid.'/duplicate').'" confirm="ต้องการนำโครงการนี้มาเริ่มพัฒนาใหม่อีกครั้ง!!!! กรุณายืนยัน" title="นำโครงการนี้มาเริ่มพัฒนาใหม่อีกรอบ">นำมาพัฒนาใหม่</a>');

		if ($isDeleteDevelop) {
			$dboxUi->add('<sep>');
			$dboxUi->add('<a href="'.url('project/develop/'.$tpid.'/delete').'" title="ลบโครงการกำลังพัฒนา"><i class="icon -material">delete</i><span>ลบโครงการกำลังพัฒนา</span></a>');
		}
		if ($isAdmin) {
			$dboxUi->add('<a class="" href="'.url('project/'.$tpid.'/setting').'"><i class="icon -material">settings</i><span>กำหนดค่า</span></a>');
			$dboxUi->add('<a class="sg-action" href="'.url('project/develop/'.$tpid.'/history').'" data-rel="box"><i class="icon -material">history</i><span>ประวัติ</span></a>');
		}
	}

	$ui->add('<sep>');

	$ui->add('<a class="" href="javascript:window.print()"><i class="icon -print"></i><span>พิมพ์</span></a>');

	$ret .= $ui->build()._NL;


	$ret .= $dboxUi->count() ? sg_dropbox($dboxUi->build(),'{class:"leftside -atright"}') : '';

	return $ret;
}
?>