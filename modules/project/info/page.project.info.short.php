<?php
/**
* Project View
*
* @param Object $self
* @param Int $tpid
* @return String
*/
function project_info_short($self, $tpid = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid);
	$tpid = $projectInfo->tpid;

	if (!$tpid) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

	$self->theme->class .= ' project-status-'.$projectInfo->info->project_statuscode;

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo,'{showPrint: true}');

	$basicInfo = reset(SG\getFirst(project_model::get_tr($tpid, 'info:basic')->items['basic'], []));

	$ret .= '<header class="header -box"><h3>'.$projectInfo->title.'</h3><nav class="nav"><a class="btn" href="'.url('project/'.$tpid).'"><i class="icon -material">pageview</i><span>รายละเอียดโครงการ</span></a></header>';


	$ret .= '<div id="project-info">'._NL;




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

	$tables->rows[] = array(
			'วันที่อนุมัติ',
			view::inlineedit(array('group'=>'project','fld'=>'date_approve','ret'=>'date:ว ดดด ปปปป','value'=>$projectInfo->info->date_approve?sg_date($projectInfo->info->date_approve,'d/m/Y'):''),
			$projectInfo->info->date_approve,
			$isEditDetail,
			'datepicker')
			.($isEdit?' <span class="form-required">*</span>':'')
		);

		//view::inlineedit(array('group'=>'project','fld'=>'date_approve','value'),$projectInfo->info->date_approve,$isEditDetail,'datepicker',$pryearList).' <span class="form-required">*</span>'.'นำไปคำนวนปีงบประมาณ');

	$openYear = SG\getFirst($projectInfo->info->pryear,date('Y'));
	$pryearList = array();
	for ($i = $openYear-1; $i <= date('Y')+1; $i++) {
		$pryearList[$i] = $i + 543;
	}


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




	$ret .= $tables->build()._NL;



	// รายละเอียดโครงการ
	$ret .= '<section id="project-detail-information" class="project-detail-information"><!-- section start -->'._NL;


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
														'callback' => 'refreshContent',
														'redresh-url' => url('project/'.$tpid)
													),
													$rs->catid.':'.$rs->name,
													$isEdit,
													'checkbox')
												.' </label></abbr>';
			} else {
				if ($rs->trid) $optionsIssue[] = $rs->name;
			}
		}

		$ret .= '<p><b>ประเด็น</b> '.implode(' , ', $optionsIssue);
	}

	$ret .= '<h3>สถานการณ์/หลักการและเหตุผล</h3>';
	if ($projectInfo->problem) {
		$ret .= '<ol>';
		foreach ($projectInfo->problem as $rs) $ret .= '<li>'.$rs->problem.' ('.$rs->problemsize.')</li>';
		$ret .= '</ol>';
	}
	if ($basicInfo->text1) $ret .= sg_text2html($basicInfo->text1) . _NL;
	if ($basicInfo->text6) $ret .= sg_text2html($basicInfo->text6) . _NL;


	// Show project objective
	$ret .= '<h3>วัตถุประสงค์/เป้าหมาย</h3>'._NL;
	if ($projectInfo->objective) {
		$ret .= '<ol>';
		foreach ($projectInfo->objective as $rs) $ret .= '<li>'.$rs->title.'</li>';
		$ret .= '</ol>';
	}
	if ($projectInfo->info->objective) $ret .= sg_text2html($projectInfo->info->objective);


	$ret .= '<h3>การดำเนินงาน/กิจกรรม</h3>'._NL;
	if ($projectInfo->activity) {
		$ret .= '<ol>';
		foreach ($projectInfo->activity as $rs) $ret .= '<li>'.$rs->title.'</li>';
		$ret .= '</ol>';
	}


	$ret .= '<div><b>วิธีดำเนินการ</b>'._NL
			. view::inlineedit(
					array('group' => 'project', 'fld' => 'activity', 'ret' => 'html', 'class' => '-fill', 'placeholder' => 'กรณีที่ต้องการบรรยายรายละเอียดวิธีดำเนินการเพิ่มเติม ให้บันทึกไว้ในช่องบรรยายนี้'),
					$projectInfo->info->activity,
					$isEdit,
					'textarea')
			. '</div>'._NL;


	$ret .= '<h3>ผลที่คาดว่าจะได้รับ</h3>';
	$ret .= view::inlineedit(array('group'=>'info:basic','fld'=>'text5', 'tr'=>$basicInfo->trid, 'ret'=>'html','class'=>'-fill'),$basicInfo->text5,$isEdit,'textarea');


	$ret .= '</section><!-- project-detail-information -->'._NL._NL._NL;



	$ret .= '</div><!-- project-info -->'._NL._NL;

	//$ret .= print_o($projectInfo,'$projectInfo');


	return $ret;
}
?>
