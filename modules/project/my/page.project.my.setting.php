<?php
/**
 * My relate project such as Owner, Trainer
 *
 * @return String
 */
function project_my_setting($self) {
	//R::View('project.toolbar',$self,'โครงการในความรับผิดชอบ');
	$title='@'.(i()->ok?i()->name:'Welcome');
	R::View('project.toolbar',$self,$title,'my',$projectInfo,'{modulenav:false}');

	if (!i()->ok) return '<p class="notify">กรุณาเข้าสู่ระบบสมาชิก</p>'.R::View('signform');

	$isAdmin=user_access('admin');

	if ($isAdmin) {
		$ret.='<a class="btn -info" href="'.url('project/my/action/*').'">All Actions</a>';
	} else {
		$ret.='<p class="notify">อยู่ระหว่างดำเนินการ</p>';
	}
	return $ret;
}
?>