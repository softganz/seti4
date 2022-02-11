<?php
/**
* Add calendar to main activity
*
* @param Object $projectInfo
* @param Object $data
* @param JSON String/Object $options
* @return String
*/
function view_project_calendar_form($projectInfo, $data = NULL, $options = '{}') {
	$tpid = $projectInfo->tpid;
	$formType = SG\getFirst($data->formType,'detail');
	$options = options('project');

	$ret .= '<header class="header -box -hidden"><h3>รายละเอียดกิจกรรม<h3></header>';

	//$ret .= print_o($data,'$data');
	//$ret.=print_o($projectInfo,'$projectInfo');

	if ($formType != 'short') {
		$ret .= '<h3 class="title">'.$mainact->title.'</h3>';
		$ret .= '<h5 class="title -box">'.($data?'แก้ไข':'เพิ่ม').'กิจกรรมย่อย</h5>';
		$ret .= '<div class="box--mainbar--no">'._NL;
	}

	if (is_array($data)) $data = (object) $data;
	if (empty($data->tpid)) $data->tpid = $tpid;

	// Set default value from main activity
	//if (empty($data->mainact)) $data->mainact=$actid;
	if (empty($data->title)) $data->title = $mainact->title;
	if (empty($data->objective)) $data->objective = $mainact->objectiveTitle;
	if (empty($data->detail)) $data->detail = $mainact->desc;
	if (empty($data->budget)) {
		$data->budget = $mainact->budget - $mainact->totalBudget;
		if ($data->budget < 0) $data->budget = 0;
	}
	if (empty($data->targetpreset)) $data->targetpreset = number_format($mainact->target);

	// Set default value from current date
	if (empty($data->from_date)) $data->from_date = date('j/n/Y');
	if (empty($data->to_date)) $data->to_date = $data->from_date;
	if (empty($data->from_time)) $data->from_time = '09:00';
	if (empty($data->to_time)) {
		list($hr,$min) = explode(':',$data->from_time);
		$data->to_time = sprintf('%02d',$hr+1).':'.$min;
	}
	if (empty($data->privacy)) $data->privacy = 'public';

	list(,$month,$year) = explode('/',$data->from_date);





	$form = new Form('calendar',url('project/calendar/'.$tpid.'/save'),'edit-calendar','sg-form');
	$form->addData('checkValid',true);

	if (_AJAX) {
		$form->addData('rel','refresh');
		$form->addData('complete',$formType == 'short' ? 'closebox' : 'closebox');
	}


	if ($data->id) $form->addField('calid',array('type'=>'hidden','value'=>$data->calid));
	if ($data->tpid) $form->addField('tpid',array('type'=>'hidden','value'=>$data->tpid));
	if ($data->activityId) $form->addField('activityid',array('type'=>'hidden','value'=>$data->activityId));

	$form->addField('type',array('type'=>'hidden','name'=>'type','value'=>$formType));
	$form->addField('privacy',array('type'=>'hidden','value'=>'public'));
	$form->addField('calowner',array('type'=>'hidden','value'=>1));

	$form->addField(
					'title',
					array(
						'type'=>'text',
						'label'=>'ทำอะไร',
						'class'=>'-fill',
						'maxlength'=>255,
						'require'=>true,
						'placeholder'=>'ระบุชื่อกิจกรรม',
						'value'=>htmlspecialchars($data->title)
					)
				);

	mydb::where('c.`tpid` = :tpid AND a.`parent` IS NULL', ':tpid', $tpid);
	if ($data->calid) mydb::where('c.`id` != :calid', ':calid', $data->calid);
	$stmt = 'SELECT a.`trid` `activityId`, `title` FROM %calendar% c LEFT JOIN %project_tr% a ON a.`tpid` = c.`tpid` AND a.`calid` = c.`id` AND a.`formid` = "info" AND a.`part` = "activity" %WHERE%';
	$dbs = mydb::select($stmt);
	//$ret .= print_o($dbs);

	$optionMainAct = array(''=>'== กิจกรรมระดับบนสุด ==');
	foreach ($dbs->items as $rs) $optionMainAct[$rs->activityId] = $rs->title;

	$form->addField(
		'parent',
		array(
			'type' => 'select',
			'label' => 'ภายใต้กิจกรรม:',
			'class' => '-fill',
			'value' => $data->parent,
			'options' => $optionMainAct,
		)
	);

	for ($hr=7;$hr<24;$hr++) {
		for ($min=0;$min<60;$min+=30) {
			$times[]=sprintf('%02d',$hr).':'.sprintf('%02d',$min);
		}
	}

	$minDate = sg_date(SG\getFirst($projectInfo->info->date_from,date('Y-m-d')),'j/n/Y');
	$maxDate = sg_date(SG\getFirst($projectInfo->info->date_end,date('Y-m-d')),'j/n/Y');
	$form->date->type='textfield';
	$form->date->label=sg_client_convert('เมื่อไหร่');
	$form->date->require=true;
	$form->date->value='<input type="text" name="calendar[from_date]" id="edit-calendar-from_date" maxlength="10" class="sg-datepicker form-text require -date" style="width:6em;" value="'.htmlspecialchars(sg_date($data->from_date,'d/m/Y')).'" data-min-date="'.$minDate.'" data-max-date="'.$maxDate.'" data-diff="edit-calendar-to_date"> <select class="form-select" name="calendar[from_time]" id="edit-calendar-from_time">';
	foreach ($times as $time) $form->date->value.='<option value="'.$time.'"'.($time==$data->from_time?' selected="selected"':'').'>'.$time.'</option>';
	$form->date->value.='</select>
	ถึง <select class="form-select" name="calendar[to_time]" id="edit-calendar-to_time">';
	foreach ($times as $time) $form->date->value.='<option value="'.$time.'"'.($time==$data->to_time?' selected="selected"':'').'>'.$time.'</option>';
	$form->date->value.='</select>
	<input type="text" name="calendar[to_date]" id="edit-calendar-to_date" maxlength="10" class="sg-datepicker form-text require -date" style="width:6em;" value="'.htmlspecialchars(sg_date($data->to_date,'d/m/Y')).'" data-min-date="'.sg_date($projectInfo->info->date_from,'j/n/Y').'" data-max-date="'.sg_date($projectInfo->info->date_end,'j/n/Y').'">';
	$form->date->value .= ' ('.$minDate.' - '.$maxDate.')';

	$form->addField('areacode',array('type'=>'hidden','value'=>$data->areacode));
	$form->addField('latlng',array('type'=>'hidden','value'=>$data->latlng));


	$form->addField(
					'location',
					array(
						'type'=>$formType=='short'?'hidden':'text',
						'label'=>'ที่ไหน',
						'maxlength'=>255,
						'placeholder'=>'ระบุสถานที่ หมู่ที่ ตำบล',
						'value'=>htmlspecialchars($data->location),
						'class'=>"sg-address -fill",
						'attr'=>'data-altfld="edit-calendar-areacode"'
					)
				);


	if ($options->multipleTarget) {
		$joinListTable=new table('item -table');
		$joinListTable->thead=array('กลุ่มเป้าหมาย','amt'=>'จำนวนคน');
		$joinListTable->rows[]=array('<td class="subheader" colspan="2">กลุ่มเป้าหมายที่เข้าร่วม');
		foreach (cfg('project.target') as $key => $value) {
			$joinListTable->rows[]=array($value,'<input class="form-text -numeric" type="text" name="calendar['.$key.']" size="5" value="'.$data->{'targt_'.$key}.'" /> คน');
		}

		$joinListTable->rows[]=array('<td class="subheader" colspan="2">ผู้มีส่วนร่วม/ผู้สนับสนุน');
		foreach (cfg('project.support') as $key => $value) {
			$joinListTable->rows[]=array($value,'<input class="form-text -numeric" type="text" name="calendar['.$key.']" size="5" value="'.$data->{'targt_'.$key}.'" /> คน');
		}

		$form->addField(
			'joinlist',
			[
				'label' => 'กลุ่มเป้าหมาย/ผู้มีส่วนร่วม/ผู้สนับสนุนที่เข้าร่วมกิจกรรม',
				'type' => 'textfield'
				'value' => $joinListTable->build(),
			]
		);
	} else {
		$form->addField(
						'targetpreset',
						array(
							'type'=>'text',
							'label'=>'กลุ่มเป้าหมาย (คน)',
							'maxlength'=>5,
							'class'=>'-numeric',
							'placeholder'=>'0',
							'value'=>number_format($data->targetpreset,0,'','')
						)
					);

		$form->addField('targetdetail',
						array(
							'type' => 'textarea',
							'label' => 'รายละเอียดกลุ่มเป้าหมาย',
							'class' => '-fill',
							'rows' => 3,
							'value' => $data->target,
						)
					);
	}



	if ($options->multipleBudget) {
		$stmt='SELECT `tpid`,`trid`,`parent`,`gallery` `expcode`,`num1` `amt`, `num2` `unitprice`, `num3` `times`, `num4` `total`,`detail1` `unitname`, `text1` `detail`
					FROM %project_tr%
					WHERE `tpid`=:tpid AND `formid`="develop" AND `part`="exptr" AND `calid`=:calid ';
		foreach (mydb::select($stmt,':tpid',$tpid,':calid',$data->calid)->items as $item) $expTr[$item->expcode]=$item;

		$expCategoty=model::get_category('project:expcode');
		$tables = new Table();
		$tables->thead=array('ประเภทรายจ่าย/รายละเอียด','รวมเงิน (บาท)');
		foreach ($expCategoty as $expCode=>$expName) {
			$exp=$expTr[$expCode];
			//$ret.=print_o($exp,'$exp');
			$tables->rows[]=array(
					'<input type="hidden" name="exp['.$expCode.'][expid]" value="'.$exp->trid.'" type="hidden" />'
					.'<b>'.$expName.' :</b><br />'
					.'<textarea name="exp['.$expCode.'][detail]" rows="2" cols="50" placeholder="ระบุรายละเอียดค่าใช้จ่าย" style="width:95%;">'.htmlspecialchars($exp->detail).'</textarea>',
					//.'<input type="text" name="exp['.$expCode.'][detail]" value="'.htmlspecialchars($exp->detail).'" size="50" placeholder="ระบุรายละเอียดค่าใช้จ่าย" style="width:95%;" />',
					'<br /><input type="text" name="exp['.$expCode.'][total]" size="10" value="'.$exp->total.'" style="text-align:center; margin:0 auto; display:block;" />',
					);
		}
		$tables->tfoot[]=array('รวมงบประมาณที่ตั้งไว้',$data->budget);
		$form->addText($tables->build());
	} else {
		$form->addField(
				'budget',
				array(
					'type'=>'text',
					'label'=>'งบประมาณที่ตั้งไว้ (บาท)',
					'maxlength'=>11,
					'class'=>'-money',
					'placeholder'=>'0.00',
					'value'=>number_format($data->budget,2,'.','')
				)
			);
	}

	$form->addField(
			'detail',
			array(
				'type'=>$formType=='short'?'hidden':'textarea',
				'label'=>'รายละเอียดกิจกรรมตามแผน',
				'rows'=>5,
				'class'=>'-fill',
				'placeholder'=>'ระบุรายละเอียดของกิจกรรมที่วางแผนว่าจะทำ',
				'value'=>$data->detail
			)
		);

	/*
	$form->addField(
					'outputoutcome',
					array(
						'type'=>$formType=='short'?'hidden':'textarea',
						'label'=>'ผลการดำเนินงานตามแผน',
						'rows'=>3,
						'class'=>'-fill',
						'placeholder'=>'ระบุรายละเอียดขผลการดำเนินงานตามแผน',
						'value'=>$data->detail
					)
				);
*/


	$form->addField(
		'color',
		[
			'type' => 'colorpicker',
			'label' => 'สีของกิจกรรม',
			'color' => 'Red, Green, Blue, Black, Purple, Aquamarine, Aqua, Chartreuse,Coral, DarkGoldenRod, Olive, Teal, HotPink, Brown',
			'value' => $data->color,
		]
	);

	$form->addField(
			'save',
			array(
				'type' => 'button',
				'value' => '<i class="icon -save -white"></i>{tr:SAVE}',
				'pretext' => ($data->calid && user_access(false) ? '<a class="sg-action btn -link" href="'.url('project/calendar/'.$tpid.'/edit/'.$data->calid).'" data-rel="box"><i class="icon -refresh -gray"></i><span>Refresh</span></a>' : '').'<a class="sg-action btn -link -cancel" data-rel="close" href="javascript:voud(0)""><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
				'container' => array('class'=>'-sg-text-right')
			)
		);

	if ($para->module) 	$form=do_class_method($para->module.'.extension','calendar_form', $form, $data, $para);


	$ret .= $form->build();


	//$ret.=print_o($data,'$data').print_o($mainact,'mainact');
	//$ret.=print_o($project,'$project');

	if ($formType != 'short') {
		$ret .= '</div>';

		//$ret.='<div class="box--sidebar">';
		//$ret.=__project_mainact_listcalendar($tpid,$actid,$project);
		//$ret.=__project_mainact_detail($tpid,$actid);
		//$ret.='</div>';
	}

	$ret .= '<script type="text/javascript">
	var from=$("#edit-calendar-from_date").val().split("/");
	var to=$("#edit-calendar-to_date").val().split("/");
	var fromDate=new Date(from[2],from[1]-1,from[0]);
	var toDate=new Date(to[2],to[1]-1,to[0]);

	var minutes = 1000*60;
	var hours = minutes*60;
	var days = hours*24;

	var diff_date = Math.round((toDate - fromDate)/days);
	//console.log("diff_date="+diff_date)


	$("#edit-calendar-from_date").change(function() {
		var from=$(this).val().split("/");
		toDate=new Date(from[2],from[1]-1,from[0]);
		toDate.setDate(toDate.getDate()+diff_date);
		$("#edit-calendar-to_date").val($.datepicker.formatDate("dd/mm/yy",toDate));
		//console.log("from date change")
	});
	$("#edit-calendar-to_date").change(function() {
		from=$("#edit-calendar-from_date").val().split("/");
		to=$("#edit-calendar-to_date").val().split("/");
		fromDate=new Date(from[2],from[1]-1,from[0]);
		toDate=new Date(to[2],to[1]-1,to[0]);
		diff_date = Math.round((toDate - fromDate)/days);
	});

  setTimeout(function() { $("#edit-calendar-title").focus() }, 500);
	</script>';
	return $ret;
}
?>