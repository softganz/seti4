<?php
function view_project_nav_idea($info=NULL,$options='{}') {
	$ret='';

	$isAdmin=user_access('administer projects');

	//$ret.='<form id="search" class="search-box" method="get" action="'.url('project/idea/search').'" name="memberlist" role="search"><input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="ป้อนชื่อแนวคิด" data-query="'.url('project/get/idea').'" data-callback="'.url('project/idea/view/').'" data-altfld="sid"><input type="submit" class="button" value="ค้นหาแนวคิด"></form>'._NL;

	$ui=new ui(NULL,'ui-nav');
	$dboxUi=new Ui(NULL,'ui-dropbox');

	$ui->add('<a class="" href="'.url('project/idea').'" title="Project Concept Paper"><i class="icon -home"></i><span class="-hidden">รวมแนวคิด</span></a>');
	$ret.=$ui->build();

	$dboxUi->add('<a class="" href="'.url('project/idea').' " title="Project Concept Paper"><i class="icon -home"></i><span class="-hidden">รวมแนวคิด</span></a>');
	if (!empty($info->proposalId)) {
		$dboxUi->add('<a class="" href="'.url('project/develop/'.$info->tpid).'"><i class="icon -viewdoc"></i><span>พัฒนาโครงการ</span></a>');
	}
	if (!empty($info->followId)) {
		$dboxUi->add('<a class="" href="'.url('paper/'.$info->tpid).'"><i class="icon -viewdoc"></i><span>ติดตามโครงการ</span></a>');
	}

	if ($info->tpid) {
		$ui=new ui(NULL,'ui-nav');
		$ui->add('<a class="" href="'.url('project/idea/view/'.$info->tpid).'"><i class="icon -viewdoc"></i><span>แนวคิดเบื้องต้น</span></a>');
		if ($info->proposalId) $ui->add('<a class="" href="'.url('project/develop/'.$info->tpid).'"><i class="icon -viewdoc"></i><span>พัฒนาโครงการ</span></a>');
		if ($info->followId) $ui->add('<a class="" href="'.url('paper/'.$info->tpid).'"><i class="icon -viewdoc"></i><span>ติดตามโครงการ</span></a>');
		if ($isAdmin) {
			if (empty($info->proposalId)) {
				$dboxUi->add('<a class="sg-action" href="'.url('project/idea/view/'.$info->tpid.'/todev').'" data-confirm="ต้องการสร้างโครงการพัฒนาจากแนวคิดนี้ กรุณายืนยัน?"><i class="icon -adddoc"></i><span>สร้างเป็นโครงการพัฒนา</span></a>');
			}
		}
		$ret.=$ui->build()._NL;

		$ui=new ui(NULL,'ui-nav');
		$ui->add('<a class="" href="javascript:window.print()"><i class="icon -print"></i><span class="-hidden">พิมพ์</span></a>');
		$ret.=$ui->build()._NL;
	}


	$ret.=sg_dropbox($dboxUi->build(),'{class:"leftside -atright"}');

	return $ret;
}
?>