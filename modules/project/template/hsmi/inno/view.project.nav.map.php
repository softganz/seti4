<?php
/**
* Project detail
*
* @param Object $self
* @param Object $projectInfo
* @param Object $options
* @return String
*/
function view_project_nav_map($projectInfo, $options = NULL) {
	$ret = R::View('project.nav.default', $projectInfo, $options);
	return $ret;
}
?>