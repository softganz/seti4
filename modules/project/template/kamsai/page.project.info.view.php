<?php
/**
* Project detail
*
* @param Object $self
* @param Int $projectInfo
* @return String
*/

import('widget:project.like.status.php');

function project_info_view($self, $tpid) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid);

	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;

	$self->theme->class .= ' project-status-'.$projectInfo->info->project_statuscode;

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo,'{showPrint: true}');



	$basicInfo = reset(SG\getFirst(project_model::get_tr($tpid, 'info:basic')->items['basic'], []));

	$isAdmin = $projectInfo->info->isAdmin;
	$isEdit = $projectInfo->info->isEdit && post('mode') != 'view';
	$isEditDetail = $projectInfo->info->isEditDetail;
	$lockReportDate = $projectInfo->info->lockReportDate;

	$showBudget = $projectInfo->is->showBudget;

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
	$tables->colgroup=array('width="30%"','width="70%"');
	$tables->caption='รายละเอียดโครงการ';

	if ($isAdmin) {
		$tables->rows[]=array('เลขที่ข้อตกลง',view::inlineedit(array('group'=>'project','fld'=>'agrno'),$projectInfo->info->agrno,$isAdmin));
		$tables->rows[]=array('รหัสโครงการ',view::inlineedit(array('group'=>'project','fld'=>'prid'),$projectInfo->info->prid,$isAdmin));
		//$tables->rows[]=array('ชุดโครงการ',view::inlineedit(array('group'=>'project','fld'=>'projectset','value'=>$projectInfo->info->projectset),$projectInfo->info->projectset_name,$isAdmin,'select',$projectSets));
	}
	$tables->rows[]=array('ชื่อโรงเรียน','<strong>'.view::inlineedit(array('group'=>'topic','fld'=>'title', 'options'=>'{class: "-fill"}'),$projectInfo->title,$isAdmin).'</strong>');
	$tables->rows[]=array('สังกัด',view::inlineedit(array('group'=>'info:basic','fld'=>'detail1','tr'=>$basicInfo->trid, 'options'=>'{class: "-fill"}'),$basicInfo->detail1,$isEditDetail));

	$tables->rows[] = array(
			'โรงเรียนเครือข่าย',
			($projectInfo->orgid ? '<a class="btn -link" href="'.url('project/knet/'.$projectInfo->orgid).'"><i class="icon -material">view_list</i><span>รายชื่อโรงเรียนเครือข่าย</span></a>' : '').
			($isEdit && empty($projectInfo->orgid) ? '<a class="sg-action btn -link" href="'.url('project/'.$tpid.'/knet.host.create').'" data-title="สร้างโรงเรียนแม่ข่าย" data-confirm="ต้องการสร้างโรงเรียนนี้ให้เป็นโรงเรียนแม่ข่าย กรุณายืนยัน?"><i class="icon -material">playlist_add</i><span>สร้างโรงเรียนแม่ข่าย</span></a>':'')
		);






	$tables->rows[]=array('ระยะเวลาดำเนินโครงการ',view::inlineedit(array('group'=>'project','fld'=>'date_from','ret'=>'date:ว ดดด ปปปป','value'=>$projectInfo->info->date_from?sg_date($projectInfo->info->date_from,'d/m/Y'):''),
		$projectInfo->info->date_from
		,$isEditDetail,'datepicker').' - '.view::inlineedit(array('group'=>'project','fld'=>'date_end','ret'=>'date:ว ดดด ปปปป', 'value'=>$projectInfo->info->date_end?sg_date($projectInfo->info->date_end,'d/m/Y'):''),
		$projectInfo->info->date_end
		,$isEditDetail,'datepicker'));

	if ($showBudget) {
		$tables->rows[]=array('งบประมาณจากโครงการ(สสส.)',view::inlineedit(array('group'=>'project','fld'=>'budget', 'ret'=>'money','callback'=>'projectSumBudget'),$projectInfo->info->budget,$isEditDetail,'money').' บาท');
		$tables->rows[]=array('งบประมาณสมทบจากราชการ',view::inlineedit(array('group'=>'project','fld'=>'budggov', 'ret'=>'money','callback'=>'projectSumBudget'),$projectInfo->info->budggov,$isEditDetail,'money').' บาท');
		$tables->rows[]=array('งบประมาณสมทบจากท้องถิ่น',view::inlineedit(array('group'=>'project','fld'=>'budglocal', 'ret'=>'money','callback'=>'projectSumBudget'),$projectInfo->info->budglocal,$isEditDetail,'money').' บาท');
		$tables->rows[]=array('งบประมาณสมทบจากเอกชน',view::inlineedit(array('group'=>'project','fld'=>'budgprivate', 'ret'=>'money','callback'=>'projectSumBudget'),$projectInfo->info->budgprivate,$isEditDetail,'money').' บาท');
		$tables->rows[]=array('งบประมาณสมทบจากชุมชน',view::inlineedit(array('group'=>'project','fld'=>'budgcommune', 'ret'=>'money','callback'=>'projectSumBudget'),$projectInfo->info->budgcommune,$isEditDetail,'money').' บาท');

		$totalBudget=$projectInfo->info->budget+$projectInfo->info->budggov+$projectInfo->info->budglocal+$projectInfo->info->budgprivate+$projectInfo->info->budgcommune;
		$tables->rows[]=array('รวมงบประมาณทั้งหมด','<strong><span id="budgetTotal">'.number_format($totalBudget,2).'</span></strong> บาท');
	}


	$tables->rows[]=array('ผู้รับผิดชอบโครงการ',view::inlineedit(array('group'=>'project','fld'=>'prowner', 'options'=>'{class: "-fill"}'),$projectInfo->info->prowner,$isEdit));
	$tables->rows[]=array('ผู้ร่วมรับผิดชอบโครงการ 1',view::inlineedit(array('group'=>'project','fld'=>'prcoowner1', 'options'=>'{class: "-fill"}'),$projectInfo->info->prcoowner1,$isEdit));
	$tables->rows[]=array('ผู้ร่วมรับผิดชอบโครงการ 2',view::inlineedit(array('group'=>'project','fld'=>'prcoowner2', 'options'=>'{class: "-fill"}'),$projectInfo->info->prcoowner2,$isEdit));
	$tables->rows[]=array('ผู้ร่วมรับผิดชอบโครงการ 3',view::inlineedit(array('group'=>'project','fld'=>'prcoowner3', 'options'=>'{class: "-fill"}'),$projectInfo->info->prcoowner3,$isEdit));

	$tables->rows[]=array('ที่ปรึกษาโครงการ 1',view::inlineedit(array('group'=>'project','fld'=>'prtrainer', 'options'=>'{class: "-fill"}'),$projectInfo->info->prtrainer,$isEdit));
	$tables->rows[]=array('ที่ปรึกษาโครงการ 2',view::inlineedit(array('group'=>'project','fld'=>'prcotrainer1', 'options'=>'{class: "-fill"}'),$projectInfo->info->prcotrainer1,$isEdit));
	$tables->rows[]=array('ที่ปรึกษาโครงการ 3',view::inlineedit(array('group'=>'project','fld'=>'prcotrainer2', 'options'=>'{class: "-fill"}'),$projectInfo->info->prcotrainer2,$isEdit));

	$tables->rows[]=array('ผู้ประสานงานภาค',view::inlineedit(array('group'=>'project','fld'=>'prcoordinatorsector', 'options'=>'{class: "-fill"}'),$projectInfo->info->prcoordinatorsector,$isEdit));





	$tables->rows[]=array('หลักการและเหตุผล',view::inlineedit(array('group'=>'info:basic','fld'=>'text1', 'tr'=>$basicInfo->trid, 'ret'=>'html'),$basicInfo->text1,$isEdit,'textarea'));
	$tables->rows[]=array('กรอบแนวคิด',view::inlineedit(array('group'=>'info:basic','fld'=>'text6', 'tr'=>$basicInfo->trid, 'ret'=>'html'),$basicInfo->text6,$isEdit,'textarea'));





	// กลุ่มเป้าหมาย
	$targetTables = new Table();
	$targetTables->thead = ['กลุ่มเป้าหมาย', 'amt' => 'จำนวน(คน)'];
	$targetTables->rows[] = ['<td class="subheader" colspan="2"><strong>กลุ่มเป้าหมายหลัก</strong>'];
	$totalTarget = 0;
	foreach (cfg('project.target') as $key => $value) {
		$targetTables->rows[] = [
			$value,
			view::inlineedit(
				[
					'group' => 'project',
					'fld' => $key,
					'value' => $projectInfo->info->{$key},
					'callback' => 'projectSumTarget'
				],
				$projectInfo->info->{$key},
				$isEditDetail
			)
		];
		$totalTarget += $projectInfo->info->{$key};
	}

	$targetTables->rows[] = [
		'รวมกลุ่มเป้าหมายหลัก'
		,'<span style="display:block;text-align:center;" align="center" id="targetTotal">'.$totalTarget.'</span>'
		. '<span class="-hidden">'
		. view::inlineedit(
			[
				'group' => 'project',
				'options' => ['id' => 'total-target'],
				'fld' => 'totaltarget',
				'value' => $projectInfo->info->totaltarget
			],
			$totalTarget,
			$isEditDetail
		)
		. '</span>'
	];

	$targetTables->rows[] = ['<td class="subheader" colspan="2"><strong>ผู้มีส่วนร่วม/ผู้สนับสนุน</strong>'];

	foreach (cfg('project.support') as $key => $value) {
		$targetTables->rows[] = [
			$value,
			view::inlineedit(
				[
					'group' => 'project',
					'fld' => $key,
					'value' => $projectInfo->info->{$key},
					'callback' => 'projectSumSupport'
				],
				$projectInfo->info->{$key},
				$isEditDetail
			)
		];
		$totalTargetSupport += $projectInfo->info->{$key};
	}
	$targetTables->rows[] = [
		'รวมผู้มีส่วนร่วม/ผู้สนับสนุน',
		'<span style="display:block;text-align:center;" align="center" id="targetSupport">'.$totalTargetSupport.'</span>'
	];
	$tables->rows[] = ['<td colspan="2">'.$targetTables->build().'</td>'];



	$tables->rows[] = [
		'ผลที่คาดว่าจะได้รับ',
		view::inlineedit(
			[
				'group' => 'info:basic',
				'fld' => 'text5',
				'tr' => $basicInfo->trid,
				'ret' => 'html'
			],
			$basicInfo->text5,
			$isEdit,
			'textarea'
		)
	];



	// เปลี่ยนเป็น "ตัวชี้วัดผลสำเร็จ" => ตรวจสอบว่าตัวชี้วัดใช้ฟิลด์อะไร
	$tables->rows[]=array('เป้าหมายภาวะโภชนาการ',view::inlineedit(array('group'=>'info:basic','fld'=>'text4', 'tr'=>$basicInfo->trid, 'ret'=>'html'),$basicInfo->text4,$isEdit,'textarea'));
	$tables->rows[]=array('ความต่อเนื่องยั่งยืนหรือแนวทางการขยายผล',view::inlineedit(array('group'=>'info:basic','fld'=>'text8', 'tr'=>$basicInfo->trid, 'ret'=>'html'),$basicInfo->text8,$isEdit,'textarea'));



	$tables->rows[] = array(
			'พื้นที่ตั้งโรงเรียน',
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

	$gis['address']=array();
	$gis['address'][]=$projectInfo->info->area;

	//$ret.=print_o($provList,'$provList');




	//$tables->rows[]=array('พื้นที่ตั้งโรงเรียน',view::inlineedit(array('group'=>'project','fld'=>'area'),$project->area,$isEditDetail));
	//$tables->rows[]=array('จังหวัด',$projectInfo->info->provname);

	/*
	$provStr=R::Page('project.form.addprov',$this,$topic);
	if ($isEdit) {
		$provStr.='<a class="sg-action" data-rel="#project-addprovlink" href="'.url('project/form/'.$tpid.'/addprov',array('form'=>'show')).'" title="เพิ่มพื้นที่ตั้งโรงเรียน">+</a><div id="project-addprovlink"></div>';
	}
	$tables->rows[]=array('พื้นที่ตั้งโรงเรียน'.($isEdit?'<span class="require">!</span>':''),$provStr);
	*/

	$tables->rows[] = array(
		'ละติจูด-ลองจิจูด',
		view::inlineedit(
			array('group'=>'project','fld'=>'location'),
			($projectInfo->info->location?$projectInfo->info->lat.','.$projectInfo->info->lnt:''),
			$isEdit
		)
		. '<a class="sg-action" href="'.url('project/'.$tpid.'/info.map').'" data-rel="box" data-width="640"><i class="icon -pin"></i></a>'
	);


	//$tables->rows[]=array('ผู้ดำเนินการติดตามสนับสนุนโครงการ',$name);

	if ($projectInfo->info->objective) $tables->rows[]=array('วัตถุประสงค์ของกิจกรรม/โครงการ',view::inlineedit(array('group'=>'project','fld'=>'objective','button'=>'yes','ret'=>'html'),$projectInfo->info->objective,false,'textarea'));
	if ($projectInfo->info->activity) $tables->rows[]=array('กิจกรรมหลัก',view::inlineedit(array('group'=>'project','fld'=>'activity','button'=>'yes','ret'=>'html'),$projectInfo->info->activity,false,'textarea'));

	if (cfg('project.show.detail')) $tables->rows[]=array('รายละเอียดโครงการ',$projectInfo->info->body);






	$ret .= $tables->build();

	if ($showBudget) {
		$ret .= R::PageWidget('project.info.period', [$projectInfo])->build();
	}




	// Show project objective
	$ret.='<h3>วัตถุประสงค์/เป้าหมาย</h3>';
	$ret.=R::Page(
						'project.info.objective',
						$self,
						$projectInfo
						);

	//if (empty($info->objective)) $ret.='<p class="notify">ยังไม่มีการกำหนดวัตถุประสงค์ของโครงการ</p>';


	// Show project plan
	$ret .= '<h3>กิจกรรมหลัก</h3>';
	$ret .= R::Page(
		'project.plan',
		$self,
		$tpid,
		NULL,
		NULL,
		NULL,
		$info,
		"{isEdit:$isEdit, isEditDetail:$isEditDetail}"
	);

	// Show project calendar
	$activityGroupBy=SG\getFirst(post('gr'),$_COOKIE['maingrby'],'act');

	$ret .= '<h3>กิจกรรมย่อย</h3>';
	$ret .= '<div class="sg-tabs">'._NL;
	$ret.='<ul class="tabs">'._NL;
	$ret.='<li class="'.(empty($activityGroupBy) || $activityGroupBy=='act'?'active':'').'"><a href="'.url('project/calendar/'.$tpid,array('gr'=>'act')).'">เรียงลำดับตามเวลา</a></li>';
	$ret.='<li class="'.($activityGroupBy=='plan'?'active':'').'"><a href="'.url('project/calendar/'.$tpid,array('gr'=>'plan')).'">จำแนกตามกิจกรรมหลัก</a></li>';
	$ret.='<li class="'.($activityGroupBy=='obj'?'active':'').'"><a href="'.url('project/calendar/'.$tpid,array('gr'=>'obj')).'">จำแนกตามวัตถุประสงค์</a></li>';
	$ret.='<li class="'.($activityGroupBy=='guide'?'active':'').'"><a href="'.url('project/calendar/'.$tpid,array('gr'=>'guide')).'">จำแนกตาม 8 แนวทางการดำเนินงาน</a></li>';
	$ret.='</ul>'._NL;
	$ret.='<div id="project-calendar-wrapper">';
	$ret .= R::Page(
		'project.calendar',
		$self,
		$tpid,
		NULL,
		NULL,
		NULL,
		$info,
		"{isEdit:$isEdit, isEditDetail:$isEditDetail}"
	);
	$ret.='</div><!-- project-calendar-wrapper -->'._NL;

	$ret .= '</div><!-- sg-tabs -->'._NL;



	//$ret .= print_o($projectInfo, '$projectInfo');

	// Section :: วัตถุประสงค์ของโครงการ /กิจกรรม / การดำเนินงาน



	$ret.='<h4>ไฟล์เอกสาร</h4>';
	$ret .= R::PageWidget('project.info.docs', [$projectInfo])->build();

	// Section :: Project Creator
	$ret.='<p>โครงการเข้าสู่ระบบโดย <a href="'.url('project/list',array('u'=>$projectInfo->info->uid)).'" title="'.htmlspecialchars($projectInfo->info->ownerName).'"><img src="'.model::user_photo($projectInfo->info->username).'" width="32" height="32" alt="'.htmlspecialchars($projectInfo->info->ownerName).'" /> '.$projectInfo->info->ownerName.'</a> เมื่อวันที่ '.sg_date($projectInfo->info->created,'ว ดดด ปปปป H:i').' น.</p>';
	$ret.='</div>'._NL;





	// Section :: Social share
	if (_ON_HOST && in_array($projectInfo->info->type,explode(',',cfg('social.share.type'))) && !is_home() && $projectInfo->info->property->option->social) {
		$ret.=view::social(url('paper/'.$tpid));
	}



	$ret.='</div><!--detail-->'._NL;


	$ret.='</div><!--sg-tabs-->'._NL;


	unset($body->comment);

	// Section :: Script
	if ($isEdit) $ret .= '<script type="text/javascript">
		function projectSumTarget($this, data) {
			let total = 0
			let $totalTarget = $("#total-target")
			$("span[data-callback=\'projectSumTarget\']").each(function() {
				total += parseInt($(this).data("value"))
			})
			$("#targetTotal").text(total)
			$totalTarget.sgInlineEdit().update($totalTarget, total.toString())
		}

		function projectSumSupport() {
			let total = 0
			$("span[data-callback=\'projectSumSupport\']").each(function() {
				total += parseInt($(this).data("value"))
			});
			$("#targetSupport").text(total)
		}

		function projectSumBudget() {
			let total = 0
			$("span[data-callback=\'projectSumBudget\']").each(function() {
				total+=parseInt($(this).data("value"))
			});
			$("#budgetTotal").text(total)
		}
		</script>';
	return $ret;
}

?>