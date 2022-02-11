<?php
/**
* Lock/Unlock money on activity
*
* @param Integer $trid
* @return String
*/
function project_edit_lockmoney($self,$trid) {
	$ret['msg']='';
	$ret['value']='';

	$rs = mydb::select('SELECT * FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$trid);

	$projectInfo = R::Model('project.get',$rs->tpid);
	$tpid = $projectInfo->tpid;

	$isAdmin = user_access('administer projects') || $projectInfo->right->isAdmin;

	if (!$isAdmin) return $ret;


	if ($tpid) {
		$newFlag = $rs->flag == _PROJECT_LOCKREPORT ? _PROJECT_COMPLETEPORT : _PROJECT_LOCKREPORT;
		$ret['value'] = $newFlag == _PROJECT_LOCKREPORT ? 'Locked' : 'Unlock';
		$ret['msg'] = $ret['value'].' รายงานการเงินเรียบร้อยแล้ว';
		$ret['html'] .= 'New flag='.$newFlag;

		$stmt = 'UPDATE %project_tr% SET `flag`=:flag WHERE `trid`=:trid LIMIT 1';
		mydb::query($stmt,':trid',$trid, ':flag',$newFlag);

		//$ret['html'].=print_o($rs,'$rs');
	}

	return $ret;
}
?>