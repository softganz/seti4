<?php
/**
* Project owner
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_edit($self, $tpid = NULL, $action = NULL, $tranId = NULL) {

	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid, '{initTemplate: true}');
	$tpid = $projectInfo->tpid;

	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	if (!$tpid) return 'ERROR : NO PROJECT';
	else if (!$isEdit) return 'ERROR: ACCESS DENIED';


	$ret = '';
	//$ret .= 'Action = '.$action. ' Is edit = '.($isEdit ? 'YES' : 'NO').'<br />';
	//$ret .= print_o($planningInfo, '$planningInfo');
	//$ret .= print_o($orgInfo, '$orgInfo');

	switch ($action) {

		case 'period.add' :
			$lastPeriod = mydb::select('SELECT MAX(`period`) `lastPeriod` FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = "info" AND `part` = "period" LIMIT 1',':tpid',$tpid)->lastPeriod;

			if ($lastPeriod < cfg('project.period.max')) {
				$stmt = 'INSERT INTO %project_tr%
								(`tpid`, `uid`, `formid`, `part`, `period`, `created`)
								VALUES
								(:tpid, :uid, :formid, :part, :period, :created)';
				mydb::query($stmt,':tpid',$tpid, ':uid', i()->uid, ':formid', 'info', ':part', 'period', ':period', $lastPeriod+1, ':created',date('U'));

				$ret .= 'Add completed';
			} else $ret .= 'จำนวนงวดสูงสุดแล้ว';
			break;

		case 'period.remove' :
			mydb::query('DELETE FROM %project_tr% WHERE `trid` = :trid LIMIT 1',':trid',$tranId);
			$ret .= 'Remove complete';
			break;

	}

	return $ret;
}
?>