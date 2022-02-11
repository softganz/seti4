<?php
/**
* Project :: Calendar Information
*
* @param Object $self
* @param Integer $tpid
* @param String $action
* @param Integer $actid
* @param $topic Object
* @param $info Array
* @param $options JSON
* @param
* @return String
*/
function project_calendar($self,$tpid=NULL,$action=NULL,$actid=NULL,$topic=NULL,$info=NULL,$options=NULL) {

	if (!is_object($topic)) {
		$topic=project_model::get_topic($tpid);
		$info=project_model::get_info($tpid);
		$options=NULL;
	} else if (is_object($topic)) {
		$options=sg_json_decode($options);
	}

	$projectInfo = R::Model('project.get',$tpid);

	$showBudget = $projectInfo->is->showBudget;

	//$ret .= print_o($topic,'$topic');
	//$ret .= print_o($info,'$info');

	//return print_o($topic);

	if ($topic->type!='project') return $ret.message('error','This is not a project');

	$action=SG\getFirst($action,post('act'));
	$isEdit=$topic->project->isEdit;
	$isEditDetail=$info->project->isEditDetail;

	$project=$info->project;

	if (post('gr')) {
		setcookie('maingrby',post('gr'),time()+10*365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));
	}
	$activityGroupBy=SG\getFirst(post('gr'),$_COOKIE['maingrby'],'act');

	switch ($action) {

		case 'add' :
			if ($isEdit) $ret.=__project_calendar_add($tpid,$actid,$data,$project);
			return $ret;
			break;

		case 'edit':
			if ($isEdit && $actid) {
				$stmt='SELECT * FROM %calendar% c LEFT JOIN %project_activity% a ON a.`calid`=c.`id` WHERE c.`id`=:calid AND c.`tpid`=:tpid LIMIT 1';
				$data=mydb::select($stmt,':calid',$actid,':tpid',$tpid);
				$data->from_date=sg_date($data->from_date,'d/m/Y');
				$data->to_date=sg_date($data->to_date,'d/m/Y');
				$data->from_time=substr($data->from_time,0,5);
				$data->to_time=substr($data->to_time,0,5);
				$data->color=property('calendar:color:'.$data->id);
				$ret.=__project_calendar_add($tpid,$actid,$data,$project);
			}
			return $ret;
			break;

		case 'remove' :
			if ($isEdit && $actid) {
				$calendarTitle=mydb::select('SELECT `title` FROM %calendar% WHERE `id`=:id LIMIT 1',':id',$actid)->title;
				$ret.='Remove calendar '.$calendarTitle;
				mydb::query('DELETE FROM %calendar% WHERE `id`=:calid LIMIT 1',':calid',$actid);
				mydb::query('DELETE FROM %project_activity% WHERE `calid`=:calid LIMIT 1',':calid',$actid);
				mydb::query('DELETE FROM %project_actguide% WHERE `calid`=:calid',':calid',$actid);
				// Add log
				model::watch_log('project','Calendar remove','ลบกิจกรรมย่อย '.$actid.' กิจกรรมหลัก '.$actid.' : ' .$calendarTitle,NULL,$tpid);
			}
			//$ret.=__project_mainact_info($tpid,$actid,NULL,$project);
			return $ret;
			break;

		case 'info':
			$ret.=__project_calendar_info($tpid,$actid,$info);
			return $ret;
			break;

		default:
			if ($action) {
				$ret.='<p class="notify">ไม่มีเงื่อนไขตามระบุ</p>';
				return $ret;
			}
			break;
	}


	if ($activityGroupBy=='act') $ret.=__project_calendar_list_act($tpid, $projectInfo,$info,$isEdit);
	else if ($activityGroupBy=='obj') $ret.=__project_calendar_list_obj($tpid, $projectInfo,$info,$isEdit);
	else if ($activityGroupBy=='plan') $ret.=__project_calendar_list_plan($tpid, $projectInfo,$info,$isEdit);
	else if ($activityGroupBy=='guide') $ret.=__project_calendar_list_guide($tpid, $projectInfo,$info,$isEdit);

	//$ret.=print_o($topic,'$topic');
	//$ret.=print_o($mainact,'$mainact');
	//$ret.=print_o($info,'$info');
	//$ret.=print_o($options,'$options');
	return $ret;
}



function __project_calendar_list_act($tpid, $projectInfo,$info,$isEdit) {
	$calendarList=project_model::get_calendar($tpid,$period,$owner="owner");
	$showBudget = $projectInfo->is->showBudget;

	$tables = new Table();
	$tables=new table('item project-mainact-items');
	$tables->thead=array(
		'date no'=>'วันที่',
		'title'=>'ชื่อกิจกรรม',
		'amt target'=>'กลุ่มเป้าหมาย (คน)',
		'amt budget'=>'งบกิจกรรม (บาท)',
		'amt done'=>'ทำแล้ว',
		'amt expend'=>'ใช้จ่ายแล้ว (บาท)',
		'',
	);

	foreach ($calendarList->items as $crs) {
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
					$ui->add('<a class="sg-action" href="'.url('project/calendar/'.$tpid.'/remove/'.$crs->id).'" data-rel="#project-calendar-wrapper" data-ret="'.url('project/calendar/'.$tpid).'" data-confirm="ลบกิจกรรมย่อย กรุณายืนยัน" title="ลบกิจกรรม"><i class="icon -delete -showtext"></i><span>ลบกิจกรรม</span></a>');
				}
			}
		}

		$submenu='<span class="iconset">'._NL;
		$submenu.=sg_dropbox($ui->build('ul'),'{type:"hover",class:"leftside submenu -no-print"}')._NL;
		$submenu.='</span>'._NL;


		if ($crs->from_date==$crs->to_date) $actionDate= sg_date($crs->from_date,'ว ดด ปป');
		else if (sg_date($crs->from_date,'Y-m')==sg_date($crs->to_date,'Y-m')) $actionDate=sg_date($crs->from_date,'ว').'-'.sg_date($crs->to_date,'ว').' '.sg_date($crs->from_date,'ดด ปป');
		else $actionDate=sg_date($crs->from_date,'ว ดด ปป').'-'.sg_date($crs->to_date,'ว ดด ปป');
		$tables->rows[]=array(
			$isEditCalendar?'<a class="sg-action inline-edit-field" href="'.url('project/calendar/'.$tpid.'/edit/'.$crs->id).'" data-rel="box" data-type="link" data-width="640" title="คลิกเพื่อแก้ไข">'.$actionDate.'</a>':$actionDate,
			view::inlineedit(array('group'=>'calendar','fld'=>'title','tr'=>$crs->id, 'value'=>$crs->title),$crs->title,$isEditCalendar),
			$crs->targetpreset,
			$showBudget ? number_format($crs->budget,2) : '-',
			$crs->activityId?'<a class="sg-action" href="'.url('project/'.$tpid.'/action.view/'.$crs->activityId).'" data-rel="box">✔</a>':'',
			$showBudget ? ($crs->exp_total ? number_format($crs->exp_total,2) : '-') : '-',
			$submenu,
			'config'=>array('class'=>'calendar')
		);
	}
	$tables->rows[]=array(
		'',
		'รวม',
		number_format($info->summary->target),
		$showBudget ? number_format($info->summary->totalBudget,2) : '-',
		number_format($info->summary->activity),
		$showBudget ? number_format($info->summary->expense,2) : '-',
		'',
		'config'=>array('class' => 'subfooter')
	);

	$ret.=$tables->build();

	if ($isEdit && $info->summary->totalBudget!=$info->project->budget) $ret.='<p class="notify">คำเตือน : งบประมาณรวมทุกกิจกรรรม ('.number_format($info->summary->totalBudget,2).' บาท) ไม่เท่ากับ งบประมาณของโครงการ ('.number_format($info->project->budget,2).' บาท)<br />กรุณาตรวจสอบความถูกต้องด้วยค่ะ!!!</p>';
	/*
	if ($info->summary->totalBudget!=$info->summary->budget) $ret.='<p class="notify">คำเตือน : งบประมาณของกิจกรรมหลัก ('.number_format($info->summary->budget,2).' บาท) ไม่เท่ากับ งบประมาณของกิจกรรมย่อย ('.number_format($info->summary->totalBudget,2).' บาท)</p>';

	if ($info->project->budget!=$info->summary->budget) $ret.='<p class="notify">คำเตือน : งบประมาณของโครงการ ('.number_format($info->project->budget,2).' บาท) ไม่เท่ากับ งบประมาณของกิจกรรมหลัก ('.number_format($info->summary->budget,2).' บาท)</p>';
	*/

	if ($isEdit) {
		$ret.='<nav class="nav -page -sg-text-right"><a class="sg-action btn -primary" href="'.url('project/calendar/'.$tpid.'/add').'" data-rel="box" data-width="640"><i class="icon -material">add</i><span>เพิ่มกิจกรรมย่อย</span></a></p>';
	}
	return $ret;
}

