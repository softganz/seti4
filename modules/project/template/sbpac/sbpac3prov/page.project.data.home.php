<?php
function project_data_home($self,$tpid=NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get', $tpid, '{initTemplate:true}');

	$tpid = $projectInfo->tpid;

	if (empty($projectInfo)) return message('error','ERROR : No Project');

	R::View('project.toolbar',$self, $projectInfo->title, NULL, $projectInfo);

	$isAdmin = $projectInfo->info->RIGHT & _IS_ADMIN;

	$ret .= '<h2>แบบบันทึกข้อมูล</h2>';

	$ui = new Ui(NULL, 'ui-card');

	$ui->add('<a href="'.url('project/data/'.$tpid.'/info').'"><i class="icon -viewdoc"></i><span><b>แผนปฏิบัติการหมู่บ้านเข้มแข็ง มั่นคง มั่งคั่ง ยั่งยืน</b></span></a><p><em>บันทึกข้อมูลแผนปฏิบัติการหมู่บ้านเข้มแข็ง มั่นคง มั่งคั่ง ยั่งยืน โครงการขยายผลพัฒนาหมู่บ้าน/ชุมชนเข้มแข็ง มั่นคง มั่งคง ยั่งยืน</em></p>');

	if ($isAdmin) {
		$ui->add('<a href="'.url('project/qt/'.$tpid.'/board').'"><b>แบบสำรวจความคิดเห็นของกลไกคณะกรรมการหมู่บ้าน</b></a><p><em>บันทึกข้อมูลแบบสำรวจความคิดเห็นของกลไกคณะกรรมการหมู่บ้าน</em></p>');

		$ui->add('<a href="'.url('project/qt/'.$tpid.'/people').'"><b>แบบสำรวจความคิดเห็นของประชาชน</b></a><p><em>บันทึกแบบสำรวจความคิดเห็นของประชาชน</em></p>');
	}

	$ret .= $ui->build();

	return $ret;
}
?>