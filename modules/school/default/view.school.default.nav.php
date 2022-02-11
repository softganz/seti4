<?php
/**
* Project detail
*
* @param Object $self
* @param Object $psn
* @param Object $para
* @return String
*/
function view_school_default_nav($info,$options) {
	$orgid=$info->orgid;
	$submenu=q(2);
	$ret='';
	$isAdmin=$info->RIGHT & _IS_ADMIN;
	$isAccess=$info->RIGHT & _IS_ACCESS;
	$isEditable=$info->RIGHT & _IS_EDITABLE;

	if ($orgid) {
		$ui=new Ui(NULL,'ui-nav -info');
		$ui->add('<a href="'.url('school').'" title="School Kids Homepage"><i class="icon -home"></i><span class="-hidden">Home</span></a>');
		$ui->add('<a href="'.url('school/my').'" title="My Schools"><i class="icon -person"></i><span class="-hidden">My School</span></a>');
		$ret.=$ui->build();

		$ui=new Ui(NULL,'ui-nav -school');
		$ui->add('<a href="'.url('school/info/'.$orgid).'" title="School Information"><i class="icon -diagram"></i><span class="-hidden">School</span></a>');
		$ui->add('<a href="'.url('school/kids/'.$orgid).'" title="Kids Personal Base"><i class="icon -person-add"></i><span class="-hidden">Kids</span></a>');
		$ui->add('<a href="'.url('school/summary/'.$orgid).'" title="Kids Summary Base"><i class="icon -list"></i><span class="-hidden">Summary</span></a>');
		$ui->add('<a href="'.url('school/report/'.$orgid).'" title="Situation Analysis"><i class="icon -report"></i><span class="-hidden">Analysis</span></a>');
		if ($isEditable) {
			$ui->add('<a href="'.url('school/dashboard/'.$orgid).'" title="School Dashboard"><i class="icon -setting"></i><span class="-hidden">Dashboard</span></a>');
		}
		$ret.=$ui->build();
	} else {
		$ui=new Ui(NULL,'ui-nav -info');
		$ui->add('<a href="'.url('school').'" title="School Kids Homepage"><i class="icon -home"></i><span class="-hidden">Home</span></a>');
		$ui->add('<a href="'.url('school/my').'" title="My Schools"><i class="icon -person"></i><span class="-hidden">My School</span></a>');
		$ret.=$ui->build();
		
	}

	return $ret;
}
?>