function __project_calendar_list_plan($tpid, $projectInfo,$info,$isEdit) {
	$showBudget = $projectInfo->is->showBudget;

	$tables=new table('item project-mainact-items');
	$tables->thead=array(
		'date no'=>'วันที่',
		'title'=>'กิจกรรมหลัก/กิจกรรมย่อย',
		'money'=>'งบประมาณ<br />(บาท)',
		'amt target'=>'กลุ่มเป้าหมาย<br />(คน)',
		'amt calendar'=>'กิจกรรมย่อย<br />(ครั้ง)',
		'money budget'=>'งบกิจกรรม<br />(บาท)',
		'amt done'=>'ทำแล้ว<br />(ครั้ง)',
		'money expend'=>'ใช้จ่ายแล้ว<br />(บาท)',
		'icons -c2'=>'',
	);

	foreach ($info->mainact as $mrs) {
		//$ret.=print_o($mrs,'$mrs');
		$mrs->no=++$j;
		$tables->rows[]=__project_mainact_show_mainactivity_item($projectInfo,$mrs,$isEdit,$isEditDetail);
		if (!$mrs->trid) continue;
		$isSubCalendar=false;
		foreach ($info->calendar[$mrs->trid] as $crs) {
			$isSubCalendar=true;
			$isEditCalendar=$isEdit && $crs->from_date>$lockReportDate;

			if ($crs->from_date==$crs->to_date) $actionDate= sg_date($crs->from_date,'ว ดด ปป');
			else if (sg_date($crs->from_date,'Y-m')==sg_date($crs->to_date,'Y-m')) $actionDate=sg_date($crs->from_date,'ว').'-'.sg_date($crs->to_date,'ว').' '.sg_date($crs->from_date,'ดด ปป');
			else $actionDate=sg_date($crs->from_date,'ว ดด ปป').'-'.sg_date($crs->to_date,'ว ดด ปป');

			$tables->rows[]=array(
				$isEditCalendar?'<a class="sg-action inline-edit-field" href="'.url('project/calendar/'.$tpid.'/edit/'.$crs->id).'" data-rel="box" data-type="link" title="คลิกเพื่อแก้ไข">'.$actionDate.'</a>':$actionDate,
				view::inlineedit(array('group'=>'calendar','fld'=>'title','tr'=>$crs->id, 'value'=>$crs->title),$crs->title,$isEditCalendar),
				'',
				$crs->targetpreset,
				'',
				$showBudget ? number_format($crs->budget,2) : '-',
				$crs->activityId?'<a class="sg-action" href="'.url('project/'.$tpid.'/action.view/'.$crs->activityId).'" data-rel="box">✔</a>':'',
				$showBudget ? ($crs->exp_total?number_format($crs->exp_total,2):'-') : '-',
				$isEditCalendar?'<span class="iconset -hover"><a class="sg-action" href="'.url('project/calendar/'.$tpid.'/edit/'.$crs->id).'" data-rel="box" title="แก้ไขรายละเอียดกิจกรรมย่อย"><i class="icon -edit"></i></a>'.($crs->activityId?'':' <a class="sg-action" href="'.url('project/calendar/'.$tpid.'/remove/'.$crs->id).'" data-rel="#project-objective-wrapper" data-ret="'.url('project/calendar/'.$tpid).'" data-confirm="ลบกิจกรรมย่อย กรุณายืนยัน" title="ลบกิจกรรมย่อย"><i class="icon -delete"></i></a>'):'',
				'config'=>array('class'=>'calendar')
			);
		}
		if ($isEdit && !$isSubCalendar) $tables->rows[]=array('','<td colspan="8">ยังไม่ได้กำหนดกิจกรรมย่อย กรุณา<a class="sg-action" href="'.url('project/calendar/'.$tpid.'/add/'.$mrs->trid).'">เพิ่มกิจกรรมย่อย</a>อย่างน้อย 1 กิจกรรม</td>','config'=>array('class'=>'calendar'));
	}

	// กิจกรรมย่อยที่ไม่ได้กำหนดกิจกรรมหลักหรือกำหนดไม่ถูกต้อง
	$stmt='SELECT c.`id`, c.`from_date`, c.`title`
						, a.*, m.`trid`,m.`detail1`,m.`formid`,m.`part`
						, r.`trid` activityId
					FROM %calendar% c
						LEFT JOIN %project_activity% a ON a.`calid`=c.`id`
						LEFT JOIN %project_tr% m ON m.`trid`=a.`mainact`
						LEFT JOIN %project_tr% r ON r.`calid`=c.`id` AND r.`formid`="activity" AND r.`part`="owner"
					WHERE c.`tpid`=:tpid AND a.`calowner`=1 ORDER BY `from_date` ASC';
	$dbs=mydb::select($stmt,':tpid',$tpid);
	//if (i()->username=='softganz') $ret.=print_o($dbs,'$dbs');


	$noMainAct=false;
	foreach ($dbs->items as $rs) {
		if (empty($rs->mainact) || empty($rs->trid)) {
			$noMainAct=true;
			break;
		}
	}
	if ($noMainAct) $tables->rows[]='<tr><td colspan="9"><h4>กิจกรรมย่อยที่ยังไม่กำหนดกิจกรรมหลัก'.($isEdit?' (กรุณาแก้ไขโดยกำหนดกิจกรรมหลักหรือลบทิ้ง) !!!':'').'</h4></td></tr>';

	foreach ($dbs->items as $rs) {
		//$ret.=print_o($rs,'$rs');
		if (empty($rs->mainact) || empty($rs->trid)) {
			$noMainAct=true;
			$tables->rows[]=array(
				sg_date($rs->from_date,'ว ดด ปปปป'),
				$rs->title, //.print_o($rs,'$rs'),
				'',
				$rs->targetpreset,
				'',
				$showBudget ? $rs->budget : '-',
				'',
				'',
				$isEdit?'<span class="iconset -hover"><a class="sg-action" href="'.url('project/calendar/'.$tpid.'/edit/'.$rs->id).'" data-rel="box" title="แก้ไขรายละเอียดกิจกรรมย่อย"><i class="icon -edit"></i></a>'.($rs->activityId?'':' <a class="sg-action" href="'.url('project/calendar/'.$tpid.'/remove/'.$rs->id).'" data-rel="#project-objective-wrapper" data-ret="'.url('project/calendar/'.$tpid).'" data-confirm="ลบกิจกรรมย่อย กรุณายืนยัน" title="ลบกิจกรรมย่อย"><i class="icon -delete"></i></a>'):'',
					'config'=>array('class'=>'calendar error')
			);
		}
	}

	$tables->tfoot[]=array(
		'',
		'รวม',
		$showBudget ? number_format($info->summary->budget,2) : '-',
		number_format($info->summary->target),
		number_format($info->summary->calendar),
		$showBudget ? number_format($info->summary->totalBudget,2) : '-',
		number_format($info->summary->activity),
		$showBudget ? number_format($info->summary->expense,2) : '-',
		'',
	);

	$ret.=$tables->build();


	if ($info->summary->totalBudget!=$info->summary->budget) $ret.='<p class="notify">คำเตือน : งบประมาณของกิจกรรมหลัก ('.number_format($info->summary->budget,2).' บาท) ไม่เท่ากับ งบประมาณของกิจกรรมย่อย ('.number_format($info->summary->totalBudget,2).' บาท)</p>'._NL;

	if ($info->project->budget!=$info->summary->budget) $ret.='<p class="notify">คำเตือน : งบประมาณของโครงการ ('.number_format($info->project->budget,2).' บาท) ไม่เท่ากับ งบประมาณของกิจกรรมหลัก ('.number_format($info->summary->budget,2).' บาท)</p>'._NL;

	if ($isEditDetail) {
		$ret.='<p><a class="sg-action button raised" href="'.url('project/plan/'.$tpid.'/add').'" data-rel="box">+เพิ่มกิจกรรมหลักตาม TOR</a></p>'._NL;
		$ret.='<p>( ชื่อกิจกรรมจะต้องเป็นชื่อกิจกรรมที่ระบุไว้ใน TOR เท่านั้น ส่วนกิจกรรมย่อยแต่ละครั้งสามารถเพิ่มได้จาก ปุ่ม <span style="font-size:1.8em;">'._CHAR_3DOTS.'</span> หลังกิจกรรมหลักแต่ละรายการ หรือ ใน <a href="'.url('project/'.$tpid.'/info.calendar').'">ปฏิทินโครงการ</a> )</p>'._NL;
	}
	return $ret;
}

