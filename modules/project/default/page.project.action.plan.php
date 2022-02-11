<?php
function project_action_plan($self,$tpid = NULL, $owner = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid, '{initTemplate: true}');
	$tpid = $projectInfo->tpid;
	$userId = i()->uid;

	$isEdit = $projectInfo->info->isEdit;
	$isAddAction = $isEdit || $projectInfo->info->membershipType;



	if (empty($projectInfo))
		return message('error','ERROR : No Project');

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo,'{showPrint: true}');

	if (!$isEdit) return '';


	$ret .= '<div id="project-action-plan" class="-sg-scroll-width">';


	$lockReportDate=project_model::get_lock_report_date($tpid);

	$calendarList = R::Model('project.calendar.get',array('tpid'=>$tpid));

	// No activity was set, show list of activity for send activity
	$tables = new Table();
	$tables->addClass('project-activity-title -no-print');
	$order=SG\getFirst(post('o'),'date');
	$calowner=$part=='owner'?1:2;
	$tables->thead=array(
		'no'=>'',
		'date'=>'<a class="sg-action" href="'.url(q(),array('o'=>'date')).'" data-rel="#main">วันที่ทำกิจกรรม<br />(ตามแผน)'.($order=='date'?' <i class="icon -sort"></i> ':'').'</a>',
		'<a class="sg-action" href="'.url(q(),array('o'=>'title')).'" data-rel="#main">รายชื่อกิจกรรมตามแผนที่วางไว้'.($order=='title'?' <i class="icon -sort"></i> ':'').'</a>',
		'money -budget'=>'งบประมาณ<br />(บาท)',
		'money -expense'=>'ค่าใช้จ่าย<br />(บาท)',
		'date submenu'=>'บันทึกกิจกรรม',
		'amt'=>'รายงานช้า<br />(วัน)',
		''
	);
	$orders=array('date'=>'fromdate','title'=>'c.title');


	if (empty($calendarList)) $ret.='<p class="notify">ยังไม่มีการสร้างกิจกรรมในปฏิทินกิจกรรมของโครงการ กรุณา <b><a href="'.url('project/'.$tpid).'">คลิกที่รายละเอียดโครงการ</a></b> เพื่อ <b>"เพิ่มกิจกรรมย่อยของโครงการ"</b> ก่อน</p>';

	foreach ($calendarList as $rs) {
		if ($rs->tagName == 'group' || $rs->childs) {
			// $tables->rows[] = [
			// 	++$no,
			// 	$date,
			// 	'<td colspan="5">'.$rs->title.'</td>',
			// 	'',
			// ];
			continue;
		}


		$isLate=$rs->to_date>$lockReportDate && empty($rs->actionId) && $rs->late>0;
		$ui = new Ui();
		if ($rs->trtotal) {
			$ui->add('<a href="#project-action-'.$rs->actionId.'">รายละเอียดบันทึกกิจกรรม</a>');
			if ($isEdit || $rs->uid == $userId) {
				$ui->add('<a class="sg-action" href="'.url('project/'.$rs->tpid.'/info/action.post/'.$rs->actionId).'" data-rel="box" data-width="640" title="แก้ไข'.($rs->flag==_PROJECT_DRAFTREPORT?'(ร่าง)':'').'บันทึกกิจกรรม">แก้ไข'.($rs->flag==0?'(ร่าง)':'').'บันทึกกิจกรรม</a>');
				$ui->add('<a href="'.url('project/'.$tpid.'/info.expense/'.$rs->calid).'">ค่าใช้จ่าย/เอกสารการเงิน</a>');
			}
		}
		if ($isEdit) $ui->add('<a href="'.url('project/'.$tpid.'/info.join/'.$rs->id).'">บันทึกผู้เข้าร่วมกิจกรรม</a>');
		//$ui->add('<a href="'.url('project/'.$tpid.'/owner/menu',array('calid'=>$rs->id)).'">ช่วยเหลือ</a>');
		$submenu = $ui->count() ? sg_dropbox($ui->build('ul'),'{type:"click"}') : '';


		$date=$rs->from_date?(sg_date($rs->from_date,'ว ดด ปป').($rs->to_date && $rs->to_date!=$rs->from_date?' - '.sg_date($rs->to_date,'ว ดด ปป'):'')):'(ยังไม่ระบุ)';

		$addButton='';
		if (cfg('project.activity.multiplereport')) {
			// Multiple report
			//$addButton='<a class="btn" href="'.(url('project/'.$tpid.'/'.$part,'calid='.$rs->id)).'" title="บันทึกผลการทำกิจกรรมที่เสร็จเรียบร้อยแล้ว">บันทึกกิจกรรม</a>'.($rs->trtotal?'<span class="moredetail"> ('.$rs->trtotal.' บันทึก)<span>':'');
		} else {
			// Single report
			if ($rs->actionId && $rs->flag==_PROJECT_DRAFTREPORT) $addButton.='(ร่าง)';

			// Have report send
			if ($isEdit || $rs->owner == $userId) {
				if ($rs->from_date <= $lockReportDate && $rs->actionId) {
					$addButton .= '<a class="sg-action btn -link" href="'.(url('project/'.$tpid.'/action.view/'.$rs->actionId)).'" data-rel="box" title="ดูบันทึกกิจกรรม"><i class="icon -viewdoc -gray"></i><span>'.($rs->trtotal > 1 ? $rs->trtotal.' ' : '').'บันทึกกิจกรรม</a></span>';
				} else if ($rs->from_date <= $lockReportDate) {
					$addButton.='-';
				} else if ($rs->from_date > $lockReportDate && $rs->actionId) {
					$addButton .= '<a class="sg-action btn" href="'.(url('project/'.$tpid.'/info/action.post/'.$rs->actionId)).'" data-rel="box" data-width="640" title="แก้ไขบันทึกกิจกรรม"><i class="icon -edit -gray"></i><span>'.($rs->trtotal > 1 ? $rs->trtotal.' ' : '').'แก้ไขกิจกรรม</a></span>';
				} else if ($rs->from_date > $lockReportDate) {
					$addButton .= '<a class="sg-action btn" href="'.url('project/'.$tpid.'/info/action.post',array('calid'=>$rs->calid)).'" data-rel="box" data-width="640" title="คลิกเพื่อเขียนบันทึกกิจกรรม"><i class="icon -addbig -white -circle -primary"></i><span>บันทึกกิจกรรม</span></a>';
				} else if ($rs->actionId) {
					$addButton .= '<a class="sg-action btn -link" href="'.(url('project/'.$tpid.'/action.view/'.$rs->actionId)).'" data-rel="box" title="คลิกเพื่อดูบันทึกกิจกรรม"><i class="icon -viewdoc"></i><span>'.($rs->trtotal > 1 ? $rs->trtotal.' ' : '').'บันทึกกิจกรรม</a></span>';
				}
			}
		}
		$tables->rows[]=array(
			++$no,
			$date,
			($rs->actionId ? '<a class="" href="#project-action-'.$rs->actionId.'">'.$rs->title.'</a>' : $rs->title)
			//.print_o($rs,'$rs')
			,
			$rs->budget > 0 ? number_format($rs->budget,2) : '',
			$rs->exp_total ? number_format($rs->exp_total,2) : '',
			$addButton,
			$isLate ? $rs->late.' วัน' : '',
			$submenu,
			'config' => array(
				'class' => ($isLate ? 'late' : '').($rs->from_date < $lockReportDate ? ' lockreport' : '')
			),
		);
	}
	$ret.=$tables->build();

	//$ret.=print_o($calendarList,'$calendarList');
	//$ret.=print_o($projectInfo,'$projectInfo');

	$ret .= '</div><!-- -sg-scroll-width -->';
	if ($isEdit && $projectInfo->settings->addNewAction) {
		$ret .= '<nav class="nav -page -sg-text-right"><a class="sg-action btn -primary" href="'.url('project/my/action/post/'.$tpid, array('ref'=>'box','ret'=>url('project/'.$tpid.'/info.action'))).'" data-rel="box" data-width="640"><i class="icon -material">add</i><span>เขียนบันทึกกิจกรรม</span></a></nav>';
	}
	$ret.='<style type="text/css">.project-activity-title th {white-space:nowrap;}</style>';
	return $ret;
}
?>