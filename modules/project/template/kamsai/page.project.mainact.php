<?php
/**
* Project main activity information
*
* @param Object $self
* @param Integer $tpid
* @param String $action
* @param Integer $actid
* @return String
*/
function project_mainact($self,$tpid,$action,$actid,$subid) {
	$mainact=project_model::get_main_activity($tpid,'owner')->info[$actid];

	$action=SG\getFirst($action,post('act'));

	/*
	if ($action) {
		$actid=$tpid;
		$tpid=mydb::select('SELECT `tpid` FROM %project_tr% WHERE `trid`=:trid AND `formid`="info" AND `part`="mainact" LIMIT 1',':trid',$tpid)->tpid;
	}
	*/
	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" data-rel="back" href="javascript:void(0)"><i class="icon -material">arrow_back</i></a></nav><h3>'.$mainact->title.'</h3></header>';


	if (empty($tpid)) return '<p class="notify">ไม่มีข้อมูลกิจกรรมหลักตามที่ระบุ</p>';


	$project=project_model::get_project($tpid);
	if ($project->_empty) return '<p class="notify">ไม่มีโครงการตามที่ระบุ</p>';

	$isAdmin=$project->isAdmin;
	$isEdit=$project->isEdit;
	$isEditDetail=$project->isEditDetail;
	$lockReportDate=$project->lockReportDate;

	if (post('gr')) {
		setcookie('maingrby',post('gr'),time()+10*365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));
	}
	$activityGroupBy=SG\getFirst(post('gr'),$_COOKIE['maingrby'],'act');

	//$ret.='action='.$action.' actid='.$actid;

	switch ($action) {
		case 'info':
			$ret.=__project_mainact_info($tpid,$actid,$subid,$project);
			return $ret;
			break;

		case 'move' :
			if ($isEdit) $ret.=__project_mainact_move($tpid,$actid);
			return $ret;
			break;

		case 'add' :
			if ($isEdit) $ret.=__project_mainact_add($tpid,$actid);
			return $ret;
			break;

		case 'remove' :
			if ($isEdit) $ret.=__project_mainact_remove($tpid,$actid);
			return $ret;
			break;

		case 'calendar' :
			if ($activityGroupBy=='act') $ret.=__project_mainact_calendar_list_act($tpid,$info,$isEdit);
			else if ($activityGroupBy=='obj') $ret.=__project_mainact_calendar_list_obj($tpid,$info,$isEdit);
			else if ($activityGroupBy=='plan') $ret.=__project_mainact_calendar_list_plan($tpid,$info,$isEdit);
			else if ($activityGroupBy=='guide') $ret.=__project_mainact_calendar_list_guide($tpid,$info,$isEdit);
			//$ret.=__project_mainact_info($tpid,$actid,$subid,$project);
			return $ret;
			break;

		case 'addcalendar' :
			if ($isEdit) $ret.=__project_mainact_addcalendar($tpid,$actid,$data,$project);
			return $ret;
			break;

		case 'editcalendar':
			if ($isEdit && $actid) {
				$stmt='SELECT * FROM %calendar% c LEFT JOIN %project_activity% a ON a.`calid`=c.`id` WHERE `id`=:calid LIMIT 1';
				$data=mydb::select($stmt,':calid',$actid);
				$data->from_date=sg_date($data->from_date,'d/m/Y');
				$data->to_date=sg_date($data->to_date,'d/m/Y');
				$data->from_time=substr($data->from_time,0,5);
				$data->to_time=substr($data->to_time,0,5);
				$data->color=property('calendar:color:'.$data->id);
				$ret.=__project_mainact_addcalendar($tpid,$actid,$data,$project);
			}
			return $ret;
			break;

		case 'removecalendar' :
			if ($isEdit && $actid) {
				$calendarTitle=mydb::select('SELECT `title` FROM %calendar% WHERE `id`=:id LIMIT 1',':id',$actid)->title;
				$ret.='Remove calendar '.$calendarTitle;
				mydb::query('DELETE FROM %calendar% WHERE `id`=:calid LIMIT 1',':calid',$actid);
				mydb::query('DELETE FROM %project_activity% WHERE `calid`=:calid LIMIT 1',':calid',$actid);
				mydb::query('DELETE FROM %project_actguide% WHERE `calid`=:calid',':calid',$actid);
				// Add log
				model::watch_log('project','Calendar remove','ลบกิจกรรมย่อย '.$actid.' กิจกรรมหลัก '.$actid.' : ' .$calendarTitle,NULL,$tpid);
			}
			$ret.=__project_mainact_info($tpid,$actid,NULL,$project);
			return $ret;
			break;

		default:
			if ($action) {
				$ret.='<p class="notify">ไม่มีเงื่อนไขตามระบุ</p>';
				return $ret;
			}
			break;
	}





	//$ret.=print_o($options,'$options');

	$objTypeList=model::get_category('project:objtype','catid');
	$info=project_model::get_info($tpid);









	//$ret.=print_o($objTypeList,'$objTypeList');
	//$ret.=print_o($info,'$info');


	// Show objective when group by main activity

	//$ret.=print_o($objectiveList,'$objectiveList');

	// Show main activities
	if ($activityGroupBy=='obj') {


		$objectiveNo=0;
		$totalMainActBudget=0;
		foreach ($objectiveList as $irs) {
			//$ret.=print_o($irs,$irs);
			unset($row);
			$ret.='<div id="project-objective-'.$irs->trid.'" class="project-objective">'._NL;
			$ret.='<h4>แนวทางดำเนินงาน ข้อที่ '.(++$objectiveNo).'</h4>'._NL;
			$ret.='<h5>วัตถุประสงค์ : </h5>'._NL;
			$ret.=view::inlineedit(array('group'=>'info:objective', 'fld'=>'text1', 'tr'=>$irs->trid, 'class'=>'project-object-field', 'button'=>'yes' ), $irs->title, $isEditDetail, 'textarea')._NL;
			$ret.='<h5>ตัวชี้วัด</h5>'._NL;
			$ret.=view::inlineedit(array('group'=>'info:objective','fld'=>'text2','tr'=>$irs->trid, 'button'=>'yes', 'ret'=>'html'),$irs->indicator,$isEditDetail,'textarea')._NL;
			$ret.='<h5>กิจกรรม</h5>'._NL;

			unset($tables);
			$j=0;
			$totalBudget=$totalTarget=$totalActivity=$totalActivityBudget=0;
			$totalDone=$totalExpend=0;

			// Show main activity each objective
			$tables->class='item project-mainact-items';
			$tables->thead=array('no'=>'','ชื่อกิจกรรมหลัก','money'=>'งบประมาณ<br />(บาท)','amt target'=>'กลุ่มเป้าหมาย<br />(คน)','amt calendar'=>'กิจกรรม<br />(ครั้ง)','money budget'=>'งบกิจกรรม<br />(บาท)','amt done'=>'ทำแล้ว<br />(ครั้ง)','money expend'=>'ใช้จ่ายแล้ว<br />(บาท)','center'=>'');
			foreach ($info->mainact as $mrs) {
				//$ret.='trid='.$irs->trid.' Object='.$mrs->parentObjectiveId.'<br />';
				if (!in_array($irs->trid,explode(',',$mrs->parentObjectiveId))) continue;
				//if ($mrs->objectiveId!=$irs->trid) continue;

				$mrs->no=++$j;
				$tables->rows[]=__project_mainact_show_mainactivity_item($project,$mrs,$isEdit,$isEditDetail);

				$totalBudget+=$mrs->budget;
				$totalTarget+=$mrs->target;
				$totalActivity+=$mrs->totalCalendar;
				$totalActivityBudget+=$mrs->totalBudget;
				$totalDone+=$mrs->totalActitity;
				$totalExpend+=$mrs->totalExpense;
			}
			$totalMainActBudget+=$totalActivityBudget;
			$tables->tfoot[]=array(
															'',
															'',
															number_format($totalBudget,2),
															number_format($totalTarget),
															number_format($totalActivity),
															number_format($totalActivityBudget,2),
															$totalDone,
															number_format($totalExpend,2),
															'',
														);
			$ret.=theme('table',$tables);

			$ret.='</div>'._NL;
		}

	} else {
		// Show main activity by timeline
		$ret.='<div id="project-objective-1" class="project-objective">'._NL;

		$ret.='</div>';
	}

	//$ret.=print_o($project,'$project');
	//$ret.=print_o($info,'$info');

	return $ret;
}



