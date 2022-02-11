<?php
function view_org_nav_docs($info = NULL, $options = '{}') {
	$ret='';
	$ui=new Ui(NULL,'ui-nav -info');
	$ui->add('<a href="'.url('org/docs').'"><i class="icon -home"></i><span class="-hidden">หนัาหลัก</span></a>');

	if ($info->orgid) {
		$ui->add('<a href="'.url('org/docs/o/'.$info->orgid.'/out').'"><i class="icon -upload"></i><span class="">หนังสือออก</span></a>');
		$ui->add('<a href="'.url('org/docs/o/'.$info->orgid.'/in').'"><i class="icon -download"></i><span class="">หนังสือเข้า</span></a>');
	}

	$ui->add('<a href="'.url('org/docs/setting').'"><i class="icon -setting"></i><span class="-hidden">Setting</span></a>');
	$ret.=$ui->build();

	/*
	$ui=new Ui(NULL,'ui-nav -add -toright');
	$ui->add('<a class="btn -primary" href="'.url('project/assessor/register').'" style="border-radius: 8px; box-shadow: none; border: none;"><i class="icon -addbig -white -circle"></i><span>ลงทะเบียนนักติดตามประเมินผล</span></a>');
	$ret.=$ui->build();
	*/
	return $ret;
}
?>