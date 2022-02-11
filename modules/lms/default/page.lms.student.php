<?php
/**
* LMS :: Student Controler
* Created 2020-07-11
* Modify  2020-07-11
*
* @param Object $self
* @param Int $studentId
* @param String $action
* @return String
*/

$debug = true;

function lms_student($self, $studentId = NULL, $action = NULL) {
	$isAdmin = user_access('administer lms');

	$ret = '';

	if ($studentId) {
		$studentInfo = R::Model('lms.student.get', $studentId);
	}

	if (empty($studentId)) $studentInfo = $studentId;

	$args = func_get_args();
	$argIndex = 3; // Start argument

	if (empty($action) && empty($studentId)) $action = 'home';
	else if (empty($action) && $studentId) $action = 'view';


	//debugMsg('PAGE LMS = '.$studentId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg['.$argIndex.'] = '.$args[$argIndex]);
	//debugMsg(func_get_args(), '$args');


	$ret = R::Page(
		'lms.student.'.$action,
		$self,
		$studentInfo,
		$args[$argIndex],
		$args[++$argIndex],
		$args[++$argIndex],
		$args[++$argIndex],
		$args[++$argIndex]
	);

	if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

	//$ret .= print_o($studentInfo,'$studentInfo');

	return $ret;
}
?>