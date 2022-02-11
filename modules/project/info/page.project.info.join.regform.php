<?php
/**
* Project Info Invite People to join
*
* @param Object $self
* @param Int $tpid
* @return String
*/
function project_info_join_regform($self, $projectInfo, $calId = NULL) {
	$tpid = $projectInfo->tpid;

	$calId = SG\getFirst($calId,post('calid'));
	$action=post('action');

	$projectInfo->calid = $calId;
	if ($calId) {
		$calendarInfo = R::Model('project.calendar.get', $calId);
		$doingInfo = R::Model('org.doing.get', array('calid' => $calId),'{data: "info"}');
		$projectInfo->calid = $calendarInfo->calid;
		$projectInfo->calendarInfo = $calendarInfo;
		$projectInfo->doingInfo = $doingInfo;
	}

	$ret = '';
	R::View('project.toolbar', $self, $projectInfo->title, 'people', $projectInfo);

	$isMember = $projectInfo->info->membershipType;
	$isEdit = $projectInfo->info->isEdit;

	$isViewable = $isMember || $isEdit;

	if (!$isViewable) return message('error','Access Denied');
	if (empty($projectInfo->orgid)) {
		return message('error', 'ไม่สามารถสร้างบันทึกผู้เข้าร่วมกิจกรรมได้ เนื่องจากโครงการนี้ไม่ได้สังกัดภายใต้องค์กรใด ๆ');
	}

	$doRs = mydb::select('SELECT * FROM %org_doings% WHERE `calid`=:calid LIMIT 1',':calid',$calId);

	if ($doRs->doid) {
		$ret .= R::Page('project.join.printregister',$self, $projectInfo);
	}

	$ret .= '<style>.nav.-page {display: none;}</style>';

	//$ret .= print_o($doRs,'$doRs');
	//$ret .= print_o($projectInfo);

	return $ret;
}
?>