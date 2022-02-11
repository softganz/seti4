<?php
/**
* Project detail
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_project_nav_assessor($rs,$options = NULL) {
	$ret='';
	$ui=new Ui(NULL,'ui-nav -info');
	$ui->add('<a href="'.url('project').'"><i class="icon -home"></i><span class="-hidden">โครงการ</span></a>');
	$ui->add('<a href="'.url('project/assessor').'"><i class="icon -people"></i><span class="-hidden">รายชื่อนักติดตามประเมินผล</span></a>');
	$ret.=$ui->build();

	if (i()->ok) $isRegister=mydb::select('SELECT `psnid` FROM %person_group% WHERE `groupname`="assessor" AND `uid` = :uid LIMIT 1',':uid',i()->uid)->psnid;

	$ui=new Ui(NULL,'ui-nav -add -toright');
	if ($isRegister) {
		$ui->add('<a class="btn -circle48" href="'.url('project/assessor/'.i()->uid).'" style="border-radius: 50%; box-shadow: none; border: none;"><i class="icon -edit"></i><span class="-hidden">แก้ไขข้อมูล</span></a>');
	} else {
		$ui->add('<a class="btn -primary" href="'.url('project/assessor/register').'" style="border-radius: 8px; box-shadow: none; border: none;"><i class="icon -addbig -white -circle"></i><span>ลงทะเบียนนักติดตามประเมินผล</span></a>');
	}
	$ret.=$ui->build();

	return $ret;
}
?>