<?php
/**
* Project Development Menu
*
* @param Object $devInfo
* @return Object $options
*/

function view_project_nav_develop($devInfo = NULL, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$tpid = $devInfo->tpid;
	$ret = '';

	$isAdmin = $devInfo->RIGHT & _IS_ADMIN;
	$isOwner = $devInfo->RIGHT & _IS_OWNER;
	$isDeletable = $isAdmin || $isOwner;

	//$ret .= print_o($devInfo,'$devInfo');

	$ui = new Ui(NULL,'ui-nav -sg-text-center');
	$ui->add('<a class="" href="'.url('project/my').'" title="Project Development"><i class="icon -home"></i><span class="">หน้าหลัก</span></a>');
	//$ui->add('<a href="'.url('project/my/develop').'" title="พัฒนาโครงการของฉัน"><i class="icon -person"></i><span class="">ของฉัน</span></a>');
	$ret .= $ui->build();

	$ui = new Ui(NULL,'ui-nav -sg-text-center');
	$dboxUi = new Ui(NULL,'ui-dropbox');



	if ($tpid) {
		// Nav Bar Menu
		$ui->add('<a class="" href="'.url('project/develop/'.$tpid).'"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>');
		if ($devInfo->followId)
			$ui->add('<a class="" href="'.url('paper/'.$tpid).'"><i class="icon -walk"></i><span>ติดตาม</span></a>');
		$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.share').'" data-rel="box" data-width="640" data-class-name="-transparent"><i class="icon -material">share</i><span>แบ่งปัน</span></a>');


		// Dropbox Menu
		$dboxUi->add('<a href="'.url('project/develop/'.$tpid,array('o'=>'word','a'=>'download')).'"><i class="icon -download"></i><span>ดาวน์โหลด</span></a>');
		$dboxUi->add('<a href="'.url('project/develop/proposal/'.$tpid).'"><i class="icon -viewdoc"></i><span>แบบฟอร์มเสนอโครงการ</span></a>');

		$dboxUi->add('<sep>');

		if (!empty($devInfo->followId))
			$dboxUi->add('<a class="" href="'.url('paper/'.$tpid).'"><i class="icon -walk"></i><span>ติดตามโครงการ</span></a>');

		if ($isAdmin && empty($devInfo->followId))
			$dboxUi->add('<a class="" href="'.url('project/develop/'.$tpid.'/createproject').'"><i class="icon -adddoc"></i><span>สร้างเป็นติดตามโครงการ</span></a>');


		if ((user_access('administer projects')
			|| i()->uid==$devInfo->uid)
			&& in_array($devInfo->status, array(8,9)))
			$dboxUi->add('<a href="'.url('project/develop/duplicate/'.$tpid).'" confirm="ต้องการนำโครงการนี้มาเริ่มพัฒนาใหม่อีกครั้ง!!!! กรุณายืนยัน" title="นำโครงการนี้มาเริ่มพัฒนาใหม่อีกรอบ">นำมาพัฒนาใหม่</a>');

		if ($isDeletable) {
			$dboxUi->add('<sep>');
			$dboxUi->add('<a class="sg-action" href="'.url('project/develop/'.$tpid.'/delete').'" title="ลบโครงการกำลังพัฒนา" data-rel="none" data-done="reload:'.url('project/my/all').'" data-title="ลบพัฒนาโครงการ" data-confirm="ต้องการลบพัฒนาโครงการ กรุณายืนยัน?"><i class="icon -delete"></i><span>ลบโครงการกำลังพัฒนา</span></a>');
		}
		if ($isAdmin) {
			$dboxUi->add('<a class="sg-action" href="'.url('project/develop/history/'.$tpid).'" data-rel="box"><i class="icon -view-info"></i><span>ประวัติ</span></a>');
		}
	}

	$ui->add('<a class="" href="javascript:window.print()"><i class="icon -print"></i><span class="">พิมพ์</span></a>');
	$ret .= $ui->build()._NL;


	$ret .= sg_dropbox($dboxUi->build(),'{class:"leftside -atright"}');

	return $ret;
}
?>