function __project_calendar_list_obj($tpid, $projectInfo,$info,$isEdit) {
	$showBudget = $projectInfo->is->showBudget;

	$calendarList=project_model::get_calendar($tpid,$period,$owner="owner");
	$calendarListByCalId=array();
	foreach ($calendarList->items as $rs) $calendarListByCalId[$rs->calid]=$rs;
	//$ret.=print_o($calendarListByCalId,'$calendarListByCalId');

	$stmt='SELECT o.`trid` objId, o.`text1` `objTitle`
			, m.`gallery` planId, a.`calid`
		FROM %project_tr% o
			LEFT JOIN %project_tr% m ON m.`tpid`=:tpid AND m.`formid`="info" AND m.`part`="actobj" AND m.`parent`=o.`trid`
			LEFT JOIN %project_activity% a ON m.`gallery`=a.`mainact`
		WHERE o.`tpid`=:tpid AND o.`formid`="info" AND o.`part`="objective"
		HAVING calid IS NOT NULL';

	$calObjList=mydb::select($stmt,':tpid',$tpid);
	//$ret.=print_o($calObjList,'$calObjList');

	$objectiveNo=0;
	$tables = new Table();
	$tables=new table('item project-mainact-items');
	$tables->thead=array(
		'date no'=>'วันที่',
		'title'=>'ชื่อกิจกรรม',
		'amt target'=>'กลุ่มเป้าหมาย (คน)',
		'amt budget'=>'งบกิจกรรม (บาท)',
		'amt done'=>'ทำแล้ว',
		'amt expend'=>'ใช้จ่ายแล้ว (บาท)',
		'',
	);

	foreach ($info->objective as $objId => $rs) {
		$subTarget=$subBudget=$subActivity=$subExpense=0;
		$objectiveNo++;

		$tables->rows[]=array('<td colspan="7"><h4>วัตถุประสงค์ข้อที่ '.$objectiveNo.' : '.$rs->title.'</h4></td>');


		foreach ($calObjList->items as $calObj) {
			if ($calObj->objId!=$objId) continue;
			//$ret.=$objId.' : '.print_o($calObj,'$calObj');

			$crs=$calendarListByCalId[$calObj->calid];
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
						$ui->add('<a class="sg-action" href="'.url('project/calendar/'.$tpid.'/remove/'.$crs->id).'" data-rel="#project-calendar-wrapper" data-ret="'.url('project/calendar/'.$tpid).'" data-confirm="ลบกิจกรรมย่อย กรุณายืนยัน" title="ลบกิจกรรม"><i class="icon -delete -showtext"></i><span>ลบกิจกรรม</span></a>');
					}
				}
			}

			$submenu='<span class="iconset">'._NL;
			$submenu.=sg_dropbox($ui->build('ul'),'{type:"hover",class:"leftside submenu -no-print"}')._NL;
			$submenu.='</span>'._NL;


			if ($crs->from_date==$crs->to_date) $actionDate= sg_date($crs->from_date,'ว ดด ปป');
			else if (sg_date($crs->from_date,'Y-m')==sg_date($crs->to_date,'Y-m')) $actionDate=sg_date($crs->from_date,'ว').'-'.sg_date($crs->to_date,'ว').' '.sg_date($crs->from_date,'ดด ปป');
			else $actionDate=sg_date($crs->from_date,'ว ดด ปป').'-'.sg_date($crs->to_date,'ว ดด ปป');
			$tables->rows[]=array(
				$isEditCalendar?'<a class="sg-action inline-edit-field" href="'.url('project/calendar/'.$tpid.'/edit/'.$crs->id).'" data-rel="box" data-type="link" title="คลิกเพื่อแก้ไข">'.$actionDate.'</a>':$actionDate,
				view::inlineedit(array('group'=>'calendar','fld'=>'title','tr'=>$crs->id, 'value'=>$crs->title),$crs->title,$isEditCalendar),
				$crs->targetpreset,
				$showBudget ? number_format($crs->budget,2) : '-',
				$crs->activityId?'<a class="sg-action" href="'.url('project/'.$tpid.'/action.view/'.$crs->activityId).'" data-rel="box">✔</a>':'',
				$showBudget ? ($crs->exp_total?number_format($crs->exp_total,2):'-') : '-',
				$submenu,
				'config'=>array('class'=>'calendar')
			);

			$subTarget+=$crs->targetpreset;
			$subBudget+=$crs->budget;
			if ($crs->activityId) $subActivity++;
			$subExpense+=$crs->exp_total;
		}
		$tables->rows[]=array(
			'',
			'รวม',
			number_format($subTarget),
			$showBudget ? number_format($subBudget,2) : '-',
			number_format($subActivity),
			$showBudget ? number_format($subExpense,2) : '-',
			'',
			'config'=>array('class'=>'subfooter')
		);

	}
	$ret.=$tables->build();
	$ret.='<p>หมายเหตุ : งบประมาณ และ ค่าใช้จ่าย รวมทุกวัตถุประสงค์อาจจะไม่เท่ากับงบประมาณรวมได้</p>';

	//$ret.=print_o($info,'$info');
	return $ret;
}

