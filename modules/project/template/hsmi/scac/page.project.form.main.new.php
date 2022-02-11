<?php
/**
* Project detail
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_form_main($self, $topic, $para, $body) {
	$tpid = $topic->tpid;
	$strategy = $roadmap='';
	$project = $topic->project;

	$self->theme->class .= ' project-status-'.$projectInfo->info->project_statuscode;

	$info = project_model::get_info($tpid, 'info');
	$basicInfo = reset(SG\getFirst(project_model::get_tr($tpid, 'info:basic')->items['basic'], []));

	$projectInfo = R::Model('project.get', $tpid);


	$options = options('project',$tpid);

	//$ret.=print_o($options,'$options');

	//$ret.=print_o($projectInfo,'$projectInfo');

	$isAdmin = $projectInfo->info->isAdmin;
	$isEdit = $projectInfo->info->isEdit;
	$isEditDetail = $projectInfo->info->isEditDetail;
	$lockReportDate = $projectInfo->info->lockReportDate;

	//$ret.= print_o($projectInfo,'$projectInfo');


	$liketitle = $isEdit ? 'คลิกเพื่อแก้ไข' : '';
	$editclass = $isEdit ? 'editable' : '';
	$emptytext = $isEdit ? '<span style="color:#999;">แก้ไข</span>' : '';

	// รายละเอียดโครงการ
	if ($isEdit) {
		$inlineAttr['class'] = 'sg-inline-edit';
		$inlineAttr['data-update-url'] = url('project/edit/tr');
		$inlineAttr['data-tpid'] = $tpid;
		if (post('inline')) $inlineAttr['data-debug'] = 'inline';
	}
	$ret .= '<div id="project-info" '.sg_implode_attr($inlineAttr).'>'._NL;

	$tables = new Table();
	$tables->addClass('item__card project-info');
	$tables->colgroup = array('width="30%"','width="70%"');
	$tables->caption = 'รายละเอียดโครงการ';

	/*
	if ($isAdmin) {
		$tables->rows[]=array('ประเภท',view::inlineedit(array('group'=>'project','fld'=>'prtype'),$projectInfo->info->prtype,$isEdit,'select',array('แผนงาน'=>'แผนงาน','ชุดโครงการ'=>'ชุดโครงการ','โครงการ'=>'โครงการย่อย')));
	}
	*/

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
	$tables->rows[] = array(
											'ชื่อโครงการ/กิจกรรม'.($projectInfo->info->prtype!='โครงการ'?'<br />('.$projectInfo->info->prtype.')':''),
											'<strong>'.view::inlineedit(array('group'=>'topic','fld'=>'title','class'=>'-fill'),$topic->title,$isEditDetail).'</strong>'.($isEdit?'<span class="form-required" style="margin-left:-16px;">*</span>':'')
										);
	if ($projectInfo->info->projectset_name) {
		$tables->rows[] = array(
												'ภายใต้โครงการ',
												'<a href="'.url('project/set/'.$projectInfo->info->projectset).'">'.$projectInfo->info->projectset_name.'</a>'
											);
	}


	/*
	$tables->rows[] = array(
											'ชื่อองค์กรที่รับผิดชอบ',
											view::inlineedit(array('group'=>'project','fld'=>'orgnamedo','class'=>'-fill'),$projectInfo->info->orgnamedo,$isEditDetail)
										);
	*/

	$openYear = SG\getFirst(date('Y'));
	$pryearList = array();
	for ($i = $openYear; $i <= date('Y'); $i++) {
		$pryearList[$i] = $i + 543;
	}

	/*
	$tables->rows[] = array(
											'วันที่อนุมัติ',
											view::inlineedit(array('group'=>'project','fld'=>'date_approve','ret'=>'date:ว ดดด ปปปป','value'=>$projectInfo->info->date_approve?sg_date($projectInfo->info->date_approve,'d/m/Y'):''),
											$projectInfo->info->date_approve,
											$isEditDetail,
											'datepicker')
											.($isEdit?' <span class="form-required">*</span>':'')
										);
	*/

		//view::inlineedit(array('group'=>'project','fld'=>'date_approve','value'),$projectInfo->info->date_approve,$isEditDetail,'datepicker',$pryearList).' <span class="form-required">*</span>'.'นำไปคำนวนปีงบประมาณ');
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


	$tables->rows[]=array('จำนวนกลุ่มเป้าหมาย (คน)',view::inlineedit(array('group'=>'project','fld'=>'totaltarget','value'=>$project->totaltarget),$project->totaltarget,$isEditDetail));
	$tables->rows[]=array('รายละเอียดกลุ่มเป้าหมาย',view::inlineedit(array('group'=>'project','fld'=>'target','button'=>'yes','ret'=>'html'),$project->target,$isEditDetail,'textarea'));



	// ข้อมูลผู้รับผิดชอบโครงการ
	$tables->rows[] = array(
											'ผู้รับผิดชอบโครงการ',
											view::inlineedit(array('group'=>'project','fld'=>'prowner','class'=>'-fill'),$projectInfo->info->prowner,$isEdit)
										);
	$tables->rows[]=array('คณะทำงาน <sup><a href="#" title="แยกแต่ละรายชื่อด้วยเครื่องหมาย ,">?</a></sup>',view::inlineedit(array('group'=>'project','fld'=>'prteam','class'=>'-fill'),$project->prteam,$isEditDetail));

	/*
	$tables->rows[] = array(
											'ผู้ร่วมรับผิดชอบโครงการ 1',
											view::inlineedit(array('group'=>'project','fld'=>'prcoowner1','class'=>'-fill'),$projectInfo->info->prcoowner1,$isEdit)
											);
	$tables->rows[] = array(
											'ผู้ร่วมรับผิดชอบโครงการ 2',
											view::inlineedit(array('group'=>'project','fld'=>'prcoowner2','class'=>'-fill'),$projectInfo->info->prcoowner2,$isEdit)
											);
	$tables->rows[] = array(
											'ผู้ร่วมรับผิดชอบโครงการ 3',
											view::inlineedit(array('group'=>'project','fld'=>'prcoowner3','class'=>'-fill'),$projectInfo->info->prcoowner3,$isEdit)
											);
	*/

	$tables->rows[] = array(
											'พี่เลี้ยงโครงการ',
											view::inlineedit(array('group'=>'project','fld'=>'prtrainer','class'=>'-fill'),$projectInfo->info->prtrainer,$isEdit)
										);








	if (empty($projectInfo->info->area))
		$projectInfo->info->area=$projectInfo->info->areaName;

	$tables->rows[] = array(
			'พื้นที่ดำเนินการ',
			view::inlineedit(
				array(
					'group' => 'project',
					'fld' => 'area',
					'areacode' => $projectInfo->info->areacode,
					'class' => '-fill',
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


	$gis['address'] = array();

	//$ret.=print_o($provList,'$provList');


	if ($isEdit)
		$tables->rows[] = array(
												'ละติจูด-ลองจิจูด',
												view::inlineedit(array('group'=>'project','fld'=>'location'),($projectInfo->info->location?$projectInfo->info->lat.','.$projectInfo->info->lnt:''),$isEdit)
											);


	// Show member of this project
	$member = mydb::select('SELECT u.`uid`, u.`username`, u.`name`, tu.`membership` FROM %topic_user% tu LEFT JOIN %users% u USING(`uid`) WHERE `tpid` = :tpid ORDER BY FIELD(`membership`,"Owner","Trainer","Manager") ASC', ':tpid', $topic->tpid);
	$isViewMemberProfile = user_access('access user profiles');
	$name = '<ul class="project__member">'._NL;
	foreach ($member->items as $mrs) {
		$name .= '<li>'
					. '<img src="'.model::user_photo($mrs->username).'" width="32" height="32" alt="'.htmlspecialchars($mrs->name).'" title="'.htmlspecialchars($mrs->name).'" />'
					. ($isViewMemberProfile ? '<span><a class="sg-action" href="'.url('profile/'.$mrs->uid).'" data-rel="box">' : '')
					. $mrs->name
					. ' ('.$mrs->membership.')'
					. ($isViewMemberProfile ? '</a></span>':'')
					. '&nbsp;&nbsp;'
					. ($isAdmin || $projectInfo->info->isTrainer ? '<a class="sg-action" href="'.url('project/edit/removeowner/'.$tpid.'/'.$mrs->uid).'" data-rel="notify" data-confirm="ต้องการลบสมาชิกออกจากโครงการ กรุณายืนยัน?" data-removeparent="li"><i class="icon -cancel -gray"></i></a> ' : '')
					.'</li>'
					._NL;
	}
	$name .= '</ul>'._NL;

	//$ui=new Ui();
	//if ((user_access('administer projects') || in_array('trainer',i()->roles)) && !project_model::is_trainer_of($tpid)) $ui->add('<a href="'.url('project/edit/addtrainer/'.$topic->tpid).'">เพิ่มเป็นพี่เลี้ยง</a>');
	if ($isAdmin || $projectInfo->info->isTrainer) {
		$name .= '<nav class="-sg-text-right">'
					. '<a class="sg-action btn" href="'.url('project/edit/addowner/'.$tpid).'" data-rel="#addowner">'
					. '<i class="icon -person-add -gray"></i>'
					. '<span>เพิ่มเจ้าของโครงการ</span></a>'
					. '</nav>'
					. '<div id="addowner"></div>';
	}
	//$name.=$ui->build();
	$tables->rows[] = array('ผู้ดำเนินการติดตามสนับสนุนโครงการ',$name);




	// งวดสำหรับทำรายงาน
	if (cfg('project.period.max')) {
		$ptables = new Table();
		$ptables->addClass('project-period-items');
		$ptables->caption='งวดสำหรับการทำรายงาน';
		$ptables->colgroup=array(
												'no'=>'align="center" width="5%"',
												'date fromdate'=>'align="center" width="20%"',
												'date todate'=>'align="center" width="20%"',
												'date rfromdate'=>'align="center" width="20%"',
												'date rtodate'=>'align="center" width="20%"',
												'money budget'=>'align="center" width="20%"',
												'align="center" width="5%"',
												);
		$ptables->thead='<thead><tr><th rowspan="2">งวด</th><th colspan="2">วันที่งวดโครงการ</th><th colspan="2">วันที่งวดรายงาน</th><th rowspan="2">งบประมาณ<br />(บาท)</th><th rowspan="2"></th></tr><tr><th>จากวันที่</th><th>ถึงวันที่</th><th>จากวันที่</th><th>ถึงวันที่</th></tr></thead>';

		$projectPeriod=project_model::get_period($tpid);
		$budgetPeriodSum=0;
		foreach ($projectPeriod as $period) {
			unset($row);
			$isEditPeriod=$isEdit && (empty($period->report_from_date) || $period->report_to_date>$lockReportDate);

			$row[]=$period->period;
			$row[]=view::inlineedit(array('group'=>'info:period','fld'=>'date1','tr'=>$period->trid,'ret'=>'date:j ดด ปปปป','value'=>$period->from_date?sg_date($period->from_date,'d/m/Y'):''),$period->from_date,$isEditPeriod,'datepicker');
			$row[]=view::inlineedit(array('group'=>'info:period','fld'=>'date2','tr'=>$period->trid,'ret'=>'date:j ดด ปปปป','value'=>$period->to_date?sg_date($period->to_date,'d/m/Y'):''),$period->to_date,$isEditPeriod,'datepicker');

			$row[]=$period->report_from_date?sg_date($period->report_from_date,'j ดด ปปปป'):'';
			$row[]=$period->report_to_date?sg_date($period->report_to_date,'j ดด ปปปป'):'';

			$row[]=view::inlineedit(array('group'=>'info:period','fld'=>'num1','tr'=>$period->trid, 'ret'=>'money','callback'=>'refreshContent'),$period->budget,$isEditPeriod,'money');
			if ($isEdit && $period->period==count($projectPeriod)) {
				$row[]='<a class="sg-action" href="'.url('project/edit/period/remove/'.$period->trid).'" data-rel="#main" data-confirm="ต้องการลบงวดรายงานนี้ กรุณายืนยัน?" title="ลบงวดสำหรับการทำรายงาน"><i class="icon -cancel"></i></a>';
			} else {
				$row[]='';
			}
			$ptables->rows[]=$row;
			$budgetPeriodSum+=$period->budget;
		}
		$ptables->tfoot[]=array('<td colspan="5" align="right"><strong>รวมงบประมาณ</strong></td>','<td align="right"><strong>'.number_format($budgetPeriodSum,2).'</strong></td>','');

		$periodStr=$ptables->build();
		if ($isEdit && count($projectPeriod)<cfg('project.period.max')) {
			$periodStr.='<p align="right"><a class="sg-action btn -primary" href="'.url('project/edit/period/add/'.$tpid).'" data-rel="#main"><i class="icon -addbig -white"></i><span>เพิ่มงวด</span></a></p>';
		}
		if ($projectInfo->info->budget!=$budgetPeriodSum) {
			$periodStr.='<p class="notify">คำเตือน : รวมงบประมาณของทุกงวด ('.number_format($budgetPeriodSum,2).' บาท) ไม่เท่ากับ งบประมาณโครงการ ('.number_format($projectInfo->info->budget,2).' บาท)</p>';
		}
	}


	$ret .= '<div class="project-info-general">'._NL
			. $tables->build()._NL
			. $periodStr
			. '</div><!-- project-info-general -->'._NL._NL;

	$ret .= '<div id="project-map" width="400" height="400">'._NL
			. '<div class="project-status project-status-'.$projectInfo->info->project_statuscode.'">สถานภาพโครงการ <span>'.$projectInfo->info->project_status.'</span></div>'._NL
			. '<div id="map_canvas"></div>'._NL
			. '</div><!-- project-map --><br clear="all" />'._NL._NL;



	// รายละเอียดโครงการ
	$ret .= '<section id="project-detail-information" class="project-detail-information"><!-- section start -->'._NL;

	$ret .= '<h2>ข้อมูลในการดำเนินโครงการ</h2>'._NL;

	$ret .= '<section id="project-section-problem">';
	$ret .= '<h3>สถานการณ์</h3>';
	$ret .= '<div class="project-info-problem box" id="project-info-problem">';
	$ret .= R::PageWidget('project.info.problem', [$projectInfo])->build();
	$ret .= '<p><b>สถานการณ์/หลักการและเหตุผล (บรรยายเพิ่มเติม)</b></p>'
			. view::inlineedit(array('group'=>'info:basic','fld'=>'text1', 'tr'=>$basicInfo->trid, 'ret'=>'html', 'placeholder' => 'บรรยายสถานการณ์/หลักการและเหตุเพิ่มเติมได้ในช่องนี้'),$basicInfo->text1,$isEdit,'textarea')
			. _NL;
	$ret.='</div><!-- project-info-problem -->';
	$ret .= '</section><!-- project-section-problem -->'._NL._NL;


	$ret .= '<h3>กรอบแนวคิด</h3>';
	$ret .= '<div class="project-info-idea box" id="project-info-idea">';
	$ret .= '<p><b>กรอบแนวคิดและยุทธศาสตร์หลัก</b></p>'
			. view::inlineedit(array('group'=>'info:basic','fld'=>'text6', 'tr'=>$basicInfo->trid, 'ret'=>'html', 'placeholder' => 'บรรยายกรอบแนวคิดและยุทธศาสตร์หลักเพิ่มเติมได้ในช่องนี้'),$basicInfo->text6,$isEdit,'textarea')
			. _NL
			. _NL;
	$ret .= '</div>';

	$ret .= '<section id="project-section-target">';
	$ret .= '<h3>กลุ่มเป้าหมาย</h3>'._NL;
	$ret .= '<div class="box">'._NL;
	$ret .= R::Page('project.info.target',$self,$tpid);
	$ret .= '</div>';
	$ret .= '</section><!-- project-section-target -->'._NL._NL;

	/*
	// ข้อมูลหลักการ
	$ret.='<div><b>หลักการและเหตุผล</b>'.view::inlineedit(array('group'=>'bigdata:project.info','fld'=>'project-problem', 'tr'=>$basicInfo->trid, 'ret'=>'html','class'=>'-fill'),$projectInfo->bigdata['project-problem'],$isEdit,'textarea').'</div>';

	//$ret.='<b>กรอบแนวคิด</b>'.view::inlineedit(array('group'=>'info:basic','fld'=>'text6', 'tr'=>$basicInfo->trid, 'ret'=>'html','class'=>'-fill'),$basicInfo->text6,$isEdit,'textarea');
	$ret.='<div><b>วิธีดำเนินการ</b>'.view::inlineedit(array('group'=>'project','fld'=>'activity', 'ret'=>'html','class'=>'-fill'),$projectInfo->info->activity,$isEdit,'textarea').'</div>';
	$ret.='<div><b>ผลที่คาดว่าจะได้รับ</b>'.view::inlineedit(array('group'=>'info:basic','fld'=>'text5', 'tr'=>$basicInfo->trid, 'ret'=>'html','class'=>'-fill'),$basicInfo->text5,$isEdit,'textarea').'</div>';
	*/



	// Show project objective
	$ret .= '<h3>วัตถุประสงค์/เป้าหมาย</h3>'._NL;
	$ret .= '<div id="project-info-objective" class="project-info-objective box">'._NL;
	$ret .= R::PageWidget('project.info.objective', [$projectInfo])->build();

	if ($projectInfo->info->objective)
		$tables->rows[] = array(
												'วัตถุประสงค์ของกิจกรรม/โครงการ',
												view::inlineedit(array('group'=>'project','fld'=>'objective','button'=>'yes','ret'=>'html'),$projectInfo->info->objective,false,'textarea')
											);
	$ret .= '</div><!-- project-objective -->'._NL._NL;


	// Show project plan
	$_COOKIE['maingrby'] = 'act';

	$ret .= '<h3>การดำเนินงาน/กิจกรรม</h3>'._NL;
	$ret .= '<div id="project-plan" class="project-plan box">'._NL;
		$ret .= '<div id="project-calendar-wrapper">'._NL;
		$ret .= R::Page('project.calendar', $self, $tpid)._NL;
		$ret .= '</div><!-- project-calendar-wrapper -->'._NL;

		$ret .= '<div>'._NL
				. view::inlineedit(
						array('group' => 'project', 'fld' => 'activity', 'ret' => 'html', 'class' => '-fill', 'placeholder' => 'กรณีที่ต้องการบรรยายรายละเอียดวิธีดำเนินการเพิ่มเติม ให้บันทึกไว้ในช่องบรรยายนี้'),
						$project->activity,
						$isEdit,
						'textarea')
				. '</div>'._NL;
	$ret .= '</div><!-- project-plan -->'._NL._NL;

	$ret .= '<h3>ผลที่คาดว่าจะได้รับ</h3>';
	$ret .= '<div class="box">'.view::inlineedit(array('group'=>'info:basic','fld'=>'text5', 'tr'=>$basicInfo->trid, 'ret'=>'html','class'=>'-fill'),$basicInfo->text5,$isEdit,'textarea').'</div>';



	if ($projectInfo->info->ischild) {
		// Show Project Development

		$stmt='SELECT t.`tpid`, t.`title`, d.`budget`, d.`status`
						FROM %project_dev% d
							LEFT JOIN %topic% t USING(`tpid`)
						WHERE t.`parent` = :tpid';
		$dbs=mydb::select($stmt,':tpid',$tpid);

		$ret.='<h3>พัฒนาโครงการ</h3>'._NL;
		$ret.='<div id="develop-child" class="develop-child box">'._NL;
		if ($dbs->_num_rows) {
			$no=0;
			$tables = new Table();
			$tables->thead=array('no' => '', 'ชื่อโครงการ', 'budget -money' => 'งบประมาณ', 'approve -date' => 'วันที่อนุมัติ', 'status -center' => 'สถานภาพ');
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(
													++$no,
													'<a href="'.url('project/develop/'.$rs->tpid).'">'.$rs->title.'</a>',
													number_format($rs->budget,2),
													$rs->date_approve ? sg_date($rs->date_approve, 'ว ดด ปปปป') : '',
													$rs->status,
													);
			}
			$ret.=$tables->build();
		}
		if ($isEdit) {
			$ret .= '<nav class="nav -sg-text-right"><a class="btn -primary" href="'.url('project/develop/create/'.$tpid).'"><i class="icon -addbig -white"></i><span>เพิ่มพัฒนาโครงการ</span></a></nav>'._NL;
		}
		$ret.='</div><!-- develop-child -->'._NL._NL;


		// Show Project Follow
		$stmt='SELECT t.`tpid`, t.`title`, p.`prtype`, p.`project_status`, p.`date_approve`, p.`budget`
						FROM %topic% t
							LEFT JOIN %project% p USING(`tpid`)
						WHERE t.`type` = "project" AND t.`parent` = :tpid';
		$dbs=mydb::select($stmt,':tpid',$tpid);

		$ret.='<h3>ติดตามโครงการ</h3>'._NL;
		$ret.='<div id="project-child" class="project-child box">'._NL;
		if ($dbs->_num_rows) {
			$no=0;
			$tables = new Table();
			$tables->thead=array('no' => '', 'ชื่อโครงการ', 'budget -money' => 'งบประมาณ', 'approve -date' => 'วันที่อนุมัติ', 'status -center' => 'สถานภาพโครงการ');
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(
													++$no,
													'<a href="'.url(($rs->prtype=='โครงการ'?'paper/':'paper/').$rs->tpid).'">'.$rs->title.'</a>',
													number_format($rs->budget,2),
													$rs->date_approve ? sg_date($rs->date_approve, 'ว ดด ปปปป') : '',
													$rs->project_status,
													);
			}
			$ret.=$tables->build();
		}
		if ($isEdit) {
			$ret .= '<nav class="nav -sg-text-right"><a class="btn -primary" href="'.url('project/create/'.$tpid).'"><i class="icon -addbig -white"></i><span>เพิ่มติดตามโครงการ</span></a></nav>'._NL;
		}
		$ret.='</div><!-- project-child -->'._NL._NL;
	}


	$ret .= '<h3>เอกสารประกอบโครงการ</h3>';
	$ret .='<div id="project-docs" class="project-docs box">'._NL
			.__project_form_main_doc($tpid,$isEdit)
			.'</div><!-- project-docs -->'._NL._NL;


	$ret .= '</section><!-- project-detail-information -->'._NL._NL._NL;




	// Section :: Project Creator
	$ret .= '<p>โครงการเข้าสู่ระบบโดย <a href="'.url('project/list',array('u'=>$topic->uid)).'" title="'.htmlspecialchars($mrs->name).'"><img src="'.model::user_photo($topic->username).'" width="32" height="32" alt="'.htmlspecialchars($topic->owner).'" /> '.$topic->owner.'</a> เมื่อวันที่ '.sg_date($topic->created,'ว ดดด ปปปป H:i').' น.</p>'._NL;






	// Section :: Social share
	if (_ON_HOST && in_array($topic->type,explode(',',cfg('social.share.type'))) && !is_home() && $topic->property->option->social) {
		$ret .= view::social(url('paper/'.$topic->tpid));
	}

	$ret .= '</div><!-- project-info -->'._NL._NL;








	unset($body->comment);



	$gis['center'] = SG\getFirst(property('project:map.center:0'),'13.2000,100.0000');
	$gis['zoom'] = SG\getFirst((int)property('project:map.zoom:0'),6);

	$stmt = 'SELECT pv.*, cot.`subdistname`, coa.`distname`, cop.`provname`
					FROM %project_prov% pv
						LEFT JOIN %co_province% cop ON cop.`provid`=pv.`changwat`
						LEFT JOIN %co_district% coa ON coa.`distid`=CONCAT(pv.`changwat`,pv.`ampur`)
						LEFT JOIN %co_subdistrict% cot ON cot.`subdistid`=CONCAT(pv.`changwat`,pv.`ampur`,pv.`tambon`)
					WHERE `tpid`=:tpid'.($autoid?' AND `autoid`=:autoid':'');
	$provList = mydb::select($stmt,':tpid',$tpid,':autoid',$autoid);
	if ($projectInfo->info->area)
		$gis['address'][] = substr($projectInfo->info->area,strpos($projectInfo->info->area,'ตำบล'));
	foreach ($provList->items as $item) {
		$gis['address'][] = 'ต.'.$item->subdistname.' อ.'.$item->distname.' จ.'.$item->provname;
	}

	if ($projectInfo->info->lat) {
		$gis['center'] = $projectInfo->info->lat.','.$projectInfo->info->lnt;
		$gis['zoom'] = 8;
		$gis['current'] = array(
											'latitude' => $projectInfo->info->lat,
											'longitude' => $projectInfo->info->lnt,
											'title' => $projectInfo->info->title,
											'content' => '<h4>'.$projectInfo->info->title.'</h4><p>พื้นที่ : '.$projectInfo->info->area.'</p>'
											);
	}

	$ret .= '<script type="text/javascript">
	function updateMap() {
		var address=$("span[data-fld=\'area\']").text();
		var latlng
		var geocoder = new google.maps.Geocoder();
		var center
		geocoder.geocode( { "address": address}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				latlng = results[0].geometry.location;
				$map.gmap("addMarker", {
					position: latlng,
					draggable: false,
					icon: "https://softganz.com/library/img/geo/circle-green.png",
				}).click(function() {
					$map.gmap("openInfoWindow", { "content": address }, this);
				});
				$map.gmap("get","map").setOptions({"center":latlng});
				//alert(latlng.lat()+","+latlng.lng())
			}
		});
	}
	</script>';

	head('<script type="text/javascript">
		$(document).on("click",".project-target-item",function() {
			var $this=$(this);
			var $target=$(".project-plan.-i"+$this.data("tgtid"));
			if ($target.is(":visible")) {
				console.log("Visible")
				$target.addClass("hidden").hide();
			} else {
				$target.removeClass("hidden").show();
			}
			console.log("Click "+$this.data("tgtid"));
		});
		</script>');



	// Section :: Script
	if ($isEdit)
		$ret .= '<script type="text/javascript"><!--
		var $map=$("#map_canvas");
		$(document).ready(function() {
			var imgSize = new google.maps.Size(16, 16);
			var gis='.json_encode($gis).';
			var is_point=false;
			$map.gmap({
					center: gis.center,
					zoom: gis.zoom,
					scrollwheel: true
				})
				.bind("init", function(event, map) {
					if (gis.current) {
						marker=gis.current;
						is_point=true;
						$map.gmap("addMarker", {
							position: new google.maps.LatLng(marker.latitude, marker.longitude),
							draggable: true,
						}).click(function() {
							//$map.gmap("openInfoWindow", { "content": "ลากหมุดเพื่อเปลี่ยนตำแหน่ง" }, this);
						}).mouseover(function() {
							//	$map.gmap("openInfoWindow", { "content": "ลากหมุดเพื่อเปลี่ยนตำแหน่ง" }, this);
						}).dragend(function(event) {
							var latLng=event.latLng.lat()+","+event.latLng.lng();
							projectUpdate($("[data-fld=\"location\"]"), latLng);
						});
					}

					if (gis.address) {
						var geocoder = new google.maps.Geocoder();
						var center
						$.each( gis.address, function(i, address) {
							console.log(address);
							geocoder.geocode( { "address": address}, function(results, status) {
								if (status == google.maps.GeocoderStatus.OK) {
									var latlng = results[0].geometry.location;
									$map.gmap("addMarker", {
										position: latlng,
										draggable: false,
										icon: "https://softganz.com/library/img/geo/circle-green.png",
									}).click(function() {
										$map.gmap("openInfoWindow", { "content": address }, this);
									});
									if (!gis.current) {
										center=latlng;
										map.setCenter(center);
									}
								}
							});
						});

									/*
									$map.gmap("addMarker")
										lat: latlng.lat(),
										lng: latlng.lng(),
										icon: "/library/img/geo/circle-green.png",
										infoWindow: {content: address},
									});

									map.setCenter(results[0].geometry.location);
									var marker = new google.maps.Marker({
										map: map,
										position: results[0].geometry.location
									});
									*/

						/*
						geocoder.geocode({
								address: address,
								function(results, status) {
									if (status == "OK") {
										var latlng = results[0].geometry.location;
										map.addMarker({
											lat: latlng.lat(),
											lng: latlng.lng(),
											icon: "https://softganz.com/library/img/geo/circle-green.png",
											infoWindow: {content: address},
										});
										if (!gis.current) map.setCenter(latlng.lat(), latlng.lng());
										notify(address+" , "+latlng.lat()+","+ latlng.lng()+" zoom:"+gis.zoom);
									}
								}
							});
						*/

					}


					$(map).click(function(event, map) {
						if (!is_point) {
							$map.gmap("addMarker", {
								position: event.latLng,
								draggable: true,
								bounds: false
							}, function(map, marker) {
								// After add point
								var latLng=event.latLng.lat()+","+event.latLng.lng();
								projectUpdate($("[data-fld=\"location\"]"), latLng);
							}).dragend(function(event) {
								var latLng=event.latLng.lat()+","+event.latLng.lng();
								projectUpdate($("[data-fld=\"location\"]"), latLng);
							});
							is_point=true;
						}
					});
				});
		});

		$(document).on("click","span[data-fld=\'area\']", function() {
		});

		function projectSumTarget() {
			var total=0
			$("span[data-callback=\'projectSumTarget\']").each(function() {
				total+=parseInt($(this).data("value"))
			});
			$("#targetTotal").text(total)
			projectUpdate($("[data-fld=\"totaltarget\"]"), total.toString());
		}

		function projectSumSupport() {
			var total=0
			$("span[data-callback=\'projectSumSupport\']").each(function() {
				total+=parseInt($(this).data("value"))
			});
			$("#targetSupport").text(total)
		}


		--></script>';
	else
		$ret.='<script type="text/javascript"><!--
		$(document).ready(function() {
			var imgSize = new google.maps.Size(16, 16);
			var gis='.json_encode($gis).';
			var is_point=false;
			$map=$("#map_canvas");
			$map.gmap({
					center: gis.center,
					zoom: gis.zoom,
					scrollwheel: true
				})
				.bind("init", function(event, map) {
					if (gis.current) {
						marker=gis.current;
						is_point=true;
						$map.gmap("addMarker", {
							position: new google.maps.LatLng(marker.latitude, marker.longitude),
							draggable: false,
						}).click(function() {
							$map.gmap("openInfoWindow", { "content": marker.content }, this);
						}).mouseover(function() {
							$map.gmap("openInfoWindow", { "content": marker.content }, this);
						});
					}
				});

				if (gis.address) {
					var geocoder = new google.maps.Geocoder();
					var center
					$.each( gis.address, function(i, address) {
						geocoder.geocode( { "address": address}, function(results, status) {
							if (status == google.maps.GeocoderStatus.OK) {
								var latlng = results[0].geometry.location;
								$map.gmap("addMarker", {
									position: latlng,
									draggable: false,
									icon: "https://softganz.com/library/img/geo/circle-green.png",
								}).click(function() {
									$map.gmap("openInfoWindow", { "content": address }, this);
								});
								if (!gis.current) {
									center=latlng;
									map.setCenter(center);
								}
							}
						});
					});
				}
		});
		--></script>';

	return $ret;
}

