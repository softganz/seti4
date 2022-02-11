<?php
/**
* Module Method
* Created 2019-06-01
* Modify  2019-06-01
*
* @param Object $self
* @param Int $seqId
* @return String
*/

$debug = true;

import('model:imed.visit');

function imed_app_visit_render($self, $seqId) {
	$ret = '';

	$psnId = mydb::select('SELECT `pid` `psnid` FROM %imed_service% WHERE `seq` = :seq LIMIT 1', ':seq', $seqId)->psnid;

	$psnInfo = R::Model('imed.patient.get', $psnId, '{data: "info"}');
	$psnId = $psnInfo->psnId;

	if ($psnInfo->_empty) return message('error','ไม่มีข้อมูล');

	//debugMsg($psnInfo,'$psnInfo');
	$myUid = i()->uid;

	$visitInfo = ImedVisitModel::get($psnId, $seqId);

	// debugMsg($visitInfo,'$visitInfo');


	$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
	$isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

	if ($isAccess || $visitInfo->uid == $myUid) {
		$ret .= '<div class="ui-item" id="noteUnit-'.$visitInfo->seqId.'">';
		$ret .= R::View('imed.visit.render', $visitInfo, '{page: "app"}');
		$ret .= '</div>';
	} else if (cfg('imed.visit.realtime.show.unaccess')) {
		$visitInfo->rx = '...';
		$ret .= '<div class="ui-item" id="noteUnit-'.$visitInfo->seqId.'">';
		$ret .= R::View('imed.visit.render', $visitInfo, '{page: "app"}');
		$ret .= '</div>';
	}

	return $ret;
}
?>