function __project_calendar_list_guide($tpid, $projectInfo,$info,$isEdit) {
	$showBudget = $projectInfo->is->showBudget;

	$stmt='SELECT `catid`, `name`, `description` `indicator` FROM %tag% WHERE `taggroup`="project:activitygroup" ORDER BY `catid` ASC';
	$guideList=mydb::select($stmt)->items;

	$calendarList=project_model::get_calendar($tpid,$period,$owner="owner");

	$guideIdList=array();
	foreach (mydb::select('SELECT * FROM %project_actguide% WHERE `tpid`=:tpid',':tpid',$tpid)->items as $rs) {
		$guideIdList[$rs->calid][]=$rs->guideid;
	}
	// debugMsg($guideIdList,'$guideIdList');

	$objectiveNo=0;
	$guideTables = new Table();
	$guideTables->colgroup=array('no'=>'width="5%"','objective'=>'width="45%"','indicator'=>'width="50%"');
	$guideTables->thead=array('','แนวทางดำเนินงาน 8 องค์ประกอบ','ตัวชี้วัด');

	$tables = new Table();
	$tables=new table('item project-mainact-items');
	$tables->thead=array(
		'date no'=>'วันที่',
		'title'=>'ชื่อกิจกรรม',
		'amt target'=>'กลุ่มเป้าหมาย (คน)',
		'amt budget'=>'งบกิจกรรม (บาท)',
		'amt done'=>'ทำแล้ว',
		'amt expend'=>'ใช้จ่ายแล้ว (บาท)',
		'',
	);

	foreach ($guideList as $guideId => $rs) {
		$subTarget=$subBudget=$subActivity=$subExpense=0;

		$tables->rows[] = ['<td colspan="7"><h4>แนวทางดำเนินงานที่ '.$rs->catid.' : '.$rs->name.'</h4>'._NL.'<strong>ตัวชี้วัด</strong><br />'.sg_text2html($rs->indicator).'</td>'];



		foreach ($calendarList->items as $crs) {
			if (!array_key_exists($crs->id, $guideIdList) || !in_array($rs->catid, $guideIdList[$crs->id])) continue;
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
						$ui->add('<a class="sg-action" href="'.url('project/calendar/'.$tpid.'/remove/'.$crs->id).'" data-rel="#project-calendar-wrapper" data-ret="'.url('project/calendar/'.$tpid).'" data-confirm="ลบกิจกรรมย่อย กรุณายืนยัน" title="ลบกิจกรรม"><i class="icon -delete -showtext"></i><span>ลบกิจกรรม</span></a>');
					}
				}
			}

			$submenu='<span class="iconset">'._NL;
			$submenu.=sg_dropbox($ui->build('ul'),'{type:"hover",class:"leftside submenu -no-print"}')._NL;
			$submenu.='</span>'._NL;


			if ($crs->from_date==$crs->to_date) $actionDate= sg_date($crs->from_date,'ว ดด ปป');
			else if (sg_date($crs->from_date,'Y-m')==sg_date($crs->to_date,'Y-m')) $actionDate=sg_date($crs->from_date,'ว').'-'.sg_date($crs->to_date,'ว').' '.sg_date($crs->from_date,'ดด ปป');
			else $actionDate=sg_date($crs->from_date,'ว ดด ปป').'-'.sg_date($crs->to_date,'ว ดด ปป');
			$tables->rows[]=array(
				$isEditCalendar?'<a class="sg-action inline-edit-field" href="'.url('project/calendar/'.$tpid.'/edit/'.$crs->id).'" data-rel="box" data-type="link" title="คลิกเพื่อแก้ไข">'.$actionDate.'</a>':$actionDate,
				view::inlineedit(array('group'=>'calendar','fld'=>'title','tr'=>$crs->id, 'value'=>$crs->title),$crs->title,$isEditCalendar),
				$crs->targetpreset,
				$showBudget ? number_format($crs->budget,2) : '-',
				$crs->activityId?'<a class="sg-action" href="'.url('project/'.$tpid.'/action.view/'.$crs->activityId).'" data-rel="box">✔</a>':'',
				$showBudget ? ($crs->exp_total?number_format($crs->exp_total,2):'-') : '-',
				$submenu,
				'config'=>array('class'=>'calendar')
			);

			$subTarget+=$crs->targetpreset;
			$subBudget+=$crs->budget;
			if ($crs->activityId) $subActivity++;
			$subExpense+=$crs->exp_total;
		}
		$tables->rows[]=array(
			'',
			'รวม',
			number_format($subTarget),
			$showBudget ? number_format($subBudget,2) : '-',
			number_format($subActivity),
			$showBudget ? number_format($subExpense,2) : '-',
			'',
			'config'=>array('class'=>'subfooter')
		);

	}
	$ret.=$tables->build();

	//$ret.=print_o($info,'$info');
	return $ret;
}

