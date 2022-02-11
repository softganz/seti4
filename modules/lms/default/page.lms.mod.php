<?php
/**
* LMS :: Main Page
* Created 2020-07-01
* Modify  2020-07-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function lms_mod($self, $moduleId = NULL, $action = NULL) {
	$ret = '';

	if ($moduleId) {
		$moduleInfo = R::Model('lms.mod.get', $moduleId, '{initTemplate: true, type: "'.($isProjectAllType ? '*' : '').'"}');
	}

	if (empty($moduleInfo)) $courseInfo = $moduleId;

	$args = func_get_args();
	$argIndex = 3; // Start argument

	if (empty($action) && empty($moduleId)) $action = 'home';
	else if (empty($action) && $moduleId) $action = 'mod.view';

	//debugMsg('PAGE LMS = '.$moduleId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg['.$argIndex.'] = '.$args[$argIndex]);
	//debugMsg(func_get_args(), '$args');

	$ret = R::Page(
		'lms.mod.'.$action,
		$self,
		$moduleInfo,
		$args[$argIndex],
		$args[++$argIndex],
		$args[++$argIndex],
		$args[++$argIndex],
		$args[++$argIndex]
	);

	if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

	return $ret;
}
?>