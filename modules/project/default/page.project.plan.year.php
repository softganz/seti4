<?php
function project_plan_year($self,$tpid=NULL) {
	if (!is_object($tpid)) {
		$projectInfo=R::Model('project.get',$tpid);
	} else {
		$projectInfo=$tpid;
		$tpid=$projectInfo->tpid;
	}

	setcookie('maingrby','year',time()+10*365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));

	$tables = new Table();
	$tables->addClass('--plan');
	$tables->thead=array('กิจกรรมหลัก','center budget'=>'งบประมาณ');
	$start = $month = strtotime($projectInfo->info->date_from);
	$end = strtotime($projectInfo->info->date_end);
	while($month < $end) {
		$tables->thead[]=sg_date($month,'ดด ปป');
		$monthList[]=date('Y-m',$month);
		$month = strtotime("+1 month", $month);
	}
	$tables->thead[]=$isEdit && $isEditDetail ?'<a class="sg-action" href="'.url('project/plan/'.$tpid.'/add').'" data-rel="box"><i class="icon -adddoc"></i><span class="-hidden">+เพิ่มกิจกรรมหลัก</span></a>':'';
	foreach ($projectInfo->activity as $rs) {
		if (empty($rs->trid)) continue;

		// Create submenu
		$ui=new Ui();
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
		$row[]=$rs->title;
		$row[]=number_format($rs->planBudget,2);
		if (empty($rs->parent)) $row['config']=array('class'=>'subheader');
		foreach ($monthList as $month) {
			$row[]='<span class="project-plan-month'.($month>=sg_date($rs->fromdate,'Y-m') && $month<=sg_date($rs->todate,'Y-m')?' -active':'').'">&nbsp;<!-- '.$month.'<br />'.sg_date($rs->fromdate,'Y-m').'<br />'.sg_date($rs->todate,'Y-m').'--></span>';
		}
		$row[]=$submenu;
		$tables->rows[]=$row;
	}
	$tables->tfoot[1]=array(
											'รวม',
											number_format($projectInfo->info->planBudget,2)
											);
	foreach ($monthList as $month) $tables->tfoot[1][]='';
	$tables->tfoot[1][]='';

	$ret.=$tables->build();

	/*
	if ($info->objective) {
		if ($isEdit && $info->summary->budget!=$info->project->budget) $ret.='<p class="notify">คำเตือน : งบประมาณรวมทุกกิจกรรมหลัก ('.number_format($info->summary->budget,2).' บาท) ไม่เท่ากับ งบประมาณของโครงการ ('.number_format($projectInfo->info->planBudget,2).' บาท)<br />กรุณาตรวจสอบความถูกต้องด้วยค่ะ!!!</p>';
	} else {
		if ($isEdit && empty($info->objective)) $ret.='<p class="notify">ยังไม่มีการกำหนดกิจกรรมหลักของโครงการ</p>';
	}

	if ($isEdit && $isEditDetail) {
		$ret.='<div class="actionbar -project -plan"><a class="sg-action btn -primary" href="'.url('project/plan/'.$tpid.'/add').'" data-rel="box"><i class="icon -addbig -white"></i><span>เพิ่มกิจกรรมหลัก</span></a></div>'._NL;
	}
	*/

	//$ret.=print_o($projectInfo,'$projectInfo');

	$ret.='<style type="text/css">
	.project-plan-month {display:block; vertical-align:middle;}
	.-active {background:green;}
	</style>';

	return $ret;
}
?>