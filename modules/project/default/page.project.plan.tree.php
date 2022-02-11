<?php
/**
* Project calendar information
*
* @param Object $self
* @param Integer $tpid or Object $projectInfo
* @return String
*/
function project_plan_tree($self,$tpid=NULL) {
	if (!is_object($tpid)) {
		$projectInfo=R::Model('project.get',$tpid);
	} else {
		$projectInfo=$tpid;
		$tpid=$projectInfo->tpid;
	}

	setcookie('maingrby','tree',time()+10*365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));

	if ($projectInfo->info->type!='project') return $ret.message('error','This is not a project');

	//$ret.='<p>Child of '.$actid.'</p>';
	$ret.=__project_plan_tree_child($projectInfo,$actid);

	$ret.='<p>รวมงบประมาณตามแผนงาน <b>'.number_format($projectInfo->info->planBudget,2).'</b> บาท</p>';

	/*
	$ret.='<ol>';
	foreach ($projectInfo->activity as $activity) {
		if (is_null($activity->parent)) {
			$ret.='<li>'.$activity->title;
			$ret.=__project_plan_tree_child($projectInfo->activity,$activity->trid);
			$ret.='</li>';
		}
	}
	$ret.='</ol>';
	*/

	//$ret.=print_o($projectInfo->activity,'$activity');

	$ret.='<style type="text/css">
	.-init-detail {font-size:0.9em;color:#999;display:block;padding:0 0 8px 0;}
	</style>
	<script type="text/javascript">
	function planViewClick($this) {
		var $container=$this.closest(".-header").next().children(":first-child");
		//console.log("Click "+$container.attr("id"));
		if ($container.hasClass("-init")) {
			$container.show();
		} else if ($container.is(":visible")) {
			$container.hide();
		} else {
			$container.show();
		}
		$container.removeClass("-init");
	}
	</script>';
	return $ret;
}

