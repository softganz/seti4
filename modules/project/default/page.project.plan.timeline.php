<?php
function project_plan_timeline($self,$tpid=NULL) {
	if (!is_object($topic)) {
		$topic=project_model::get_topic($tpid);
		$info=project_model::get_info($tpid);
		$options=NULL;
	} else if (is_object($topic)) {
		$options=sg_json_decode($options);
	}

	if ($topic->type!='project') return message('error','This is not a project');

	$action=SG\getFirst($action,post('act'));
	$isEdit=$topic->project->isEdit;
	$isEditDetail=$info->project->isEditDetail;




	$tables = new Table();
	$tables->addClass('--plan');
	$tables->thead=array('no'=>'ลำดับ','กิจกรรมหลัก','center budget'=>'งบประมาณ');
	$start = $month = strtotime($info->project->date_from);
	$end = strtotime($info->project->date_end);
	while($month < $end) {
		$tables->thead[]=sg_date($month,'ดด ปป');
		$monthList[]=date('Y-m',$month);
		$month = strtotime("+1 month", $month);
	}
	$tables->thead[]=$isEdit && $isEditDetail ?'<a class="sg-action" href="'.url('project/plan/'.$tpid.'/add').'" data-rel="box"><i class="icon -adddoc"></i><span class="-hidden">+เพิ่มกิจกรรมหลัก</span></a>':'';
	foreach ($info->mainact as $rs) {
		if (!$rs->trid) continue;

		// Create submenu
		$ui=new ui();
		$ui->add('<a href="'.url('project/plan/'.$tpid.'/info/'.$rs->trid).'" class="sg-action" data-rel="box"><i class="icon -view"></i> รายละเอียด</a>');
		if ($isEdit) {
			$ui->add('<sep>');
			//$ui->add('<a href="'.url('project/develop/objective/'.$tpid,array('action'=>'move','id'=>$objective->trid)).'" class="sg-action" data-rel="box" title="ย้ายวัตถุประสงค์">ย้ายวัตถุประสงค์</a>');
			$ui->add('<a class="sg-action" href="'.url('project/plan/'.$tpid.'/edit/'.$rs->trid).'" data-rel="box" title="แก้ไขรายละเอียดกิจกรรมหลัก"><i class="icon -edit -showtext"></i> <span>แก้ไขรายละเอียด</span></a>');
			if (empty($rs->totalCalendar)) {
				$ui->add('<a class="sg-action" href="'.url('project/plan/'.$tpid.'/remove/'.$rs->trid).'" data-confirm="คุณต้องการลบกิจกรรมหลักนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="this" data-removeparent="tr"><i class="icon -delete"></i> ลบกิจกรรมหลัก</a>');
			} else {
				$ui->add('<a href="javascript:void(0)">ลบกิจกรรมหลักไม่ได้</a>');
			}
		}
		$submenu=sg_dropbox($ui->build());
		unset($row);
		$row[]=++$no;
		$row[]=$rs->title;
		$row[]=number_format($rs->budget,2);
		foreach ($monthList as $month) {
			$row[]='<span class="project-plan-month'.($month>=sg_date($rs->fromdate,'Y-m') && $month<=sg_date($rs->todate,'Y-m')?' -active':'').'">&nbsp;<!-- '.$month.'<br />'.sg_date($rs->fromdate,'Y-m').'<br />'.sg_date($rs->todate,'Y-m').'--></span>';
		}
		$row[]=$submenu;
		$tables->rows[]=$row;
	}
	$tables->tfoot[1]=array('<td></td>','รวม',number_format($info->summary->budget,2));
	foreach ($monthList as $month) $tables->tfoot[1][]='';
	$tables->tfoot[1][]='';

	$ret.=$tables->build();

	if ($info->objective) {
		if ($isEdit && $info->summary->budget!=$info->project->budget) $ret.='<p class="notify">คำเตือน : งบประมาณรวมทุกกิจกรรมหลัก ('.number_format($info->summary->budget,2).' บาท) ไม่เท่ากับ งบประมาณของโครงการ ('.number_format($info->project->budget,2).' บาท)<br />กรุณาตรวจสอบความถูกต้องด้วยค่ะ!!!</p>';


		//$mainact=project_model::get_main_activity($tpid,'owner')->info[$actid];
	} else {
		if ($isEdit && empty($info->objective)) $ret.='<p class="notify">ยังไม่มีการกำหนดกิจกรรมหลักของโครงการ</p>';
	}

	if ($isEdit && $isEditDetail) {
		$ret.='<div class="actionbar -project -plan"><a class="sg-action btn -primary" href="'.url('project/plan/'.$tpid.'/add').'" data-rel="box"><i class="icon -addbig -white"></i><span>เพิ่มกิจกรรมหลัก</span></a></div>'._NL;
	}

	//$ret.=print_o($topic,'$topic');
	//$ret.=print_o($mainact,'$mainact');
	//$ret.=print_o($info,'$info');
	//$ret.=print_o($options,'$options');

	$ret.='<style type="text/css">
	.project-plan-month {display:block; vertical-align:middle;}
	.-active {background:green;}
	</style>';

	return $ret;
}
?>