<?php
/**
* Project owner
*
* @param Object $self
* @return String
*/
function project_qt($self, $tpid = NULL, $form = NULL, $action = NULL, $trid = NULL) {
	$self->theme->title='จัดการแบบสอบถาม';

	if (!is_numeric($tpid)) unset($tpid);

	if (empty($tpid)) return R::Page('project.qt.home');



	R::Module('project.template',$self,$tpid);

	$projectInfo=R::Model('project.get', $tpid, '{data: "info"}');

	if (empty($projectInfo)) return message('error','ERROR : No Project');

	R::View('project.toolbar',$self, $projectInfo->title, NULL, $projectInfo);



	if (empty($action)) $action='home';
	$ret.=R::Page('project.qt.'.$form, $self, $tpid, $action, $trid);

	//$ret.=print_o($projectInfo,'$projectInfo');


	return $ret;
}
?>