<?php
/**
 * My relate project such as Owner, Trainer
 *
 * @return String
 */
function project_my($self) {
	//R::View('project.toolbar',$self,'โครงการในความรับผิดชอบ');
	$title='@'.(i()->ok?i()->name:'Welcome');
	R::View('project.toolbar',$self,$title,'my',$projectInfo,'{modulenav:false}');

	if (!i()->ok) return '<p class="notify">กรุณาเข้าสู่ระบบสมาชิก</p>'.R::View('signform');

	$ret.=R::Page('project.my.eval',NULL);
	return $ret;
}
?>