<?php
/**
* OrgDg report สถานะ
*
*/
function org_report($self) {
	$self->theme->title.=' - รายงาน';

	$self->theme->title='รายงาน';

	$ui=new ui();
	$ui->add('<a href="'.url('org/report/status').'">จำนวนสมาชิกใหม่แต่ละเดือน</a>');
	$ui->add('<a href="'.url('org/report/issue').'">จำนวนคนเข้าร่วมกิจกรรมแยกตามประเด็น</a>');
	$ui->add('<a href="'.url('org/report/memberbyarea').'">รายชื่อสมาชิกในแต่ละพื้นที่</a>');
	$ui->add('<a href="'.url('org/report/times').'">รายชื่อสมาชิกเข้าร่วมกิจกรรมนับครั้ง</a>');
	$ui->add('<a href="'.url('org/report/orgdoing').'">กิจกรรมขององค์กร</a>');
	$ui->add('<a href="'.url('org/report/project').'">กิจกรรมของโครงการ</a>');
/*
	$ui->add('<a href="'.url('org/report/org_type').'">จำนวนองค์กรใหม่ในแต่ละปี - แยกตามประเภท</a>');
	$ui->add('<a href="'.url('org/report/org_issue').'">จำนวนองค์กรใหม่ในแต่ละปี - แยกตามประเด็น</a>');
	$ui->add('<a href="'.url('org/report/member_type').'">จำนวนใหม่สมาชิกในแต่ละปี - แยกตามประเภท</a>');
	$ui->add('<a href="'.url('org/report/member_in_issue').'">รายชื่อผู้เข้าร่วมแต่ละประเด็น</a>');
	$ui->add('<a href="'.url('org/report/member_email').'">รายชื่อสมาชิกที่มีอีเมล์</a>');
	$ui->add('<a href="'.url('org/report/member_cid').'">รายชื่อสมาชิกที่มีหมายเลขบัตรประชาชน (13 หลัก)</a>');
	$ui->add('<a href="'.url('org/report/member_noorg').'">รายชื่อสมาชิกที่ไม่มีองค์กร</a>');
	$ui->add('<a href="'.url('org/report/member_dup').'">รายชื่อ-นามสกุลซ้ำ</a>');
	$ui->add('<a href="'.url('org/report/member_ampur').'">จำนวนคนในแต่ละอำเภอ</a>');
*/
	$ret.=$ui->build('ul');
/*
	$myorg=org_model::get_my_org();
	$ret.='<div class="sg-tabs"><ul class="tabs"><li class="-active"><a href="'.url('org/report/status',array('gt'=>'year')).'">กราฟรายปี</a></li><li><a href="'.url('org/report/status',array('gt'=>'month')).'">กราฟรายเดือน</a></li><li><a href="'.url('org/report/status',array('gt'=>'table')).'">ตาราง</a></li></ul>';
	$ret.='<div id="org-meeting-info" data-load="'.url('org/report/status').'">';
	$ret.='</div>';
*/
	return $ret;
}
?>