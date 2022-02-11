<?php
/**
 * Automatic saving activity post
 *
 * @param Integer $tpid
 * @param String $action
 * @param Array $_POST[activity]
 * @return Array
 */

// TODO: Disable auto save maybe bug duplicate record
function project_edit_activityautosave($self,$tpid,$action=NULL) {
	//return;
	$is_edit=user_access('administer projects') || (project_model::is_owner_of($tpid)) || (project_model::is_trainer_of($tpid));
	$post=(object)post('activity');
	$lockReportDate=project_model::get_lock_report_date($tpid);

	if (!$is_edit) {
		$error='การตรวจสอบสิทธิ์ผิดพลาด';
	} else if ($post->actiondate && $lockReportDate && $post->actiondate<=$lockReportDate) {
		$error=date('H:i:s').' : ไม่สามารถบันทึกกิจกรรมที่วันที่ปฎิบัติจริง ('.sg_date($post->actiondate).') เกิดขึ้นก่อนการส่งรายงานการเงินที่ปิดงวดไปแล้ว ('.sg_date($lockReportDate).')'.'LockDate='.$lockReportDate.print_o($post,'$post');
	}

	if ($error) {
		$ret['msg']=$ret['error']=$error;
		return $ret;
	}

	$ret['msg']='บันทึกข้อมูลอัตโนมัติเรียบร้อย';
	$ret['debbug']='AutoSave to trid='.$post->trid.' topic='.$tpid.'<br />';

	if ($post->trid && $action=='remove') {
		mydb::query('DELETE FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$post->trid);
		$ret['msg']='ยกเลิกรายการเรียบร้อย';
		return $ret;
	}
	$post->trid=empty($post->trid)?'func.NULL':$post->trid;
	$post->formid='activity';
	$post->flag=_PROJECT_DRAFTREPORT;
	$post->uid=i()->ok?i()->uid:'func.NULL';
	if (empty($post->rate1)) $post->rate1=0;
	if (empty($post->budget)) $post->budget=0;
	$post->created=date('U');
	if ($post->trid) {
		$post->modified=date('U');
		$post->modifyby=SG\getFirst(i()->uid,'func.NULL');
	}

	$fldCheckEmpty = 'date1,detail1,detail2,detail3,text1,text2,text3,text4,text5,text6,text7,text8,text9,rate1,num1,num2,num3,num4,num5,num6,num7';
	foreach (explode(',', $fldCheckEmpty) as $value) {
		if (trim($post->{$value} == '')) $post->{$value} = NULL;
	}


	$stmt='INSERT INTO %project_tr_bak%
					(`trid`, `tpid`, `calid`, `formid`, `part`, `uid`, `date1`, `detail1`, `detail2`,`detail3`, `text1`, `text2`, `text3`, `text4`, `text5`, `text6`, `text7`, `text8`, `text9`, `rate1`, `num1`, `num2`, `num3`, `num4`, `num5`, `num6`, `num7`, `created`)
				VALUES
					(:trid, :tpid, :calid, :formid, :part, :uid, :date1, :detail1, :detail2, :detail3, :text1, :text2, :text3, :text4, :text5, :text6, :text7, :text8, :text9, :rate1, :num1, :num2, :num3, :num4, :num5, :num6, :num7, :created)
				ON DUPLICATE KEY
				UPDATE `calid`=:calid, `date1`=:date1, `detail1`=:detail1, `detail2`=:detail2, `detail3`=:detail3, `text1`=:text1, `text2`=:text2, `text3`=:text3, `text4`=:text4, `text5`=:text5, `text6`=:text6, `text7`=:text7, `text8`=:text8, `text9`=:text9, `rate1`=:rate1, `num1`=:num1, `num2`=:num2, `num3`=:num3, `num4`=:num4, `num5`=:num5, `num6`=:num6, `num7`=:num7, `modified`=:modified, modifyby=:modifyby;';
	mydb::query($stmt,$post);

	$stmt='INSERT INTO %project_tr%
					(`trid`, `tpid`, `calid`, `formid`, `part`, `flag`, `uid`, `date1`, `detail1`, `detail2`,`detail3`, `text1`, `text2`, `text3`, `text4`, `text5`, `text6`, `text7`, `text8`, `text9`, `rate1`, `num1`, `num2`, `num3`, `num4`, `num5`, `num6`, `num7`, `created`)
				VALUES
					(:trid, :tpid, :calid, :formid, :part, 0, :uid, :date1, :detail1, :detail2, :detail3, :text1, :text2, :text3, :text4, :text5, :text6, :text7, :text8, :text9, :rate1, :num1, :num2, :num3, :num4, :num5, :num6, :num7, :created)
				ON DUPLICATE KEY
				UPDATE
					`calid`=:calid, `date1`=:date1, `detail1`=:detail1, `detail2`=:detail2, `detail3`=:detail3,
					`text1`=:text1, `text2`=:text2, `text3`=:text3, `text4`=:text4, `text5`=:text5, `text6`=:text6, `text7`=:text7, `text8`=:text8, `text9`=:text9,
					`rate1`=:rate1, `num1`=:num1, `num2`=:num2, `num3`=:num3, `num4`=:num4, `num5`=:num5, `num6`=:num6, `num7`=:num7,
					`modified`=:modified, modifyby=:modifyby;';
	mydb::query($stmt,$post);
	//$ret['query']=mydb()->_query;
	if (mydb()->_error) {
		$ret['trid']=NULL;
	} else {
		$ret['trid']=is_numeric($post->trid) ? NULL : mydb()->insert_id;
	}

	$post->calowner=$post->part=='owner'?_PROJECT_OWNER_ACTIVITY:_PROJECT_TRAINER_ACTIVITY;

	$stmt='INSERT INTO %project_activity% (`calid`, `calowner`, `mainact`, `targetpreset`, `budget`) VALUES (:calid, :calowner, :mainact, :targetpreset, :budget)
					ON  DUPLICATE KEY UPDATE `mainact`=:mainact, `targetpreset`=:targetpreset, `budget`=:budget';
	mydb::query($stmt, ':calid', $post->calid, ':calowner', $post->calowner, ':mainact', $post->mainact, ':targetpreset', $post->targetpreset, ':budget',$post->budget);

	return $ret;
}
?>