function __project_form_main_doc($tpid, $isEdit = false) {
	$isAdmin = user_access('access administrator pages,administer projects');
	$docDb = mydb::select('SELECT f.*, u.`name` poster FROM %topic_files% f LEFT JOIN %users% u USING(`uid`) WHERE `tpid`=:tpid AND `type`="doc" AND `cid`=0 ORDER BY `fid`',':tpid',$tpid);
	if ($docDb->_num_rows) {
		$ret .= '<h3>ไฟล์เอกสาร</h3>';
		$tableDoc = new Table();
		$tableDoc->thead = array('no'=>'ลำดับ','วันที่ส่งเอกสาร','ชื่อเอกสาร','ผู้ส่ง','','icons'=>'&nbsp;&nbsp;');
		$propersalNo = 0;
		foreach ($docDb->items as $item) {
			if ($item->title == "ไฟล์ข้อเสนอโครงการ")
				++$propersalNo;
			$tableDoc->rows[] = array(
				++$no,
				sg_date($item->timestamp,'ว ดด ปปปป'),
				$item->title.($item->title=="ไฟล์ข้อเสนอโครงการ"?' ครั้งที่ '.$propersalNo:'').' (.'.sg_file_extension($item->file).')',
				$item->poster,
				'<a href="'.cfg('url').'upload/forum/'.$item->file.'">ดาวน์โหลด</a>',
				($isEdit && strtotime($item->timestamp)>strtotime('-7 day')) || $isAdmin?'<a href="'.url('paper/info/api/'.$tpid.'/doc.delete/'.$item->fid.'/confirm/yes').'" class="sg-action" data-removeparent="tr" data-rel="this" data-confirm="ต้องการลบไฟล์นี้ กรุณายืนยัน?"><i class="icon -delete -hover"></i></a>':''
			);
		}
		$ret .= $tableDoc->build();
	}

	// Upload document form
	if ($isEdit) {
		$form = new Form('document',url('paper/info/api/'.$tpid.'/doc.upload'),'project-edit-doc');
		$form->addConfig('enctype','multipart/form-data');

		$form->ret->type = 'hidden';
		$form->ret->value = 'paper/'.$tpid;

		$form->title->type = 'select';
		$form->title->label = 'อัพโหลดไฟล์ประกอบโครงการ';
		$form->title->options = array(
			'ไฟล์ข้อเสนอโครงการ' => 'ไฟล์ข้อเสนอโครงการ',
			'ไฟล์โครงการฉบับสมบูรณ์' => 'ไฟล์โครงการฉบับสมบูรณ์',
			'ไฟล์ข้อตกลงดำเนินงาน' => 'ไฟล์ข้อตกลงดำเนินงาน(TOR)',
			/*
			'ไฟล์กรอบแนวคิด'=>'ไฟล์กรอบแนวคิด',
			'ไฟล์รายงานการเงินโครงการประจำงวด 1'=>'ไฟล์รายงานการเงินโครงการประจำงวด 1',
			'ไฟล์รายงานการเงินโครงการประจำงวด 2'=>'ไฟล์รายงานการเงินโครงการประจำงวด 2',
			'ไฟล์รายงานความก้าวหน้าโครงการประจำงวด 1'=>'ไฟล์รายงานความก้าวหน้าโครงการประจำงวด 1',
			'ไฟล์รายงานความก้าวหน้าโครงการประจำงวด 2'=>'ไฟล์รายงานความก้าวหน้าโครงการประจำงวด 2',
			*/
			'ไฟล์รายงานปิดโครงการ-ฉบับสมบูรณ์' => 'ไฟล์รายงานปิดโครงการ-ฉบับสมบูรณ์'
		);

		$form->document->name = 'document';
		$form->document->type = 'file';
		$form->document->size = 50;

		$maxsize = intval(ini_get('post_max_size')) < intval(ini_get('upload_max_filesize')) ? ini_get('post_max_size') : ini_get('upload_max_filesize');

		$form->addField('save',array('type'=>'button','value'=>'<i class="icon -upload -white"></i><span>อัพโหลดไฟล์</span>'));

		$form->submit->description = '<strong>ข้อกำหนดในการส่งไฟล์ไฟล์รายละเอียดโครงการ</strong><ul><li>ไฟล์เอกสารจะต้องเป็นไฟล์ประเภท <strong>.'.implode(' , .',cfg('topic.doc.file_ext')).'</strong> เท่านั้น </li><li>ขนาดไฟล์ต้องไม่เกิน <strong>'.ini_get('upload_max_filesize').'B</strong></li><li>หากไฟล์เอกสารเป็นในรูปแบบอื่น ท่านควรแปลงให้เป็น Acrobat reader (pdf) ให้เรียบร้อยก่อนส่งขึ้นเว็บ</li></ul>';

		$ret .= $form->build();
	}
	return $ret;
}
?>