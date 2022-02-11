<?php
/**
* Project calendar information
*
* @param Object $self
* @param Integer $tpid or Object $projectInfo
* @return String
*/
function project_plan_time($self,$tpid=NULL) {
	if (!is_object($tpid)) {
		$projectInfo=R::Model('project.get',$tpid);
	} else {
		$projectInfo=$tpid;
		$tpid=$projectInfo->tpid;
	}

	setcookie('maingrby','time',time()+10*365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));

	if ($projectInfo->info->type!='project') return $ret.message('error','This is not a project');

	//$calendarList=project_model::get_calendar($tpid,$period,$owner="owner");
	$stmt='SELECT `trid` FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="activity" ORDER BY `date1` ASC';
	$calendarList=mydb::select($stmt,':tpid',$tpid);
	//$ret.=print_o($calendarList);

	$totalTarget=0;

	$tables = new Table();
	$tables=new table('item project-mainact-items');
	$tables->thead=array('date no'=>'วันที่','title'=>'ชื่อกิจกรรม','amt target'=>'กลุ่มเป้าหมาย (คน)','amt budget'=>'งบกิจกรรม (บาท)','amt done'=>'ทำแล้ว','amt expend'=>'ใช้จ่ายแล้ว (บาท)', $isEdit?'<a class="sg-action" href="'.url('project/calendar/'.$tpid.'/add').'" data-rel="box" title="เพิ่มกิจกรรม"><i class="icon -adddoc -hidetext"></i><span class="-hidden">เพิ่มกิจกรรม</span></a>':'');
	foreach ($calendarList->items as $rs) {
		$crs=$projectInfo->activity[$rs->trid];
		$isSubCalendar=true;
		$isEditCalendar=$isEdit && $crs->from_date>$lockReportDate;

		$ui=new ui();
		$ui->add('<a class="sg-action" href="'.url('project/calendar/'.$tpid.'/info/'.$crs->id).'" data-rel="box" data-height="90%"><i class="icon -view"></i>รายละเอียดกิจกรรม</a>');
		if ($isEdit) {
			if ($isEditCalendar) $ui->add('<a class="sg-action" href="'.url('project/calendar/'.$tpid.'/edit/'.$crs->id).'" data-rel="box" title="แก้ไขรายละเอียดกิจกรรมย่อย"><i class="icon -edit -showtext"></i><span>แก้ไขรายละเอียด</span></a>');
			$ui->add('<sep>');
			if ($isEditCalendar) {
				if ($crs->activityId) {
					$ui->add('<a href="javascript:void(0)" style="text-decoration:line-through;">ลบกิจกรรมไม่ได้</a>');
				} else {
					$ui->add('<a class="sg-action" href="'.url('project/calendar/'.$tpid.'/remove/'.$crs->id).'" data-rel="#project-plan-wrapper" data-ret="'.url('project/calendar/'.$tpid).'" data-confirm="ลบกิจกรรมย่อย กรุณายืนยัน" title="ลบกิจกรรม"><i class="icon -delete -showtext"></i><span>ลบกิจกรรม</span></a>');
				}
			}
		}

		$submenu='<span class="iconset">'._NL;
		$submenu.=sg_dropbox($ui->build('ul'),'{type:"hover",class:"leftside submenu -no-print"}')._NL;
		$submenu.='</span>'._NL;

		if (empty($crs->fromdate)) $actionDate='';
		else if ($crs->fromdate==$crs->todate) $actionDate= sg_date($crs->fromdate,'ว ดด ปป');
		else if (sg_date($crs->fromdate,'Y-m')==sg_date($crs->todate,'Y-m')) $actionDate=sg_date($crs->fromdate,'ว').'-'.sg_date($crs->todate,'ว').' '.sg_date($crs->fromdate,'ดด ปป');
		else $actionDate=sg_date($crs->fromdate,'ว ดด ปป').'-'.sg_date($crs->todate,'ว ดด ปป');
		$tables->rows[]=array(
											$isEditCalendar?'<a class="sg-action inline-edit-field -fill" href="'.url('project/calendar/'.$tpid.'/edit/'.$crs->id).'" data-rel="box" data-type="link" title="คลิกเพื่อแก้ไข">'.$actionDate.'</a>':$actionDate,
											view::inlineedit(array('group'=>'calendar','fld'=>'title','tr'=>$crs->id, 'class'=>'-fill', 'value'=>$crs->title),$crs->title,$isEditCalendar),
											$crs->targetpreset,
											number_format($crs->budget,2),
											$crs->activityId?'<a href="'.url('paper/'.$tpid.'/owner#tr-'.$crs->activityId).'" title="บันทึกหมายเลข '.$crs->activityId.'">✔</a>':'',
											$crs->exp_total?number_format($crs->exp_total,2):'-',
											$submenu,
											'config'=>array('class'=>'calendar')
											);
		$totalTarget+=$crs->targetpreset;
	}
	$tables->rows[]=array(
											'',
											'รวม',
											number_format($totalTarget),
											number_format($info->summary->totalBudget,2),
											number_format($info->summary->activity),
											number_format($info->summary->expense,2),
											''
											'config'=>array('class' => 'subfooter')
										);

	$ret.=$tables->build();

	if ($isEdit && $info->summary->totalBudget!=$info->project->budget) $ret.='<p class="notify">คำเตือน : งบประมาณรวมทุกกิจกรรรม ('.number_format($info->summary->totalBudget,2).' บาท) ไม่เท่ากับ งบประมาณของโครงการ ('.number_format($info->project->budget,2).' บาท)<br />กรุณาตรวจสอบความถูกต้องด้วยค่ะ!!!</p>';
	/*
	if ($info->summary->totalBudget!=$info->summary->budget) $ret.='<p class="notify">คำเตือน : งบประมาณของกิจกรรมหลัก ('.number_format($info->summary->budget,2).' บาท) ไม่เท่ากับ งบประมาณของกิจกรรมย่อย ('.number_format($info->summary->totalBudget,2).' บาท)</p>';

	if ($info->project->budget!=$info->summary->budget) $ret.='<p class="notify">คำเตือน : งบประมาณของโครงการ ('.number_format($info->project->budget,2).' บาท) ไม่เท่ากับ งบประมาณของกิจกรรมหลัก ('.number_format($info->summary->budget,2).' บาท)</p>';
	*/

	if ($isEdit) {
		$ret.='<div class="actionbar -project -objective"><a class="sg-action btn -primary" href="'.url('project/calendar/'.$tpid.'/add').'" data-rel="box"><i class="icon -addbig -white"></i><span>เพิ่มกิจกรรมย่อย</span></a></div>';
	}
	//$ret.=print_o($projectInfo->activity,'$activity');
	return $ret;
}
?>