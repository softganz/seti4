<?php
/**
* Add/edit objective of project
*
* @param Integer $actid
* @return Location
*/
function project_edit_objective($self,$action,$actid) {
if ($action=='remove') {
	$tpid=mydb::select('SELECT `tpid` FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$actid)->tpid;
} else {
	$tpid=$actid;
}

if ( ! (user_access('administer projects') || project_model::is_trainer_of($tpid) || project_model::is_owner_of($tpid)) ) return 'Access denied';
if (empty($tpid) || mydb::select('SELECT `tpid` FROM %topic% WHERE `tpid`=:tpid AND `type`="project" LIMIT 1',':tpid',$tpid)->_empty) return 'No project';

switch ($action) {
	case 'add' :
		$stmt='INSERT INTO %project_tr% (`tpid`, `parent`, `uid`, `formid`, `part`, `created`) VALUES (:tpid, 1, :uid, :formid, :part, :created)';
		mydb::query($stmt,':tpid',$tpid, ':uid', i()->uid, ':formid', 'info', ':part', 'objective', ':created',date('U'));
		$ret['html'].='Add completed';
		break;
	case 'remove' :
		mydb::query('DELETE FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$actid);
		$ret['html'].='Remove complete';
		break;
	}
location('paper/'.$tpid);
return $ret;
}

?>