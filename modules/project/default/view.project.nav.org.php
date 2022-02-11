<?php
/**
* Project detail
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_project_nav_org($info, $options = NULL) {
	$fundid = $info->fundid;
	$orgId = $info->orgid;
	$submenu = q(2);
	$ret = '';

	if ($fundid) {
		$ui = new Ui(NULL,'ui-nav -fund -sg-text-center');
		$ui->add('<a href="'.url('project/fund/'.$orgId).'"><i class="icon -home"></i><span class="">กองทุน</span></a>');
		$ui->add('<a href="'.url('project/fund/'.$orgId.'/planning').'"><i class="icon -diagram"></i><span>แผนงาน</span></a>');
		$ui->add('<a href="'.url('project/fund/'.$orgId.'/financial').'"><i class="icon -money"></i><span>การเงิน</span></a>');
		$ui->add('<a href="'.url('project/fund/'.$orgId.'/proposal').'"><i class="icon -nature-people"></i><span>พัฒนาโครงการ</span></a>');
		$ui->add('<a href="'.url('project/fund/'.$orgId.'/follow').'"><i class="icon -walk"></i><span>ติดตามโครงการ</span></a>');
		$ui->add('<a href="'.url('project/fund/'.$orgId.'/estimate').'" title="แบบประเมินตนเองของกองทุน"><i class="icon -description"></i>แบบประเมิน</span></a>');
		//$ui->add('<a href="'.url('project/fund/report/'.$fundid).'">รายงาน</a>');
		$ui->add('<a href="javascript:window.print()" title="พิมพ์"><i class="icon -print"></i><span class="">พิมพ์</span></a>');
		$ret .= $ui->build();
	} else if ($orgId) {
		$ui = new Ui(NULL,'ui-nav -org -sg-text-center');
		$ui->add('<a href="'.url('org/'.$orgId).'"><i class="icon -home"></i><span class="">องค์กร</span></a>');
		$ui->add('<a href="'.url('org/'.$orgId.'/planning').'"><i class="icon -diagram"></i><span>แผนงาน</span></a>');
		//$ui->add('<a href="'.url('project/org/financial/'.$orgId).'">การเงิน</a>');
		$ui->add('<a href="'.url('org/'.$orgId.'/proposal').'"><i class="icon -nature-people"></i><span>พัฒนา</span></a>');
		$ui->add('<a href="'.url('org/'.$orgId.'/follow').'"><i class="icon -walk"></i><span>ติดตาม</span></a>');
		//$ui->add('<a href="'.url('project/org/estimate/'.$orgId).'" title="แบบประเมินตนเองของกองทุน">แบบประเมิน</a>');
		//$ui->add('<a href="'.url('project/fund/report/'.$orgId).'">รายงาน</a>');
		$ui->add('<a href="javascript:window.print()" title="พิมพ์"><i class="icon -print"></i><span class="">พิมพ์</span></a>');
		$ret .= $ui->build();
	} else {
		$ui = new Ui(NULL,'ui-nav -org -sg-text-center');
		$ui->add('<a href="'.url('project/org').'"><i class="icon -home"></i><span class="-hidden">องค์กร</span></a>');
		$ret .= $ui->build();
	}
	//$ret.=print_o($rs,'$rs');
	return $ret;
}
?>