<?php
/**
* Project :: Admin Create New Follow Project
* Created 2018-10-13
* Modify  2021-02-26
*
* @param Object $self
* @return String
*
* @usage project/admin/follow/create
*/

$debug = true;

function project_admin_follow_create($self) {
	R::View('project.toolbar',$self,'Create new project','admin');
	$self->theme->sidebar = R::View('project.admin.menu','follow');

	$post = (Object) post('data');

	if (empty($post->title)) {
		$ret .= R::View('project.admin.project.form',$post);
	} else {
		$result = R::Model('project.create',$post);
		$ret .= R::View('project.admin.project.form',$post);
		//$ret .= print_o($result,'$result');
		location('project/admin/follow');
	}
	//$ret.=print_o($post,'$post');
	return $ret;
}
?>