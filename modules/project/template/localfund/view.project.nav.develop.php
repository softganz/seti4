<?php
function view_project_nav_develop($devInfo=NULL,$options='{}') {
	$tpid = $devInfo->tpid;
	$fundid = $devInfo->fundid;

	$ret = '';

	$isAdmin = $devInfo->RIGHT & _IS_ADMIN;
	$isOwner = $devInfo->RIGHT & _IS_OWNER;
	$isDeletable = ($isOwner || $isAdmin) && empty($devInfo->info->followId);

	$orgInfo = R::Model('project.fund.get', $devInfo->orgid);
	$orgId = $orgInfo->orgid;

	$ui = new Ui(NULL,'ui-nav -sg-text-center');
	$dboxUi = new Ui(NULL,'ui-dropbox');

	if ($orgId) {
		// Menu localfund
		$ui->add('<a class="" href="'.url('project/fund/'.$orgId).'" title="หน้าหลักกองทุนฯ"><i class="icon -material">home</i><span class="">หน้าหลัก</span></a>');
	} else {
		// menu for no org or fund
		$ui->add('<a class="" href="'.url('project/develop').'" title="หน้าหลัก"><i class="icon -material">home</i><span class="">หน้าหลัก</span></a>');
		$ui->add('<a class="" href="'.url('project/develop/list').'" title="รายชื่อพัฒนาโครงการ"><i class="icon -material">view_list</i><span class="">รายชื่อ</span></a>');
		$ui->add('<a class="" href="'.url('project/develop/nofund').'" title="รายชื่อพัฒนาโครงการยังไม่มีองค์กร"><i class="icon -material">view_module</i><span class="">รอส่ง</span></a>');
	}


	$ui->add('<a class="" href="'.url('project/develop/my').'" title="รายชื่อพัฒนาโครงการของฉัน"><i class="icon -material">person</i><span class="">ของฉัน</span></a>');

	$ui->add('<sep>');




	$dboxUi->add('<a class="" href="'.url('project/develop').' " title="Project Development"><i class="icon -material">view_list</i><span>พัฒนาโครงการ</span></a>');

	if ($tpid) {
		$ui->add('<a class="" href="'.url('project/develop/'.$tpid).'"><i class="icon -material">find_in_page</i><span class="">รายละเอียด</span></a>');
		if ($devInfo->info->followId) {
			$ui->add('<a class="" href="'.url('project/'.$tpid).'"><i class="icon -material">find_in_page</i><span class="">ติดตามประเมินผล</span></a>');
			$dboxUi->add('<a class="" href="'.url('project/'.$tpid).'"><i class="icon -material">find_in_page</i><span>ติดตามโครงการ</span></a>');
		}
		$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'*/info.share').'" data-rel="box" data-width="640" data-class-name="-transparent"><i class="icon -material">share</i><span>แบ่งปัน</span></a>');

		$ui->add('<sep>');
		$status=array(1=>'กำลังพัฒนา',2=>'พิจารณา',3=>'ปรับแก้',5=>'ผ่าน',8=>'ไม่ผ่าน',9=>'ยกเลิก','10'=>'ดำเนินการ');

		if ($isAdmin) {
			$ui->add('<a class="sg-action" href="'.url('project/proposal/'.$tpid.'/info.status').'" data-rel="box" title="'.(in_array($devInfo->info->status, array(1,3))?'':'ไม่สามารถแก้ไข').'" data-width="480"><i class="icon -material">assignment_turned_in</i><span>'.$status[$devInfo->info->status].'</span></a>');

		} else {
			$ui->add('<a class="" href="javascript:void(0)" title="'.(in_array($devInfo->info->status, array(1,3))?'':'ไม่สามารถแก้ไข').'"><i class="icon -material">assignment_turned_in</i><span>'.$status[$devInfo->info->status].'</span></a>');
		}

		$dboxUi->add('<a href="'.url('project/develop/'.$tpid,array('o'=>'word','a'=>'download')).'"><i class="icon -material">cloud_download</i><span>ดาวน์โหลด</span></a>');
		$dboxUi->add('<a href="'.url('project/develop/proposal/'.$tpid).'"><i class="icon -material">find_in_page</i><span>แบบฟอร์มเสนอโครงการ</span></a>');


		if ($devInfo->info->thread) {
			$dboxUi->add('<a href="'.url('project/'.$devInfo->info->thread.'/eval.valuation').'">ประเมินผลโครงการเดิม</a>');
		}
		if ($oldDevelop) {
			$dboxUi->add('<a href="'.url('project/develop/'.$oldDevelop).'" target="_blank">โครงการพัฒนาเดิม</a>');
		}
		if ((user_access('administer projects') || i()->uid==$devInfo->uid) && in_array($devInfo->info->status, array(8,9))) $dboxUi->add('<a href="'.url('project/develop/duplicate/'.$tpid).'" confirm="ต้องการนำโครงการนี้มาเริ่มพัฒนาใหม่อีกครั้ง!!!! กรุณายืนยัน" title="นำโครงการนี้มาเริ่มพัฒนาใหม่อีกรอบ">นำมาพัฒนาใหม่</a>');

		// Menu for admin
		if ($isAdmin) {
			$dboxUi->add('<sep>');
			if ($devInfo->orgid && empty($devInfo->info->followId)) {
				$dboxUi->add('<a class="" href="'.url('project/develop/'.$tpid.'/createproject').'"><i class="icon -material">noteadd</i><span>สร้างเป็นติดตามโครงการ</span></a>');
			}
		}

		$dboxUi->add('<sep>');

		if ($isDeletable) {
			$dboxUi->add('<a class="sg-action" href="'.url('project/develop/'.$tpid.'/delete').'" title="ลบโครงการกำลังพัฒนา" data-rel="none" data-done="reload:'.url($devInfo->orgid  ? 'project/fund/'.$devInfo->orgid .'/proposal' : 'project/my/all').'" data-title="ลบพัฒนาโครงการ" data-confirm="ต้องการลบพัฒนาโครงการ กรุณายืนยัน?"><i class="icon -delete"></i><span>ลบโครงการกำลังพัฒนา</span></a>');
		} else {
			$dboxUi->add('<a class="-disabled" href="jajascript:void(0)"><i class="icon -material">delete</i><span>ลบพัฒนาโครงการไม่ได้</span></a>');
		}

		if ($isAdmin) {
			$dboxUi->add('<sep>');
			$dboxUi->add('<a class="sg-action" href="'.url('project/develop/history/'.$tpid).'" data-rel="box"><i class="icon -material">history</i>ประวัติ</a>');
		}

		$ui->add('<sep>');

		$ui->add('<a class="" href="javascript:window.print()"><i class="icon -material">print</i><span class="">พิมพ์</span></a>');
	}

	$ret.=$ui->build()._NL;



	$ret.=sg_dropbox($dboxUi->build(),'{class:"leftside -atright"}');

	//debugMsg($devInfo,'$devInfo');

	return $ret;
}
?>