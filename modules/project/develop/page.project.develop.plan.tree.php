<?php
/**
* Project calendar information
*
* @param Object $self
* @param Integer $tpid or Object $devInfo
* @param String $action
* @param Integer $actid
* @return String
*/
function project_develop_plan_tree($self,$tpid=NULL,$action=NULL,$actid=NULL) {
	if (!is_object($tpid)) {
		$devInfo=R::Model('project.develop.get',$tpid);
	} else {
		$devInfo=$tpid;
		$tpid=$devInfo->tpid;
	}

	setcookie('maingrby','tree',time()+10*365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));

	//if ($devInfo->info->type!='project') return $ret.message('error','This is not a project');

	//$ret.='<p>Child of '.$actid.'</p>';
	$ret.=__project_develop_plan_tree_child($devInfo,$actid);

	if (empty($actid)) $ret.='<p>รวมงบประมาณตามแผนงาน <b>'.number_format($devInfo->info->planBudget,2).'</b> บาท</p>';

	//$ret.=print_o($devInfo->activity,'$activity');

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

function __project_develop_plan_tree_child($devInfo,$parent=NULL,$pretext='') {
	//$ret.='<p>Child Activity Of '.$parent.'</p>';
	$parentAct=$devInfo->activity[$parent];
	$tpid=$devInfo->tpid;
	$isEdit=$devInfo->RIGHT & _IS_EDITABLE;
	$isAdmin=$devInfo->RIGHT & _IS_ADMIN;
	$tagId='project-plan-item-'.SG\getFirst($parent,'master');
	$actid=0;


	$ret.='<div id="'.$tagId.'" class="ui-tree '.($parent?'-child':'-master').'">';

	foreach ($devInfo->activity as $item) {
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
		$isSubActivity=!empty($item->expense);

		//$ret.='FOUND '.$parent.'=>'.$item->parent.'<br />';
		//$ret.='<h3><span class="">'.(++$no).'</span><a href="javascript:void(0)"><b>'.$item->title.'</b><i class="icon -down"></i></a></h3>';
		$ret.='<div id="plan-header-'.$item->trid.'" class="ui-item -header'.($isSubActivity?' -activity':'').'"><a class="title -showdetail" href="javascript:void(0)" data-rel="after">'
			.'<span class="-bullet">'.$pretext.(++$actid).'</span> '
			.'<span class="-title">'.$item->title.'</span>'
			.'<i class="icon -up -gray"></i></a>'
			._NL;

		//$ret.='Tr='.$item->trid.' Parent='.$parent.'=>'.$item->parent.'<br />';

		$ui=new Ui(NULL,'ui-menu -main -no-print');
		if ($isSubActivity || !$isEdit) {
			$ui->add('<i class="icon -blank"></i>');
		} else {
			$ui->add('<a class="sg-action -add-plan" href="'.url('project/develop/plan/'.$tpid.'/add/'.$item->trid).'" data-rel="box" data-tooltip="เพิ่มกิจกรรมย่อยภายใต้แผนงานนี้"><i class="icon -addbig -gray"></i></a>');
		}
		$ui->add('<a class="sg-action" href="'.url('project/develop/plan/'.$tpid.'/detail/'.$item->trid).'" data-rel="#plan-detail-'.$item->trid.'" data-callback="planViewClick" onClick="console.log(\'toogle\');$(this).next(\'.-child>.-detail\').toggle();return false;"><i class="icon -viewdoc -gray"></i><span class="-hidden">รายละเอียด</span></a>');
		if ($isSubActivity) {
			$ui->add('<a class="sg-action" href="'.url('project/activity/'.$activity->trid.'/add').'" data-rel="#plan-detail-'.$item->trid.'"><i class="icon -save -gray"></i></a>');
		} else {
			$ui->add('<i class="icon -blank"></i>');
		}

		$dui=new Ui();
		$dui->add('<a class="sg-action" href="'.url('project/develop/plan/detail/'.$tpid.'/detail/'.$item->trid).'" data-rel="#plan-detail-'.$item->trid.'"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>');
		if ($isEdit) {
			$dui->add('<a class="sg-action" href="'.url('project/develop/plan/'.$tpid.'/detail/'.$item->trid).'" data-rel="#plan-detail-'.$item->trid.'"><i class="icon -edit"></i><span>แก้ไขรายละเอียด</span></a>');
			$dui->add('<a class="sg-action" href="'.url('project/develop/plan/'.$tpid.'/reorder/'.$item->trid).'" data-rel="box"><i class="icon -sort"></i><span>เปลี่ยนลำดับการทำกิจกรรม</span></a>');
			$dui->add('<a class="sg-action" href="'.url('project/develop/plan/'.$tpid.'/changeparent/'.$item->trid).'" data-rel="box"><i class="icon -back"></i><span>ย้ายไปอยู่ภายใต้กิจกรรมอื่น</span></a>');
			if (empty($item->childsCount)) {
				$dui->add('<sep>');
				$dui->add('<a class="sg-action" href="'.url('project/develop/plan/'.$tpid.'/remove/'.$item->trid).'" data-rel="replace:#project-plan-item-'.$item->trid.'" data-confirm="ต้องการลบกิจกรรมนี้ กรุณายืนยัน?" data-callback="planRemove"><i class="icon -delete"></i><span>ลบแผนงาน/กิจกรรม</span></a>');
			}
		}
		$ui->add(sg_dropbox($dui->build()));
		$ret.=$ui->build();
		$ret.='</div>';



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

		$ret.=__project_develop_plan_tree_child($devInfo,$item->trid,$pretext);

		$ret.='</div><!-- ui-tree -->';
		//$ret.=print_o($item,'$item');
	}




	if (is_null($parent) && $isEdit) {
		$ret.='<form class="sg-form -no-print" action="'.url('project/develop/plan/'.$tpid.'/add').'" style="margin:16px 0;position: relative; left:0;" data-rel="replace:#project-plan-item-master" data-checkvalid="true"><span class="-bullet" style="position: absolute;">'.$pretext.(++$actid).'</span> <input class="form-text -require -fill" type="text" name="title" size="60" placeholder="ระบุ ชื่อแผนงาน/กิจกรรมหลัก" style="margin-left:38px;width: calc( 100% - 54px );" /><div class="form-item" style="text-align:right;"><button class="btn -primary"><i class="icon -addbig -white"></i><span>เพิ่มแผนงาน/กิจกรรมหลัก</span></button></div>';
		$ret.='</form>';
	}
	$ret.='</div><!-- ui-tree '.$tagId.' -->';

	return $ret;
}
?>