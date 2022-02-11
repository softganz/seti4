<?php
/**
* Set Project Proposal Status
*
* @param Object $self
* @param Object $proposalInfo
* @return String
*/

$debug = true;

function project_proposal_info_status($self, $proposalInfo) {
	$tpid = $proposalInfo->tpid;

	if (!$tpid) return message('error', 'PROCESS ERROR');

	$isAdmin = $proposalInfo->RIGHT & _IS_ADMIN;

	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>Project Proposal Status</h3></header>';


	$status = array(
		1 => 'กำลังพัฒนาโครงการ',
		2 => 'กำลังพิจารณาโครงการ',
		3 => 'ปรับแก้โครงการ',
		5 => 'โครงการผ่านการพิจารณา',
		8 => 'โครงการไม่ผ่านการพิจารณา',
		9 => 'ยกเลิกโครงการ',
		10 => 'ติดตามและประเมินผล',
	);

	if ($isAdmin) {
		$ui = new Ui();
		foreach ($status as $key => $value) {
			$isActive = $proposalInfo->info->status == $key;
			$ui->add('<a class="sg-action btn'.($isActive ? ' -active' : '').'" href="'.url('project/proposal/api/'.$tpid.'/status.set/'.$key).'" style="width: 20em; display: block; margin: 32px auto;" data-rel="none" data-done="close | reload" title="กำหนดสถานะเป็น '.$value.'"><i class="icon -material">'.($isActive ? 'done_all' : 'done').'</i><span>'.$value.'</span></a>');
		}

		$ret .= $ui->build();
	} else {
		$ret .= 'สถานะข้อเสนอโครงการ : '.$status[$proposalInfo->info->status];
	}

	//$ret .= print_o($proposalInfo,'$proposalInfo');
	$ret .= '<style type="text/css">
	.box-page .ui-action .sg-action:not(.-active)>.icon {display: none;}
	.box-page .ui-action .sg-action:hover>.icon {display: inline-block;}
	</style>';
	return $ret;
}
?>