function __project_mainact_calendar_list_act($tpid,$info,$isEdit) {
	//$mainActGroupBy=$_COOKIE['maingrby'];
	$activityGroupBy=SG\getFirst(post('gr'),$_COOKIE['maingrby'],'act');

	$ret.='List calendar by '.$activityGroupBy;

	$calendarList=project_model::get_calendar($tpid,$period,$owner="owner");

	$tables=new table();
	$tables=new table('item project-mainact-items');
	$tables->thead=array('date no'=>'วันที่','title'=>'ชื่อกิจกรรม','amt target'=>'กลุ่มเป้าหมาย (คน)','amt budget'=>'งบกิจกรรม (บาท)','amt done'=>'ทำแล้ว','amt expend'=>'ใช้จ่ายแล้ว (บาท)', $isEdit?'<a class="sg-action" href="'.url('project/mainact/'.$tpid.'/addcalendar').'" data-rel="box" title="เพิ่มกิจกรรม"><i class="icon -add -hidetext"></i><span class="-hidden">เพิ่มกิจกรรม</span></a>':'');
	foreach ($calendarList->items as $crs) {
		$isSubCalendar=true;
		$isEditCalendar=$isEdit && $crs->from_date>$lockReportDate;

		$ui=new ui();
		$ui->add('<a class="sg-action" href="'.url('project/mainact/'.$tpid.'/calendar/'.$crs->id).'" data-rel="box" data-height="90%"><i class="icon -view"></i>รายละเอียดกิจกรรม</a>');
		if ($isEdit) {
			if ($isEditCalendar) $ui->add('<a class="sg-action" href="'.url('project/mainact/'.$tpid.'/editcalendar/'.$crs->id).'" data-rel="box" title="แก้ไขรายละเอียดกิจกรรมย่อย"><i class="icon -edit -showtext"></i><span>แก้ไขรายละเอียด</span></a>');
			$ui->add('<sep>');
			if ($isEditCalendar) {
				if ($crs->activityId) {
					$ui->add('<a href="javascript:void(0)" style="text-decoration:line-through;">ลบกิจกรรมไม่ได้</a>');
				} else {
					$ui->add('<a class="sg-action" href="'.url('project/mainact/'.$tpid.'/removecalendar/'.$crs->id).'" data-rel="#project-objective-wrapper" data-ret="'.url('project/mainact/'.$tpid).'" data-confirm="ลบกิจกรรมย่อย กรุณายืนยัน" title="ลบกิจกรรม"><i class="icon -delete -showtext"></i><span>ลบกิจกรรม</span></a>');
				}
			}
		}

		$submenu='<span class="iconset">'._NL;

		$submenu.=sg_dropbox($ui->show('ul'),'{type:"hover",class:"leftside submenu -no-print"}')._NL;
		$submenu.='</span>'._NL;


		if ($crs->from_date==$crs->to_date) $actionDate= sg_date($crs->from_date,'ว ดด ปป');
		else if (sg_date($crs->from_date,'Y-m')==sg_date($crs->to_date,'Y-m')) $actionDate=sg_date($crs->from_date,'ว').'-'.sg_date($crs->to_date,'ว').' '.sg_date($crs->from_date,'ดด ปป');
		else $actionDate=sg_date($crs->from_date,'ว ดด ปป').'-'.sg_date($crs->to_date,'ว ดด ปป');
		$tables->rows[]=array(
											$isEditCalendar?'<a class="sg-action inline-edit-field" href="'.url('project/mainact/'.$tpid.'/editcalendar/'.$crs->id).'" data-rel="box" data-type="link" title="คลิกเพื่อแก้ไข">'.$actionDate.'</a>':$actionDate,
											view::inlineedit(array('group'=>'calendar','fld'=>'title','tr'=>$crs->id, 'value'=>$crs->title),$crs->title,$isEditCalendar),
											$crs->targetpreset,
											number_format($crs->budget,2),
											$crs->activityId?'<a href="'.url('paper/'.$tpid.'/owner#tr-'.$crs->activityId).'" title="บันทึกหมายเลข '.$crs->activityId.'">✔</a>':'',
											$crs->exp_total?number_format($crs->exp_total,2):'-',
											$submenu,
											'config'=>array('class'=>'calendar')
											);
	}
	$tables->tfoot[]=array(
											'',
											'รวม',
											number_format($info->summary->target),
											number_format($info->summary->totalBudget,2),
											number_format($info->summary->activity),
											number_format($info->summary->expense,2),
											$isEdit?'<a class="sg-action" href="'.url('project/mainact/'.$tpid.'/addcalendar').'" data-rel="box" title="เพิ่มกิจกรรม"><i class="icon -add -hidetext"></i><span class="-hidden">เพิ่มกิจกรรม</span></a>':'',
										);

	$ret.=$tables->show();

	if ($info->summary->totalBudget!=$info->project->budget) $ret.='<p class="notify">คำเตือน : งบประมาณรวมทุกกิจกรรรม ('.number_format($info->summary->totalBudget,2).' บาท) ไม่เท่ากับ งบประมาณของโครงการ ('.number_format($info->project->budget,2).' บาท)</p>';
	/*
	if ($info->summary->totalBudget!=$info->summary->budget) $ret.='<p class="notify">คำเตือน : งบประมาณของกิจกรรมหลัก ('.number_format($info->summary->budget,2).' บาท) ไม่เท่ากับ งบประมาณของกิจกรรมย่อย ('.number_format($info->summary->totalBudget,2).' บาท)</p>';

	if ($info->project->budget!=$info->summary->budget) $ret.='<p class="notify">คำเตือน : งบประมาณของโครงการ ('.number_format($info->project->budget,2).' บาท) ไม่เท่ากับ งบประมาณของกิจกรรมหลัก ('.number_format($info->summary->budget,2).' บาท)</p>';
	*/

	if ($isEdit) {
		$ret.='<p><a class="sg-action button raised" href="'.url('project/mainact/'.$tpid.'/addcalendar').'" data-rel="box">+เพิ่มกิจกรรม</a></p>';
	}
	return $ret;
}

function __project_mainact_calendar_list_plan($tpid,$info,$isEdit) {
	$ret.='กิจกรรมตามกิจกรรมหลัก';
	return $ret;
}

function __project_mainact_calendar_list_obj($tpid,$info,$isEdit) {
	$ret.='กิจกรรมตามวัตถุประสงค์';
	return $ret;
}

