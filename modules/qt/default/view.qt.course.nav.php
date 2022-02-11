<?php
/**
* Project detail
*
* @param Object $self
* @param Object $psn
* @param Object $para
* @return String
*/
function view_qt_course_nav($psn=NULL,$options=NULL) {
	$ret='';

	if ($psn->psnid) {
		$ui=new Ui(NULL,'ui-nav -info');
		$ui->add('<a href="'.url('qt/group/course').'"><i class="icon -home"></i><span class="-hidden">Home</span></a>');
		$ret.=$ui->build();
	} else {
		$ui=new Ui(NULL,'ui-nav -info');
		$ui->add('<a href="'.url('qt/group/course').'"><i class="icon -home"></i><span class="-hidden">Home</span></a>');
		if (i()->ok) $ui->add('<a href="'.url('qt/group/course/profile/'.i()->uid).'"><i class="icon -person"></i><span class="-hidden">Profile</span></a>');
		$ui->add('<a href="'.url('qt/group/course/summary').'"><i class="icon -report"></i><span class="-hidden">Report</span></a>');
		$ret.=$ui->build();		
	}
	return $ret;
}
?>