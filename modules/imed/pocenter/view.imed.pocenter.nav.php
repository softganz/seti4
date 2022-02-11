<?php
/**
* Project detail
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_imed_pocenter_nav($orgInfo, $options) {
	$orgId = $orgInfo->orgid;
	$submenu=q(2);
	$ret='';
	$isAdmin=$rs->project->RIGHT & IS_ADMIN;
	$isRight=$rs->project->RIGHT & _IS_ACCESS;
	$isEdit=$rs->project->RIGHT & IS_EDITABLE;

	$ui = new Ui();
	//$ui->add('<a href="'.url('imed/pocenter').'"><i class="icon -home"></i><span>{tr:Home,หน้าแรก}</span></a>');

	if ($orgId) {
		$ui->add('<a href="'.url('imed/pocenter/'.$orgId).'"><i class="icon -material">home</i><span>{tr:Home,หน้าแรก}</span></a>');
		//$ui->add('<a href="'.url('imed/pocenter/'.$orgId.'/stock.balance').'"><i class="icon -material">list</i><span>LIST</span></a>');
		//$ui->add('<a href="'.url('imed/pocenter/'.$orgId.'/stock').'"><i class="icon -material">swap_horiz</i><span>STOCK</span></a>');
		$ui->add('<a href="'.url('imed/pocenter/'.$orgId.'/contact').'"><i class="icon -material">contacts</i><span>CONTACT</span></a>');
		$ui->add('<a href="'.url('imed/pocenter/'.$orgId.'/setting').'"><i class="icon -material">settings</i><span>SETTING</span></a>');
	} else {
		$ui->add('<a class="" href="{url:imed/pocenter/register}"><i class="icon -material">add_circle</i><span>ลงทะเบียน</span></a>');
	}
	$ret .= '<nav class="nav -icons -sg-text-center">'.$ui->build().'</nav>';

	return $ret;
}
?>