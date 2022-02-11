<?php
/**
* Project Info Invite People to join
*
* @param Object $self
* @param Int $tpid
* @return String
*/
function project_info_join_joined($self, $projectInfo, $calId = NULL) {
	$tpid = $projectInfo->tpid;

	$calId = SG\getFirst($calId,post('calid'));
	$action=post('action');

	$projectInfo->calid = $calId;

	$ret = '';
	R::View('project.toolbar', $self, $projectInfo->title, 'people', $projectInfo);

	$isEdit = $projectInfo->info->isEdit;

	$calRs=mydb::select('SELECT * FROM %calendar% WHERE `id`=:calid LIMIT 1',':calid',$calId);

	if (empty($projectInfo->orgid)) {
		return message('error', 'ไม่สามารถสร้างบันทึกผู้เข้าร่วมกิจกรรมได้ เนื่องจากโครงการนี้ไม่ได้สังกัดภายใต้องค์กรใด ๆ');
	}

	$doRs = mydb::select('SELECT * FROM %org_doings% WHERE `calid`=:calid LIMIT 1',':calid',$calId);

	//$ret .= print_o($doRs,'$doRs');

	if ($doRs->doid) {

	//$ret .= R::Page('org.meeting.info',NULL, $orgId, $doid, 'invite');
		$ret.='<div class="sg-load" id="org-meeting-info" data-url="'.url('org/'.$doRs->orgid.'/meeting.info/'.$doRs->doid.'/join').'"></div>';
	}

	head('js.org.js','<script type="text/javascript" src="org/js.org.js"></script>');

	return $ret;
}
?>