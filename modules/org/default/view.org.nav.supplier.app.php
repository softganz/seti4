<?php
/**
* Project detail
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_org_nav_supplier_app($rs,$options) {
	$ui=new Ui(NULL,'ui-nav -info');
	$ui->add('<a href="'.url('org/gogreen/app').'"><i class="icon -home"></i><span class="-hidden">หน้าแรก</span></a>');
	$ui->add('<a href="'.url('org/gogreen/app/supplier/list').'"><i class="icon -list"></i><span class="-hidden">รายชื่อเครือข่าย</span></a>');
	$ret.=$ui->build();

	$ui=new Ui(NULL,'ui-nav -add');
	$ui->add('<a class="btn -primary" href="'.url('org/gogreen/app/supplier/form').'"><i class="icon -addbig -white -circle"></i><span class="">ลงทะเบียนเครือข่าย</span></a>');
	$ret.=$ui->build();

	return $ret;
}
?>