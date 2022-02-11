<?php
/**
* Create new project planning
*
* @param Object $self
* @return String
*/

$debug = true;

import('model:project.planning.php');

function project_planning_valuation($self, $tpid) {
	$planningInfo = ProjectPlanningModel::get($tpid, '{debug:false}');

	R::View('project.toolbar',$self, $planningInfo->title,'planning', $planningInfo);

	$ret .= R::Page('project.valuation', NULL, $tpid);
	return $ret;
}
?>