function __project_mainact_calendar_list_guide($tpid,$info,$isEdit) {
	$ret.='กิจกรรมตามแนวทางการดำเนินงาน';
	$stmt='SELECT `catid`, `name`, `description` `indicator` FROM %tag% WHERE `taggroup`="project:activitygroup" ORDER BY `catid` ASC';
	$guideList=mydb::select($stmt)->items;

	$ret.='<h/4>แนวทางดำเนินงาน</h4>'._NL;
	$objectiveNo=0;
	$tables->class='item project-develop-objective';
	$tables->colgroup=array('no'=>'5%','objective'=>'width="45%"','indicator'=>'width="50%"');
	$tables->thead=array('no'=>'','แนวทางดำเนินงาน 8 องค์ประกอบ','ตัวชี้วัด');
	foreach ($guideList as $guideId => $rs) {
		$tables->rows[]=array(
											$rs->catid,
											$rs->name,
											sg_text2html($rs->indicator),
										);
	}

	$ret.=theme('table',$tables);
	return $ret;
}

function __project_mainact_show_mainactivity_item($project,$mrs,$isEdit=false,$isEditDetail=false) {
	if (empty($mrs->trid)) return;
	$ui=new ui();
	$ui->add('<a class="sg-action" href="'.url('project/mainact/'.$mrs->tpid.'/info/'.$mrs->trid).'" data-rel="box" data-height="90%">รายละเอียดกิจกรรมหลัก</a>');
	$ui->add('<a class="sg-action" href="'.url('project/mainact/'.$mrs->tpid.'/calendar/'.$mrs->trid).'" data-rel="box" data-height="90%">กิจกรรมย่อยในปฏิทินโครงการ</a>');
	if ($isEdit) $ui->add('<a class="sg-action" href="'.url('project/mainact/'.$mrs->tpid.'/addcalendar/'.$mrs->trid).'" data-rel="box" title="เพิ่มกิจกรรมย่อยของกิจกรรมหลัก" data-height="90%">เพิ่มกิจกรรมย่อยของกิจกรรมหลัก</a>');

	if ($isEdit) {
		$ui->add('<sep>');
		$ui->add('<a href="'.url('project/mainact/'.$mrs->tpid.'/move/'.$mrs->trid).'" class="sg-action" data-rel="box" title="กำหนดหรือเพิ่มวัตถุประสงค์">กำหนดหรือเพิ่มวัตถุประสงค์</a>');
		if ($isEditDetail && empty($mrs->totalCalendar)) $ui->add('<a class="sg-action" href="'.url('project/mainact/'.$mrs->tpid.'/remove/'.$mrs->trid).'" data-confirm="คุณกำลังลบกิจกรรมหลัก กรุณายืนยัน?" data-rel="this" data-removeparent="tr">ลบกิจกรรมหลัก</a>');
	}

	$submenu='<span class="iconset">'._NL;
	if ($isEdit) $submenu.=sg_dropbox('<form>ทำอะไร : <input type="text" /><br />เมื่อไหร่ : <input type="text" /><br />งบประมาณ : <input type="text" /></form>','{type:"box", class:"leftside -no-print add hover--menu",text:"Add", icon:"add", title:"เพิ่มกิจกรรมย่อยของกิจกรรมหลัก", url:"'.url('project/mainact/'.$mrs->tpid.'/addcalendar/'.$mrs->trid,array('type'=>'short')).'"}')._NL;

	$submenu.=sg_dropbox($ui->show('ul'),'{type:"hover",class:"leftside submenu -no-print"}')._NL;
	$submenu.='</span>'._NL;

	// เงื่อนไขของการแก้ไขงบประมาณของกิจกรรมหลักคือ ไม่มีการพัฒนาโครงการหรือพัฒนาโครงการก่อนปี 2558 ซึ่งจะไม่มีรหัสโครงการพัฒนา devtpid
	$row=array(
		$mrs->no,
		view::inlineedit(array('group'=>'info:mainact','fld'=>'detail1','tr'=>$mrs->trid, 'value'=>$mrs->title),SG\getFirst($mrs->title,'ระบุชื่อกิจกรรมหลัก'),$isEdit),
		view::inlineedit(array('group'=>'info:mainact','fld'=>'num1','tr'=>$mrs->trid, 'value'=>$mrs->budget,'ret'=>'money'),number_format($mrs->budget,2),!$project->proposalId && $isEdit),
		number_format($mrs->target),
		'<a href="'.url('project/mainact/'.$mrs->tpid.'/calendar/'.$mrs->trid).'" class="sg-action" data-rel="box" data-height="90%" title="ดูรายการกิจกรรมย่อย">'.($mrs->totalCalendar?$mrs->totalCalendar:'-').'</a>',
		$mrs->totalBudget?number_format($mrs->totalBudget,2):'-',
		'<a href="'.url('project/mainact/'.$mrs->tpid.'/calendar/'.$mrs->trid).'" class="sg-action" data-rel="box" data-height="90%" title="ดูรายการกิจกรรมย่อย">'.($mrs->totalActitity?$mrs->totalActitity:'-').'</a>',
		$mrs->totalExpense?number_format($mrs->totalExpense,2):'-',
		$submenu,
		'config'=>array('class'=>'mainact'),
	);
	return $row;
}

