<?php
/**
 * Move activity between owner and trainer
 *
 * @param Integer $trid
 * @return String
 */
function project_edit_moveactivity($self,$trid) {
	$rs=mydb::select('SELECT * FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$trid);
	if ($rs->_empty) return 'No activity';
	$is_edit=user_access('administer projects') || (project_model::is_trainer_of($rs->tpid));
	if (!$is_edit) return 'Access denied';

	$ret='';

	/* Remove activity from project transaction */
	if ($rs->part=='owner') $newpart='trainer';
	else if ($rs->part=='trainer') $newpart='owner';
	if ($newpart) mydb::query('UPDATE %project_tr% SET `part`=:newpart WHERE `trid`=:trid LIMIT 1',':trid',$trid,':newpart',$newpart);
	$ret.='ย้ายกิจกรรมเรียบร้อย';
	model::watch_log('project','move activity','Activity id '.$rs->trid.' of calid '.$rs->calid.' was moved from part '.$rs->part.' to '.$newpart.' by '.i()->name.'('.i()->uid.')');
	return $ret;
}
?>