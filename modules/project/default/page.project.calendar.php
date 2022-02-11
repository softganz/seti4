<?php
/**
* Project calendar information
*
* @param Object $self
* @param Object/Integer $projectInfo
* @param String $action
* @param Integer $calid
* @return String
*/
function project_calendar($self, $tpid = NULL, $action = NULL, $calid = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid, '{initTemplate: true}');
	$tpid = $projectInfo->tpid;


	//$ret.='TPID='.$tpid.'<br />'.print_o($projectInfo,'$projectInfo');

	if (empty($projectInfo)) return $ret.message('error','This is not a project');


	$info = project_model::get_info($tpid);
	$options = options('project');
	$action = SG\getFirst($action,post('act'));
	$isEdit = $projectInfo->info->isEdit;
	$isEditDetail = $projectInfo->info->isEditDetail;




	if (post('gr')) {
		setcookie('maingrby',post('gr'),time()+10*365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));
	}
	$activityGroupBy=SG\getFirst(post('gr'),$_COOKIE['maingrby'],'act');
	//$ret.='Act='.$activityGroupBy.' _COOKIE='.$_COOKIE['maingrby'];
	//setcookie('maingrby',post('gr'),time()-10*365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));

	switch ($action) {

		case 'add' :
			if ($isEdit || $projectInfo->info->membershipType) $ret.=R::View('project.calendar.form',$projectInfo);
			return $ret;
			break;

		case 'edit':
			if ($calid) {
				$calendar = R::Model('project.calendar.get', $calid);
				if (($isEdit || $calendar->owner == i()->uid) && $calendar->calid) {
					$calendar->color = property('calendar:color:'.$calid);
					//$ret.=print_o($calendar,'$calendar');
					$ret .= R::View('project.calendar.form', $projectInfo, $calendar);
				}
			}
			return $ret;
			break;

		case 'save':
			$post = (object) post('calendar');
			//$ret .= print_o($post,'$post');
			$result = R::Model('project.calendar.save', $projectInfo, $post, '{debug:false}');
			$ret .= 'บันทึกเรียบร้อย';
			//$ret.=print_o($result,'$result');
			return $ret;
			break;

		case 'remove' :
			$calendar = R::Model('project.calendar.get', $calid);
			if (($isEdit || $calendar->owner == i()->uid) && $calendar->calid && SG\confirm()) {
				$calendarTitle=mydb::select('SELECT `title` FROM %calendar% WHERE `id`=:id LIMIT 1',':id',$calid)->title;
				$ret .= 'Remove calendar '.$calendarTitle;
				mydb::query('DELETE FROM %calendar% WHERE `id`=:calid LIMIT 1',':calid',$calid);
				//$ret .= mydb()->_query.'<br />';
				mydb::query('DELETE FROM %project_tr% WHERE `tpid` = :tpid AND `calid` = :calid AND `formid` = "info" AND `part` = "activity" LIMIT 1', ':tpid',$tpid, ':calid', $calid);
				//$ret .= mydb()->_query.'<br />';
				mydb::query('DELETE FROM %project_activity% WHERE `calid`=:calid LIMIT 1',':calid',$calid);
				//$ret .= mydb()->_query.'<br />';
				if (mydb::table_exists('%project_actguide%')) {
					mydb::query('DELETE FROM %project_actguide% WHERE `calid`=:calid',':calid',$calid);
					//$ret .= mydb()->_query.'<br />';
				}
				// Add log
				model::watch_log('project','Calendar remove','ลบกิจกรรมย่อย '.$calid.' กิจกรรมหลัก '.$calid.' : ' .$calendarTitle,NULL,$tpid);
			}
			//$ret.=R::View('project.mainact.info',$tpid,$calid,NULL,$project);
			//$ret .= print_o($calendar);
			return $ret;
			break;

		case 'info':
			$ret.=R::View('project.calendar.info',$projectInfo, $calid, $info);
			return $ret;
			break;

		default:
			if ($action) {
				$ret.='<p class="notify">ไม่มีเงื่อนไขตามระบุ</p>';
				return $ret;
			}
			break;
	}


	if ($activityGroupBy=='obj') $ret.=R::View('project.calendar.list.obj',$tpid,$info,$isEdit);
	else if ($activityGroupBy=='plan') $ret.=R::View('project.calendar.list.plan',$tpid,$info,$isEdit);
	else if ($activityGroupBy=='guide') $ret.=R::View('project.calendar.list.guide',$tpid,$info,$isEdit);
	else $ret.=R::View('project.calendar.list.act', $projectInfo, $info, $isEdit);

	//$ret.=print_o($projectInfo,'$projectInfo');
	//$ret.=print_o($mainact,'$mainact');
	//$ret.=print_o($info,'$info');
	//$ret.=print_o($options,'$options');
	return $ret;
}
?>