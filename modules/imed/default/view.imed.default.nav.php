<?php
/**
* Project detail
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_imed_default_nav($rs,$options) {
	$tpid=$rs->tpid;
	$submenu=q(2);
	$ret='';
	$isAdmin=$rs->project->RIGHT & IS_ADMIN;
	$isRight=$rs->project->RIGHT & _IS_ACCESS;
	$isEdit=$rs->project->RIGHT & IS_EDITABLE;

	if ($rs->psnid) {
		$ui=new Ui(NULL,'ui-nav -info');
		$ui->add('<a href="'.url('imed').'"><i class="icon -home"></i><span class="-hidden">Home</span></a>');
		$ret.=$ui->build();
	} else {
		$ui=new Ui(NULL,'ui-nav -info');
		$ui->add('<a href="'.url('imed').'"><i class="icon -home"></i><span class="-hidden">Home</span></a>');
		$ret.=$ui->build();
	}

	return $ret;
}
?>