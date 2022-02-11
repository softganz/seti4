<?php
/**
* Project calendar information
*
* @param Object $self
* @param Integer $tpid or Object $projectInfo
* @param String $action
* @param Integer $actid
* @return String
*/
function project_plan_activity1($self,$tpid,$action = NULL,$actid=NULL) {
	if (!is_object($tpid)) {
		$projectInfo=R::Model('project.get',$tpid);
	} else {
		$projectInfo=$tpid;
		$tpid=$projectInfo->tpid;
	}
	//$ret.=print_o($projectInfo->activity,'$activity');

	setcookie('maingrby','activity1',time()+10*365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));

	if ($projectInfo->info->type!='project') return $ret.message('error','This is not a project');

	//$ret.='<p>Child of '.$actid.'</p>';
	$ret.=__project_calendar_plan_child($projectInfo,$actid);

	/*
	$ret.='<ol>';
	foreach ($projectInfo->activity as $activity) {
		if (is_null($activity->parent)) {
			$ret.='<li>'.$activity->title;
			$ret.=__project_calendar_plan_child($projectInfo->activity,$activity->trid);
			$ret.='</li>';
		}
	}
	$ret.='</ol>';
	*/

	$ret.='<style type="text/css">
	.nav.-plan {padding:0px 0;border-bottom:1px #ddd solid; text-align: right;}
	.project-plan-wrapper ol {margin: 0 0 0px 0; padding: 0 0 0 30px; border-left: 1px #ccc solid; border-bottom: 1px #ccc solid; border-radius: 0 0 0 8px;}
	.project-plan-wrapper>ol {border:none;}
	.project-plan-wrapper>ol>li {border:none;margin-bottom:64px;}
	</style>';
	return $ret;
}

function __project_calendar_plan_child($projectInfo,$trid=NULL,$pretext='') {
	//$ret.='<p>Child '.$trid.'</p>';
	$parentAct=$projectInfo->activity[$trid];
	$tpid=$projectInfo->tpid;
	$isEdit=$projectInfo->RIGHT & _IS_EDITABLE;
	$isAdmin=$projectInfo->RIGHT & _IS_ADMIN;
	$tagId='project-plan-item-'.SG\getFirst($trid,'master');
	$actid=0;


	$ret.='<div id="'.$tagId.'" class="__plan_item '.($trid?'-child':'-master').'">';

	foreach ($projectInfo->activity as $item) {
		//$ret.=$trid.'=>'.$item->parent.'<br />';
		if ($item->parent==$trid) {
			//$ret.='FOUND '.$trid.'=>'.$item->parent.'<br />';
			//$ret.='<h3><span class="">'.(++$no).'</span><a href="javascript:void(0)"><b>'.$item->title.'</b><i class="icon -down"></i></a></h3>';
			$ret.='<a class="-showafter" href="javascript:void(0)" data-rel="after">'
				.'<span class="-bullet">'.$pretext.(++$actid).'</span> '
				.'<span class="-title">'.$item->title.'</span>'
				.'<span class="-subcount">'.($item->childsCount?' ('.$item->childsCount.' กิจกรรมย่อย)':'').'</span>'
				.($isAdmin?'<span class="-trid -no-print"> [trid='.$item->trid.($item->expense?' budget='.number_format($item->budget,2):'').']</span>':'')
				.'<i class="icon -up -no-print"></i></a>'
				._NL;

			if ($item->childsCount) {
				/*
				$ret.='<nav class="nav -plan">';
				if ($isEdit) {
				}
				$ret.=' <a class="sg-action btn" href="'.url('project/plan/detail/'.$tpid.'/view/'.$parentAct->trid).'" data-rel="parent:.__plan_item"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>';
				//$ret.='<a class="btn" href="javascript:void(0)"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>';
				if ($isEdit && empty($parentAct->childsCount)) {
					$ret.='<a class="sg-action btn" href="'.url('project/plan/'.$tpid.'/remove/'.$parentAct->trid).'" data-rel="replace:#project-plan-item-'.$parentAct->parent.'" data-confirm="ต้องการลบกิจกรรมนี้ กรุณายืนยัน?"><i class="icon -delete"></i></a>';
				}
				$ret.='</nav>';
				*/
				//$pretext=$pretext.$actid.'.';
				$ret.=__project_calendar_plan_child($projectInfo,$item->trid,$pretext);
			} else {
				$ret.='<div id="project-plan-item-'.$item->trid.'" class="__plan_detail">';
				if ($item->calid) {
					$ret.=$item->fromdate?sg_date($item->fromdate,'ว ดด ปป'):'';
					if ($item->expense) {
						$ret.=' งบประมาณ '.number_format($item->budget,2).' บาท';
						$ret.=' ค่าใช้จ่าย ??? บาท';
					}
					$ret.='<nav class="nav -plan">';
					if ($isEdit) {
						$ret.='<a class="btn" href="'.url('paper/'.$tpid.'/owner/activity?act=addreport&calid='.$item->calid).'"><i class="icon -save"></i><span>บันทึกกิจกรรม</span></a>';
					}
					$ret.=' <a class="sg-action btn" href="'.url('project/plan/detail/'.$tpid.'/view/'.$item->trid).'" data-rel="parent:.__plan_detail"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>';
					$ret.='</nav>';
				} else {
					$ret.='<nav class="nav -plan">';
					if ($isEdit) {
						$ret.='<form class="sg-form" action="'.url('project/plan/'.$tpid.'/add/'.$item->trid).'" style="display:inline-block; position: absolute; left:0;" data-rel="replace:#project-plan-item-'.$item->trid.'" data-checkvalid="true"><span class="-bullet">'.$pretext.'1</span> <input class="form-text -require" type="text" name="title" /><button class="btn" data-tooltip="เพิ่มกิจกรรมย่อยภายใต้ '.$item->title.'"><i class="icon -addbig"></i><span>เพิ่มกิจกรรมย่อย</span></button></form>';
						//if (!$item->childsCount) $ret.=' <a class="btn" href="javascript:void(0)"><i class="icon -add"></i><span>บันทึกงบประมาณ</span></a>';
					}
					$ret.=' <a class="sg-action btn" href="'.url('project/plan/detail/'.$tpid.'/view/'.$item->trid).'" data-rel="parent:.__plan_detail"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>';
					if ($isEdit && empty($item->childsCount)) {
						$ret.='<a class="sg-action btn" href="'.url('project/plan/'.$tpid.'/remove/'.$item->trid).'" data-rel="replace:#project-plan-item-'.$item->parent.'" data-confirm="ต้องการลบกิจกรรมนี้ กรุณายืนยัน?"><i class="icon -delete"></i></a>';
					}
					$ret.='</nav>';
				}
				$ret.='</div>';
			}
			//$ret.=print_o($item,'$item');
		}
	}

	if ($isEdit) {
		$ret.='<form class="sg-form" action="'.url('project/plan/'.$tpid.'/add/'.$parentAct->trid).'" style="display:inline-block; position: relative; left:0;" data-rel="replace:#project-plan-item-'.$parentAct->trid.'" data-checkvalid="true"><span class="-bullet">'.$pretext.(++$actid).'</span> <input class="form-text -require" type="text" name="title" /><button class="btn" data-tooltip="เพิ่มกิจกรรมย่อยภายใต้ '.$parentAct->title.'"><i class="icon -addbig"></i><span>เพิ่มกิจกรรมย่อย</span></button>';
		$ret.='</form>';
		//if (!$parentAct->childsCount) $ret.=' <a class="btn" href="javascript:void(0)"><i class="icon -add"></i><span>บันทึกงบประมาณ</span></a>';
	}

	$ret.='<nav class="nav -plan" style="position:relative;top:0;right:32px;margin-">';
	$ret.=' <a class="sg-action btn" href="'.url('project/plan/detail/'.$tpid.'/view/'.$parentAct->trid).'" data-rel="parent:.__plan_item"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>';
	//$ret.='<a class="btn" href="javascript:void(0)"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>';
	if ($isEdit && empty($parentAct->childsCount)) {
		$ret.='<a class="sg-action btn" href="'.url('project/plan/'.$tpid.'/remove/'.$parentAct->trid).'" data-rel="replace:#project-plan-item-'.$parentAct->parent.'" data-confirm="ต้องการลบกิจกรรมนี้ กรุณายืนยัน?"><i class="icon -delete"></i></a>';
	}
	$ret.='</nav>';

	$ret.='</div><!-- '.$tagId.' -->';

	return $ret;
}
?>