function __project_mainact_show_mainactivity_item($projectInfo,$mrs,$isEdit=false,$isEditDetail=false) {
	if (empty($mrs->trid)) return;

	$showBudget = $projectInfo->is->showBudget;

	// Menu for main activity
	$ui=new ui();
	$ui->add('<a class="sg-action" href="'.url('project/plan/'.$mrs->tpid.'/info/'.$mrs->trid).'" data-rel="box" data-height="90%">รายละเอียดกิจกรรมหลัก</a>');
	$ui->add('<a class="sg-action" href="'.url('project/plan/'.$mrs->tpid.'/calendar/'.$mrs->trid).'" data-rel="box" data-height="90%">กิจกรรมย่อยในกิจกรรมหลัก</a>');
	if ($isEdit) {
		$ui->add('<sep>');
		$ui->add('<a class="sg-action" href="'.url('project/calendar/'.$mrs->tpid.'/add/'.$mrs->trid).'" data-rel="box" title="เพิ่มกิจกรรมย่อยในกิจกรรมหลักนี้" data-height="90%">เพิ่มกิจกรรมย่อยในกิจกรรมหลักนี้</a>');
	}

	if ($isEdit) {
		$submenu.='<a class="sg-action" href="'.url('project/calendar/'.$mrs->tpid.'/add/'.$mrs->trid).'" data-rel="box" title="เพิ่มกิจกรรมย่อยในกิจกรรมหลักนี้" data-height="90%"><i class="icon -add"></i></a>';
	}
	$submenu.=sg_dropbox($ui->build('ul'),'{type:"hover",class:"leftside submenu -no-print"}')._NL;

	// เงื่อนไขของการแก้ไขงบประมาณของกิจกรรมหลักคือ ไม่มีการพัฒนาโครงการหรือพัฒนาโครงการก่อนปี 2558 ซึ่งจะไม่มีรหัสโครงการพัฒนา devtpid
	$row=array(
		'<td style="text-align:center;font-weight:normal;"><big style="background:#f60; border-radius:50%; display:inline-block;padding:4px; width:1.4em;height:1.4em;line-height:1.4em; color:#fff;">'.$mrs->no.'</big></td>',
		$mrs->title,
		$showBudget ? number_format($mrs->budget,2) : '-',
		number_format($mrs->target),
		'<a href="'.url('project/plan/'.$mrs->tpid.'/calendar/'.$mrs->trid).'" class="sg-action" data-rel="box" data-height="90%" title="ดูรายการกิจกรรมย่อย">'.($mrs->totalCalendar?$mrs->totalCalendar:'-').'</a>',
		$showBudget ? ($mrs->totalBudget?number_format($mrs->totalBudget,2):'-') : '-',
		'<a href="'.url('project/plan/'.$mrs->tpid.'/calendar/'.$mrs->trid).'" class="sg-action" data-rel="box" data-height="90%" title="ดูรายการกิจกรรมย่อย">'.($mrs->totalActitity?$mrs->totalActitity:'-').'</a>',
		$showBudget ? ($mrs->totalExpense?number_format($mrs->totalExpense,2):'-') : '-',
		$submenu,
		'config'=>array('class'=>'mainact'),
	);
	return $row;
}

