<?php
function view_project_nav_develop($info=NULL,$options='{}') {
	$tpid=$info->tpid;
	$ret='';

	$isAdmin=user_access('administer projects');

	//$ret.='<form id="search" class="search-box" method="get" action="'.url('project/develop/search').'" name="memberlist" role="search"><input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="ป้อนชื่อพัฒนาโครงการ" data-query="'.url('project/get/develop').'" data-callback="'.url('project/idea/view/').'" data-altfld="sid"><input type="submit" class="button" value="ค้นหาพัฒนาโครงการ"></form>'._NL;

	$ui=new ui(NULL,'ui-nav');
	$ui->add('<a class="" href="'.url('project/develop').'" title="Project Development"><i class="icon -home"></i><span class="-hidden">หน้าหลัก</span></a>');
	$ret.=$ui->build();

	$ui=new ui(NULL,'ui-nav');
	$dboxUi=new Ui(NULL,'ui-dropbox');


	$dboxUi->add('<a class="" href="'.url('project/develop').' " title="Project Development"><i class="icon -list"></i><span>รวมพัฒนาโครงการ</span></a>');

	if ($tpid) {
		$ui->add('<a class="" href="'.url('project/develop/'.$tpid).'"><i class="icon -viewdoc"></i><span>พัฒนาโครงการ</span></a>');
		if ($info->followId) $ui->add('<a class="" href="'.url('paper/'.$tpid).'"><i class="icon -viewdoc"></i><span>ติดตามโครงการ</span></a>');

		if (!empty($info->followId)) {
			$dboxUi->add('<a class="" href="'.url('paper/'.$tpid).'"><i class="icon -viewdoc"></i><span>ติดตามโครงการ</span></a>');
		}
		$dboxUi->add('<a href="'.url('project/develop/'.$tpid,array('o'=>'word','a'=>'download')).'"><i class="icon -download"></i><span>ดาวน์โหลด</span></a>');


		if ($info->thread) {
			$dboxUi->add('<a href="'.url('project/'.$info->thread.'/eval.valuation').'">ประเมินผลโครงการเดิม</a>');
		}
		if ($oldDevelop) {
			$dboxUi->add('<a href="'.url('project/develop/'.$oldDevelop).'" target="_blank">โครงการพัฒนาเดิม</a>');
		}
		if ((user_access('administer projects') || i()->uid==$info->uid) && in_array($info->status, array(8,9))) $dboxUi->add('<a href="'.url('project/develop/duplicate/'.$tpid).'" confirm="ต้องการนำโครงการนี้มาเริ่มพัฒนาใหม่อีกครั้ง!!!! กรุณายืนยัน" title="นำโครงการนี้มาเริ่มพัฒนาใหม่อีกรอบ">นำมาพัฒนาใหม่</a>');
		if ($isAdmin) {
			$dboxUi->add('<a href="'.url('project/develop/link/'.$tpid).'">บันทึกเชื่อมโยงโครงการเดิม</a>');
			$dboxUi->add('<a class="sg-action" href="'.url('project/develop/history/'.$tpid).'" data-rel="box">ประวัติ</a>');
			$dboxUi->add('<sep>');
			$dboxUi->add('<a class="sg-action" href="'.url('project/develop/'.$tpid.'/delete').'" title="ลบโครงการกำลังพัฒนา" data-rel="notify" data-done="reload:'.url('project/my/all').'" data-title="ลบพัฒนาโครงการ" data-confirm="ต้องการลบพัฒนาโครงการ กรุณายืนยัน?"><i class="icon -delete"></i><span>AAAลบโครงการกำลังพัฒนา</span></a>');
		}

		if ($isAdmin) {
			if (empty($info->followId)) {
				$dboxUi->add('<sep>');
				$dboxUi->add('<a class="" href="'.url('project/develop/'.$tpid.'/createproject').'"><i class="icon -adddoc"></i><span>สร้างเป็นติดตามโครงการ</span></a>');
			}
		}
	}

	$ret.=$ui->build()._NL;

	$ui=new ui(NULL,'ui-nav');
	$ui->add('<a class="" href="javascript:window.print()"><i class="icon -print"></i><span class="-hidden">พิมพ์</span></a>');
	$ret.=$ui->build()._NL;


	$ret.=sg_dropbox($dboxUi->build(),'{class:"leftside -atright"}');

	return $ret;
}
?>