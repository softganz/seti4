<?php
/**
* Module Method
* Created 2019-01-01
* Modify  2019-01-01
*
* @param
* @return String
*/

$debug = true;

function view_paper_edit_menu($tpid) {
	$ret .= '<nav class="nav" style="padding: 4px;"><a class="btn -fill" href="'.url('paper/'.$tpid).'"><i class="icon -material">arrow_back</i> '.tr('Back to topic').'</a></nav>';

	//$ret .= '<header><h3>Menu</h3></header>';

	$ui = new Ui(NULL, 'ui-menu');

	$ui->add('<a href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material">home</i><span>สถานะ</span></a>');
	$ui->add('<a class="sg-action" href="'.url('paper/'.$tpid.'/edit.main').'" data-rel="#main"><i class="icon -material">settings</i><span>จัดการเอกสาร</span></a>');
	$ui->add('<a class="sg-action" href="'.url('paper/'.$tpid.'/edit.detail').'" data-rel="box"><i class="icon -material">description</i><span>รายละเอียด</span></a>');
	if (user_access('upload photo')) $ui->add('<a class="sg-action" href="'.url('paper/'.$tpid.'/edit.photo').'" data-rel="#main"><i class="icon -material">photo_library</i><span>ภาพประกอบ</span></a>');
	if (user_access('upload document')) $ui->add('<a class="sg-action" href="'.url('paper/'.$tpid.'/edit.docs').'" data-rel="#main"><i class="icon -material">attachment</i><span>เอกสารประกอบ</span></a>');
	$ui->add('<a class="sg-action" href="'.url('paper/'.$tpid.'/edit.prop').'" data-rel="#main"><i class="icon -material">text_format</i><span>รูปแบบการแสดงผล</span></a>');

	if (user_access('administer contents,administer papers,administer paper tags')) $ui->add('<a class="sg-action" href="'.url('paper/'.$tpid.'/edit.tag').'" data-rel="#main"><i class="icon -material">category</i><span>จัดการหมวด</span></a>');

	$ui->add('<a class="sg-action" href="'.url('paper/'.$tpid.'/edit.etc').'" data-rel="#main"><i class="icon -material">all_out</i><span>ข้อมูลอื่น ๆ</span></a>');

	if (user_access('administer contents,administer papers')) {
		$ui->add('<a class="sg-action" href="'.url('paper/'.$tpid.'/edit.owner').'" data-rel="#main"><i class="icon -material">person</i><span>Change owner</span></a>');
		$ui->add('<a class="sg-action" href="'.url('paper/'.$tpid.'/edit.weight').'" data-rel="#main"><i class="icon -material">swap_vert</i><span>Weight</span></a>');
		$ui->add('<a class="sg-action" href="'.url('paper/'.$tpid.'/edit.repaircomment').'" data-rel="#main"><i class="icon -material">bug_report</i><span>Repair Comment</span></a>');

		if (mydb::table_exists('%poll%')) {
			$ui->add('<a class="sg-action" href="'.url('paper/'.$tpid.'/edit.makepoll').'" data-rel="#main"><i class="icon -material">poll</i><span>Make Poll</span></a>');
		}

		if (mydb::table_exists('%archive_topic%')) {
			$ui->add('<a class="sg-action" href="'.url('paper/'.$tpid.'/edit.archive').'" data-rel="#main"><i class="icon -material">archive</i><span>Move to Archive</span></a>');
		}
	}

	if ($topic->status != _LOCK && user_access('administer contents,administer papers','edit own paper',$topic->uid)) {
		$ui->add('<sep>');
		$ui->add('<a class="sg-action" href="'.url('paper/'.$tpid.'/delete').'" data-rel="#main" title="ลบหัวข้อนี้"><i class="icon -material">delete</i><span>'.tr('Delete topic').'</span></a>');
	}

	$ret .= $ui->build();
	return $ret;
}
?>