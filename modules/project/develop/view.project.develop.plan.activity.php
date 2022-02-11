<?php
function view_project_develop_plan_activity($devInfo,$trid=NULL) {
	$tpid=$devInfo->tpid;

	$isEdit=$devInfo->RIGHT & _IS_EDITABLE;
	$isAdmin=$devInfo->RIGHT & _IS_ADMIN;


	foreach ($devInfo->activity as $activity) {
		if (empty($activity->trid)) continue;
		if ($activity->parent!=$trid) continue;

		// Generate main activity information
		$ret.='<div class="__plan_level -level1">'._NL
				.'<a class="__plan_showmore" href="'.url('project/develop/plan/view/'.$tpid.'/child/'.$activity->trid).'" data-rel="after">'
				.'<span class="project-develop -bullet">'.(++$actid).'</span> '
				.'<span class="-title">'.$activity->title.'</span>'
				.'<span class="-subcount">'.($activity->childsCount?' ('.$activity->childsCount.' กิจกรรมย่อย)':'').'</span>'
				.($isAdmin?'<span class="-trid -no-print"> [trid='.$activity->trid.($activity->expense?' budget='.number_format($activity->budget,2):'').']</span>':'')
				.'<i class="icon -down -no-print"></i></a>'
				._NL;
		$ret.='</div>'._NL;
	}

	//$ret.='Count='.count($devInfo->activity[$trid]->expense).print_o($activity,'$activity');
	if ($isEdit && count($devInfo->activity[$trid]->expense)==0) {
		$ret.='<div class="__plan_level -level1 -addform">';
		$ret.='<form class="sg-form project-develop-plan-add -no-print" method="post" action="'.url('project/develop/plan/'.$tpid.'/add/'.$trid).'" data-checkvalid="true" data-rel="#project-develop-plan-item-'.SG\getFirst($devInfo->activity[$trid]->trid,'master').'"><span class="project-develop -bullet">'.(++$actid).'</span> ';
		if ($trid) {
			$ret.='<input class="form-text -require" type="text" name="title" size="40" placeholder="ระบุชื่อกิจกรรมย่อย" />';
			$ret.='<button class="btn"><i class="icon -addbig"></i><span>เพิ่มกิจกรรมย่อย</button>';
		} else {
			$ret.='<input class="form-text -require" type="text" name="title" size="40" placeholder="ระบุชื่อกิจกรรม/แผนการดำเนินงาน" />';
			$ret.='<button class="btn -primary"><i class="icon -addbig -white"></i><span>เพิ่มกิจกรรมหลัก/แผนการดำเนินงาน</button>';
		}
		$ret.='</form></div>';
	}

	return $ret;
}
?>