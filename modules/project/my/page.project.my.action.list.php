<?php
/**
 * Project Application
 *
 * @param Object $topic
 */
function project_my_action_list($self,$tpid=NULL,$action=NULL) {
	R::View('project.toolbar',$self,'กิจกรรมโครงการ','my',$projectInfo,'{modulenav:false}');

	if (!i()->ok) return '<p class="notify">กรุณาเข้าสู่ระบบสมาชิก</p>'.R::View('signform');

	$isAdmin=user_access('administer projects');

	$ret='';

	if ($tpid) {
		$projectInfo=R::Model('project.get',$tpid);
		$self->theme->title=$projectInfo->title;
		if ($projectInfo->info->project_statuscode==1) {
			$ret.='<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/my/action/post/'.$tpid).'" data-rel="#main"><i class="icon -addbig -white"></i></a></div>';
		}
	} else {
		$ret.='<div class="btn-floating -right-bottom"><a class="btn -floating -circle48" href="'.url('project/my/action/post').'"><i class="icon -addbig -white"></i></a></div>';
	}


	$getConditions=NULL;
	$getOptions=NULL;
	$getOptions->debug=false;
	$getOptions->order="`actionDate` DESC, `actionId` DESC";

	$getConditions->userId = i()->uid;

	$actionList=R::Model('project.action.get',$getConditions,$getOptions);
	//$ret.=print_o($actionList,'$actionList');

	$isEdit=$projectInfo->info->isRight || $action->uid==i()->uid;
	$isAdmin=$projectInfo->info->isRight;
	$isAccessExpense=$isEdit;



	/*
	if ($isEdit) {
		$inlineAttr['class']='inline-edit';
		$inlineAttr['data-update-url']=url('project/edit/tr');
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}
	$inlineAttr['class'].=' ui-card project-action project-action-item';
	$ret.='<div id="ui-card project-action-'.$actid.'" '.sg_implode_attr($inlineAttr).'>'._NL;
	*/


	$ret.='<div class="ui-card project-action">'._NL;
	if (empty($actionList)) {
		$ret.='<p class="notify">ไม่มีบันทึกกิจกรรมของโครงการนี้</p>';
		if ($tpid)
			$ret.='<a class="btn" href="'.url('project/my/action/'.$tpid.'/all').'"><i class="icon -viewdoc"></i><span>ดูกิจกรรมทั้งหมดของโครงการ</span></a>';
	}

	$tables = new Table();
	$tables->thead=array('date'=>'วันที่','กิจกรรม');
	foreach ($actionList as $rs) {
		$tables->rows[]=array(
											sg_date($rs->actionDate,'ว ดด ปปปป'),
											'<a href="'.url('project/my/action/'.$rs->tpid.'/view/'.$rs->trid).'">'.$rs->title.'</a><br /><small><em>'.$rs->projectTitle.'</em></small>',
										);
	}
	$ret.=$tables->build();

	//$ret.=print_o($projectInfo,'$projectInfo');

	return $ret;
}
?>