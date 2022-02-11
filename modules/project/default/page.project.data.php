<?php
function project_data($self, $tpid = NULL, $action = NULL, $pageAction = NULL, $transId = NULL) {
	if (!is_numeric($tpid)) unset($tpid);

	if (empty($tpid)) return R::Page('project.data.home');



	R::Module('project.template',$self,$tpid);

	$projectInfo = R::Model('project.get', $tpid);

	if (empty($projectInfo)) return message('error','ERROR : No Project');

	R::View('project.toolbar', $self, $projectInfo->title, NULL, $projectInfo);

	$isEdit = $projectInfo->info->isEdit;

	switch ($action) {
		case 'post':

			break;

		case 'edit':
			break;

		case 'save':
			return $ret;
			break;

		default:

			if (empty($action))
				$action='home';

			$ret .= R::Page('project.data.'.$action, $self, $projectInfo, $pageAction, $transId);
			break;
	}
	//$ret.=print_o($projectInfo,'$projectInfo');

	return $ret;
}
?>