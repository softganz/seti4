<?php
/**
* Supplier Nav bar
*
* @param Object $rs
* @param Object $option
* @return String
*/
function view_org_nav_supplier($rs=NULL,$options=NULL) {
	$ui=new Ui(NULL,'ui-nav -info');
	$ui->add('<a href="'.url('org/gogreen').'"><i class="icon -home"></i><span class="-hidden">หน้าแรก</span></a>');
	$ui->add('<a href="'.url('org/gogreen/supplier').'"><i class="icon -list"></i><span class="-hidden">รายชื่อเครือข่าย</span></a>');
	$ui->add('<a href="javascript:window.print()" style="border-radius: 4px;"><i class="icon -print"></i><span class="-hidden">พิมพ์</span></a>');
	$ret.=$ui->build();


	$ui=new Ui(NULL,'ui-nav -add');
	$ui->add('<a class="btn -primary" href="'.url('org/gogreen/supplier/form').'"><i class="icon -addbig -white"></i><span class="">ลงทะเบียนเครือข่าย</span></a>');
	$ret.=$ui->build();

	$ret.=R::View('button.floating',url('org/gogreen/supplier/form'),'{title:"ลงทะเบียนเครือข่าย"}');

	return $ret;
}
?>