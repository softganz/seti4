<?php
function project_data_home($self,$tpid=NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get', $tpid, '{initTemplate:true}');

	$tpid = $projectInfo->tpid;

	if (empty($projectInfo)) return message('error','ERROR : No Project');

	R::View('project.toolbar',$self, $projectInfo->title, NULL, $projectInfo);

	$isAdmin = $projectInfo->info->RIGHT & _IS_ADMIN;

	$ret .= '<h2>แบบบันทึกข้อมูล</h2>';

	$ui = new Ui(NULL, 'ui-card');

	$ui->add('<a href="'.url('project/data/'.$tpid.'/info').'"><i class="icon -viewdoc"></i><span><b>ข้อมูลองค์กร</b></span></a><p><em>บันทึกข้อมูลองค์กร</em></p>');

	$ret .= $ui->build();

	return $ret;
}
?>