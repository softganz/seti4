<?php
/**
* Project Action Join Admin Information
* Created 2019-02-20
* Modify  2019-07-30
*
* @param Object $self
* @param Pbject $projectInfo
* @return String
*/

$debug = true;

function project_join_admininfo($self, $projectInfo, $psnid = NULL) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;


	$ret .= '<h2>Register Information</h2>';

	$doingInfo = R::Model('org.doing.get', array('calid' => $calId));

	$personInfo = $doingInfo->members[$psnid];

	$tables = new Table();
	foreach ($personInfo as $key => $value) {
		$tables->rows[] = array($key, $value);
	}
	$ret .= $tables->build();

	//$ret .= print_o($personInfo);

	//$ret .= print_o($doingInfo);

	return $ret;
}
?>