/**
* Display main activity detail
* @param Integer $tpid
* @param Integer $actid
* @return String
*/
function __project_mainact_detail($tpid,$actid) {
	$ret .= '<header class="header -box -hidden"><nav class="nav -back"><a class="sg-action" data-rel="back" href="javascript:void(0)"><i class="icon -material">arrow_back</i></a></nav><h3>รายละเอียดกิจกรรม</h3><nav><ul><li><a class="sg-action" href="'.url('project/mainact/'.$tpid.'/calendar/'.$actid).'" data-rel="box">กิจกรรมย่อย</a></li></ul></nav></header>';


	$tpid=mydb::select('SELECT `tpid` FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$actid)->tpid;
	$info=project_model::get_info($tpid);
	$mainact=$info->mainact[$actid];

	$dbs=mydb::select('SELECT ec.`name` expName, e.`trid`, e.`parent`, e.`gallery` `costid`, e.`num1` amt, e.`num2` `unitprice`, e.`num3` `times`, e.`num4` `total`, e.`detail1` `unitname`, e.`text1` detail FROM %project_tr% e LEFT JOIN %tag% ec ON ec.`taggroup`="project:expcode" AND ec.`catid`=e.`gallery` WHERE `tpid`=:tpid AND `formid`="develop" AND `part`="exptr" AND `parent`=:trid ORDER BY `trid` ASC',':tpid',$tpid,':trid',$actid);

	if ($mainact->parentObjective) {
		$forObj.='<ol style="padding:0 0 0 15px;">'._NL;
		foreach (explode('|', $mainact->parentObjective) as $item) {
			list($objId,$objTitle)=explode('=', $item);
			$forObj.='<li>'.$objTitle.'</li>'._NL;
		}
		$forObj.='</ol>'._NL;
	}

	$ret.='<h2>'.$mainact->title.'</h2>';

	$ret.='<h5>วัตถุประสงค์</h5>'._NL.$forObj;

	$ret.='<h5>กลุ่มเป้าหมาย</h5>
				<table class="item">
				<tr><th colspan="3">จำแนกตามช่วงวัย</th><th colspan="3">จำแนกกลุ่มเฉพาะ</th></tr>
				<tr>
					<td>เด็กเล็ก</td><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num3','tr'=>$mainact->trid, 'value'=>$mainact->targetChild, 'ret'=>'numeric','class'=>'w-10'),number_format($mainact->targetChild),$isEdit).'</td><td>คน</td>
					<td>คนพิการ</td><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num7','tr'=>$mainact->trid, 'value'=>$mainact->targetDisabled, 'ret'=>'numeric','class'=>'w-10'),number_format($mainact->targetDisabled),$isEdit).'</td><td>คน</td>
				</tr>
				<tr>
					<td>เด็กวัยเรียน</td><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num4','tr'=>$mainact->trid, 'value'=>$mainact->targetTeen, 'ret'=>'numeric','class'=>'w-10'),number_format($mainact->targetTeen),$isEdit).'</td><td>คน</td>
					<td>ผู้หญิง</td><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num8','tr'=>$mainact->trid, 'value'=>$mainact->targetWoman, 'ret'=>'numeric','class'=>'w-10'),number_format($mainact->targetWoman),$isEdit).'</td><td>คน</td>
				</tr>
				<tr>
					<td>วัยทำงาน</td><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num5','tr'=>$mainact->trid, 'value'=>$mainact->targetWork, 'ret'=>'numeric','class'=>'w-10'),number_format($mainact->targetWork),$isEdit).'</td><td>คน</td>
					<td>มุสลิม</td><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num9','tr'=>$mainact->trid, 'value'=>$mainact->targetMuslim, 'ret'=>'numeric','class'=>'w-10'),number_format($mainact->targetMuslim),$isEdit).'</td><td>คน</td>
				</tr>
				<tr>
					<td>ผู้สูงอายุ</td><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num6','tr'=>$mainact->trid, 'value'=>$mainact->targetElder, 'ret'=>'numeric','class'=>'w-10'),number_format($mainact->targetElder),$isEdit).'</td><td>คน</td>
					<td>แรงงาน</td><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num10','tr'=>$mainact->trid, 'value'=>$mainact->targetWorker, 'ret'=>'numeric','class'=>'w-10'),number_format($mainact->targetWorker),$isEdit).'</td><td>คน</td>
				</tr>
				<tr>
					<td></td><td></td><td></td>
					<td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'detail3','tr'=>$mainact->trid, 'value'=>$mainact->targetOtherDesc, 'class'=>'w-10'),$mainact->targetOtherDesc?$mainact->targetOtherDesc:'อื่น ๆ ระบุ...',$isEdit).'</td><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num11','tr'=>$mainact->trid, 'value'=>$mainact->targetOther, 'ret'=>'numeric','class'=>'w-10'),number_format($mainact->targetOther),$isEdit).'</td><td>คน</td>
				</tr>
				</table>
				';

	$ret.='<h5>รายละเอียดกิจกรรม</h5>'.sg_text2html($mainact->desc);

	$ret.='<h5>ระยะเวลาดำเนินงาน</h5><p>'.$mainact->timeprocess.'</p>';

	$ret.='<h5>ผลผลิต (Output) / ผลลัพธ์ (Outcome)</h5>'.sg_text2html($mainact->output);

	$ret.='<h5>ภาคีร่วมสนับสนุน (ระบุชื่อภาคีและวิธีการสนับสนุน เช่น งบประมาณ สิ่งของ การเข้าร่วมอื่นๆ)</h5>'.sg_text2html($mainact->copartner);

	$ret.='<h5>รายละเอียดงบประมาณ</h5>';

	$expTotal=0;
	$expTables->class='item project-develop-exp';
	$expTables->thead[]='ประเภท';
	$expTables->thead['amt amt']='จำนวน';
	$expTables->thead['amt unitprice']='บาท';
	$expTables->thead['amt times']='ครั้ง';
	$expTables->thead['amt total']='รวม(บาท)';
	if ($isEdit) $expTables->thead[]='';
	foreach ($dbs->items as $expItem) {
		unset($erow);
		$erow[]=$expItem->expName.($expItem->detail?'<p>'.$expItem->detail.'</p>':'');
		$erow[]=number_format($expItem->amt).' '.$expItem->unitname;
		$erow[]=number_format($expItem->unitprice);
		$erow[]=number_format($expItem->times);
		$erow[]=number_format($expItem->total);
		if ($isEdit) $erow[]='<span class="sg-dropbox click -no-print"><a href="#"><i class="icon -down"></i></a><div class="-hidden"><ul><li><a href="'.url('project/develop/plan/'.$tpid,array('action'=>'addexp','id'=>$expItem->parent,'expid'=>$expItem->trid)).'" class="sg-action" data-rel="box" title="แก้ไขรายละเอียดค่าใช้จ่าย">แก้ไขรายละเอียดค่าใช้จ่าย</a></li><li><a class="sg-action" href="'.url('project/develop/plan/'.$tpid,array('action'=>'removeexp','id'=>$expItem->parent,'expid'=>$expItem->trid)).'" data-confirm="คุณต้องการลบค่าใช้จ่ายนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="#project-develop-plan">ลบค่าใช้จ่าย</a></li></ul></div></span>';
		$expTables->rows[]=$erow;
		$expTotal+=$expItem->total;
	}
	$expTables->rows[]=array('<td colspan="4"><strong>รวมค่าใช้จ่าย</strong></td>','<strong>'.number_format($expTotal).'</strong>');
	$ret.=theme('table',$expTables);

	//$ret.=print_o($mainact,'$mainact');
	//$ret.=print_o($dbs,'$dbs');

	return $ret;
}

/**
* Display main activity information
* @param Integer $tpid
* @param Integer $actid
* @param Integer $calid
* @param Object $project
* @return String
*/
function __project_mainact_info($tpid,$calid,$xcalid,$project) {
	$isEdit=$project->isEdit;


	if ($isEdit) {
		$inlineAttr['class']='inline-edit box--fullbar';
		$inlineAttr['data-update-url']=url('project/edit/tr');
		$inlineAttr['data-current-url']=url('project/mainact/'.$tpid.'/info/'.$actid);
		$inlineAttr['data-refresh-url']=url('paper/'.$tpid);
		if (post('debug')) $inlineAttr['data-debug']='yes';
	} else {
		$inlineAttr['class']='box--fullbar';
	}
	$ret.='<div '.sg_implode_attr($inlineAttr).'>'._NL;
	if ($calid) {
		$calendar=project_model::get_calendar($tpid,NULL,NULL,$calid);
		$lockReport=$calendar->from_date<=$project->lockReportDate;
		$is_item_edit=$isEdit && !$lockReport;
		$ret.='<h4>กิจกรรม : '.$calendar->title.'</h4>';
		$tables=new table();
		$tables->rows[]=array('ชื่อกิจกรรม',$calendar->title);
		$tables->rows[]=array('วันที',sg_date($calendar->from_date,'ว ดด ปปปป').($calendar->to_date==$calendar->from_date?'':' - '.sg_date($calendar->to_date,'ว ดด ปปปป')).' เวลา '.$calendar->from_time.' - '.$calendar->to_time.' น.</strong>');
		$tables->rows[]=array('สถานที่',$calendar->location);
		$tables->rows[]=array('รายละเอียดกิจกรรมตามแผน',$calendar->title);
		$tables->rows[]=array('งบประมาณที่ตั้งไว้',$calendar->budget.' บาท');
		$tables->rows[]=array('จำนวนกลุ่มเป้าหมาย',$calendar->targetpreset.' คน');
		$ret.=$tables->show();

	}


	$ret.='</div><br /><br />';


	return $ret;
}

