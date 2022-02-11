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
function project_mainact($self,$tpid,$action=NULL,$actid=NULL,$subid=NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid);
	$tpid = $projectInfo->tpid;

	if (!$tpid) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

	$mainact=project_model::get_main_activity($tpid,'owner')->info[$actid];

	$action=SG\getFirst($action,post('act'));


	if (empty($tpid)) return '<p class="notify">ไม่มีข้อมูลกิจกรรมหลักตามที่ระบุ</p>';


	$project=project_model::get_project($tpid);
	if ($project->_empty) return '<p class="notify">ไม่มีโครงการตามที่ระบุ</p>';

	$isAdmin=$project->isAdmin;
	$isEdit=$project->isEdit;
	$isEditDetail=$project->isEditDetail;
	$isAccessActivityExpense=user_access('access activity expense') || $isOwner;

	$lockReportDate=$project->lockReportDate;

	if (post('gr')) {
		setcookie('maingrby',post('gr'),time()+10*365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));
	}
	$groupBy=SG\getFirst(post('gr'),$_COOKIE['maingrby'],'act');

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
			$ret.=__project_mainact_info($tpid,$actid,$subid,$project);
			return $ret;
			break;

		case 'addcalendar' :
			if ($isEdit) $ret.=__project_mainact_addcalendar($tpid,$actid,$data,$project);
			return $ret;
			break;

		case 'editcalendar':
			if ($isEdit && $subid) {
				$stmt='SELECT * FROM %calendar% c LEFT JOIN %project_activity% a ON a.`calid`=c.`id` WHERE `id`=:calid LIMIT 1';
				$data=mydb::select($stmt,':calid',$subid);
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
			if ($isEdit && $subid) {
				$calendarTitle=mydb::select('SELECT `title` FROM %calendar% WHERE `id`=:id LIMIT 1',':id',$subid)->title;
				mydb::query('DELETE FROM %calendar% WHERE `id`=:calid LIMIT 1',':calid',$subid);
				mydb::query('DELETE FROM %project_activity% WHERE `calid`=:calid LIMIT 1',':calid',$subid);
				// Add log
				model::watch_log('project','Calendar remove','ลบกิจกรรมย่อย '.$subid.' กิจกรรมหลัก '.$actid.' : ' .$calendarTitle,NULL,$tpid);
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

	foreach(mydb::select('SELECT `catid`,`name` FROM %tag% WHERE `taggroup`="project:objtype" ORDER BY `catid` ASC')->items as $item) $objTypeList[$item->catid]=$item->name;
	$info=project_model::get_info($tpid);

	// Show project objective
	if (empty($info->objective)) $ret.='<p class="notify">ยังไม่มีการกำหนดวัตถุประสงค์ของโครงการ</p>';

	//$ret.=print_o($objTypeList,'$objTypeList');
	//$ret.=print_o($info,'$info');

	// Show objective when group by main activity

	// Show main activities
	if ($groupBy=='obj') {
		$objectiveNo=0;
		$totalMainActBudget=0;
		foreach ($info->objective as $irs) {
			//$ret.=print_o($irs,$irs);
			unset($row);
			$ret.='<div id="project-objective-'.$irs->trid.'" class="project-objective">'._NL;
			$ret.='<h4>วัตถุประสงค์ ข้อที่ '.(++$objectiveNo).'</h4>'._NL;
			$ret.='<h5>วัตถุประสงค์ : </h5>'._NL;
			$ret.=view::inlineedit(array('group'=>'info:objective', 'fld'=>'text1', 'tr'=>$irs->trid, 'class'=>'project-object-field', 'button'=>'yes' ), $irs->title, $isEditDetail, 'textarea')._NL;
			$ret.='<h5>ตัวชี้วัด</h5>'._NL;
			$ret.=view::inlineedit(array('group'=>'info:objective','fld'=>'text2','tr'=>$irs->trid, 'button'=>'yes', 'ret'=>'html'),$irs->indicator,$isEditDetail,'textarea')._NL;
			$ret.='<h5>กิจกรรม</h5>'._NL;

			$j=0;
			$totalBudget=$totalTarget=$totalActivity=$totalActivityBudget=0;
			$totalDone=$totalExpend=0;

			// Show main activity each objective
			$tables = new Table();
			$tables->addClass('project-mainact-items');
			$tables->thead=array('no'=>'','ชื่อกิจกรรมหลัก','money'=>'งบประมาณ<br />(บาท)','amt target'=>'กลุ่มเป้าหมาย<br />(คน)','amt calendar'=>'กิจกรรม<br />(ครั้ง)','money budget'=>'งบกิจกรรม<br />(บาท)','amt done'=>'ทำแล้ว<br />(ครั้ง)','money expend'=>'ใช้จ่ายแล้ว<br />(บาท)','icons -c1'=>'');
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
			$ret .= $tables->build();

			// Show notify and add main activity button for each objective
			if ($totalBudget!=$totalActivityBudget) $ret.='<p class="notify">คำเตือน : รวมงบประมาณของกิจกรรม ('.number_format($totalBudget,2).' บาท) ไม่เท่ากับ รวมงบประมาณของกิจกรรมทุกครั้ง ('.number_format($totalActivityBudget,2).' บาท)</p>';
			if ($isEdit) {
				$ret.='<p>';
				if ($isEditDetail) $ret.='<a class="sg-action btn -primary" href="'.url('project/mainact/'.$tpid.'/add/'.$irs->trid).'" data-rel="box">+เพิ่มกิจกรรมหลักตาม TOR</a> ';

				// Show delete button on empty objective
				if ($isEdit && empty($irs->title) && empty($irs->indicator) && empty($tables->rows)) {
					$ret.='<a class="sg-action button" href="'.url('project/edit/objective/remove/'.$irs->trid).'" data-confirm="ต้องการลบวัตถุประสงค์ ข้อที่ '.$objectiveNo.' จริงหรือไม่?" data-rel="this" data-removeparent="div">ลบวัตถุประสงค์ข้อที่ '.$objectiveNo.'</a>';
				}
				$ret.='</p>';
				$ret.='<p>( ชื่อกิจกรรมจะต้องเป็นชื่อกิจกรรมที่ระบุไว้ใน TOR เท่านั้น ส่วนกิจกรรมย่อยแต่ละครั้งสามารถเพิ่มได้จาก ปุ่ม <span style="font-size:1.8em;">'._CHAR_3DOTS.'</span> หลังกิจกรรมหลักแต่ละรายการ หรือ ใน <a href="'.url('project/'.$tpid.'/info.calendar').'">ปฏิทินโครงการ</a> )</p>';
			}
			$ret.='</div>'._NL;
		}

		// Show notify and add new objective button
		if ($totalMainActBudget!=$info->summary->budget) $ret.='<p class="notify">คำเตือน : งบประมาณของโครงการ ('.number_format($info->summary->budget,2).' บาท) ไม่เท่ากับ รวมงบประมาณของทุกกิจกรรมหลัก ('.number_format($totalMainActBudget,2).' บาท)</p>';

		// Show button add objective
		if ($isEditDetail && empty($info->project->proposalId)) {
			$ret.='<p><a class="sg-action button floating" data-rel="#main" href="'.url('project/edit/objective/add/'.$tpid).'" confirm="ต้องการเพิ่มวัตถุประสงค์ ข้อที่ '.($objectiveNo+1).' ใช่หรือไม่?">+เพิ่มวัตถุประสงค์ ข้อที่ '.($objectiveNo+1).'</a></p>';
		}
	} else {
		// Show main activity by timeline
		$ret.='<div id="project-objective-1" class="project-objective">'._NL;
		$ret.='<h4>วัตถุประสงค์</h4>'._NL;
		$objectiveNo=0;

		$tables = new Table();
		$tables->addClass('item__card project-develop-objective');
		$tables->colgroup=array('objectiv'=>'width="50%"','indicator'=>'width="50%"');
		$tables->thead=array('วัตถุประสงค์ / เป้าหมาย','ตัวชี้วัดความสำเร็จ','icons -c1'=>'');
		foreach ($objTypeList as $objTypeId => $objTypeName) {
			if ($objTypeId==1) $tables->rows[]='<tr><th colspan="3"><h4>วัตถุประสงค์โดยตรง</h4></th></tr>';
			else if ($objTypeId==2) $tables->rows[]='<tr><th colspan="3"><h4>วัตถุประสงค์โดยอ้อม</h4></th></tr>';

			if ($objTypeId!=1) $tables->rows[]=array('<td colspan="3"><strong>'.$objTypeName.'</strong></td>');
			foreach ($info->objective as $objective) {
				if ($objective->objectiveType!=$objTypeId) continue;

				// Create submenu
				if ($isEdit) {
					$ui=new ui();
					//$ui->add('<a href="'.url('project/develop/objective/'.$tpid,array('action'=>'move','id'=>$objective->trid)).'" class="sg-action" data-rel="box" title="ย้ายวัตถุประสงค์">ย้ายวัตถุประสงค์</a>');
					$ui->add('<a class="sg-action" href="'.url('project/objective/'.$tpid.'/remove/'.$objective->trid).'" data-confirm="คุณต้องการลบวัตถุประสงค์นี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="this" data-removeparent="tr"><i class="icon -cancel -gray"></i><span>ลบวัตถุประสงค์</span></a>');
					$submenu=sg_dropbox($ui->build('ul'));
				}

				$tables->rows[]=array(
													'<label>วัตถุประสงค์ข้อที่ '.(++$objectiveNo).' </label>'.view::inlineedit(array('group'=>'tr:info:objective', 'fld'=>'text1', 'tr'=>$objective->trid), $objective->title, $isEdit, 'textarea'),
													'<label>ตัวชี้วัดความสำเร็จ</label>'.view::inlineedit(array('group'=>'tr:info:objective','fld'=>'text2','tr'=>$objective->trid, 'ret'=>'html'),$objective->indicator,$isEdit,'textarea'),
													$submenu,
												);
			}
		}

		$ret .= $tables->build();

		// Not allow to add new objective when project is from development
		if ($isEditDetail && empty($info->project->proposalId)) {
			$ret.='<p><a class="sg-action btn -primary" data-rel="#main" href="'.url('project/edit/objective/add/'.$tpid).'" data-confirm="ต้องการเพิ่มวัตถุประสงค์ ข้อที่ '.($objectiveNo+1).' ใช่หรือไม่?"><i class="icon -addbig -white"></i><span>เพิ่มวัตถุประสงค์ ข้อที่ '.($objectiveNo+1).'</span></a></p>';

		//			$tables->rows[]=array('<td colspan="4"><a class="sg-action button floating -no-print" data-rel="#project-develop-objective" href="'.url('project/edit/objective/add/'.$tpid).'" data-rel="none" data-confirm="ต้องการเพิ่มวัตถุประสงค์ ข้อที่ '.($objectiveNo+1).' ใช่หรือไม่?">+เพิ่มวัตถุประสงค์ ข้อที่ '.($objectiveNo+1).'</a></td>');
		}




		// Show activity plan
		$ret.='<h4>กิจกรรมหลัก</h4>';
		$tables = new Table();
		$tables->addClass('project-mainact-items');
		$tables->thead=array('date no'=>'วันที่ทำกิจกรรม','title'=>'ชื่อกิจกรรมหลัก','money'=>'งบประมาณ<br />(บาท)','amt target'=>'กลุ่มเป้าหมาย<br />(คน)','amt calendar'=>'กิจกรรม<br />(ครั้ง)','money budget'=>'งบกิจกรรม<br />(บาท)','amt done'=>'ทำแล้ว<br />(ครั้ง)','money expend'=>'ใช้จ่ายแล้ว<br />(บาท)','icons -c2'=>'');
		foreach ($info->mainact as $mrs) {
			//$ret.=print_o($mrs,'$mrs');
			$mrs->no=++$j;
			$tables->rows[]=__project_mainact_show_mainactivity_item($project,$mrs,$isEdit,$isEditDetail);
			if (!$mrs->trid) continue;
			$isSubCalendar=false;
			foreach ($info->calendar[$mrs->trid] as $crs) {
				$isSubCalendar=true;
				$isEditCalendar=$isEdit && $crs->from_date>$lockReportDate;

				if ($crs->from_date==$crs->to_date) $actionDate= sg_date($crs->from_date,'ว ดด ปป');
				else if (sg_date($crs->from_date,'Y-m')==sg_date($crs->to_date,'Y-m')) $actionDate=sg_date($crs->from_date,'ว').'-'.sg_date($crs->to_date,'ว').' '.sg_date($crs->from_date,'ดด ปป');
				else $actionDate=sg_date($crs->from_date,'ว ดด ปป').'-'.sg_date($crs->to_date,'ว ดด ปป');

				$tables->rows[]=array(
					$isEditCalendar?'<a class="sg-action inline-edit-field" href="'.url('project/mainact/'.$tpid.'/editcalendar/'.$crs->mainact.'/'.$crs->id).'" data-rel="box" data-type="link" title="คลิกเพื่อแก้ไข">'.$actionDate.'</a>':$actionDate,
					view::inlineedit(array('group'=>'calendar','fld'=>'title','tr'=>$crs->id, 'class'=>'-fill', 'value'=>$crs->title),$crs->title,$isEditCalendar),
					'',
					view::inlineedit(array('group'=>'activity','fld'=>'targetpreset','tr'=>$crs->id, 'class'=>'-fill', 'value'=>$crs->targetpreset,'ret'=>'numeric'),$crs->targetpreset,$isEditCalendar),
					'',
					 $isAccessActivityExpense ? view::inlineedit(array('group'=>'activity','fld'=>'budget','tr'=>$crs->id, 'class'=>'-fill', 'value'=>$crs->budget,'ret'=>'money','callback'=>'refreshContent'),$crs->budget,$isEditCalendar) : '-',
					$crs->activityId?'<a href="'.url('project/'.$tpid.'/owner#tr-'.$crs->activityId).'" title="บันทึกหมายเลข '.$crs->activityId.'">✔</a>':'',
					$crs->exp_total && $isAccessActivityExpense?number_format($crs->exp_total,2):'-',
					$isEditCalendar?'<span class="hover--menu iconset"><a class="sg-action" href="'.url('project/mainact/'.$tpid.'/editcalendar/'.$crs->mainact.'/'.$crs->id).'" data-rel="box" title="แก้ไขรายละเอียดกิจกรรมย่อย"><i class="icon -edit"></i></a>'.($crs->activityId?'':' <a class="sg-action" href="'.url('project/mainact/'.$tpid.'/removecalendar/'.$crs->mainact.'/'.$crs->id).'" data-rel="#project-objective-wrapper" data-ret="'.url('project/mainact/'.$tpid).'" data-confirm="ลบกิจกรรมย่อย กรุณายืนยัน" title="ลบกิจกรรมย่อย"><i class="icon -delete"></i></a>'):'',
					'config'=>array('class'=>'calendar')
				);
			}
			if ($isEdit && !$isSubCalendar) $tables->rows[]=array('','<td colspan="8">ยังไม่ได้กำหนดกิจกรรมย่อย กรุณา<a class="sg-action" href="'.url('project/mainact/'.$tpid.'/addcalendar/'.$mrs->trid).'" data-rel="box">เพิ่มกิจกรรมย่อย</a>อย่างน้อย 1 กิจกรรม</td>','config'=>array('class'=>'calendar'));
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
		if ($noMainAct) $tables->rows[]='<tr><td colspan="9"><h4>กิจกรรมย่อยที่ยังไม่กำหนดกิจกรรมหลัก'.($isEdit?' (กรุณาแก้ไขหรือลบทิ้ง)':'').'</h4></td></tr>';

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
					$rs->budget,
					'',
					'',
					$isEdit?'<span class="hover--menu iconset"><a class="sg-action" href="'.url('calendar/'.$rs->id.'/edit/tpid/'.$tpid.'/module/project').'" data-rel="box" title="แก้ไขรายละเอียดกิจกรรมย่อย"><i class="icon -edit"></i></a>'.($rs->activityId?'':' <a class="sg-action" href="'.url('project/mainact/'.$tpid.'/removecalendar/XXXXX/'.$rs->id).'" data-rel="#project-objective-wrapper" data-ret="'.url('project/mainact/'.$tpid).'" data-confirm="ลบกิจกรรมย่อย กรุณายืนยัน" title="ลบกิจกรรมย่อย"><i class="icon -delete"></i></a>'):'',
						'config'=>array('class'=>'calendar error')
				);
			}
		}

		$tables->tfoot[]=array(
			'',
			'รวม',
			number_format($info->summary->budget,2),
			number_format($info->summary->target),
			number_format($info->summary->calendar),
			number_format($info->summary->totalBudget,2),
			number_format($info->summary->activity),
			number_format($info->summary->expense,2),
			'',
		);

		$ret .= $tables->build();


		if ($info->summary->totalBudget!=$info->summary->budget) $ret.='<p class="notify">คำเตือน : งบประมาณของกิจกรรมหลัก ('.number_format($info->summary->budget,2).' บาท) ไม่เท่ากับ งบประมาณของกิจกรรมย่อย ('.number_format($info->summary->totalBudget,2).' บาท)</p>';

		if ($info->project->budget!=$info->summary->budget) $ret.='<p class="notify">คำเตือน : งบประมาณของโครงการ ('.number_format($info->project->budget,2).' บาท) ไม่เท่ากับ งบประมาณของกิจกรรมหลัก ('.number_format($info->summary->budget,2).' บาท)</p>';

		if ($isEditDetail) {
			$ret.='<p><a class="sg-action btn -primary" href="'.url('project/mainact/'.$tpid.'/add').'" data-rel="box"><i class="icon -addbig -white"></i><span>เพิ่มกิจกรรมหลักตาม TOR</span></a></p>';
			$ret.='<p>( ชื่อกิจกรรมจะต้องเป็นชื่อกิจกรรมที่ระบุไว้ใน TOR เท่านั้น ส่วนกิจกรรมย่อยแต่ละครั้งสามารถเพิ่มได้จาก ปุ่ม <span style="font-size:1.8em;">'._CHAR_3DOTS.'</span> หลังกิจกรรมหลักแต่ละรายการ หรือ ใน <a href="'.url('project/'.$tpid.'/info.calendar').'">ปฏิทินโครงการ</a> )</p>';
		}
		$ret.='</div>';
	}

	//$ret.=print_o($project,'$project');

	return $ret;
}

