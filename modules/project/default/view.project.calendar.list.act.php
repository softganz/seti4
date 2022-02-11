<?php
function view_project_calendar_list_act($projectInfo, $info, $isEdit) {
	$tpid = $projectInfo->tpid;
	$calendarList = R::Model('project.calendar.get', array('tpid' => $tpid, 'owner' => 1));
	$lockReportDate = project_model::get_lock_report_date($tpid);
	$totalTarget = 0;

	$showBudget = $projectInfo->is->showBudget;

	//$activityList = R::Model('project.activity.get.bytpid', $tpid);
	//$ret.=print_o($activityList,'$activityList');
	//$ret .= $lockReportDate;

	$tables = new Table();
	$tables->addClass('project-mainact-items');
	$tables->thead = array(
										'date no' => 'วันที่',
										'title' => 'ชื่อกิจกรรม',
										'amt target' => 'กลุ่มเป้าหมาย (คน)',
										'amt budget' => 'งบกิจกรรม (บาท)',
										'amt done' => 'ทำแล้ว',
										'amt expend' => 'ใช้จ่ายแล้ว (บาท)',
										'icons -c1' => ''
									);

	foreach ($calendarList as $crs) {
		$isSubCalendar = true;
		$isEditCalendar = $isEdit && (empty($crs->from_date) || $crs->from_date > $lockReportDate);

		$ui = new Ui();
		$ui->add('<a class="sg-action" href="'.url('project/calendar/'.$tpid.'/info/'.$crs->id).'" data-rel="box" data-width="640"><i class="icon -view"></i>รายละเอียดกิจกรรม</a>');
		if ($isEdit) {
			if ($isEditCalendar) $ui->add('<a class="sg-action" href="'.url('project/calendar/'.$tpid.'/edit/'.$crs->id).'" data-rel="box" data-width="640" title="แก้ไขรายละเอียดกิจกรรมย่อย"><i class="icon -edit -showtext"></i><span>แก้ไขรายละเอียด</span></a>');
			$ui->add('<sep>');
			if ($isEditCalendar) {
				if ($crs->actionId) {
					$ui->add('<a href="javascript:void(0)" style="text-decoration:line-through;">ลบกิจกรรมไม่ได้</a>');
				} else {
					$ui->add('<a class="sg-action" href="'.url('project/calendar/'.$tpid.'/remove/'.$crs->id).'" data-rel="#project-calendar-wrapper" data-ret="'.url('project/calendar/'.$tpid).'" data-confirm="ลบกิจกรรมย่อย กรุณายืนยัน" title="ลบกิจกรรม"><i class="icon -delete -showtext"></i><span>ลบกิจกรรม</span></a>');
				}
			}
		}

		$submenu='<span class="iconset">'._NL;
		$submenu.=sg_dropbox($ui->build('ul'),'{type:"hover",class:"leftside submenu -no-print"}')._NL;
		$submenu.='</span>'._NL;




		if (empty($crs->from_date))
			$actionDate = '??/??/????';
		else if ($crs->from_date==$crs->to_date)
			$actionDate= sg_date($crs->from_date,'ว ดด ปป');
		else if (sg_date($crs->from_date, 'Y-m') == sg_date($crs->to_date, 'Y-m'))
			$actionDate = sg_date($crs->from_date, 'ว').' - '.sg_date($crs->to_date, 'ว').' '.sg_date($crs->from_date, 'ดด ปป');
		else
			$actionDate=sg_date($crs->from_date, 'ว ดด ปป').($crs->to_date ? ' - '.sg_date($crs->to_date, 'ว ดด ปป') : '');


		$tables->rows[] = array(
											$isEditCalendar ? '<a class="sg-action inline-edit-field -fill" href="'.url('project/calendar/'.$tpid.'/edit/'.$crs->id).'" data-rel="box" data-type="link" data-width="640" title="คลิกเพื่อแก้ไข">'.$actionDate.'</a>' : $actionDate,
											view::inlineedit(array('group' => 'calendar', 'fld' => 'title', 'tr' => $crs->id, 'class' => '-fill', 'value' => $crs->title), $crs->title, $isEditCalendar),
											$crs->targetpreset,
											$showBudget ? number_format($crs->budget, 2) : '-',
											$crs->actionId ? '<a class="sg-action" href="'.url('project/'.$tpid.'/action.view/'.$crs->actionId).'" data-rel="box" title="บันทึกหมายเลข '.$crs->actionId.'">✔</a>' : '',
											$showBudget && $crs->exp_total ? number_format($crs->exp_total, 2) : '-',
											$submenu,
											'config' => array('class' => 'calendar')
											);

		$totalTarget += $crs->targetpreset;
	}
	$tables->rows[] = array(
											'',
											'รวม',
											number_format($totalTarget),
											$showBudget ? number_format($info->summary->totalBudget, 2) : '-',
											number_format($info->summary->activity),
											$showBudget ? number_format($info->summary->expense, 2) : '-',
											'', //$isEdit?'<a class="sg-action" href="'.url('project/calendar/'.$tpid.'/add').'" data-rel="box" title="เพิ่มกิจกรรม"><i class="icon -add -hidetext"></i><span class="-hidden">เพิ่มกิจกรรม</span></a>':'',
											'config'=>array('class' => 'subfooter')
										);

	$ret .= $tables->build();

	if ($isEdit) {
		$ret .= '<div class="actionbar -project -objective"><a class="sg-action btn -primary" href="'.url('project/calendar/'.$tpid.'/add').'" data-rel="box"><i class="icon -addbig -white"></i><span>เพิ่มกิจกรรม</span></a></div>';
	}

	if ($isEdit && $info->summary->totalBudget > 0 && $info->summary->totalBudget != $info->project->budget)
		$ret .= '<p class="notify">คำเตือน : งบประมาณรวมทุกกิจกรรรม ('.number_format($info->summary->totalBudget, 2).' บาท) ไม่เท่ากับ งบประมาณของโครงการ ('.number_format($info->project->budget, 2).' บาท)<br />กรุณาตรวจสอบความถูกต้องด้วยค่ะ!!!</p>';
	/*
	if ($info->summary->totalBudget!=$info->summary->budget) $ret.='<p class="notify">คำเตือน : งบประมาณของกิจกรรมหลัก ('.number_format($info->summary->budget,2).' บาท) ไม่เท่ากับ งบประมาณของกิจกรรมย่อย ('.number_format($info->summary->totalBudget,2).' บาท)</p>';

	if ($info->project->budget!=$info->summary->budget) $ret.='<p class="notify">คำเตือน : งบประมาณของโครงการ ('.number_format($info->project->budget,2).' บาท) ไม่เท่ากับ งบประมาณของกิจกรรมหลัก ('.number_format($info->summary->budget,2).' บาท)</p>';
	*/

	return $ret;
}
?>
