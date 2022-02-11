<?php
/**
* Project Set View
*
* @param Object $self
* @param Int $tpid
* @return String
*/

$debug = true;

function project_set_view($self, $projectInfo = NULL) {
	if (!($tpid = $projectInfo->tpid)) return message('error', 'PROCESS ERROR');

	$ret = '';

	$ret .= R::Page('project.set.home',$self,$tpid);
	return $ret;
}
?>