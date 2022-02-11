<?php
/**
 * Add current trainer to be a trainer's project
 *
 * @param Integer $tpid
 * @return Location
 */
function project_edit_addtrainer($self,$tpid) {
	if (!((user_access('administer projects') || in_array('trainer',i()->roles)) && !project_model::is_trainer_of($tpid))) return 'Access denied';

	if (empty($tpid) || mydb::select('SELECT `tpid` FROM %topic% WHERE `tpid`=:tpid AND `type` IN ("project","project-develop") LIMIT 1',':tpid',$tpid)->_empty) return 'No project';

	//		if ($activity=='add') {
		$stmt='INSERT INTO %topic_user% (`tpid`, `uid`, `membership`) VALUES (:tpid, :uid, :membership)
						ON DUPLICATE KEY UPDATE `membership`=:membership;';
		mydb::query($stmt,':tpid',$tpid, ':uid', i()->uid, ':membership', 'Trainer');
		//			echo mydb()->_query;
		model::watch_log('project','add trainer',i()->name.'('.i()->uid.') was added to be a trainer of project '.$tpid);
		$ret['html']='เพิ่มเป็นพี่เลี้ยงเรียบร้อย';
	//		}


	$rs=mydb::select('SELECT * FROM %topic% WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid);

	if ($rs->type=='project-develop') {
		location('project/develop/'.$tpid);
	} else {
		location('paper/'.$tpid);
	}
	return $ret;
}
?>