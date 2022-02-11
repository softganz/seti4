<?php
/**
* Project View
*
* @param Object $self
* @param Int $tpid
* @return String
*/

import('widget:project.like.status.php');

function project_info_view($self, $tpid) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid);
	$tpid = $projectInfo->tpid;

	if (!$tpid) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

	$fundInfo=R::Model('project.fund.get',$projectInfo->orgid);
	$isLocalFund = $fundInfo->fundid;

	$self->theme->class .= ' project-status-'.$projectInfo->info->project_statuscode;

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo,'{showPrint: true}');

	$basicInfo = reset(SG\getFirst(project_model::get_tr($tpid, 'info:basic')->items['basic'], []));


	$isAdmin = $projectInfo->info->isAdmin;
	$isEdit = $projectInfo->info->isEdit && post('mode') != 'view';
	$isEditDetail = $projectInfo->info->isEditDetail;
	$lockReportDate = $projectInfo->info->lockReportDate;


	R::Model('reaction.add', $tpid, 'TOPIC.VIEW');

	$ret .= (new ScrollView([
		'child' => new ProjectLikeStatusWidget([
			'projectInfo' => $projectInfo,
		]),
	]))->build();

	$ret .= R::View('project.statusbar', $projectInfo)->build();

	// รายละเอียดโครงการ
	$inlineAttr['class'] = 'project-info';
	if ($isEdit) {
		$inlineAttr['class'] .= ' sg-inline-edit';
		$inlineAttr['data-update-url'] = url('project/edit/tr');
		$inlineAttr['data-tpid'] = $tpid;
		$inlineAttr['data-refresh-url'] = url('project/'.$tpid,array('debug'=>post('debug')));
		if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
	}

	$ret .= '<div id="project-info" '.sg_implode_attr($inlineAttr).'>'._NL;




	$tables = new Table();
	$tables->addClass('item__card');
	$tables->colgroup = array('width="30%"','width="70%"');
	$tables->caption = 'รายละเอียดโครงการ';


	$tables->rows[] = array(
			'ชื่อโครงการ/กิจกรรม'.($projectInfo->info->prtype!='โครงการ'?'<br />('.$projectInfo->info->prtype.')':''),
			'<strong>'.view::inlineedit(array('group'=>'topic','fld'=>'title','class'=>'-fill'),$projectInfo->title,$isEditDetail).'</strong>'.($isEdit?'<span class="form-required" style="margin-left:-16px;">*</span>':'')
		);

	$tables->rows[] = array(
			'รหัสโครงการ',
			view::inlineedit(array('group'=>'project','fld'=>'prid'),$projectInfo->info->prid,$isEditDetail)
		);

	/*
	$tables->rows[] = array(
			'เลขที่ข้อตกลง',
			view::inlineedit(array('group'=>'project','fld'=>'agrno'),$projectInfo->info->agrno,$isEdit)
		);
	*/

	$issueStr='';
	$issueStr.='<a class="sg-action" href="'.url('project/issue/'.$tpid.'/add').'" data-rel="parent"><i class="icon -add"></i></a>';

	/*
	$stmt = 'SELECT
						tg.`catid`, tg.`name`
					, tr.`trid`, tr.`refid`
					FROM %tag% tg
						LEFT JOIN %project_tr% tr ON tr.`tpid` = :tpid AND tr.`formid` = "info" AND tr.`part` = "supportplan" AND tr.`refid` = tg.`catid`
					WHERE `taggroup` = "project:planning"';
	*/
	$stmt = 'SELECT a.*, tg.`catid`, tg.`name`
		FROM %tag% tg
			LEFT JOIN
				(
					SELECT
					 tr.`trid`, tr.`refid`
					FROM %project_tr% tr
					WHERE tr.`tpid` = :tpid AND tr.`formid` = "info" AND tr.`part` = "supportplan"
				) a
			ON a.`refid` = tg.`catid`
		WHERE tg.`taggroup` = "project:planning"
		ORDER BY tg.`weight`, tg.`catid`
		';

	$issueDbs = mydb::select($stmt,':tpid',$tpid);
	//$ret .= print_o($issueDbs, '$issueDbs');
	//$ret .= mydb()->_query;

	$optionsIssue = array();

	foreach ($issueDbs->items as $rs) {
		if ($isEdit) {
			$optionsIssue[] = '<abbr class="checkbox -block"><label>'
				. view::inlineedit(
					array(
						'group'=>'info:supportplan:'.$rs->catid,
						'fld'=>'refid',
						'tr'=>$rs->trid,
						'value'=>$rs->refid,
						'removeempty'=>'yes',
						'options' => '{done: "load"}'
					),
					$rs->catid.':'.$rs->name,
					$isEdit,
					'checkbox'
				)
				.' </label></abbr>';
		} else {
			if ($rs->trid) $optionsIssue[]=$rs->name;
		}
	}
	$tables->rows[]=array('ความสอดคล้องกับแผนงาน',$isEdit ? '<div class="project-info-issue">'.implode('', $optionsIssue).'</div>':implode(' , ', $optionsIssue));

	if ($isLocalFund) {
		$supportTypeNameList = model::get_category('project:supporttype','catid');
		$tables->rows[] = array(
			'ประเภทการสนับสนุน',
			view::inlineedit(
				array(
					'group'=>'project',
					'fld'=>'supporttype',
					'class'=>'-fill',
					'value'=>$projectInfo->info->supporttype,
				),
				$supportTypeNameList[$projectInfo->info->supporttype],
				$isEdit,
				'select',
				$supportTypeNameList
			)
			. ($isEdit ? '<span class="form-required" style="margin-left:-16px;">*</span>' : '')
		);

		$supportOrgNameList=model::get_category('project:supportorg','catid');
		$tables->rows[] = array(
			'หน่วยงาน/องค์กร/กลุ่มคน ที่รับผิดชอบโครงการ',
			view::inlineedit(
				array(
					'group'=>'project',
					'fld'=>'supportorg',
					'class'=>'-fill',
					'value' => $projectInfo->info->supportorg,
				),
				$supportOrgNameList[$projectInfo->info->supportorg],
				$isEdit,
				'select',
				$supportOrgNameList
			)
			. ($isEdit ? '<span class="form-required" style="margin-left:-16px;">*</span>' : '')
		);

		/*
		if ($isEdit) {
			$tables->rows[]=array('<td colspan="2">*** ประเภทการสนับสนุน และ หน่วยงาน/องค์กร/กลุ่มคน ที่รับผิดชอบโครงการ (ตามประกาศคณะกรรมการหลักประกันฯ พ.ศ. 2557 ข้อ 7) ***</td>');
		}
		*/
		$tables->rows[] = array(
				'ชื่อองค์กรที่รับผิดชอบ',
				view::inlineedit(
					array(
						'group'=>'project',
						'fld'=>'orgnamedo',
						'class'=>'-fill'
					),
					$projectInfo->info->orgnamedo,
					$isEditDetail
				)
				. ($isEdit ? '<span class="form-required" style="margin-left:-16px;">*</span>' : '')
			);
	}

	$openYear=SG\getFirst($fundInfo->info->openyear,date('Y'));
	$pryearList=array();
	for ($i=$openYear; $i <= date('Y')+1; $i++) {
		$pryearList[$i]=$i+543;
	}

	$tables->rows[]=array(
			'วันที่อนุมัติ',
			view::inlineedit(
				array(
					'group'=>'project',
					'fld'=>'date_approve',
					'ret'=>'date:ว ดดด ปปปป',
					'value'=>$projectInfo->info->date_approve ? sg_date($projectInfo->info->date_approve,'d/m/Y') : ''
				),
				$projectInfo->info->date_approve,
				$isEditDetail,
				'datepicker')
			. ($isEdit?' <span class="form-required">*</span>':'')
		);

	$tables->rows[]=array(
			'ปีงบประมาณ',
			view::inlineedit(
				array(
					'group'=>'project',
					'fld'=>'pryear',
					'value' => $projectInfo->info->pryear,
				),
				$projectInfo->info->pryear+543,
				$isEditDetail,
				'select',
				$pryearList
			)
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

	$tables->rows[]=array(
		'กำหนดวันส่งรายงาน',
		view::inlineedit(array('group'=>'project','fld'=>'date_toreport','ret'=>'date:ว ดดด ปปปป','value'=>$projectInfo->info->date_toreport?sg_date($projectInfo->info->date_toreport,'d/m/Y'):''),
			$projectInfo->info->date_toreport,
			$isEditDetail,
			'datepicker')
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



	$tables->rows[] = array(
			'ละติจูด-ลองจิจูด',
			view::inlineedit(
				array('group'=>'project','fld'=>'location','class'=>'project-info-latlng -fill'),
				($projectInfo->info->location ? $projectInfo->info->lat.','.$projectInfo->info->lnt:''),
				$isEdit
			)
			. '<a class="sg-action project-pin" href="'.url('project/'.$tpid.'/info.map').'" data-rel="box" data-width="640" data-height="640"><i class="icon -pin"></i></a>'
		);








	$ret .= '<section id="project-detail-spec" class="project-detail-spec"><!-- section start -->'._NL;
	$ret .= $tables->build()._NL;
	$ret .= R::PageWidget('project.info.period', [$projectInfo])->build();

	// Select target with mainact
	$targetList=model::get_category('project:target','catid');
	$stmt='SELECT
					  p.`catid` `parentId`, p.`name` `parentName`
					, c.`catid`, c.`name` `targetName`
					, t.`amount`
					FROM %tag% p
						LEFT JOIN %tag% c ON c.`taggroup`="project:target" AND c.`catparent`=p.`catid`
						LEFT JOIN %project_target% t ON t.`tpid`=:tpid AND t.`tgtid`=c.`catid`
					WHERE p.`taggroup`="project:target" AND p.`catparent` IS NULL;
					-- {group:"parentId", key:"catid"}';
	$targetList=mydb::select($stmt,':tpid',$tpid)->items;
	//$ret.=print_o($targetList,'$targetList');

	$stmt='SELECT
				  p.`tgtid`, p.`tpid` `planId`, p.`weight`
				, g.`name` `targetGroup`
				, t.`title` `planName`
				, tp.`parent` `planSelect`
				FROM %project_targetplan% p
					LEFT JOIN %topic% t USING(`tpid`)
					LEFT JOIN %tag% g ON g.`taggroup`="project:target" AND g.`catid`=p.`tgtid`
					LEFT JOIN %topic_parent% tp ON tp.`tpid`=:tpid AND tp.`parent`=p.`tpid` AND tp.`tgtid`=p.`tgtid`
				ORDER BY `tgtid`,`weight`;
				-- {group:"tgtid",key:"planId"}';
	$mainactList=mydb::select($stmt,':tpid',$tpid)->items;

	$targetTables = new Table();
	$targetTables->addClass('-target');
	$targetTables->thead=array('กลุ่มเป้าหมาย','amt'=>'จำนวน(คน)','');

	foreach ($targetList as $targetGroup) {
		$h=reset($targetGroup);

		$targetTables->rows[]=array('<b>'.$h->parentName.'</b>','','');
		foreach ($targetGroup as $key=>$targetItem) {
			if ($isEdit || $targetItem->amount) {
				$targetTables->rows[]=array(
						$targetItem->targetName,
						View::inlineedit(
							array(
								'group'=>'target:'.$targetItem->catid,
								'fld'=>'amount',
								'tagname'=>'info',
								'tr'=>$targetItem->catid,
								'removeempty' => 1,
								'class'=>' -targetgroup -fill',
								'ret'=>'numeric',
							),
							is_null($targetItem->amount) ? '' : $targetItem->amount,
							$isEditDetail
						),
						'<i class="icon -down"></i>',
						'config'=>array(
											'class'=>'project-target-item',
											'data-tgtid'=>$targetItem->catid,
										)
					);
				$mainactStr='<p>กิจกรรมหลักตามกลุ่มเป้าหมาย '.$targetItem->targetName.' :</p>';
				foreach ($mainactList[$key] as $mainactItem) {
					if ($isEdit || $mainactItem->planSelect)$mainactStr.='<p><label>'.view::inlineedit(array('group'=>'parent','fld'=>'planid','tr'=>$targetItem->catid,'value'=>$mainactItem->planSelect,'planid'=>$mainactItem->planId),$mainactItem->planId.':'.$mainactItem->planName,$isEdit,'checkbox').' </label>'.($mainactItem->planName=='อื่นๆ'?' ระบุ ':'').'</p>';
				}
				$targetTables->rows[]=array(
																'<td colspan="3">'.$mainactStr.'</td>',
																'config'=>array('class'=>'project-plan -i'.$targetItem->catid.($targetItem->amount>0?'':' -hidden')));
			}
		}
	}

	$ret .= '<div class="box" style=""><h3>กลุ่มเป้าหมายหลัก</h3>'.($isLocalFund ? '<p><em>(ตามแนบท้ายประกาศคณะอนุกรรมการส่งเสริมสุขภาพและป้องกันโรคฯ พ.ศ. 2557)</em></p>' : '').$targetTables->build().'</div>';
	//,view::inlineedit(array('group'=>'project','fld'=>'totaltarget'),$project->totaltarget,$isEditDetail).' คน');

	$ret .= '</section><!-- project-detail-spec -->';




	// รายละเอียดโครงการ
	$ret .= '<section id="project-detail-information" class="project-detail-information"><!-- section start -->'._NL;

	$ret .= '<h2 class="title -main">ข้อมูลในการดำเนินโครงการ</h2>'._NL;


	$ret .= '<div class="project-info-problem box" id="project-info-problem">';
	$ret .= '<h3>สถานการณ์</h3>';
	$ret .= R::PageWidget('project.info.problem', [$projectInfo])->build();
	$ret .= '<p><b>สถานการณ์/หลักการและเหตุผล'.($isEdit ? ' (บรรยายเพิ่มเติม)':'').'</b></p>'
			. view::inlineedit(
					array('group'=>'info:basic','fld'=>'text1', 'tr'=>$basicInfo->trid, 'ret'=>'html', 'placeholder' => 'บรรยายสถานการณ์/หลักการและเหตุเพิ่มเติมได้ในช่องนี้'),
					$basicInfo->text1,
					$isEdit,
					'textarea'
				)
			. _NL;
	$ret.='</div><!-- project-info-problem -->';


	// Show project objective
	$ret .= '<div id="project-info-objective" class="project-info-objective box">'._NL;
	$ret .= '<h3>วัตถุประสงค์/เป้าหมาย</h3>'._NL;
	$ret .= R::PageWidget('project.info.objective', [$projectInfo])->build();

	if ($projectInfo->info->objective)
		$ret .= '<b>วัตถุประสงค์ของโครงการ (บรรยาย)</b>'
					. view::inlineedit(array('group'=>'project','fld'=>'objective','button'=>'yes','ret'=>'html'),$projectInfo->info->objective,$isEdit,'textarea');

	// น่าจะลบทิ้งได้
	if ($project->objective) $tables->rows[]=array('วัตถุประสงค์ของกิจกรรม/โครงการ',view::inlineedit(array('group'=>'project','fld'=>'objective','button'=>'yes','ret'=>'html'),$project->objective,false,'textarea'));
	$ret .= '</div><!-- project-info-objective -->'._NL._NL;


	// Show project plan
	$activityGroupBy = SG\getFirst($_COOKIE['planby'],'tree');

	$ret .= '<section id="project-plan" class="project-plan box">'._NL;
	$ret .= '<h3>การดำเนินงาน/กิจกรรม</h3>'._NL;
		$ret .= '<div class="sg-tabs">'._NL;
		$ret .= '<ul class="tabs">'._NL;
		$ret .= '<li class="'.($activityGroupBy == 'tree' ? '-active' : '').'"><a class="sg-action" href="'.url('project/'.$tpid.'/info.plan.tree').'" data-rel="replace:#project-plan-list">จำแนกตามกลุ่มกิจกรรม</a></li>';
		$ret .= '<li class="'.(empty($activityGroupBy) || $activityGroupBy == 'time' ? '-active' : '').'"><a class="sg-action" href="'.url('project/'.$tpid.'/info.plan.time').'" data-rel="replace:#project-plan-list">จำแนกตามวันที่</a></li>';
		$ret .= '<li class="'.($activityGroupBy == 'objective' ? '-active' : '').'"><a class="sg-action" href="'.url('project/'.$tpid.'/info.plan.objective').'" data-rel="replace:#project-plan-list">จำแนกตามวัตถุประสงค์</a></li>';
		$ret .= '</ul>'._NL;
		$ret .= R::PageWidget('project.info.plan.'.$activityGroupBy, [$projectInfo])->build()._NL;
		$ret .= '</div><!-- sg-tabs -->';

		$ret .= '<div>'._NL
				. view::inlineedit(
						array('group' => 'project', 'fld' => 'activity', 'ret' => 'html', 'class' => '-fill', 'placeholder' => 'กรณีที่ต้องการบรรยายรายละเอียดวิธีดำเนินการเพิ่มเติม ให้บันทึกไว้ในช่องบรรยายนี้'),
						$projectInfo->info->activity,
						$isEdit,
						'textarea')
				. '</div>'._NL;
	$ret .= '</section><!-- project-plan -->'._NL._NL;



	$ret .= '<h3>ผลที่คาดว่าจะได้รับ</h3>';
	$ret .= '<div class="box">'.view::inlineedit(array('group'=>'info:basic','fld'=>'text5', 'tr'=>$basicInfo->trid, 'ret'=>'html','class'=>'-fill'),$basicInfo->text5,$isEdit,'textarea').'</div>';



	// Show Project Development
	if ($projectInfo->info->ischild) {

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
	$ret.='<p>โครงการเข้าสู่ระบบโดย <a href="'.url('project/list',array('u'=>$projectInfo->info->uid)).'" title="'.htmlspecialchars($projectInfo->info->ownerName).'"><img src="'.model::user_photo($projectInfo->info->username).'" width="32" height="32" alt="'.htmlspecialchars($projectInfo->info->ownerName).'" /> '.$projectInfo->info->ownerName.'</a> เมื่อวันที่ '.sg_date($projectInfo->info->created,'ว ดดด ปปปป H:i').' น.</p>';






	// Section :: Social share
	if (_ON_HOST && in_array($projectInfo->info->type,explode(',',cfg('social.share.type'))) && !is_home() && $projectInfo->property->option->social) {
		$ret .= view::social(url('project/'.$tpid));
	}

	$ret .= '</div><!-- project-info -->'._NL._NL;

	//$ret .= print_o($projectInfo,'$projectInfo');


	$ret .= '<style type="text/css">
	.checkbox.-block {padding:0 8px;}
	</style>';

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
	if ($isEdit) $ret.='<script type="text/javascript"><!--
		$(document).ready(function() {

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
		})
		--></script>';
	return $ret;
}
?>
