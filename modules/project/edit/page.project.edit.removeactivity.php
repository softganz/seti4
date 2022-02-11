<?php
/**
 * Remove activity
 *
 * @param Integer $trid
 * @return String
 */
function project_edit_removeactivity($self,$trid) {
	$debug=false;
	$removeCalendar=post('removecalendar')=='yes';
	$ret='';

	$action=mydb::select('SELECT * FROM %project_tr% WHERE `trid`=:trid AND `formid`="activity" LIMIT 1',':trid',$trid);
	if ($action->_empty) return 'No activity';

	$projectInfo=R::Model('project.get',$action->tpid);
	$isEdit=$projectInfo->info->isRight || $action->uid==i()->uid;

	//debugMsg($action,'$action');
	//debugMsg($projectInfo,'$projectInfo');

	if (!$isEdit) return 'Access denied';

	if (!SG\confirm()) return 'Not confirm';



	$lockReportDate=project_model::get_lock_report_date($action->tpid);
	if ($action->date1<=$lockReportDate) return 'ไม่สามารถแก้ไข/ลบบันทึกกิจกรรมนี้ได้';


	// Remove gallery
	if ($action->gallery) {
		$gallery=mydb::select('SELECT * FROM  %topic_files% WHERE `gallery`=:gallery',':gallery',$action->gallery);
		foreach ($gallery->items as $action) if ($action->fid) R::Page('project.edit.delphoto',$self,$action->fid);
	}

	// Remove activity from project transaction
	mydb::query('DELETE FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$trid);
	if ($debug) debugMsg(mydb()->_query);

	// Remove expense
	mydb::query('DELETE FROM %project_tr% WHERE `calid`=:calid AND `formid`="expense" AND `part`="exptr"',':calid',$action->calid);
	if ($debug) debugMsg(mydb()->_query);



	if ($removeCalendar) {
		$stmt='DELETE FROM %calendar% WHERE `id`=:calid LIMIT 1';
		mydb::query($stmt, ':calid',$action->calid);
		if ($debug) debugMsg(mydb()->_query);

		$stmt='DELETE FROM %project_activity% WHERE `calid`=:calid LIMIT 1';
		mydb::query($stmt, ':calid',$action->calid);
		if ($debug) debugMsg(mydb()->_query);

		$stmt='DELETE FROM %project_tr% WHERE `trid`=:refid LIMIT 1';
		mydb::query($stmt, ':refid',$action->refid);
		if ($debug) debugMsg(mydb()->_query);

	}

	$ret.='ลบกิจกรรมเรียบร้อย';
	model::watch_log('project','remove activity','Activity id '.$action->trid.' of calid '.$action->calid.' was removed from project '.$action->tpid.' by '.i()->name.'('.i()->uid.')');
	return $ret;
}
?>