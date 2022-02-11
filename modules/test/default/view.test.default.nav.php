<?php
/**
* Project detail
*
* @param Object $self
* @param Object $psn
* @param Object $para
* @return String
*/
function view_test_default_nav($psn,$options) {
	$tpid=$psn->tpid;
	$submenu=q(2);
	$ret='';
	$isAdmin=$psn->project->RIGHT & IS_ADMIN;
	$isRight=$psn->project->RIGHT & _IS_ACCESS;
	$isEdit=$psn->project->RIGHT & IS_EDITABLE;

	if ($psn->psnid) {
		$ui=new Ui(NULL,'ui-nav -info');
		$ui->add('<a href="'.url('test').'"><i class="icon -home"></i><span class="-hidden">Home</span></a>');
		$ret.=$ui->build();
	} else {
		
		$ui=new Ui(NULL,'ui-nav -info');
		$ui->add('<a href="'.url('test').'"><i class="icon -home"></i><span class="-hidden">Home</span></a>');
		$ret.=$ui->build();
		
	}

	return $ret;
}
?>