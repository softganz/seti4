<?php
function view_garage_do_nav($rs=NULL,$options='{}') {
	$ret='';

	//$isEdit=user_access('administer projects','edit own project content',$uid) || project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid);
	$ui=new ui(NULL,'ui-nav');
	$dboxUi=new Ui(NULL,'ui-nav');

	$ui->add('<a class="btn" href="'.url('garage/do').'" title="ใบสั่งงาน"><i class="icon -material">assignment</i><span>ใบสั่งงาน</span></a>');
	$ui->add('<a class="btn" href="'.url('garage/do/work').'" title="ใบสั่งงานกำลังดำเนินงาน"><i class="icon -material">done</i><span>กำลังดำเนินงาน</span></a>');
	$ui->add('<a class="btn" href="'.url('garage/do/photo').'" title="ภาพถ่าย"><i class="icon -material">photo_album</i><span>ภาพถ่าย</span></a>');

	if ($rs->tpid) {
		if (q(4)!='edit') {
			$ui->add('<a class="btn" href="'.url('garage/do/view/'.$rs->tpid.'/edit').' " title=""><i class="icon -edit"></i><span>แก้ไข</span></a>');
		} else {
			$ui->add('<a class="btn" href="'.url('garage/do/view/'.$rs->tpid).' " title=""><i class="icon -view"></i><span>รายละเอียด</span></a>');
		}
		$dboxUi->add('<sep>');
		$dboxUi->add('<a class="" href="'.url('garage/do/view/'.$rs->tpid).' " title=""><i class="icon -view"></i><span>รายละเอียดใบสั่งงาน</span></a>');
		$dboxUi->add('<a class="" href="'.url('garage/do/view/'.$rs->tpid.'/edit').' " title=""><i class="icon -edit"></i><span>แก้ไขใบสั่งงาน</span></a>');
		$dboxUi->add('<a class="-disabled" href="'.url('garage/do/view/'.$rs->tpid.'/cancle').' " title=""><i class="icon -view"></i><span>ยกเลิกใบสั่งงาน</span></a>');
		$dboxUi->add('<sep>');
		$dboxUi->add('<a class="-disabled" href="'.url('garage/do/view/'.$rs->tpid.'/delete').' " title=""><i class="icon -view"></i><span>ลบใบสั่งงาน</span></a>');
	}

	return Array('main' => $ui, 'more' => $dboxUi);
}
?>