<?php
/**
* Project Localfund Menu
*
* @param Object $fundInfo
* @param Object $options
* @return String
*/
function view_project_nav_fund($fundInfo, $options = '{}') {
	$orgId = $fundInfo->orgid;
	list($submenu) = explode('.',q(3));

	$ret = '';

	$isWebAdmin = user_access('administer projects');
	$isEdit = $fundInfo->right->edit;

	$mainUi = new Ui(NULL,'ui-nav -fund -sg-text-center');
	$dboxUi = new Ui();

	if ($orgId) {
		$mainUi->add('<a href="'.url('project/fund/'.$orgId).'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>');
		$mainUi->add('<a href="'.url('project/fund/'.$orgId.'/financial').'"><i class="icon -material">attach_money</i><span>การเงิน</span></a>');
		$mainUi->add('<a href="'.url('project/fund/'.$orgId.'/planning').'"><i class="icon -material">device_hub</i><span>แผนงาน</span></a>');
		$mainUi->add('<a href="'.url('project/fund/'.$orgId.'/proposal').'"><i class="icon -material">nature_people</i><span>พัฒนาโครงการ</span></a>');
		$mainUi->add('<a href="'.url('project/fund/'.$orgId.'/follow').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ</span></a>');
		$mainUi->add('<a href="'.url('project/fund/'.$orgId.'/eval').'" title="แบบประเมินตนเองของกองทุน"><i class="icon -material">assessment</i><span>แบบประเมิน</span></a>');
		//$mainUi->add('<a href="'.url('project/fund/'.$orgId.'/report').'">รายงาน</a>');
		$mainUi->add('<a href="javascript:window.print()" title="พิมพ์"><i class="icon -material">print</i><span>พิมพ์</span></a>');

		if ($submenu == 'board') {
			$dboxUi->add('<a class="" href="'.url('project/fund/'.$orgId.'/board').'"><i class="icon -material">people</i><span>รายชื่อคณะกรรมการ</span></a>');
			if ($isEdit) {
				$dboxUi->add('<sep>');
				$dboxUi->add('<a class="sg-action" href="'.url('project/fund/'.$orgId.'/board.letter').'" data-rel="box"><i class="icon -material">assignment_turned_in</i><span>หนังสือแต่งตั้งคณะกรรมการ</span></a>');
				$dboxUi->add('<a class="sg-action" href="'.url('project/fund/'.$orgId.'/board.beover').'" data-rel="box" data-width="480"><i class="icon -material">reply_all</i><span>บันทึกคณะกรรมการหมดวาระ</span></a>');
				$menu = sg_dropbox($dboxUi->build());
			}
			$dboxUi->add('<sep>');
			$dboxUi->add('<a href="'.url('project/fund/'.$orgId.'/board.all').'"><i class="icon -material">assignment_ind</i><span>ทำเนียบกรรมการ</span></a>');
		}
	} else {
		$mainUi->add('<a href="'.url('project/fund').'"><i class="icon -material">home</i><span>กองทุนฯ</span></a>');
		$mainUi->add('<a href="'.url('project/fund/my').'"><i class="icon -material">person</i><span>กองทุนของฉัน</span></a>');
	}

	if ($isWebAdmin) {
		if ($dboxUi->count()) $dboxUi->add('<sep>');
		$dboxUi->add('<a href="'.url('project/fund/financial').'"><i class="icon -material">attach_money</i><span>บัญชี/การเงิน</span></a>');
		$dboxUi->add('<a class="" href="'.url('project/fund/setting').'"><i class="icon -material">settings</i><span>Setting</span></a>');
	}

	$ret .= $mainUi->build();
	if ($dboxUi->count()) $ret .= sg_dropbox($dboxUi->build(),'{class:"leftside -atright"}');

	return $ret;
}
?>