/**
* Add calendar to main activity
*
* @param Integer $tpid
* @param Integer $actid
* @return String
*/
function __project_calendar_add($tpid,$actid,$data,$project) {
	$formType=SG\getFirst(post('type'),'detail');

	$mainactList=project_model::get_main_activity($tpid,'owner');

	$title = ($data?'แก้ไข':'เพิ่ม').'กิจกรรมย่อย';
	if ($formType!='short') {
		$ret.='<header class="header -box"><h3>'.$title.'</h3></header>';
		$ret.='<div class="box--mainbar--no">'._NL;
	}

	if ($data) $post=$data;
	else $post=(object)post('calendar');

	if ($formType=='auto') {
		$post->tpid=$tpid;
		$post->mainact=SG\getFirst($actid,$post->mainact);
		$post->owner=SG\getFirst(i()->uid,NULL);
		$post->privacy='public';
		$post->calowner=1;
		$post->title=$mainact->title;
		$post->from_date=$post->to_date=date('Y-m-d');
		$post->from_time='09:00:00';
		$post->to_time='12:00:00';
		$post->detail=$mainact->desc;
		$post->category=NULL;
		$post->reminder='no';
		$post->repeat='no';
		$post->ip=ip2long(GetEnv('REMOTE_ADDR'));
		$post->created_date='func.NOW()';

		$stmt='INSERT INTO %calendar%
						( `tpid`, `owner`, `privacy`, `category`, `title`, `from_date`, `from_time`, `to_date`, `to_time`, `detail`, `reminder`, `repeat`, `ip`, `created_date`)
					VALUES
						(:tpid, :owner, :privacy, :category, :title, :from_date, :from_time, :to_date, :to_time, :detail, :reminder, :repeat, :ip, :created_date)';
		mydb::query($stmt,$post);
		//$ret.=mydb()->_query.'<br />';

		if (!mydb()->_error) {
			$post->calid=mydb()->insert_id;
			$post->targetpreset=$mainact->target;
			$post->target='';
			$post->budget=$mainact->budget-$mainact->totalBudget;
			if ($post->budget<0) $post->budget=0;
			$stmt='INSERT INTO %project_activity%
						(`calid`, `calowner`, `mainact`, `targetpreset`, `target`, `budget`)
						VALUES
						(:calid, :calowner, :mainact, :targetpreset, :target, :budget)';
			mydb::query($stmt, $post);
			//$ret.=mydb()->_query.'<br />';
		}

		// Add log
		model::watch_log('project','calendar add:auto','เพิ่มกิจกรรมย่อย หมายเลข '.$post->calid,NULL,$tpid);

		//$ret.=print_o($post,'$post').print_o($mainact,'$mainact');
		return $ret;
	} else if (post('calendar') && $post->title && sg_date($post->from_date,'Y-m-d')<=$project->lockReportDate) {
		$ret.='<p class="notify">วันที่ทำกิจกรรมจะต้องหลังวันส่งรายงานครั้งสุดท้าย คือ '.sg_date($project->lockReportDate,'d/m/ปปปป');
	} else if ($tpid && $post->title && post('calendar')) {
		// if check guideid => && $post->guideid
		$post->calid=empty($post->id) ? NULL : $post->id;
		$post->owner=SG\getFirst(i()->uid,NULL);

		// Change BC to DC on year > 2500
		$post->DCfrom_date=sg_date($post->from_date,'Y-m-d');
		$post->DCto_date=sg_date($post->to_date,'Y-m-d');

		$post->ip=ip2long(GetEnv('REMOTE_ADDR'));
		$post->created_date='func.NOW()';
		$post->category=SG\getFirst($post->category,'func.NULL');
		$post->reminder=SG\getFirst($post->reminder,'no');
		$post->repeat=SG\getFirst($post->repeat,'no');

		$address=SG\explode_address($post->location);
		$post->changwat=substr($post->areacode,0,2);
		$post->ampur=substr($post->areacode,2,2);
		$post->tambon=substr($post->areacode,4,2);
		$post->village=$address['village']?sprintf('%02d',$address['village']):'';

		$stmt='INSERT INTO %calendar%
						(`id`, `tpid`, `owner`, `privacy`, `category`, `title`, `location`, `latlng`, `village`, `tambon`, `ampur`, `changwat`, `from_date`, `from_time`, `to_date`, `to_time`, `detail`, `reminder`, `repeat`, `ip`, `created_date`)
					VALUES
						(:calid, :tpid, :owner, :privacy, :category, :title, :location, :latlng, :village, :tambon, :ampur, :changwat, :DCfrom_date, :from_time, :DCto_date, :to_time, :detail, :reminder, :repeat, :ip, :created_date)
					ON DUPLICATE KEY UPDATE
						`title`=:title, `location`=:location, `latlng`=:latlng, `village`=:village, `tambon`=:tambon, `ampur`=:ampur, `changwat`=:changwat, `from_date`=:DCfrom_date, `from_time`=:from_time, `to_date`=:DCto_date, `to_time`=:to_time, `detail`=:detail';
		mydb::query($stmt,$post);
		//$ret.=mydb()->_query.'<br />';

		if (empty($post->calid)) $post->calid=mydb()->insert_id;
		$post->targetpreset=0;
		foreach (cfg('project.target') as $key => $value) {
			$post->{$key}=sg_strip_money($post->{$key});
			$post->targetpreset+=$post->{$key};
		}
		foreach (cfg('project.support') as $key => $value) {
			$post->{$key}=sg_strip_money($post->{$key});
			$post->targetpreset+=$post->{$key};
		}


		$post->target=empty($post->target)?'':$post->target;
		$post->budget=abs(sg_strip_money($post->budget));
		if ($post->mainact<=0) $post->mainact=NULL;
		$stmt='INSERT INTO %project_activity%
					(
					`calid`, `calowner`, `mainact`, `targetpreset`
					, `targt_studentjoin`, `targt_teacherjoin`, `targt_parentjoin`, `targt_clubjoin`
					, `targt_localorgjoin`, `targt_govjoin`
					, `targt_boardjoin`, `targt_volunteerjoin`, `targt_communejoin`
					, `targt_otherjoin`
					, `target`, `budget`
					)
					VALUES
					(:calid, :calowner, :mainact, :targetpreset
					, :studentjoin, :teacherjoin, :parentjoin, :clubjoin
					, :localorgjoin, :govjoin
					, :boardjoin, :volunteerjoin, :communejoin
					, :otherjoin
					, :target, :budget
					)
					ON DUPLICATE KEY UPDATE
					`calowner`=:calowner
					, `mainact`=:mainact
					, `targetpreset`=:targetpreset
					, `targt_studentjoin`=:studentjoin
					, `targt_teacherjoin`=:teacherjoin
					, `targt_parentjoin`=:parentjoin
					, `targt_clubjoin`=:clubjoin
					, `targt_localorgjoin`=:localorgjoin
					, `targt_govjoin`=:govjoin
					, `targt_boardjoin`=:boardjoin
					, `targt_volunteerjoin`=:volunteerjoin
					, `targt_communejoin`=:communejoin
					, `targt_otherjoin`=:otherjoin
					, `target`=:target
					, `budget`=:budget';
		mydb::query($stmt, $post);
		//$ret.=mydb()->_query.'<br />';

		if ($post->color) property('calendar:color:'.$post->calid,$post->color);

		//$ret.=print_o($post,'$post');
		$stmt='DELETE FROM %project_actguide% WHERE `tpid`=:tpid AND `calid`=:calid'.($post->guideid?' AND `guideid` NOT IN (:guideidset)':'');
		mydb::query($stmt,':tpid',$tpid, ':calid',$post->calid, ':guideidset','SET:'.implode(',',$post->guideid));
		//$ret.=mydb()->_query.'<br />';

		foreach ($post->guideid as $key => $value) {
			$stmt='INSERT INTO %project_actguide% (`tpid`,`calid`,`guideid`) VALUES (:tpid,:calid,:guideid)
						ON DUPLICATE KEY UPDATE `guideid`=:guideid';
			mydb::query($stmt,':tpid',$tpid, ':calid',$post->calid, ':guideid',$value);
			//$ret.=mydb()->_query.'<br />';
		}

		$postExp=post('exp');
		foreach ($postExp as $expCode => $item) {
			$exp=(object)$item;
			//$ret.='expCode='.$expCode.print_o($exp,'$exp');
			if (empty($exp->expid) && empty($exp->detail) && empty($exp->total)) {
				// no input
				continue;
			} else if  ($exp->expid && empty($exp->detail) && empty($exp->total)) {
				// Old transaction with empty data
				// Delete trabsaction
				mydb::query('DELETE FROM %project_tr% WHERE `trid`=:trid AND `formid`="develop" AND `part`="exptr" LIMIT 1',':trid',$exp->expid);
				//$ret.=mydb()->_query.'<br />';
				continue;
			}
			/* else if (empty($exp->detail) && empty($exp->expid)) {
				continue;
			} else if (empty($exp->detail) && $exp->expid) {
				// remove exp
				continue;
			}
			*/

			if (empty($exp->trid)) $exp->trid=NULL;
			$exp->tpid=$tpid;
			$exp->id=NULL;
			$exp->calid=$post->calid;
			$exp->expcode=$expCode;
			$exp->total=sg_strip_money($exp->total);
			$exp->amt=1;
			$exp->unitprice=$exp->total;
			$exp->times=1;
			$exp->unitname='รายการ';
			$exp->uid=$exp->modifyby=i()->uid;
			$exp->created=$exp->modified=date('U');
			$stmt='INSERT INTO %project_tr%
							(`trid`, `tpid`, `calid`, `parent`, `gallery`, `formid`, `part`, `num1`, `num2`, `num3`, `num4`, `detail1`, `text1`, `uid`, `created`)
							VALUES
							(:expid, :tpid, :calid, :id, :expcode, "develop","exptr",:amt,:unitprice,:times,:total,:unitname,:detail,:uid,:created)
							ON DUPLICATE KEY
							UPDATE `gallery`=:expcode, `num1`=:amt, `num2`=:unitprice, `num3`=:times, `num4`=:total, `detail1`=:unitname, `text1`=:detail, `modified`=:modified, `modifyby`=:modifyby';
			mydb::query($stmt,$exp);
			$budget+=$exp->total;
			//$ret.=mydb()->_query.'<br />';
		}
		$stmt='UPDATE %project_activity% SET `budget`=:budget WHERE `calid`=:calid LIMIT 1';
		mydb::query($stmt, ':calid',$post->calid, ':budget',$budget);
		//$ret.=mydb()->_query.'<br />';

		// Add log
		model::watch_log('project','calendar add:'.$formType,'เพิ่มกิจกรรมย่อย หมายเลข '.$post->calid.' : ' .$post->title,NULL,$tpid);


		//location('project/calendar/'.$tpid.'/calendar/'.$actid);
		return $ret;
	}




	if (is_array($post)) $post=(object)$post;
	if (empty($post->tpid)) $post->tpid=$tpid;

	// Set default value from main activity
	if (empty($post->mainact) && $actid) $post->mainact=$actid;
	if (empty($post->title)) $post->title=$mainact->title;
	if (empty($post->objective)) $post->objective=$mainact->objectiveTitle;
	if (empty($post->detail)) $post->detail=$mainact->desc;
	if (empty($post->budget)) {
		$post->budget=$mainact->budget-$mainact->totalBudget;
		if ($post->budget<0) $post->budget=0;
	}
	if (empty($post->targetpreset)) $post->targetpreset=number_format($mainact->target);

	// Set default value from current date
	if (empty($post->from_date)) $post->from_date=date('j/n/Y');
	if (empty($post->to_date)) $post->to_date=$post->from_date;
	if (empty($post->from_time)) $post->from_time='09:00';
	if (empty($post->to_time)) {
		list($hr,$min)=explode(':',$post->from_time);
		$post->to_time=sprintf('%02d',$hr+1).':'.$min;
	}
	if (empty($post->privacy)) $post->privacy='public';

	list(,$month,$year)=explode('/',$post->from_date);

	$multipleSaveButton = $post->id > 0;


	$saveButton = [
		'type' => 'button',
		'value' => '<i class="icon -material">done_all</i>{tr:SAVE}',
		'pretext' => '<a class="sg-action btn -link -cancel" data-rel="close" href="javascript:void(0)"><i class="icon -material -gray">cancel</i><span>ยกเลิก</span></a>',
		'container' => '{class: "-sg-text-right"}',
	];

	$form = new Form([
		'variable' => 'calendar',
		'action' => url('project/calendar/'.$tpid.'/add/'.$actid),
		'id' => 'edit-calendar',
		'class' => !debug('form') ? 'sg-form' : '',
		'rel' => 'box',
		'done' => 'close | load',
		'children' => [
			'act' => ['type' => 'hidden', 'name' => 'act', 'value' => 'add'],
			'id' => $post->id ? ['type' => 'hidden', 'value' => $post->id] : NULL,
			'tpid' => $post->tpid ? ['type' => 'hidden', 'value' => $post->tpid] : NULL,
			'type' => ['type' => 'hidden', 'name' => 'type', 'value' => $formType],
			'privacy' => ['type' => 'hidden', 'value' => 'public'],
			'calowner' => ['type' => 'hidden', 'value' => 1],
			'save1' => $multipleSaveButton ? $saveButton : NULL,

			'mainact' => [
				'type' => 'select',
				'label' => 'กิจกรรมหลัก :',
				'require' => true,
				'class' => '-fill',
				'options' => (function($info) {
					$options = [-1 => 'ไม่มีกิจกรรมหลัก หรือ เลือกจากรายการ'];
					foreach ($info as $item) {
						if (empty($item->title)) continue;
						$objKey = $item->objectiveTitle ? $item->objectiveTitle : 'ไม่ระบุวัตถุประสงค์';
						$options[$objKey][$item->trid] = $item->title;
					}
					return $options;
				})($mainactList->info),
				'value' => $post->mainact,
			],

			'title' => [
				'type' => 'text',
				'label' => 'ทำอะไร',
				'class' => '-fill',
				'maxlength' => 255,
				'require' => true,
				'placeholder' => 'ระบุชื่อกิจกรรม',
				'value' => htmlspecialchars($post->title),
			],

			'date' => [
				'type' => 'textfield',
				'label' => 'เมื่อไหร่',
				'require' => true,
				'value' => (function($project, $post) {
					$times = [];
					for ($hr = 7; $hr < 24; $hr++) {
						for ($min = 0;$min < 60; $min += 30) {
							$times[] = sprintf('%02d',$hr).':'.sprintf('%02d',$min);
						}
					}
					$ret = '<input type="text" name="calendar[from_date]" id="edit-calendar-from_date" maxlength="10" class="sg-datepicker form-text require" style="width:80px;" value="'.htmlspecialchars($post->from_date).'" data-diff="edit-calendar-to_date" data-min-date="'.sg_date($project->date_from,'j/n/Y').'" data-max-date="'.sg_date($project->date_end,'j/n/Y').'"> '
					. '<select class="form-select" name="calendar[from_time]" id="edit-calendar-from_time">';
					foreach ($times as $time) {
						$ret .= '<option value="'.$time.'"'.($time==$post->from_time?' selected="selected"':'').'>'.$time.'</option>';
					}
					$ret .= '</select> '
						. 'ถึง <select class="form-select" name="calendar[to_time]" id="edit-calendar-to_time">';
					foreach ($times as $time) {
						$ret .= '<option value="'.$time.'"'.($time==$post->to_time?' selected="selected"':'').'>'.$time.'</option>';
					}
					$ret .= '</select> '
						. '<input type="text" name="calendar[to_date]" id="edit-calendar-to_date" maxlength="10" class="sg-datepicker form-text require" style="width:80px;" value="'.htmlspecialchars($post->to_date).'" data-min-date="'.sg_date($project->date_from,'j/n/Y').'" data-max-date="'.sg_date($project->date_end,'j/n/Y').'">';
					return $ret;
				})($project, $post),
			],

			'areacode' => ['type' => 'hidden', 'value' => $post->areacode],
			'latlng' => ['type' => 'hidden', 'value' => $post->latlng],

			'guideid' => [
				'type' => 'checkbox',
				'label' => 'แนวทางดำเนินงาน',
				'multiple' => true,
				'require' => true,
				'options' => mydb::select(
					'SELECT `trid`,`detail1` `objTitle` FROM %project_tr% WHERE `tpid` IS NULL AND `formid` = "info" AND `part` = "objective";
					-- {key: "trid", value: "objTitle"}'
				)->items,
				'value' => (function($tpid, $post) {
					if (!$post->guideid) {
						$objSelect = mydb::select('SELECT * FROM %project_actguide% WHERE `tpid`=:tpid AND `calid`=:calid',':tpid',$tpid, ':calid',$post->id);
					}
					foreach ($objSelect->items as $item) {
						$values[$item->guideid] = $item->guideid;
					}
					return $values;
				})($tpid, $post),
				// 'posttext' => !$form->guideid ? '<p class="notify">กรุณาระบุการสนับสนุนวัตถุประสงค์ของกิจกรรมนี้</p>' : NULL,
			],

			'location' => [
				'type' => $formType=='short'?'hidden':'text',
				'label' => 'ที่ไหน',
				'class' => 'sg-address -fill',
				'maxlength' => 255,
				'placeholder' => 'ระบุสถานที่ หมู่ที่ ตำบล',
				'value' => $post->location,
				'attr' => ['data-altfld' => 'edit-calendar-areacode'],
			],

			'detail' => [
				'type' => $formType=='short'?'hidden':'textarea',
				'label' => 'รายละเอียดกิจกรรมตามแผน',
				'class' => '-fill',
				'rows' => 3,
				'placeholder' => 'ระบุรายละเอียดของกิจกรรมที่วางแผนว่าจะทำ',
				'value' => $post->detail,
			],

			'save2' => $multipleSaveButton ? $saveButton : NULL,

			'joinlist' => [
				'label' => 'กลุ่มเป้าหมาย/ผู้มีส่วนร่วม/ผู้สนับสนุนที่เข้าร่วมกิจกรรม',
				'type' => 'textfield',
				'value' => (new Table([
					'thead' => ['กลุ่มเป้าหมาย','amt'=>'จำนวน (คน)'],
					'children' => (function($post) {
						$rows = [
							'<td class="subheader" colspan="2">กลุ่มเป้าหมายที่เข้าร่วม',
						];
						foreach (cfg('project.target') as $key => $value) {
							$rows[] = [$value,'<input class="form-text -numeric" type="text" name="calendar['.$key.']" size="5" value="'.$post->{'targt_'.$key}.'" />'];
						}

						$rows[] = ['<td class="subheader" colspan="2">ผู้มีส่วนร่วม/ผู้สนับสนุน'];
						foreach (cfg('project.support') as $key => $value) {
							$rows[] = [$value,'<input class="form-text -numeric" type="text" name="calendar['.$key.']" size="5" value="'.$post->{'targt_'.$key}.'" />'];
						}
						return $rows;
					})($post)
				]))->build(),
			],

			'save3' => $multipleSaveButton ? $saveButton : NULL,

			'exp' => (new Table([
				'thead' => ['ประเภทรายจ่าย/รายละเอียด','money'=>'รวมเงิน (บาท)'],
				'children' => (function($tpid, $data) {
					$rows = [];
					$expTr = [];
					$stmt = 'SELECT
						`tpid`,`trid`,`parent`,`gallery` `expcode`,`num1` `amt`, `num2` `unitprice`, `num3` `times`, `num4` `total`,`detail1` `unitname`, `text1` `detail`
						FROM %project_tr%
						WHERE `tpid` = :tpid AND `formid` = "develop" AND `part` = "exptr" AND `calid` = :calid';
					foreach (mydb::select($stmt,':tpid',$tpid,':calid',$data->calid)->items as $item) {
						$expTr[$item->expcode] = $item;
					}

					$expCategoty = model::get_category('project:expcode','tid',false,1);
					foreach ($expCategoty as $expCode => $expName) {
						$exp = $expTr[$expCode];
						$rows[] = [
								'<input type="hidden" name="exp['.$expCode.'][expid]" value="'.$exp->trid.'" type="hidden" />'
								.'<b>'.$expName.' :</b><br />'
								.'<textarea class="form-textarea" name="exp['.$expCode.'][detail]" rows="1" cols="50" placeholder="ระบุรายละเอียดค่าใช้จ่าย" style="width:95%;">'.htmlspecialchars($exp->detail).'</textarea>',
								'<br /><input class="form-text" type="text" name="exp['.$expCode.'][total]" size="10" value="'.$exp->total.'" style="text-align:center; margin:0 auto; display:block;" />',
								];
					}
					return $rows;
				})($tpid, $data),
				'tfoot' => [
					[
						'รวมงบประมาณที่ตั้งไว้',
						number_format($post->budget,2),
						'config'=> ['class'=>'subhead']
					]
				],
			]))->build(),

			// $expCategoty=model::get_category('project:expcode','tid',false,0);
			// $tables = new Table();
			// $tables->thead=array('งบประมาณผิดประเภทรายจ่าย','');
			// foreach ($expCategoty as $expCode=>$expName) {
			// 	$exp=$expTr[$expCode];
			// 	if (!$exp) continue;
			// 	//$ret.=print_o($exp,'$exp');
			// 	$tables->rows[]=array(
			// 			'<input type="hidden" name="exp['.$expCode.'][expid]" value="'.$exp->trid.'" type="hidden" />'
			// 			.'<b>'.$expName.' :</b><br />'
			// 			.'<textarea name="exp['.$expCode.'][detail]" rows="2" cols="50" placeholder="ระบุรายละเอียดค่าใช้จ่าย" style="width:95%;">'.htmlspecialchars($exp->detail).'</textarea>',
			// 			'<br /><input type="text" name="exp['.$expCode.'][total]" size="10" value="'.$exp->total.'" style="text-align:center; margin:0 auto; display:block;" />',
			// 			);
			// }
			// if ($tables->rows) $form->exp.='<p class="notify">งบประมาณผิดประเภทรายจ่าย</p>'.$tables->build();

			'color' => [
				'type' => 'colorpicker',
				'label' => 'สีของกิจกรรม',
				'color' => 'Red, Green, Blue, Black, Purple, Aquamarine, Aqua, Chartreuse,Coral, DarkGoldenRod, Olive, Teal, HotPink, Brown',
				'value' => $post->color,
			],
			$saveButton,

			// if ($para->module) 	$form=do_class_method($para->module.'.extension','calendar_form', $form, $post, $para);

		], // children
	]);

	$ret .= $form->build();

	$gis['zoom']=7;

	if ($post->latlng) {
		list($lat,$lng)=explode(',', $post->latlng);
		$gis['center']=$post->latlng;
		$gis['zoom']=10;
		$gis['current']=array('latitude'=>$lat,
			'longitude'=>$lng,
			'title'=>$post->location,
			'content'=>'<h4>'.$post->title.'</h4>'.($post->topic_title?'<p><strong>'.$post->topic_title.'</strong></p>':'').($post->location?'<p>สถานที่ : '.$post->location.'</p>':''),
			);
	} else {
		$gis['center']=property('project:map.center:NULL');
	}

	//$ret.=print_o($post,'$post').print_o($gis,'$gis').print_o($mainact,'mainact');
	//$ret.=print_o($project,'$project');

	if ($formType!='short') {
		$ret.='</div>';

		//$ret.='<div class="box--sidebar">';
		//$ret.=__project_mainact_listcalendar($tpid,$actid,$project);
		//$ret.=__project_mainact_detail($tpid,$actid);
		//$ret.='</div>';
	}

	$ret.='<script type="text/javascript">
  setTimeout(function() { $("#edit-calendar-title").focus() }, 500);
	var gis='.json_encode($gis).'
	</script>';
	return $ret;
}

