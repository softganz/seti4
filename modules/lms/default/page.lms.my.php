<?php
/**
* LMS :: My Information
* Created 2020-07-11
* Modify  2020-07-11
*
* @param Object $self
* @return String
*/

$debug = true;

function lms_my($self ) {
	R::View('toolbar', $self, 'ข้อมูลส่วนตัว/'.$studentInfo->name, 'lms', $studentInfo, '{searchform: false}');

	if (!i()->ok) {
		return R::View('signform');
	}

	$ret = '';

	$studentInfo = R::Model('lms.student.get', Array('uid' => i()->uid), '{debug: false}');

	if (empty($studentInfo)) return message('error', 'ไม่มีข้อมูลนักศึกษา');

	$args = func_get_args();
	$argIndex = 3; // Start argument

	if (empty($action) && empty($studentId)) $action = 'home';
	else if (empty($action) && $studentId) $action = 'view';

	//debugMsg('PAGE LMS = '.$studentId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg['.$argIndex.'] = '.$args[$argIndex]);
	//debugMsg(func_get_args(), '$args');

	$ret = R::Page(
		'lms.my.'.$action,
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