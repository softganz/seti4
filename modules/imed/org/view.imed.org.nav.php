<?php
/**
* Project detail
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_imed_org_nav($rs,$options) {
	$orgId = $rs->orgid;
	$submenu=q(2);
	$ret='';
	$isAdmin=$rs->project->RIGHT & IS_ADMIN;
	$isRight=$rs->project->RIGHT & _IS_ACCESS;
	$isEdit=$rs->project->RIGHT & IS_EDITABLE;

	$ui = new Ui();
	$ui->add('<a href="'.url('imed/org/'.$orgId).'"><i class="icon -home"></i><span>Home</span></a>');
	$ui->add('<a href="'.url('imed/org/'.$orgId.'/member').'"><i class="icon -people"></i><span>Member</span></a>');
	$ui->add('<a href="'.url('imed/org/'.$orgId.'/patient').'"><i class="icon -disabled-people"></i><span>Patient</span></a>');
	$ui->add('<a href="'.url('imed/org/'.$orgId.'/setting').'"><i class="icon -setting"></i><span>Setting</span></a>');
	$ret .= '<nav class="nav -icons -sg-text-center">'.$ui->build().'</nav>';

	return $ret;
}
?>