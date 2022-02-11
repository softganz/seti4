<?php
/**
* Project View
*
* @param Object $self
* @param Int $tpid
* @return String
*/

import('widget:project.like.status.php');

function project_info_view($self, $tpid = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid);
	$tpid = $projectInfo->tpid;

	if (!$tpid) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

	$self->theme->class .= ' project-status-'.$projectInfo->info->project_statuscode;

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo,'{showPrint: true}');

	$basicInfo = reset(SG\getFirst(project_model::get_tr($tpid, 'info:basic')->items['basic'], []));


	$isAdmin = $projectInfo->info->isAdmin;
	$isEdit = $projectInfo->info->isEdit;
	$isEditDetail = $projectInfo->info->isEditDetail;
	$lockReportDate = $projectInfo->info->lockReportDate;


	R::Model('reaction.add', $tpid, 'TOPIC.VIEW');

	$ret .= (new ScrollView([
		'child' => new ProjectLikeStatusWidget([
			'projectInfo' => $projectInfo,
		]),
	]))->build();


	// รายละเอียดโครงการ
	if ($isEdit) {
		$inlineAttr['class'] = 'sg-inline-edit';
		$inlineAttr['data-update-url'] = url('project/edit/tr');
		$inlineAttr['data-tpid'] = $tpid;
		$inlineAttr['data-refresh-url'] = url('project/'.$tpid,array('debug'=>post('debug')));
		if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
	}

	$ret .= '<div id="project-info" '.sg_implode_attr($inlineAttr).'>'._NL;




	$tables = new Table();
	$tables->addClass('item__card project-info');
	$tables->colgroup = array('width="30%"','width="70%"');
	$tables->caption = 'รายละเอียดโครงการ';


	$tables->rows[] = array(
			'ชื่อโครงการ/กิจกรรม'.($projectInfo->info->prtype!='โครงการ'?'<br />('.$projectInfo->info->prtype.')':''),
			'<strong>'.view::inlineedit(array('group'=>'topic','fld'=>'title','class'=>'-fill'),$projectInfo->title,$isEditDetail).'</strong>'.($isEdit?'<span class="form-required" style="margin-left:-16px;">*</span>':'')
		);

	if ($projectInfo->info->projectset_name) {
		$tables->rows[] = array(
				'ภายใต้โครงการ',
				'<a href="'
				. url('project/'.$projectInfo->info->projectset).'">'
				.$projectInfo->info->projectset_name.'</a>'
			);
	}

	if (1 || cfg('project.option.argno')) {
		$tables->rows[] = array(
				'เลขที่ข้อตกลง',
				view::inlineedit(array('group'=>'project','fld'=>'agrno'),$projectInfo->info->agrno,$isEdit)
			);
	}
	if (1 || cfg('project.option.prid')) {
		$tables->rows[] = array(
				'รหัสโครงการ',
				view::inlineedit(array('group'=>'project','fld'=>'prid'),$projectInfo->info->prid,$isEditDetail)
			);
	}




	$openYear = SG\getFirst($projectInfo->info->pryear,date('Y'));
	$pryearList = array();
	for ($i = $openYear-1; $i <= date('Y')+1; $i++) {
		$pryearList[$i] = $i + 543;
	}


	$tables->rows[]=array('ผู้รับผิดชอบโครงการ',view::inlineedit(array('group'=>'project','fld'=>'prowner','class'=>'-fill'),$projectInfo->info->prowner,$isEditDetail));

	$tables->rows[]=array('คณะทำงาน <sup><a href="#" title="แยกแต่ละรายชื่อด้วยเครื่องหมาย ,">?</a></sup>',view::inlineedit(array('group'=>'project','fld'=>'prteam','class'=>'-fill'),$projectInfo->info->prteam,$isEditDetail));

	$tables->rows[]=array('พี่เลี้ยงโครงการ',view::inlineedit(array('group'=>'project','fld'=>'prtrainer','class'=>'-fill'),$projectInfo->info->prtrainer,$isEdit));

	if ($isAdmin)
		$tables->rows[]=array(
				'ปี',
				view::inlineedit(array('group'=>'project','fld'=>'pryear'),$projectInfo->info->pryear+543,$isEditDetail,'select',$pryearList).' (เฉพาะแอดมิน)'
			);

	$tables->rows[] = array(
			'ระยะเวลาดำเนินโครงการ',
			view::inlineedit(
				array('group'=>'project','fld'=>'date_from','ret'=>'date:ว ดดด ปปปป','value'=>$projectInfo->info->date_from?sg_date($projectInfo->info->date_from,'d/m/Y'):''),
				$projectInfo->info->date_from,
				$isEdit,
				'datepicker'
			)
			. ' - '
			. view::inlineedit(
				array('group'=>'project','fld'=>'date_end','ret'=>'date:ว ดดด ปปปป', 'value'=>$projectInfo->info->date_end?sg_date($projectInfo->info->date_end,'d/m/Y'):''),
				$projectInfo->info->date_end,
				$isEdit,
				'datepicker'
			)
			.($isEdit ? ' <span class="form-required">*</span>' : '')
		);

	$tables->rows[] = array(
			'งบประมาณ',
			view::inlineedit(array('group'=>'project','fld'=>'budget', 'ret'=>'money'),$projectInfo->info->budget,$isEditDetail,'money').' บาท'.($isEdit?' <span class="form-required">*</span>':'')
		);







	$tables->rows[]=array('จำนวนกลุ่มเป้าหมาย',view::inlineedit(array('group'=>'project','fld'=>'totaltarget','class'=>'-number','value'=>$projectInfo->info->totaltarget),$projectInfo->info->totaltarget,$isEditDetail).' คน');
	$tables->rows[]=array('รายละเอียดกลุ่มเป้าหมาย',view::inlineedit(array('group'=>'project','fld'=>'target','button'=>'yes','ret'=>'html'),$projectInfo->info->target,$isEditDetail,'textarea'));

	$tables->rows[] = array(
			'พื้นที่ดำเนินการ',
			view::inlineedit(
				array(
					'group' => 'project',
					'fld' => 'area',
					'areacode' => $projectInfo->info->areacode,
					'options' => '{
						class: "-fill",
						autocomplete: {
							target: "areacode",
							query: "'.url('api/address').'",
							minlength: 5
						}
					}',
				),
				$projectInfo->info->area,
				$isEditDetail,
				'autocomplete'
			)
		);

	$tables->rows[]=array('จังหวัด',$projectInfo->info->provname);

	$tables->rows[] = array(
			'ละติจูด-ลองจิจูด',
			view::inlineedit(
				array('group'=>'project','fld'=>'location','class'=>'-fill'),
				($projectInfo->info->location ? $projectInfo->info->lat.','.$projectInfo->info->lnt:''),
				$isEdit
			)
			. '<a href="'.url('project/'.$tpid.'/info.map').'"><i class="icon -pin"></i></a>'
		);





	if ($projectInfo->info->objective) $tables->rows[]=array('วัตถุประสงค์ของกิจกรรม/โครงการ',view::inlineedit(array('group'=>'project','fld'=>'objective','button'=>'yes','ret'=>'html'),$projectInfo->info->objective,false,'textarea'));
	if ($projectInfo->info->activity) $tables->rows[]=array('กิจกรรมหลัก',view::inlineedit(array('group'=>'project','fld'=>'activity','button'=>'yes','ret'=>'html'),$projectInfo->info->activity,false,'textarea'));



	$ret .= '<section id="project-detail-spec" class="project-detail-spec"><!-- section start -->'._NL;
	$ret .= $tables->build()._NL;
	$ret .= R::PageWidget('project.info.period', [$planningInfo])->build();
	$ret .= '</section><!-- project-detail-spec -->';




	$ret .= R::PageWidget('project.info.period', [$planningInfo])->build();

	$ret.='<p><b>หลักการและเหตุผล</b></p>'.view::inlineedit(array('group'=>'info:basic','fld'=>'text1', 'tr'=>$basicInfo->trid, 'ret'=>'html'),$basicInfo->text1,$isEdit,'textarea');
	$ret.='<p><b>กรอบแนวคิดและยุทธศาสตร์หลัก</b></p>'.view::inlineedit(array('group'=>'info:basic','fld'=>'text6', 'tr'=>$basicInfo->trid, 'ret'=>'html'),$basicInfo->text6,$isEdit,'textarea');


	// Show project plan
	$ret.='<div id="project-plan" class="project-plan box">';

	$activityGroupBy=SG\getFirst(post('gr'),'act');

	if ($activityGroupBy!='act') $activityGroupBy='act';
	$_COOKIE['maingrby']=$activityGroupBy;
	$ret.='<h3>การดำเนินงาน/กิจกรรม</h3>';

	$ret.='<div id="project-calendar-wrapper">';
	$ret .= R::Page('project.calendar', NULL, $projectInfo)._NL;

	$ret.='</div><!-- project-calendar-wrapper -->'._NL;
	//$ret.='</div><!-- sg-tabs -->'._NL;
	$ret.='</div><!-- project-plan -->'._NL;


	// Section :: Project Creator
	$ret.='<p>โครงการเข้าสู่ระบบโดย <a href="'.url('project/list',array('u'=>$projectInfo->info->uid)).'" title="'.htmlspecialchars($projectInfo->info->ownerName).'"><img src="'.model::user_photo($projectInfo->info->username).'" width="32" height="32" alt="'.htmlspecialchars($projectInfo->info->ownerName).'" /> '.$projectInfo->info->ownerName.'</a> เมื่อวันที่ '.sg_date($projectInfo->info->created,'ว ดดด ปปปป H:i').' น.</p>';






	// Section :: Social share
	if (_ON_HOST && in_array($projectInfo->info->type,explode(',',cfg('social.share.type'))) && !is_home() && $projectInfo->property->option->social) {
		$ret .= view::social(url('project/'.$tpid));
	}

	$ret .= '</div><!-- project-info -->'._NL._NL;

	//$ret .= print_o($projectInfo,'$projectInfo');


	return $ret;
}
?>