/**
* Move main activity to other objective
*
* @param Integer $trid
* @return String
*/
function __project_mainact_move($tpid,$trid) {
	$objective=project_model::get_tr($tpid,'info:objective');

	if ($oid=post('oid')) {
		$value=post('value');
		$ret.='oid='.$oid.' value='.post('value');
		if ($value==1) {
			$stmt='INSERT %project_tr% (`tpid`, `parent`, `gallery`, `formid`, `part`, `uid`, `created`) VALUES (:tpid, :parent, :gallery, "info", "actobj", :uid, :created)';
			mydb::query($stmt,':tpid',$tpid, ':parent',$oid, ':gallery',$trid, ':uid',i()->uid, ':created',date('U'));
		} else if ($value==0) {
			$stmt='DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `parent`=:parent AND `gallery`=:gallery AND `formid`="info" AND `part`="actobj" LIMIT 1';
			mydb::query($stmt,':tpid',$tpid, ':parent',$oid, ':gallery',$trid);
		}
		return $ret;
	}

	$mainact=project_model::get_main_activity($tpid);
	$rs=$mainact->info[$trid];

	$ret.='<h4>เปลี่ยน/เพิ่มวัตถุประสงค์</h4>';
	$form->config->variable='data';
	$form->config->method='post';
	$form->config->action=url('project/mainact/'.$tpid.'/move/'.$trid);

	$form->objective->type='checkbox';
	$form->objective->label='เลือกวัตถุประสงค์';
	foreach ($objective->items['objective'] as $item) {
		$form->objective->options[$item->trid]=$item->text1;
	}
	$form->objective->value=explode(',',$rs->parentObjectiveId);

	$form->closs='<a class="sg-action button" data-rel="close" href="javascript:joid(0)">เรียบร้อย</a>';

	$ret .= theme('form','project-edit-movemainact',$form);
	$ret.='<script type="text/javascript">
	$("input[name=\'data[objective]\'").change(function() {
		var url=$(this).closest("form").attr("action")
		$.get(url,{oid:$(this).val(),value:$(this).is(":checked")?1:0},function(html){
			notify("บันทึกเรียบร้อย",2000)
		})
	});
	</script>';
	//$ret.=print_o(post(),'post()');
	//$ret.=print_o($rs,'$rs');
	//$ret.=print_o($mainact,'$mainact');
	return $ret;
}

/**
* Add/edit mainact of project
*
* @param Integer $tpid
* @param Integer $objectId
* @return String
*/
function __project_mainact_add($tpid,$objectiveId) {
	$post=(object)post('data');
	if (!property_exists($post, 'objective') && $objectiveId) $post->objective[$objectiveId]=$objectiveId;
	$objective=project_model::get_tr($tpid,'info:objective');

	if ($post->title && $post->objective) {
		$post->tpid=$tpid;
		$post->uid=i()->uid;
		$post->parentObjectiveId=reset($post->objective);
		$post->sorder=mydb::select('SELECT MAX(`sorder`) maxOrder FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="mainact" LIMIT 1',':tpid',$tpid)->maxOrder+1;
		$post->formid='info';
		$post->part='mainact';
		$post->created=date('U');
		$stmt='INSERT INTO %project_tr%
						(`tpid`, `parent`, `uid`, `sorder`, `formid`, `part`, `num1`, `num2`, `detail1`, `text1`, `detail2`, `text3`, `text4`, `created`)
						VALUES
						(:tpid, :parentObjectiveId, :uid, :sorder, :formid, :part, :budget, :target, :title, :desc, :timeprocess, :output, :copartner, :created)';
		mydb::query($stmt,$post);
		$mainActId=mydb()->insert_id;
		//$ret.=mydb()->_query.'<br />';
		if (!mydb()->_error) {
			foreach ($post->objective as $parentObjectiveId) {
				$parentObjective->tpid=$tpid;
				$parentObjective->parentObjectiveId=$parentObjectiveId;
				$parentObjective->mainActId=$mainActId;
				$parentObjective->uid=i()->uid;
				$parentObjective->formid='info';
				$parentObjective->part='actobj';
				$parentObjective->created=date('U');
				$stmt='INSERT INTO %project_tr% (`tpid`, `parent`, `gallery`, `uid`, `formid`, `part`, `created`) VALUES (:tpid, :parentObjectiveId, :mainActId, :uid, :formid, :part, :created)';
				mydb::query($stmt,$parentObjective);
				//$ret.=mydb()->_query.'<br />';
			}
		}
		$ret.=__project_mainact_detail($tpid,$mainActId);
		return $ret;
	}

	$ret.='<h4>เพิ่มกิจกรรมหลัก (ตาม TOR)</h4>';
	$form->config->variable='data';
	$form->config->method='post';
	$form->config->action=url('project/mainact/'.$tpid.'/add/'.$objectiveId);
	$form->config->class='sg-form';
	$form->config->attr='data-rel="box" data-callback="refreshContent" data-refresh-url="'.url('paper/'.$tpid).'" data-done="close"';

	$form->objective->type='checkbox';
	$form->objective->label='เลือกวัตถุประสงค์ :';
	$form->objective->multiple=true;
	foreach ($objective->items['objective'] as $item) {
		$form->objective->options[$item->trid]=$item->text1;
	}
	$form->objective->value=$post->objective;

	$form->title->type='text';
	$form->title->label='ชื่อกิจกรรมหลัก (ตาม TOR)';
	$form->title->class='w-9';
	$form->title->require=true;
	$form->title->value=htmlspecialchars($post->title);

	$form->target->type='text';
	$form->target->label='กลุ่มเป้าหมาย (คน)';
	$form->target->value=htmlspecialchars($post->target);

	/*
	กลุ่มเป้าหมาย
	จำแนกตามช่วงวัย	จำแนกกลุ่มเฉพาะ
	เด็กเล็ก	0	คน 	คนพิการ	0	คน
	เด็กวัยเรียน	100	คน 	ผู้หญิง	0	คน
	วัยทำงาน	20	คน 	มุสลิม	0	คน
	ผู้สูงอายุ	0	คน 	แรงงาน	0	คน
				อื่น ๆ ระบุ...	0	คน
	*/

	$form->desc->type='textarea';
	$form->desc->label='รายละเอียดกิจกรรม';
	$form->desc->rows=3;
	$form->desc->value=htmlspecialchars($post->desc);

	$form->timeprocess->type='text';
	$form->timeprocess->label='ระยะเวลาดำเนินงาน';
	$form->timeprocess->value=htmlspecialchars($post->timeprocess);

	$form->output->type='textarea';
	$form->output->label='ผลผลิต (Output) / ผลลัพธ์ (Outcome)';
	$form->output->rows=3;
	$form->output->value=htmlspecialchars($post->output);


	$form->copartner->type='textarea';
	$form->copartner->label='ภาคีร่วมสนับสนุน';
	$form->copartner->rows=3;
	$form->copartner->description='ระบุชื่อภาคีและวิธีการสนับสนุน เช่น งบประมาณ สิ่งของ การเข้าร่วมอื่นๆ';
	$form->copartner->value=htmlspecialchars($post->copartner);

	$form->budget->type='text';
	$form->budget->label='งบประมาณ (บาท)';
	$form->budget->value=htmlspecialchars($post->budget);

	// รายละเอียดงบประมาณ


	$form->button->type='submit';
	$form->button->items->save=tr('Save');
	$form->button->posttext='<a class="sg-action" href="javascript:void(0)" data-rel="close">ยกเลิก</a>';

	$ret.=theme('form','project-edit-movemainact',$form);

	$ret.=print_o($post,'$post');
	/*
	if (empty($tpid) || mydb::select('SELECT `tpid` FROM %topic% WHERE `tpid`=:tpid AND `type`="project" LIMIT 1',':tpid',$tpid)->_empty) return 'No project';

	switch ($action) {
		case 'add' :
			$stmt='INSERT INTO %project_tr% (`tpid`, `parent`, `formid`, `part`, `uid`, `created`) VALUES (:tpid, :parent, :formid, :part, :uid, :created)';
			mydb::query($stmt,':tpid',$tpid, ':parent',$actid, ':uid', i()->uid, ':formid', 'info', ':part', 'mainact', ':created',date('U'));
			$ret['html'].='Add completed';
			break;
		case 'remove' :
			mydb::query('DELETE FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$actid);
			$ret['html'].='Remove complete';
			break;
		}
	*/
	return $ret;
}