function __project_calendar_info($tpid,$calid,$info) {
	$calendar=project_model::get_calendar($tpid,NULL,NULL,$calid);
	$budgetList=mydb::select('SELECT * FROM %project_tr% WHERE `formid`="develop" AND `part`="exptr" AND `tpid`=:tpid AND `calid`=:calid',':tpid',$tpid, ':calid',$calid);
	$plan=$info->mainact[$calendar->mainact];

	$ret.='<h3>ชื่อกิจกรรม : '.$calendar->title.'</h3>';
	$ret.='<p>สถานที่ : '.$calendar->location.'</p>';
	$ret.='<p>เวลา : '.$calendar->from_date.' - '.$calendar->to_date.'</p>';
	$ret.='<p>กลุ่มเป้าหมาย : '.$calendar->targetpreset.'</p>';
	$ret.='<p>รายละเอียดกิจกรรมตามแผน : '.$calendar->detail.'</p>';

	$ret.='<h4>วัตถุประสงค์</h4>';
	$ret.='<ol>';
	foreach (explode(',',$plan->parentObjectiveId) as $item) {
		if (empty($item)) continue;
		$ret.='<li>'.$info->objective[$item]->title.'</li>';
	}
	$ret.='</ol>';

	$ret.='<p><strong>กิจกรรมหลัก : '.$plan->title.'</strong></p>';
	$ret.='<p>รายละเอียดกิจกรรมหลัก : '.$plan->desc.'</p>';
	$ret.='<p>ผลผลิต : '.$plan->output.'</p>';
	$ret.='<p>ผลลัพธ์ : '.$plan->outcome.'</p>';
	$ret.='<p>ภาคีร่วมสนับสนุน : '.$plan->copartner.'</p>';
	$ret.='<h4>งบประมาณ</h4>';
	$tables = new Table();
	$tables->thead=array('no'=>'','รายการ','amt'=>'จำนวนเงิน(บาท)');
	foreach ($budgetList->items as $item) {
		$tables->rows[]=array(++$no,nl2br($item->text1),number_format($item->num4,2));
	}
	$tables->tfoot[]=array('','รวมเงิน',number_format($calendar->budget,2));
	$ret.=$tables->build();

	//$ret.=print_o($calendar,'$calendar');
	//$ret.=print_o($budgetList,'$budgetList');
	//$ret.=print_o($plan,'$plan');
	//$ret.=print_o($info,'$info');
	return $ret;
}
?>