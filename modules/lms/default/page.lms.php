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

function lms($self, $courseId = NULL, $action = NULL) {
	$isAdmin = user_access('administer lms');

	$ret = '';

	if (preg_match('/^manage\./',$action) && !$isAdmin) return R::View('lms.error', $self, 'error', 'Access Denied');

	if (substr($courseId, -1) == '*') list($courseId, $isProjectAllType) = array(substr($tpid,0,-1),true);

	if ($courseId) {
		$courseInfo = R::Model('lms.course.get', $courseId, '{initTemplate: true, type: "'.($isProjectAllType ? '*' : '').'"}');
	}

	if (empty($courseInfo)) $courseInfo = $courseId;

	$args = func_get_args();
	$argIndex = 3; // Start argument

	if (empty($action) && empty($courseId)) $action = 'home';
	else if (empty($action) && $courseId) $action = 'course.home';


	//debugMsg('PAGE LMS = '.$courseId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg['.$argIndex.'] = '.$args[$argIndex]);
	//debugMsg(func_get_args(), '$args');


	$ret = R::Page(
		'lms.'.$action,
		$self,
		$courseInfo,
		$args[$argIndex],
		$args[++$argIndex],
		$args[++$argIndex],
		$args[++$argIndex],
		$args[++$argIndex]
	);

	//debugMsg('TYPE = '.gettype($ret));
	if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

	//$ret .= R::Page('project.'.$action, $self, $tpid);
	//$ret .= print_o($projectInfo,'$projectInfo');
	//$ret .= message('error', 'Action incorrect');

	return $ret;
}
?>