/**
* Remove main activity and relate data
*
* @param Integer $tpid
* @param Integer $actid
* @return String
*/
function __project_mainact_remove($tpid,$actid,$project) {
	$ret.='<h4>ลบกิจกรรมหลัก</h4>';
	if (SG\confirm()) {
		$mainactTitle=mydb::select('SELECT `detail1` FROM %project_tr% WHERE `trid`=:actid LIMIT 1',':actid',$actid)->detail1;
		$stmt='DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `trid`=:actid AND (`flag` IS NULL OR `flag`=0) AND `formid`="info" AND `part`="mainact" LIMIT 1';
		mydb::query($stmt,':tpid',$tpid, ':actid',$actid);

		$stmt='DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `gallery`=:actid AND (`flag` IS NULL OR `flag`=0) AND `formid`="info" AND `part`="actobj"';
		mydb::query($stmt,':tpid',$tpid, ':actid',$actid);
		// Add log
		model::watch_log('project','remove mainact','ลบกิจกรรมหลัก '.$actid.' : ' .$mainactTitle,NULL,$tpid);
	}
	return $ret;
}

/**
* Add calendar to main activity
*
* @param Integer $tpid
* @param Integer $actid
* @return String
*/
function __project_mainact_addcalendar($tpid,$actid,$data,$project) {
	$formType=SG\getFirst(post('type'),'detail');

	//$mainact=project_model::get_main_activity($tpid,'owner')->info[$actid];

	if ($formType!='short') {
		$ret.='<h3 class="title">'.$mainact->title.'</h3>';
		$ret.='<div class="box--mainbar--no">'._NL;
		$ret.='<h5>'.($data?'แก้ไข':'เพิ่ม').'กิจกรรมย่อย</h5>';
	}

	if ($data) $post=$data;
	else $post=(object)post('calendar');

	if ($formType=='auto') {
		$post->tpid=$tpid;
		$post->mainact=$actid;
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
		$post->target=empty($post->target)?'':$post->target;
		$post->budget=abs(sg_strip_money($post->budget));
		if ($post->mainact<=0) $post->mainact=NULL;
		$stmt='INSERT INTO %project_activity%
					(
					`calid`, `calowner`, `mainact`, `targetpreset`
					, `targt_studentjoin`, `targt_teacherjoin`, `targt_parentjoin`, `targt_clubjoin`
					, `targt_localorgjoin`, `targt_govjoin`, `targt_otherjoin`
					, `target`, `budget`
					)
					VALUES
					(:calid, :calowner, :mainact, :targetpreset
					, :studentjoin, :teacherjoin, :parentjoin, :clubjoin
					, :localorgjoin, :govjoin, :otherjoin
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
					, `targt_otherjoin`=:otherjoin
					, `target`=:target
					, `budget`=:budget';
		mydb::query($stmt, $post);
		$ret.=mydb()->_query.'<br />';

		if ($post->color) property('calendar:color:'.$post->calid,$post->color);

		//$ret.=print_o($post,'$post');
		$stmt='DELETE FROM %project_actguide% WHERE `tpid`=:tpid AND `calid`=:calid AND `guideid` NOT IN (:guideidset)';
		mydb::query($stmt,':tpid',$tpid, ':calid',$post->calid, ':guideidset','SET:'.implode(',',$post->guideid));
		//$ret.=$post->calid.mydb()->_query.'<br />';

		foreach ($post->guideid as $key => $value) {
			$stmt='INSERT INTO %project_actguide% (`tpid`,`calid`,`guideid`) VALUES (:tpid,:calid,:guideid)
						ON DUPLICATE KEY UPDATE `guideid`=:guideid';
			mydb::query($stmt,':tpid',$tpid, ':calid',$post->calid, ':guideid',$value);
			//$ret.=mydb()->_query.'<br />';
		}

		$postExp=post('exp');
		foreach ($postExp as $expCode => $item) {
			$exp=(object)$item;
			//$ret.=print_o($exp,'$exp');
			if (empty($exp->detail) && empty($exp->expid)) {
				continue;
			} else if (empty($exp->detail) && $exp->expid) {
				// remove exp
				continue;
			}

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


		//location('project/mainact/'.$tpid.'/calendar/'.$actid);
		return $ret;
	}

	if (is_array($post)) $post=(object)$post;
	if (empty($post->tpid)) $post->tpid=$tpid;
	//if (empty($post->mainact)) $post->mainact=$actid;
	if (empty($post->title)) $post->title=$mainact->title;
	if (empty($post->objective)) $post->objective=$mainact->objectiveTitle;
	if (empty($post->detail)) $post->detail=$mainact->desc;
	if (empty($post->budget)) {
		$post->budget=$mainact->budget-$mainact->totalBudget;
		if ($post->budget<0) $post->budget=0;
	}
	if (empty($post->targetpreset)) $post->targetpreset=number_format($mainact->target);
	if (empty($post->from_date)) $post->from_date=date('j/n/Y');
	if (empty($post->to_date)) $post->to_date=$post->from_date;
	if (empty($post->from_time)) $post->from_time='09:00';
	if (empty($post->to_time)) {
		list($hr,$min)=explode(':',$post->from_time);
		$post->to_time=sprintf('%02d',$hr+1).':'.$min;
	}
	if (empty($post->privacy)) $post->privacy='public';

	list(,$month,$year)=explode('/',$post->from_date);

	$form->config->variable='calendar';
	$form->config->method='post';
	$form->config->action=url('project/mainact/'.$tpid.'/addcalendar/'.$actid);
	$form->config->class='sg-form';
	$form->config->attr='data-rel="box" data-callback="refreshContent" data-refresh-url="'.url('paper/'.$tpid).'"'.($formType=='short'?' data-done="close"':' data-done="close"');

	$form->act=array('type'=>'hidden','name'=>'act','value'=>'add');

	if ($post->id) $form->id=array('type'=>'hidden','value'=>$post->id);
	if ($post->tpid) $form->tpid=array('type'=>'hidden','value'=>$post->tpid);

	$form->type=array('type'=>'hidden','name'=>'type','value'=>$formType);
	//$form->mainact=array('type'=>'hidden','value'=>$actid);
	$form->privacy=array('type'=>'hidden','value'=>'public');
	$form->calowner=array('type'=>'hidden','value'=>1);

	$form->title->type='text';
	$form->title->label=sg_client_convert('ทำอะไร');
	$form->title->maxlength=255;
	$form->title->size=60;
	$form->title->require=true;
	$form->title->placeholder='ระบุชื่อกิจกรรม';
	$form->title->value=htmlspecialchars($post->title);

	for ($hr=7;$hr<24;$hr++) {
		for ($min=0;$min<60;$min+=30) {
			$times[]=sprintf('%02d',$hr).':'.sprintf('%02d',$min);
		}
	}

	$form->date->type='textfield';
	$form->date->label=sg_client_convert('เมื่อไหร่');
	$form->date->require=true;
	$form->date->value='<input type="text" name="calendar[from_date]" id="edit-calendar-from_date" maxlength="10" class="sg-datepicker form-text require" style="width:80px;" value="'.htmlspecialchars($post->from_date).'"> <select name="calendar[from_time]" id="edit-calendar-from_time">';
	foreach ($times as $time) $form->date->value.='<option value="'.$time.'"'.($time==$post->from_time?' selected="selected"':'').'>'.$time.'</option>';
	$form->date->value.='</select>
	ถึง <select name="calendar[to_time]" id="edit-calendar-to_time">';
	foreach ($times as $time) $form->date->value.='<option value="'.$time.'"'.($time==$post->to_time?' selected="selected"':'').'>'.$time.'</option>';
	$form->date->value.='</select>
	<input type="text" name="calendar[to_date]" id="edit-calendar-to_date" maxlength="10" class="sg-datepicker form-text require" style="width:80px;" value="'.htmlspecialchars($post->to_date).'">';

	$form->areacode=array('type'=>'hidden','value'=>$post->areacode);
	$form->latlng=array('type'=>'hidden','value'=>$post->latlng);




	$objList=mydb::select('SELECT `trid`,`detail1` `objTitle` FROM %project_tr% WHERE `tpid` IS NULL AND `formid`="info" AND `part`="objective"');
	$form->guideid->type='checkbox';
	$form->guideid->label='สนับสนุนวัตถุประสงค์';
	$form->guideid->multiple=true;
	$form->guideid->require=true;
	foreach ($objList->items as $item) {
		$form->guideid->options[$item->trid]=$item->objTitle;
	}
	if (!$post->guideid) {
		$objSelect=mydb::select('SELECT * FROM %project_actguide% WHERE `tpid`=:tpid AND `calid`=:calid',':tpid',$tpid, ':calid',$post->id);
	}
	foreach ($objSelect->items as $item) {
		$form->guideid->value[$item->guideid]=$item->guideid;
	}
	if (!$form->guideid) $form->guideid->posttext='<p class="notify">กรุณาระบุการสนับสนุนวัตถุประสงค์ของกิจกรรมนี้</p>';
	//$ret.=print_o($form->guideid->value,'$form->guideid->value');

	$form->location->type=$formType=='short'?'hidden':'text';
	$form->location->label='ที่ไหน';
	$form->location->maxlength=255;
	$form->location->size=60;
	$form->location->placeholder='ระบุสถานที่ หมู่ที่ ตำบล';
	$form->location->value=htmlspecialchars($post->location);
	$form->location->class="sg-address";
	$form->location->attr='data-altfld="edit-calendar-areacode"';
	//$form->location->posttext=' <a href="javascript:void(0)" id="calendar-addmap">แผนที่</a><div id="calendar-mapcanvas" class="-hidden"></div>';

	$form->detail->type=$formType=='short'?'hidden':'textarea';
	$form->detail->label='รายละเอียดกิจกรรมตามแผน';
	$form->detail->rows=3;
	$form->detail->cols=60;
	$form->detail->placeholder='ระบุรายละเอียดของกิจกรรมที่วางแผนว่าจะทำ';
	$form->detail->value=$post->detail;

	$form->joinlist->label='กลุ่มเป้าหมาย/ผู้มีส่วนร่วม/ผู้สนับสนุนที่เข้าร่วมกิจกรรม';
	$form->joinlist->type='textfield';
	$joinListTable=new table('item -table');
	$joinListTable->thead=array('กลุ่มเป้าหมาย','amt'=>'จำนวนคน');
	$joinListTable->rows[]=array('<td class="subheader" colspan="2">กลุ่มเป้าหมายที่เข้าร่วม');
	foreach (cfg('project.target') as $key => $value) {
		$joinListTable->rows[]=array($value,'<input class="form-text -numeric" type="text" name="calendar['.$key.']" size="5" value="'.$post->{'targt_'.$key}.'" /> คน');
	}

	$joinListTable->rows[]=array('<td class="subheader" colspan="2">ผู้มีส่วนร่วม/ผู้สนับสนุน');
	foreach (cfg('project.support') as $key => $value) {
		$joinListTable->rows[]=array($value,'<input class="form-text -numeric" type="text" name="calendar['.$key.']" size="5" value="'.$post->{'targt_'.$key}.'" /> คน');
	}
	$form->joinlist->value=$joinListTable->show();

	$stmt='SELECT `tpid`,`trid`,`parent`,`gallery` `expcode`,`num1` `amt`, `num2` `unitprice`, `num3` `times`, `num4` `total`,`detail1` `unitname`, `text1` `detail`
				FROM %project_tr%
				WHERE `tpid`=:tpid AND `formid`="develop" AND `part`="exptr" AND `calid`=:calid ';
	foreach (mydb::select($stmt,':tpid',$tpid,':calid',$data->calid)->items as $item) $expTr[$item->expcode]=$item;

	$expCategoty=model::get_category('project:expcode');
	$tables=new table();
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
	$tables->tfoot[]=array('รวมงบประมาณที่ตั้งไว้',$post->budget);
	$form->exp=$tables->show();

	/*
	$post->unitprice=SG\getFirst($post->unitprice,0);
	$post->times=SG\getFirst($post->times,1);
	$post->amt=SG\getFirst($post->amt,1);
	$post->total=SG\getFirst($post->total,0);

	$form->config->variable='exp';
	$form->config->method='post';
	$form->config->action=url(q());
	$form->config->class='sg-form';
	$form->config->attr=array(
		'data-rel'=>'#project-develop-plan',
		'onsubmit'=>'$.colorbox.close()',
	);

	$form->action=array('type'=>'hidden','name'=>'action','value'=>'addexp');
	$form->id=array('type'=>'hidden','value'=>$actid);
	$form->expid=array('type'=>'hidden','value'=>$expid);
	$form->expcode=array('type'=>'select','label'=>'ประเภทรายจ่าย','options'=>model::get_category('project:expcode','catid'),'class'=>'w-9','value'=>$post->expcode);
	$form->amt=array('type'=>'text','label'=>'จำนวนหน่วย','class'=>'w-9','placeholder'=>0,'value'=>$post->amt);
	$form->unitname=array('type'=>'select','label'=>'หน่วยนับ','options'=>array('คน'=>'คน','ครั้ง'=>'ครั้ง','เที่ยว'=>'เที่ยว','ชิ้น'=>'ชิ้น','ชุด'=>'ชุด'),'class'=>'w-9','value'=>$post->unitname);
	$form->unitprice=array('type'=>'text','label'=>'ค่าใช้จ่ายต่อหน่วย (บาท)','class'=>'w-9','placeholder'=>0,'value'=>$post->unitprice);
	$form->times=array('type'=>'text','label'=>'จำนวนครั้งกิจกรรม','class'=>'w-9','value'=>1,'value'=>$post->times);
	$form->total=array('type'=>'text','label'=>'รวมเงิน','class'=>'w-9','placeholder'=>0,'value'=>$post->total,'readonly'=>true);
	$form->detail=array('type'=>'textarea','label'=>'รายละเอียดค่าใช้จ่าย','class'=>'w-9','rows'=>3,'value'=>$post->detail);


	$ret .= theme('form','project-edit-exp',$form);
	$ret.='<script>
	$("#project-edit-exp input").keyup(function(){
		var total=0
		var amt=parseFloat($("#edit-exp-amt").val().replace(/,/g, ""))
		var unitprice=parseFloat($("#edit-exp-unitprice").val().replace(/,/g, ""))
		var times=parseFloat($("#edit-exp-times").val().replace(/,/g, ""))
		total=amt*unitprice*times
		$("#edit-exp-total").val(total)
	});
	</script>';
	*/
	/*
	$form->targetpreset->type=$formType=='short'?'hidden':'text';
	$form->targetpreset->label='จำนวนกลุ่มเป้าหมาย (คน)';
	$form->targetpreset->size=10;
	$form->targetpreset->maxlength=7;
	$form->targetpreset->placeholder=0;
	$form->targetpreset->value=$post->targetpreset;

	$form->target->type=$formType=='short'?'hidden':'textarea';
	$form->target->label='รายละเอียดกลุ่มเป้าหมาย';
	$form->target->rows=2;
	$form->target->placeholder='ระบุรายละเอียดของกลุ่มเป้าหมายที่จะเข้าร่วม';
	$form->target->value=$post->target;
	*/

	$form->color->type='colorpicker';
	$form->color->label='สีของกิจกรรม';
	$form->color->color='Red, Green, Blue, Black, Purple, Aquamarine, Aqua, Chartreuse,Coral, DarkGoldenRod, Olive, Teal, HotPink, Brown';
	$form->color->value=$post->color;

	$form->button->type='submit';
	$form->button->items->save=tr('Save');
	$form->button->posttext=$formType=='short'?'<a class="sg-action" data-rel="close" href="">ยกเลิก</a>':'<a class="sg-action" href="'.url('project/mainact/'.$tpid.'/calendar/'.$actid).'" data-rel="box">ยกเลิก</a>';

	if ($para->module) 	$form=do_class_method($para->module.'.extension','calendar_form', $form, $post, $para);
	$ret .= theme('form','edit-calendar',$form);

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

	if ($formType!='short') {
		$ret.='</div>';

		//$ret.='<div class="box--sidebar">';
		//$ret.=__project_mainact_listcalendar($tpid,$actid,$project);
		//$ret.=__project_mainact_detail($tpid,$actid);
		//$ret.='</div>';
	}

	$ret.='<script type="text/javascript">
	var from=$("#edit-calendar-from_date").val().split("/");
	var to=$("#edit-calendar-to_date").val().split("/");
	var fromDate=new Date(from[2],from[1]-1,from[0]);
	var toDate=new Date(to[2],to[1]-1,to[0]);

	var minutes = 1000*60;
	var hours = minutes*60;
	var days = hours*24;

	var diff_date = Math.round((toDate - fromDate)/days);

	$("#edit-calendar-from_date").change(function() {
		var from=$(this).val().split("/");
		toDate=new Date(from[2],from[1]-1,from[0]);
		toDate.setDate(toDate.getDate()+diff_date);
		$("#edit-calendar-to_date").val($.datepicker.formatDate("dd/mm/yy",toDate));
	});
	$("#edit-calendar-to_date").change(function() {
		from=$("#edit-calendar-from_date").val().split("/");
		to=$("#edit-calendar-to_date").val().split("/");
		fromDate=new Date(from[2],from[1]-1,from[0]);
		toDate=new Date(to[2],to[1]-1,to[0]);
		diff_date = Math.round((toDate - fromDate)/days);
	});

  setTimeout(function() { $("#edit-calendar-title").focus() }, 500);
	var gis='.json_encode($gis).'
	</script>';
	return $ret;
}

function __project_mainact_listcalendar($tpid,$actid,$project) {
	$isEdit=$project->isEdit;
	$ret.='<h4>กิจกรรมย่อยตามปฏิทินกิจกรรมของโครงการ</h4>';
	$stmt='SELECT a.*, c.*
						, r.`trid` `reportId`
					FROM %project_activity% a
						LEFT JOIN %calendar% c ON c.`id`=a.`calid`
						LEFT JOIN %project_tr% r ON r.`calid`=a.`calid` AND r.`formid`="activity" AND r.`part`="owner"
					WHERE `mainact`=:mainact AND a.`calowner`=1
					ORDER BY c.`from_date` ASC';
	$dbs=mydb::select($stmt,':mainact',$actid);

	$tables->class='item';
	$tables->thead=array('date'=>'วันที่','กิจกรรม','amt'=>'กลุ่มเป้าหมาย(คน)','money'=>'งบประมาณ(บาท)','');
	$no=0;
	$target=0;
	$budget=0;
	foreach ($dbs->items as $rs) {
		$lockReport=$rs->from_date<=$project->lockReportDate;
		$is_item_edit=$isEdit && !$lockReport;

		$ui=new ui();
		$ui->add('<a class="sg-action" href="'.url('project/mainact/'.$tpid.'/calendar/'.$actid.'/'.$rs->calid).'" data-rel="box">รายละเอียด</a>');
		if ($is_item_edit) {
			$ui->add('<a class="sg-action" href="'.url('project/mainact/'.$tpid.'/editcalendar/'.$actid.'/'.$rs->calid).'" data-rel="box">แก้ไข</a>');
			$ui->add($rs->reportId?'<a class="disabled" href="javascript:void(0)" title="ไม่สามารถลบปฏิทินกิจกรรมได้เนื่องจากมีการบันทึกกิจกรรมเรียบร้อยแล้ว">[ลบปฏิทินกิจกรรมไม่ได้]</a>':'<a class="sg-action" href="'.url('project/mainact/'.$tpid.'/removecalendar/'.$rs->calid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบปฏิทินกิจกรรมนี้ กรุณายืนยัน?" data-callback="refreshContent" data-refresh-url="'.url('paper/'.$tpid).'">ลบ</a>');
		}
		$submenu=sg_dropbox($ui->show('ul'));

		$tables->rows[]=array(
												sg_date($rs->from_date,'ว ดด ปป'),
												view::inlineedit(array('group'=>'calendar','fld'=>'title','tr'=>$rs->calid,'class'=>'w-9'),$rs->title,$is_item_edit),
												view::inlineedit(array('group'=>'activity','fld'=>'targetpreset','tr'=>$rs->calid,'ret'=>'numeric'),number_format($rs->targetpreset),$is_item_edit,'text'),
												view::inlineedit(array('group'=>'activity','fld'=>'budget','tr'=>$rs->calid,'ret'=>'numeric','callback'=>'refreshContent'),number_format($rs->budget,2),$is_item_edit,'text'),
												$submenu,
												);
		$budget+=$rs->budget;
		$target+=$rs->targetpreset;
	}
	$tables->tfoot[]=array(
												'<td colspan="2" align="center">รวม '.$dbs->_num_rows.' กิจกรรม</td>',
												'<td align="center"><strong>'.number_format($target).'</strong></td>',
												'<td align="right"><strong>'.number_format($budget,2).'</strong></td>',
												''
											);
	$ret.=theme('table',$tables);
	return $ret;
}

/**
 * Add/edit objective of project
 *
 * @param Integer $actid
 * @return Location
 */
function _objective($action,$actid) {
	if ($action=='remove') {
		$tpid=mydb::select('SELECT `tpid` FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$actid)->tpid;
	} else {
		$tpid=$actid;
	}

	if ( ! (user_access('administer projects') || project_model::is_trainer_of($tpid) || project_model::is_owner_of($tpid)) ) return 'Access denied';
	if (empty($tpid) || mydb::select('SELECT `tpid` FROM %topic% WHERE `tpid`=:tpid AND `type`="project" LIMIT 1',':tpid',$tpid)->_empty) return 'No project';

	switch ($action) {
		case 'add' :
			$stmt='INSERT INTO %project_tr% (`tpid`, `uid`, `formid`, `part`, `created`) VALUES (:tpid, :uid, :formid, :part, :created)';
			mydb::query($stmt,':tpid',$tpid, ':uid', i()->uid, ':formid', 'info', ':part', 'objective', ':created',date('U'));
			$ret['html'].='Add completed';
			break;
		case 'remove' :
			mydb::query('DELETE FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$actid);
			$ret['html'].='Remove complete';
			break;
		}
	location('paper/'.$tpid);
	return $ret;
}
?>