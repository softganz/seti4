<?php
/**
* Create new project planning
*
* @param Object $self
* @return String
*/

$debug = true;

function project_set_valuation($self, $tpid) {
	$projectInfo=R::Model('project.get',$tpid,'{debug:false}');

	R::View('project.toolbar',$self, $projectInfo->title,'set', $projectInfo);

	$ret .= R::Page('project.valuation', NULL, $tpid);
	return $ret;
}
?>