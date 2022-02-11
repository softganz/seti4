<?php
/**
* iMed :: Home Visit Page Controller
* Created 2019-03-11
* Modify  2021-05-28
*
* @param Integer $psnId
* @param String $action
* @param Integer $seqId
* @return String/Widget
*
* @usage imed/visit/{psnId}/{action}/{seqId}
*/

$debug = true;

import('model:imed.visit');

// TODO:: Remove all switch action, Accept only page controller
function imed_visit($self, $psnId = NULL, $action = NULL, $seqId = NULL) {
	if (empty($action) && empty($psnId)) return R::Page('imed.visit.home');
	else if (empty($action) && $psnId) return R::Page('imed.visit.info', $psnId);


	$uid = i()->uid;
	$psnInfo = R::Model('imed.patient.get',$psnId);

	$visitInfo = $psnId && $seqId>0 ? ImedVisitModel::get($psnId, $seqId) : (Object)['seqId' => $seqId];
	$isAccess = $psnInfo->RIGHT & _IS_ACCESS || $visitInfo->uid == $uid;
	$isEdit = is_admin('imed') || $visitInfo->uid == $uid;

	// debugMsg('psnId = '.$psnId.' Action = '.$action);
	// debugMsg($visitInfo,'$visitInfo');

	if (empty($psnInfo)) return message('error','ไม่มีข้อมูลบุคคลตามที่ระบุ');
	else if ($seqId>0 && empty($visitInfo)) return message('error', 'ไม่มีข้อมูลเยี่ยมบ้านตามที่ระบุ');
	else if ($seqId>0 && !$isAccess) return message('error',$psnInfo->error);

	$argIndex = 3; // Start argument

	//$ret .= 'PAGE IMED psnId = '.$psnId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex).'<br />';
	//$ret .= print_o(func_get_args(), '$args');

	$ret = R::Page(
		'imed.visit.'.$action,
		$psnInfo,
		$visitInfo,
		func_get_arg($argIndex+1),
		func_get_arg($argIndex+2),
		func_get_arg($argIndex+3),
		func_get_arg($argIndex+4)
	);
	if (is_string($ret) && trim($ret) == '') $ret = 'ERROR : PAGE NOT FOUND';

	return $ret;
}
?>