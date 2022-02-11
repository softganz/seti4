<?php
function project_action_all($self,$tpid=NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get', $tpid, '{initTemplate:true}');

	$tpid = $projectInfo->tpid;

	R::View('project.toolbar', $self, $projectInfo->title, NULL, $projectInfo);

	$isAdmin = user_access('administer projects');

	$ret='';

	if ($tpid) {
		$self->theme->title=$projectInfo->title;
		if ($projectInfo->info->project_statuscode==1) {
			$ret.='<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/my/action/post/'.$tpid).'" data-rel="#main"><i class="icon -addbig -white"></i></a></div>';
		}
	} else {
		$ret.='<div class="btn-floating -right-bottom"><a class="btn -floating -circle48" href="'.url('project/my/action/post').'"><i class="icon -addbig -white"></i></a></div>';
	}

	
	$getOptions=NULL;
	$getOptions->debug=false;


	$actionList = R::Model('project.action.get',$tpid,$getOptions);
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
		$ret.='<a class="btn" href="'.url('project/my/action/'.$tpid.'/all').'"><i class="icon -viewdoc"></i><span>ดูกิจกรรมทั้งหมดของโครงการ</span></a>';
	}

	$tables = new Table();
	$tables->thead=array('date'=>'วันที่','กิจกรรม');
	foreach ($actionList as $rs) {
		$tables->rows[]=array(
			sg_date($rs->action_date,'ว ดด ปปปป'),
			'<a href="'.url('project/'.$rs->tpid.'/action.view/'.$rs->actionId).'">'.$rs->title.'</a><br /><small><em>'.$rs->projectTitle.'</em></small>',
		);
	}
	$ret.=$tables->build();

	//$ret.=print_o($projectInfo);
	return $ret;
}
?>