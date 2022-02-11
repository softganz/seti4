<?php
/**
 * My relate project such as Owner, Trainer
 *
 * @return String
 */
function project_my_project($self, $action = NULL) {
	R::View('project.toolbar',$self,'ติดตามและประเมินผลโครงการ','my',$projectInfo,'{modulenav:false}');

	if (!i()->ok) return '<p class="notify">กรุณาเข้าสู่ระบบสมาชิก</p>'.R::View('signform');


	if ($action) {
		switch ($action) {
			case 'new':
				$ret .= R::Page('project.create',NULL);
				break;

			default:
				# code...
				break;
		}
		return $ret;
	}




	$ret.=R::Page('project.my.action.post',NULL);
	//$ret.='<div data-load="project/list?u='.i()->uid.'"></div>';

	$isCreateProject = user_access('create project content')
		&&  in_array('my/project', explode(',', cfg('PROJECT.PROJECT.ADD_FROM_PAGE')));

	if ($isCreateProject) {
		$ret .= (new FloatingActionButton([
			'children' => [
				'<a class="sg-action btn -floating" href="'.url('project/create/'.$tpid, array('rel' => 'box')).'" data-rel="box" data-width="640" title="Create New Project"><i class="icon -material">add</i><span>เพิ่มโครงการ</span></a>',
			],
		]))->build();

		// $ret.='<nav class="nav btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/my/project/new').'" title="Create New Project"><i class="icon -addbig -white"></i></a></nav>';
	}


	return $ret;
}
?>