function __project_plan_tree_child($projectInfo,$parent=NULL,$pretext='') {
	//$ret.='<p>Child Activity Of '.$parent.'</p>';
	$parentAct=$projectInfo->activity[$parent];
	$tpid=$projectInfo->tpid;
	$isEdit=$projectInfo->RIGHT & _IS_EDITABLE;
	$isAdmin=$projectInfo->RIGHT & _IS_ADMIN;
	$tagId='project-plan-item-'.SG\getFirst($parent,'master');
	$actid=0;


	$ret.='<div id="'.$tagId.'" class="ui-tree '.($parent?'-child':'-master').'">';

	foreach ($projectInfo->activity as $item) {
		//$ret.='Tr='.$item->trid.' Parent='.$parent.'=>'.$item->parent.'<br />';
		// Check error transaction
		if ($item->parent!=$parent) {
			continue;
		} else if (empty($item->trid)) {
			$ret.='<p>Error : Empty Transaction.</p>';
			continue;
		} else if ($item->trid==$parent) {
			$ret.='<p>Error : Transaction '.$item->trid.' is infinite loop.</p>';
			continue;
		}

		// Start show transaction
		$isSubActivity=!empty($item->budget);

		//$ret.='FOUND '.$parent.'=>'.$item->parent.'<br />';
		//$ret.='<h3><span class="">'.(++$no).'</span><a href="javascript:void(0)"><b>'.$item->title.'</b><i class="icon -down"></i></a></h3>';
		$ret.='<div id="plan-header-'.$item->trid.'" class="ui-item -header'.($isSubActivity?' -activity':'').'"><a class="title -showdetail" href="javascript:void(0)" data-rel="after">'
			.'<span class="-bullet">'.$pretext.(++$actid).'</span> '
			.'<span class="-title">'.$item->title.'</span>'
			//.'<span class="-subcount">'.($item->childsCount?' ('.$item->childsCount.' กิจกรรมย่อย)':'').'</span>'
			//.($isAdmin?'<span class="-trid -no-print"> [trid='.$item->trid.($item->expense?' budget='.number_format($item->budget,2):'').']</span>':'')
			.'<i class="icon -up -gray" title="ย่อ/ขยาย"></i></a>'
			._NL;

		//$ret.='Tr='.$item->trid.' Parent='.$parent.'=>'.$item->parent.'<br />';

		$ui=new Ui(NULL,'ui-menu -main -no-print');
		if ($isSubActivity || !$isEdit) {
			$ui->add('<i class="icon -blank"></i>');
		} else {
			$ui->add('<a class="sg-action -add-plan" href="'.url('project/plan/'.$tpid.'/add/'.$item->trid).'" data-rel="box"><i class="icon -addbig -gray" title="เพิ่มกิจกรรมย่อย"></i></a>');
		}
		$ui->add('<a class="sg-action" href="'.url('project/plan/detail/'.$tpid.'/view/'.$item->trid).'" data-rel="#plan-detail-'.$item->trid.'" data-callback="planViewClick" onClick="console.log(\'toogle\');$(this).next(\'.-child>.-detail\').toggle();return false;" title="รายละเอียด"><i class="icon -viewdoc -gray"></i><span class="-hidden">รายละเอียด</span></a>');
		//$ui->add('<a href=""><i class="icon -edit"></i></a>');
		if ($isSubActivity) {
			$ui->add('<a class="sg-action" href="'.url('project/activity/'.$item->trid.'/post/'.$item->trid).'" data-rel="#plan-detail-'.$item->trid.'" title="บันทึกกิจกรรม"><i class="icon -save -gray"></i></a>');
		} else {
			$ui->add('<i class="icon -blank"></i>');
		}

		$dui=new Ui();
		$dui->add('<a class="sg-action" href="'.url('project/plan/detail/'.$tpid.'/view/'.$item->trid).'" data-rel="#plan-detail-'.$item->trid.'"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>');
		if ($isEdit) {
			$dui->add('<a class="sg-action" href="'.url('project/plan/detail/'.$tpid.'/view/'.$item->trid).'" data-rel="#plan-detail-'.$item->trid.'"><i class="icon -edit"></i><span>แก้ไขรายละเอียด</span></a>');
			$dui->add('<a class="sg-action" href="'.url('project/plan/'.$tpid.'/reorder/'.$item->trid).'" data-rel="box"><i class="icon -sort"></i><span>เปลี่ยนลำดับการทำกิจกรรม</span></a>');
			$dui->add('<a class="sg-action" href="'.url('project/plan/'.$tpid.'/changeparent/'.$item->trid).'" data-rel="box"><i class="icon -back"></i><span>ย้ายไปอยู่ภายใต้กิจกรรมอื่น</span></a>');
			if (empty($item->childsCount)) {
				$dui->add('<sep>');
				$dui->add('<a class="sg-action" href="'.url('project/plan/'.$tpid.'/remove/'.$item->trid).'" data-rel="replace:#project-plan-item-'.$item->trid.'" data-confirm="ต้องการลบกิจกรรมนี้ กรุณายืนยัน?" data-callback="treeRemove"><i class="icon -delete"></i><span>ลบแผนงาน/กิจกรรม</span></a>');
			}
		}
		$ui->add(sg_dropbox($dui->build()));
		$ret.=$ui->build();
		$ret.='</div>';
		//$ret.=$ui->build();



		$ret.='<div class="ui-item -child">';
		$ret.='<div id="plan-detail-'.$item->trid.'" class="ui-item -detail -init">';
		$ret.='<span class="-init-detail">';
		if ($isSubActivity) {
			$ret.=$item->fromdate?sg_date($item->fromdate,'ว ดด ปป'):'';
			if ($item->budget) {
				$ret.=' งบกิจกรรม '.number_format($item->budget,2).' บาท';
				$ret.=' ค่าใช้จ่าย ??? บาท';
			}
		} else {
			if ($item->planBudget) $ret.='งบแผนงาน '.number_format($item->planBudget,2).' บาท';
			$ret.=($item->childsCount?' '.$item->childsCount.' กิจกรรมย่อย':'');
		}
		$ret.='</span>';
		$ret.='</div><!-- plan-detail-'.$item->trid.' -->';

		$ret.=__project_plan_tree_child($projectInfo,$item->trid,$pretext);

		if ($item->childsCount) {
			/*
				$ret.='<nav class="nav -plan">';
				if ($isEdit) {
				}
				$ret.=' <a class="sg-action btn" href="'.url('project/plan/detail/'.$tpid.'/view/'.$parentAct->trid).'" data-rel="parent:.ui-tree"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>';
				//$ret.='<a class="btn" href="javascript:void(0)"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>';
				if ($isEdit && empty($parentAct->childsCount)) {
					$ret.='<a class="sg-action btn" href="'.url('project/plan/'.$tpid.'/remove/'.$parentAct->trid).'" data-rel="replace:#project-plan-item-'.$parentAct->parent.'" data-confirm="ต้องการลบกิจกรรมนี้ กรุณายืนยัน?"><i class="icon -delete"></i></a>';
				}
				$ret.='</nav>';
				*/
			//$pretext=$pretext.$actid.'.';
			//$ret.=__project_plan_tree_child($projectInfo,$item->trid,$pretext);
		} else {
			/*
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
				*/
		}
		$ret.='</div><!-- ui-tree -->';
		//$ret.=print_o($item,'$item');
	}




	/*
	if ($isEdit) {
		$ret.='<form class="sg-form" action="'.url('project/plan/'.$tpid.'/add/'.$parentAct->trid).'" style="display:inline-block; position: relative; left:0;" data-rel="replace:#project-plan-item-'.$parentAct->trid.'" data-checkvalid="true"><span class="-bullet">'.$pretext.(++$actid).'</span> <input class="form-text -require" type="text" name="title" /><button class="btn" data-tooltip="เพิ่มกิจกรรมย่อยภายใต้ '.$parentAct->title.'"><i class="icon -addbig"></i><span>เพิ่มกิจกรรมย่อย</span></button>';
		$ret.='</form>';
		//if (!$parentAct->childsCount) $ret.=' <a class="btn" href="javascript:void(0)"><i class="icon -add"></i><span>บันทึกงบประมาณ</span></a>';
	}

	$ret.='<nav class="nav -plan" style="position:relative;top:0;right:32px;margin-">';
	$ret.=' <a class="sg-action btn" href="'.url('project/plan/detail/'.$tpid.'/view/'.$parentAct->trid).'" data-rel="parent:.ui-tree"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>';
	//$ret.='<a class="btn" href="javascript:void(0)"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>';
	if ($isEdit && empty($parentAct->childsCount)) {
		$ret.='<a class="sg-action btn" href="'.url('project/plan/'.$tpid.'/remove/'.$parentAct->trid).'" data-rel="replace:#project-plan-item-'.$parentAct->parent.'" data-confirm="ต้องการลบกิจกรรมนี้ กรุณายืนยัน?"><i class="icon -delete"></i></a>';
	}
	$ret.='</nav>';
	*/

	if (is_null($parent) && $isEdit) {
		$ret.='<form class="sg-form -no-print" action="'.url('project/plan/'.$tpid.'/add').'" style="margin:16px 0;position: relative; left:0;" data-rel="replace:#project-plan-item-master" data-checkvalid="true"><span class="-bullet" style="position: absolute;">'.$pretext.(++$actid).'</span> <input class="form-text -require -fill" type="text" name="title" size="60" placeholder="ระบุ ชื่อชุดโครงการ/โครงการ/กิจกรรมหลัก" style="margin-left:38px;width: calc( 100% - 54px );" /><div class="form-item" style="text-align:right;"><button class="btn -primary"><i class="icon -addbig -white"></i><span>เพิ่มแผนงาน/กิจกรรมหลัก</span></button></div>';
		$ret.='</form>';
	}
	$ret.='</div><!-- ui-tree '.$tagId.' -->';

	return $ret;
}
?>