function __project_mainact_show_mainactivity_item($project,$mrs,$isEdit=false,$isEditDetail=false) {
	if (empty($mrs->trid)) return;
	$ui=new ui();
	$ui->add('<a class="sg-action" href="'.url('project/mainact/'.$mrs->tpid.'/info/'.$mrs->trid).'" data-rel="box"  data-height="90%">รายละเอียดกิจกรรมหลัก</a>');
	$ui->add('<a class="sg-action" href="'.url('project/mainact/'.$mrs->tpid.'/calendar/'.$mrs->trid).'" data-rel="box" data-height="90%">กิจกรรมย่อยในปฏิทินโครงการ</a>');
	if ($isEdit) $ui->add('<a class="sg-action" href="'.url('project/mainact/'.$mrs->tpid.'/addcalendar/'.$mrs->trid).'" data-rel="box" title="เพิ่มกิจกรรมย่อยของกิจกรรมหลัก" data-height="90%">เพิ่มกิจกรรมย่อยของกิจกรรมหลัก</a>');

	if ($isEdit) {
		$ui->add('<sep>');
		$ui->add('<a href="'.url('project/mainact/'.$mrs->tpid.'/move/'.$mrs->trid).'" class="sg-action" data-rel="box" title="กำหนดหรือเพิ่มวัตถุประสงค์">กำหนดหรือเพิ่มวัตถุประสงค์</a>');
		if ($isEditDetail && empty($mrs->totalCalendar)) $ui->add('<a class="sg-action" href="'.url('project/mainact/'.$mrs->tpid.'/remove/'.$mrs->trid).'" data-confirm="คุณกำลังลบกิจกรรมหลัก กรุณายืนยัน?" data-rel="this" data-removeparent="tr">ลบกิจกรรมหลัก</a>');
	}

	$submenu='<span class="iconset">'._NL;
	//if ($isEdit) $submenu.=sg_dropbox('<form>ทำอะไร : <input type="text" /><br />เมื่อไหร่ : <input type="text" /><br />งบประมาณ : <input type="text" /></form>','{type:"box", class:"leftside -no-print add -hover--menu",text:"Add", icon:"add", title:"เพิ่มกิจกรรมย่อยของกิจกรรมหลัก", url:"'.url('project/mainact/'.$mrs->tpid.'/addcalendar/'.$mrs->trid,array('type'=>'short')).'"}')._NL;
	if ($isEdit) $submenu.='<a class="sg-action" href="'.url('project/mainact/'.$mrs->tpid.'/addcalendar/'.$mrs->trid).'" data-rel="box" title="เพิ่มกิจกรรมย่อยของกิจกรรมหลัก" data-height="90%"><i class="icon -add"></i></a>';

	//	if ($isEdit) $submenu.='<a class="sg-action hover--menu" href="'.url('project/mainact/'.$mrs->tpid.'/addcalendar/'.$mrs->trid,array('type'=>'auto')).'" data-rel="#project-objective-wrapper" data-ret="'.url('project/mainact/'.$mrs->tpid).'" title="เพิ่มกิจกรรมย่อยของกิจกรรมหลัก" data-height="90%"><i class="icon -add"></i><span>เพิ่มกิจกรรมย่อยของกิจกรรมหลัก</span></a>';
	//if ($isEdit) $submenu.='<a class="sg-action hover--menu" href="'.url('project/mainact/'.$mrs->tpid.'/addcalendar/'.$mrs->trid).'" data-rel="box" title="เพิ่มกิจกรรมย่อยของกิจกรรมหลัก" data-height="90%"><i class="icon -add"></i><span>เพิ่มกิจกรรมย่อยของกิจกรรมหลัก</span></a>';
	$submenu.=sg_dropbox($ui->build('ul'),'{type:"hover",class:"leftside submenu -no-print"}')._NL;
	$submenu.='</span>'._NL;

	// เงื่อนไขของการแก้ไขงบประมาณของกิจกรรมหลักคือ ไม่มีการพัฒนาโครงการหรือพัฒนาโครงการก่อนปี 2558 ซึ่งจะไม่มีรหัสโครงการพัฒนา devtpid
	$row=array(
		$mrs->no,
		view::inlineedit(array('group'=>'info:mainact','fld'=>'detail1','tr'=>$mrs->trid,'class'=>'-fill', 'value'=>$mrs->title),SG\getFirst($mrs->title,'ระบุชื่อกิจกรรมหลัก'),$isEdit),
		view::inlineedit(array('group'=>'info:mainact','fld'=>'num1','tr'=>$mrs->trid, 'class'=>'-fill', 'value'=>$mrs->budget,'ret'=>'money'),number_format($mrs->budget,2),!$project->proposalId && $isEdit),
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
function __project_mainact_detail($tpid,$actid,$project=NULL) {
	$isEdit=$project->isEdit;

	$ret.='<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)">'._CHAR_BACKARROW.'</a</nav><h3 class="title">รายละเอียดกิจกรรม</h3><nav><ul><li><a class="sg-action" href="'.url('project/mainact/'.$tpid.'/calendar/'.$actid).'" data-rel="box">กิจกรรมย่อย</a></li></ul></nav></header>';

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
	$expTables = new Table();
	$expTables->addClass('project-develop-exp');
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
	$ret .= $expTables->build();

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
function __project_mainact_info($tpid,$actid,$calid,$project) {
	$isEdit=$project->isEdit;

	if ($isEdit) {
		$inlineAttr['class']='inline-edit box--sidebar';
		$inlineAttr['data-update-url']=url('project/edit/tr');
		$inlineAttr['data-current-url']=url('project/mainact/'.$tpid.'/info/'.$actid);
		$inlineAttr['data-refresh-url']=url('project/'.$tpid);
		if (post('debug')) $inlineAttr['data-debug']='yes';
	} else {
		$inlineAttr['class']='box--sidebar';
	}
	$ret.='<div '.sg_implode_attr($inlineAttr).'>'._NL;
	$ret.=__project_mainact_detail($tpid,$actid,$project);
	$ret.='</div>';





	if ($isEdit) {
		$inlineAttr['class']='inline-edit box--mainbar';
		$inlineAttr['data-update-url']=url('project/edit/tr');
		$inlineAttr['data-current-url']=url('project/mainact/'.$tpid.'/info/'.$actid);
		$inlineAttr['data-refresh-url']=url('project/'.$tpid);
		if (post('debug')) $inlineAttr['data-debug']='yes';
	} else {
		$inlineAttr['class']='box--mainbar';
	}
	$ret.='<div '.sg_implode_attr($inlineAttr).'>'._NL;




	if ($calid) {
		$calendar=project_model::get_calendar($tpid,NULL,NULL,$calid);
		$lockReport=$calendar->from_date<=$project->lockReportDate;
		$is_item_edit=$isEdit && !$lockReport;
		$ret.='<h4>กิจกรรม : '.$calendar->title.'</h4>';
		$ret.=view::inlineedit(array('group'=>'calendar','fld'=>'title','tr'=>$calendar->calid,'class'=>'w-9'),$calendar->title,$is_item_edit);

		$ret.='<p><strong>วันที '.sg_date($calendar->from_date,'ว ดด ปปปป').($calendar->to_date==$calendar->from_date?'':' - '.sg_date($calendar->to_date,'ว ดด ปปปป')).' เวลา '.$calendar->from_time.' - '.$calendar->to_time.' น.</strong></p>';
		$ret.='<p><strong>สถานที่</strong><br />';
		$ret.=view::inlineedit(array('group'=>'calendar','fld'=>'location','tr'=>$calendar->calid,'class'=>'w-9'),$calendar->location,$is_item_edit).'</p>';
		$ret.='<strong>รายละเอียดกิจกรรมตามแผน</strong>';
		$ret.=view::inlineedit(array('group'=>'calendar','fld'=>'detail','tr'=>$calendar->calid,'class'=>'w-9','ret'=>'html'),$calendar->detail,$is_item_edit,'textarea');
		$ret.='<p><strong>งบประมาณที่ตั้งไว้ '.view::inlineedit(array('group'=>'activity','fld'=>'budget','tr'=>$calendar->calid,'ret'=>'money'),$calendar->budget,$is_item_edit).' บาท</strong></p>';
		$ret.='<p><strong>จำนวนกลุ่มเป้าหมาย '.view::inlineedit(array('group'=>'activity','fld'=>'targetpreset','tr'=>$calendar->calid,'ret'=>'numeric'),$calendar->targetpreset,$is_item_edit).' คน</strong></p>';
		$ret.='<strong>รายละเอียดกลุ่มเป้าหมาย</strong><br />'.view::inlineedit(array('group'=>'activity','fld'=>'target','tr'=>$calendar->calid,'ret'=>'html'),$calendar->target,$is_item_edit,'textarea');
		//$ret.=print_o($calendar,'$calendar');
	}



	$ret.=__project_mainact_listcalendar($tpid,$actid,$project);



	if ($isEdit) $ret.='<a class="sg-action floating circle add--main" href="'.url('project/mainact/'.$tpid.'/addcalendar/'.$actid).'" data-rel="box" title="เพิ่มกิจกรรมย่อย">+</a>';

	$ret.='<hr /><a class="sg-action" href="'.url('project/mainact/'.$tpid.'/calendar/'.$actid).'" data-rel="box">Refresh</a>';

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

	$form = new Form([
		'variable' => 'data',
		'action' => url('project/mainact/'.$tpid.'/move/'.$trid),
		'id' => 'project-edit-movemainact',
		'children' => [
			'objective' => [
				'type' => 'checkbox',
				'label' => 'เลือกวัตถุประสงค์',
				'options' => (function($objective){
					$options = [];
					foreach ($objective->items['objective'] as $item) {
						$options[$item->trid] = $item->text1;
					}
					return $options;
				})($objective),
				'value' => explode(',',$rs->parentObjectiveId),
			],
			'<div class="-sg-text-right"><a class="sg-action btn -primary" data-rel="close" href="javascript:joid(0)"><i class="icon -material">done</i><span>เรียบร้อย</span></a></div>',
		],
	]);

	$ret .= $form->build();

	$ret .= '<script type="text/javascript">
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
function __project_mainact_add($tpid,$objectiveId=NULL) {
	$post=(object)post('data');
	if (!property_exists($post, 'objective') && $objectiveId) $post->objective[$objectiveId]=$objectiveId;
	$objective = project_model::get_tr($tpid,'info:objective');

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

		// debugMsg(mydb()->_query);

		$mainActId=mydb()->insert_id;
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
				// debugMsg(mydb()->_query);
			}
		}
		$ret.=__project_mainact_detail($tpid,$mainActId);
		return $ret;
	}

	$ret.='<h4>เพิ่มกิจกรรมหลัก (ตาม TOR)</h4>';

	$form=new Form([
		'variable' => 'data',
		'action' => url('project/mainact/'.$tpid.'/add/'.$objectiveId),
		'id' => 'project-edit-movemainact',
		'class' => 'sg-form',
		'checkValid' => true,
		'rel' => 'box',
		'done' => 'close | load:#main:'.url('project/'.$tpid),
		'children' => [
			'objective' => [
				'type' => 'checkbox',
				'label' => 'เลือกวัตถุประสงค์ :',
				'multiple' => true,
				'require' => true,
				'options' => (function($objective) {
					$options = [];
					foreach ($objective->items['objective'] as $item) {
						$options[$item->trid] = $item->text1;
					}
					return $options;
				})($objective),
				'value' => $post->objective,
			],
			'title' => [
				'type' => 'text',
				'label' => 'ชื่อกิจกรรมหลัก (ตาม TOR)',
				'class' => '-fill',
				'require' => true,
				'value' => htmlspecialchars($post->title),
			],
			'target' => [
				'type' => 'text',
				'label' => 'กลุ่มเป้าหมาย (คน)',
				'value' => htmlspecialchars($post->target),
				'placeholder' => '0',
			],
			/*
			กลุ่มเป้าหมาย
			จำแนกตามช่วงวัย	จำแนกกลุ่มเฉพาะ
			เด็กเล็ก	0	คน 	คนพิการ	0	คน
			เด็กวัยเรียน	100	คน 	ผู้หญิง	0	คน
			วัยทำงาน	20	คน 	มุสลิม	0	คน
			ผู้สูงอายุ	0	คน 	แรงงาน	0	คน
						อื่น ๆ ระบุ...	0	คน
			*/
			'desc' => [
				'type' => 'textarea',
				'label' => 'รายละเอียดกิจกรรม',
				'rows' => 3,
				'class' => '-fill',
				'value' => htmlspecialchars($post->desc),
			],
			'timeprocess' => [
				'type' => 'text',
				'label' => 'ระยะเวลาดำเนินงาน',
				'value' => htmlspecialchars($post->timeprocess),
			],
			'output' => [
				'type' => 'textarea',
				'label' => 'ผลผลิต (Output) / ผลลัพธ์ (Outcome)',
				'class' => '-fill',
				'rows' => 3,
				'value' => htmlspecialchars($post->output),
			],
			'copartner' => [
				'type' => 'textarea',
				'label' => 'ภาคีร่วมสนับสนุน',
				'class' => '-fill',
				'rows' => 3,
				'description' => 'ระบุชื่อภาคีและวิธีการสนับสนุน เช่น งบประมาณ สิ่งของ การเข้าร่วมอื่นๆ',
				'value' => htmlspecialchars($post->copartner),
			],
			'budget' => [
				'type' => 'text',
				'label' => 'งบประมาณ (บาท)',
				'value' => htmlspecialchars($post->budget),
				'placeholder' => '0.00',
			],
			'save' => [
				'type'=>'button',
				'value'=>'<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
				'pretext'=>'<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="close"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			],
		],
	]);

	$ret.=$form->build();

	if (user_access(false)) $ret.='<hr /><a class="sg-action" href="'.url('project/mainact/'.$tpid.'/add/'.$objectiveId).'" data-rel="box">Refresh</a>';

	//$ret.=print_o($post,'$post');
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
function __project_mainact_remove($tpid,$actid,$project=NULL) {
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
	if (user_access(false)) $ret.='<hr /><a class="sg-action" href="'.url('project/mainact/'.$tpid.'/remove/'.$actid).'" data-rel="box">Refresh</a>';
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

	$mainact=project_model::get_main_activity($tpid,'owner')->info[$actid];

	if ($formType!='short') {
		$ret.='<h3 class="title">'.$mainact->title.'</h3>';
		$ret.='<div class="box--mainbar">'._NL;
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
	} else if ($tpid && $actid && $post->title && post('calendar')) {
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
		$post->targetpreset=intval(abs(sg_strip_money($post->targetpreset)));
		$post->budget=abs(sg_strip_money($post->budget));
		if ($post->mainact<=0) $post->mainact=NULL;
		$stmt='INSERT INTO %project_activity%
					(`calid`, `calowner`, `mainact`, `targetpreset`, `target`, `budget`)
					VALUES
					(:calid, :calowner, :mainact, :targetpreset, :target, :budget)
					ON DUPLICATE KEY UPDATE
					`calowner`=:calowner, `mainact`=:mainact, `targetpreset`=:targetpreset, `target`=:target, `budget`=:budget';
		mydb::query($stmt, $post);
		//$ret.=mydb()->_query.'<br />';

		if ($post->color) property('calendar:color:'.$post->calid,$post->color);

		// Add log
		model::watch_log('project','calendar add:'.$formType,'เพิ่มกิจกรรมย่อย หมายเลข '.$post->calid.' : ' .$post->title,NULL,$tpid);


		location('project/mainact/'.$tpid.'/calendar/'.$actid);
		return $ret;
	}

	if (is_array($post)) $post=(object)$post;
	if (empty($post->tpid)) $post->tpid=$tpid;
	if (empty($post->mainact)) $post->mainact=$actid;
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

	$form = new Form([
		'variable' => 'calendar',
		'action' => url('project/mainact/'.$tpid.'/addcalendar/'.$actid),
		'id' => 'edit-calendar',
		'class' => 'sg-form',
		'rel' => 'box',
		'done' => 'load:box:'.url('project/'.$tpid).'"'.($formType=='short'?' data-done="close"':''),
		'children' => [
			'act' => ['type' => 'hidden', 'name' => 'act', 'value' => 'add'],
			'id' => $post->id ? ['type' => 'hidden', 'value' => $post->id] : '',
			'tpid' => $post->tpid ? ['type' => 'hidden', 'value' => $post->tpid] : '',
			'type' => ['type' => 'hidden', 'name' => 'type', 'value' => $formType],
			'mainact' => ['type' => 'hidden', 'value' => $actid],
			'privacy' => ['type' => 'hidden', 'value' => 'public'],
			'calowner' => ['type' => 'hidden', 'value' => 1],
			'title' => [
				'type' => 'text',
				'label' => 'ทำอะไร',
				'maxlength' => 255,
				'class' => '-fill',
				'require' => true,
				'placeholder' => 'ระบุชื่อกิจกรรม',
				'value' => htmlspecialchars($post->title),
			],
			'date' => [
				'type' => 'textfield',
				'label' => 'เมื่อไหร่',
				'require' => true,
				'value' => (function($post) {
					for ($hr=7;$hr<24;$hr++) {
						for ($min=0;$min<60;$min+=30) {
							$times[]=sprintf('%02d',$hr).':'.sprintf('%02d',$min);
						}
					}
					$result = '<input type="text" name="calendar[from_date]" id="edit-calendar-from_date" maxlength="10" class="sg-datepicker form-text require" style="width:80px;" value="'.htmlspecialchars($post->from_date).'"> '
						. '<select class="form-select" name="calendar[from_time]" id="edit-calendar-from_time">';
					foreach ($times as $time) {
						$result .= '<option value="'.$time.'"'.($time==$post->from_time?' selected="selected"':'').'>'.$time.'</option>';
					}
					$result .= '</select>
					ถึง <select class="form-select" name="calendar[to_time]" id="edit-calendar-to_time">';
					foreach ($times as $time) {
						$result .= '<option value="'.$time.'"'.($time==$post->to_time?' selected="selected"':'').'>'.$time.'</option>';
					}
					$result .= '</select>
					<input type="text" name="calendar[to_date]" id="edit-calendar-to_date" maxlength="10" class="sg-datepicker form-text require" style="width:80px;" value="'.htmlspecialchars($post->to_date).'">';
					return $result;
				})($post),
			],
			'areacode' => ['type' => 'hidden', 'value' => $post->areacode],
			'latlng' => ['type' => 'hidden', 'value' => $post->latlng],
			'budget' => [
				'type' => 'text',
				'label' => 'งบประมาณที่ตั้งไว้ (บาท)',
				'maxlength' => 12,
				'placeholder' => '0.00',
				'value' => $post->budget,
			],
			'location' => [
				'type' => $formType=='short'?'hidden':'text',
				'label' => 'ที่ไหน',
				'class' => 'sg-address -fill',
				'maxlength' => 255,
				'placeholder' => 'ระบุสถานที่ หมู่ที่ ตำบล',
				'value' => htmlspecialchars($post->location),
				'attr' => 'data-altfld="edit-calendar-areacode"',
				'posttext' => ' <a href="javascript:void(0)" id="calendar-addmap">แผนที่</a><div id="calendar-mapcanvas" class="-hidden"></div>',
			],
			'detail' => [
				'type' => $formType=='short'?'hidden':'textarea',
				'label' => 'รายละเอียดกิจกรรมตามแผน',
				'class' => '-fill',
				'rows' => 3,
				'placeholder' => 'ระบุรายละเอียดของกิจกรรมที่วางแผนว่าจะทำ',
				'value' => $post->detail,
			],
			'targetpreset' => [
				'type' => $formType=='short'?'hidden':'text',
				'label' => 'จำนวนกลุ่มเป้าหมาย (คน)',
				'maxlength' => 7,
				'placeholder' => 0,
				'value' => $post->targetpreset,
			],
			'target' => [
				'type' => $formType=='short'?'hidden':'textarea',
				'label' => 'รายละเอียดกลุ่มเป้าหมาย',
				'class' => '-fill',
				'rows' => 2,
				'placeholder' => 'ระบุรายละเอียดของกลุ่มเป้าหมายที่จะเข้าร่วม',
				'value' => $post->target,
			],
			'color' => [
				'type' => 'colorpicker',
				'label' => 'สีของกิจกรรม',
				'color' => 'Red, Green, Blue, Black, Purple, Aquamarine, Aqua, Chartreuse,Coral, DarkGoldenRod, Olive, Teal, HotPink, Brown',
				'value' => $post->color,
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}',
				'pretext' => $formType=='short'?'<a class="sg-action btn -link -cancel" data-rel="close" href=""><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>':'<a class="sg-action btn -link -cancel" href="'.url('project/mainact/'.$tpid.'/calendar/'.$actid).'" data-rel="box"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			],
		],
	]);

	if ($para->module) 	$form=do_class_method($para->module.'.extension','calendar_form', $form, $post, $para);
	$ret .= $form->build();

	$gis['zoom']=7;

	if ($post->latlng) {
		list($lat,$lng)=explode(',', $post->latlng);
		$gis['center']=$post->latlng;
		$gis['zoom']=10;
		$gis['current'] = [
			'latitude'=>$lat,
			'longitude'=>$lng,
			'title'=>$post->location,
			'content'=>'<h4>'.$post->title.'</h4>'.($post->topic_title?'<p><strong>'.$post->topic_title.'</strong></p>':'').($post->location?'<p>สถานที่ : '.$post->location.'</p>':''),
		];
	} else {
		$gis['center']=property('project:map.center:NULL');
	}
	if (user_access(false)) $ret.='<hr /><a class="sg-action" href="'.url('project/mainact/'.$tpid.'/addcalendar/'.$actid).'" data-rel="box">Refresh</a>';

	//$ret.=print_o($post,'$post').print_o($gis,'$gis').print_o($mainact,'mainact');

	if ($formType!='short') {
		$ret.='</div>';

		$ret.='<div class="box--sidebar">';
		$ret.=__project_mainact_listcalendar($tpid,$actid,$project);
		//$ret.=__project_mainact_detail($tpid,$actid);
		$ret.='</div>';
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
	$isAccessActivityExpense=user_access('access activity expense') || $project->isOwner;

	$ret.='<h4>กิจกรรมย่อยตามปฏิทินกิจกรรมของโครงการ</h4>';
	$stmt = 'SELECT a.*, c.*
			, r.`trid` `reportId`
		FROM %project_activity% a
			LEFT JOIN %calendar% c ON c.`id`=a.`calid`
			LEFT JOIN %project_tr% r ON r.`calid`=a.`calid` AND r.`formid`="activity" AND r.`part`="owner"
		WHERE `mainact`=:mainact AND a.`calowner`=1
		ORDER BY c.`from_date` ASC';
	$dbs=mydb::select($stmt,':mainact',$actid);

	$tables = new Table();
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
			$ui->add($rs->reportId?'<a class="disabled" href="javascript:void(0)" title="ไม่สามารถลบปฏิทินกิจกรรมได้เนื่องจากมีการบันทึกกิจกรรมเรียบร้อยแล้ว">[ลบปฏิทินกิจกรรมไม่ได้]</a>':'<a class="sg-action" href="'.url('project/mainact/'.$tpid.'/removecalendar/'.$actid.'/'.$rs->calid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบปฏิทินกิจกรรมนี้ กรุณายืนยัน?" data-callback="refreshContent" data-refresh-url="'.url('project/'.$tpid).'">ลบ</a>');
		}
		$submenu=sg_dropbox($ui->build('ul'));

		$tables->rows[] = [
			sg_date($rs->from_date,'ว ดด ปป'),
			view::inlineedit(array('group'=>'calendar','fld'=>'title','tr'=>$rs->calid,'class'=>'w-9'),$rs->title,$is_item_edit),
			view::inlineedit(array('group'=>'activity','fld'=>'targetpreset','tr'=>$rs->calid,'ret'=>'numeric'),number_format($rs->targetpreset),$is_item_edit,'text'),
			$isAccessActivityExpense?view::inlineedit(array('group'=>'activity','fld'=>'budget','tr'=>$rs->calid,'ret'=>'numeric','callback'=>'refreshContent'),number_format($rs->budget,2),$is_item_edit,'text'):'-',
			$submenu,
		];
		$budget+=$rs->budget;
		$target+=$rs->targetpreset;
	}
	$tables->tfoot[] = [
		'<td colspan="2" align="center">รวม '.$dbs->_num_rows.' กิจกรรม</td>',
		'<td align="center"><strong>'.number_format($target).'</strong></td>',
		'<td align="right"><strong>'.number_format($budget,2).'</strong></td>',
		''
	];
	$ret .= $tables->build();
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
	location('project/'.$tpid);
	return $ret;
}
?>