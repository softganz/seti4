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
		$inlineAttr['class'] = 'inline-edit';
		$inlineAttr['data-update-url'] = url('project/edit/tr');
		if (post('debug')) $inlineAttr['data-debug'] = 'yes';
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

	$tables->rows[] = array(
											'ชื่อโครงการ/กิจกรรม'.($projectInfo->info->prtype!='โครงการ'?'<br />('.$projectInfo->info->prtype.')':''),
											'<strong>'.view::inlineedit(array('group'=>'topic','fld'=>'title','class'=>'-fill'),$topic->title,$isEditDetail).'</strong>'.($isEdit?'<span class="form-required" style="margin-left:-16px;">*</span>':'')
										);
	if ($projectInfo->info->projectset_name) {
		$tables->rows[] = array(
												'ภายใต้โครงการ',
												'<a href="'.url('paper/'.$projectInfo->info->projectset).'">'.$projectInfo->info->projectset_name.'</a>'
											);
	}

	if (cfg('project.option.argno')) {
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
											'ชื่อองค์กรที่รับผิดชอบ',
											view::inlineedit(array('group'=>'project','fld'=>'orgnamedo','class'=>'-fill'),$projectInfo->info->orgnamedo,$isEditDetail)
										);

	$openYear = SG\getFirst(date('Y'));
	$pryearList = array();
	for ($i = $openYear; $i <= date('Y'); $i++) {
		$pryearList[$i] = $i + 543;
	}

	$tables->rows[] = array(
											'วันที่อนุมัติ',
											view::inlineedit(array('group'=>'project','fld'=>'date_approve','ret'=>'date:ว ดดด ปปปป','value'=>$projectInfo->info->date_approve?sg_date($projectInfo->info->date_approve,'d/m/Y'):''),
											$projectInfo->info->date_approve,
											$isEditDetail,
											'datepicker')
											.($isEdit?' <span class="form-required">*</span>':'')
										);

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



	// ข้อมูลผู้รับผิดชอบโครงการ
	$tables->rows[] = array(
											'ผู้รับผิดชอบโครงการ',
											view::inlineedit(array('group'=>'project','fld'=>'prowner','class'=>'-fill'),$projectInfo->info->prowner,$isEdit)
										);
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
												. '<a href="'.url('project/'.$tpid.'/info.map').'"><i class="icon -pin"></i></a>'
											);

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


	$ret .= '<section id="project-detail-spec" class="project-detail-spec"><!-- section start -->'._NL;
	$ret .= $tables->build()._NL;
	$ret .= $periodStr;
	$ret .= '</section><!-- project-detail-spec -->';





	// รายละเอียดโครงการ
	$ret .= '<section id="project-detail-information" class="project-detail-information"><!-- section start -->'._NL;

	$ret .= '<h2 class="title -main">ข้อมูลในการดำเนินโครงการ</h2>'._NL;

	$stmt = 'SELECT
					tg.`catid`,tg.`name`,tr.`trid`,tr.`refid`, tg.`process`
					FROM %tag% tg
						LEFT JOIN %project_tr% tr ON tr.`tpid` = :tpid AND tr.`formid` = "info" AND tr.`part` = "supportplan" AND tr.`refid` = tg.`catid`
					WHERE tg.`taggroup` = "project:planning" AND tg.`process` IS NOT NULL';
	$issueDbs=mydb::select($stmt,':tpid',$tpid);

	if ($issueDbs->_num_rows) {
		$optionsIssue = array();
		foreach ($issueDbs->items as $rs) {
			if ($isEdit) {
				$optionsIssue[] = '<abbr class="checkbox -block"><label>'
												.view::inlineedit(
													array(
														'group'=>'info:supportplan:'.$rs->catid,
														'fld'=>'refid',
														'tr'=>$rs->trid,
														'value'=>$rs->refid,
														'removeempty'=>'yes',
														'callback' => 'projectDevelopIssueChange',
														'callback-url' => url('paper/'.$tpid)
													),
													$rs->catid.':'.$rs->name,
													$isEdit,
													'checkbox')
												.' </label></abbr>';
			} else {
				if ($rs->trid) $optionsIssue[] = $rs->name;
			}
		}

		$ret .= '<h3>ประเด็นที่เกี่ยวข้อง</h3>';
		$ret .= '<div class="project-info-issue box" id="project-info-issue">';
		//if ($issueDbs->_empty) $ret .= '<div class="-no-print">ยังไม่มีการสร้างรายการความสอดคล้องในระบบ</div>';
		$ret .= ($isEdit ? implode('', $optionsIssue) : implode(' , ', $optionsIssue));
		$ret .= '</div><!-- project-info-issue -->';
	}

	$ret .= '<h3>สถานการณ์</h3>';
	$ret .= '<div class="project-info-problem box" id="projectinfo-problem">';
	$ret .= R::PageWidget('project.info.problem', [$projectInfo])->build();
	$ret .= '<p><b>สถานการณ์/หลักการและเหตุผล (บรรยายเพิ่มเติม)</b></p>'
			. view::inlineedit(array('group'=>'info:basic','fld'=>'text1', 'tr'=>$basicInfo->trid, 'ret'=>'html', 'placeholder' => 'บรรยายสถานการณ์/หลักการและเหตุเพิ่มเติมได้ในช่องนี้'),$basicInfo->text1,$isEdit,'textarea')
			. _NL;
	$ret.='</div><!-- project-info-problem -->';




	$ret .= '<h3>กรอบแนวคิด</h3>';
	$ret .= '<div class="project-info-idea box" id="project-info-idea">';
	$ret .= '<p><b>กรอบแนวคิดและยุทธศาสตร์หลัก</b></p>'
			. view::inlineedit(array('group'=>'info:basic','fld'=>'text6', 'tr'=>$basicInfo->trid, 'ret'=>'html', 'placeholder' => 'บรรยายกรอบแนวคิดและยุทธศาสตร์หลักเพิ่มเติมได้ในช่องนี้'),$basicInfo->text6,$isEdit,'textarea')
			. _NL
			. _NL;
	$ret .= '</div>';

	$ret .= '<section id="project-section-target">';
	$ret .= '<h3>กลุ่มเป้าหมาย.</h3>'._NL;
	$ret .= '<div id="project-target" class="box">'._NL;
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
		$ret .= '<b>วัตถุประสงค์ของโครงการ (บรรยาย)</b>'
					. view::inlineedit(array('group'=>'project','fld'=>'objective','button'=>'yes','ret'=>'html'),$projectInfo->info->objective,$isEdit,'textarea');
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

		$ret .= '<section id="project-detail-childdev" class="project-detail-childdev">';
		$ret .= '<h3>พัฒนาโครงการ</h3>'._NL;
		$ret .= '<div id="develop-child" class="develop-child box">'._NL;
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
		$ret .= '</section>';



		// Show Project Follow
		$stmt = 'SELECT t.`tpid`, t.`title`, p.`prtype`, p.`project_status`, p.`date_approve`, p.`budget`
						FROM %topic% t
							LEFT JOIN %project% p USING(`tpid`)
						WHERE t.`type` = "project" AND t.`parent` = :tpid';
		$dbs = mydb::select($stmt,':tpid',$tpid);

		$ret .= '<section id="project-detail-childproject" class="project-detail-childproject">';
		$ret .= '<h3>โครงการย่อย</h3>'._NL;
		$ret .= '<div id="project-child" class="project-child box">'._NL;
		if ($dbs->_num_rows) {
			$no = 0;
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
			$ret .= $tables->build();
		}


		if ($isEdit && $projectInfo->info->ischild) {
			$ret .= '<nav class="nav -sg-text-right"><a class="btn -primary" href="'.url('project/my/project/new',array('parent'=>$tpid)).'"><i class="icon -addbig -white"></i><span>เพิ่มโครงการย่อย</span></a></nav>'._NL;
		}
		$ret.='</div><!-- project-child -->'._NL._NL;
		$ret .= '<section>';
	}


	$ret .= '<h3>เอกสารประกอบโครงการ</h3>';
	$ret .='<div id="project-docs" class="project-docs box">'._NL
		. R::PageWidget('project.info.docs', [$projectInfo])->build()
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

	return $ret;
}
?>