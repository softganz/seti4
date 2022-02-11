<?php
/**
 * Remove Activity And Calendar
 *
 * @param Integer $trid
 * @return String
 */
function r_project_calendar_remove($calid,$options='{}') {
	$result=NULL;
	$result->error=false;
	$result->msg='';
	$result->query=NULL;

	$calendar=R::Model('project.calendar.get',$calid);

	//debugMsg($calendar,'$calendar');

	if (empty($calendar->calid)) {
		$result->error=true;
		$result->msg='No Calendar';
		return $result;
	}

	$lockReportDate=project_model::get_lock_report_date($calendar->tpid);
	if ($calendar->from_date<=$lockReportDate) {
		$result->error=true;
		$result->msg='ไม่สามารถแก้ไข/ลบบันทึกกิจกรรมนี้ได้';
		return $result;
	}


	$stmt='DELETE FROM %calendar% WHERE `id`=:calid LIMIT 1';
	mydb::query($stmt, ':calid',$calendar->calid);
	$result->query[]=mydb()->_query;

	$stmt='DELETE FROM %project_activity% WHERE `calid`=:calid LIMIT 1';
	mydb::query($stmt, ':calid',$calendar->calid);
	$result->query[]=mydb()->_query;

	$stmt='DELETE FROM %project_tr% WHERE `formid`="info" AND `part`="activity" AND `calid`=:calid LIMIT 1';
	mydb::query($stmt, ':calid',$calendar->calid);
	$result->query[]=mydb()->_query;


	$result->msg.='ลบปฏิทินติดตามกิจกรรมเรียบร้อย';
	model::watch_log('project','remove calendar','Calendar id '.$calendar->calid.' was removed from project '.$calendar->tpid.' by '.i()->name.'('.i()->uid.')');
	return $result;
}
?>