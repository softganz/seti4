<?php
function view_project_calendar_list_plan($tpid,$info,$isEdit) {
	$tables=new table('item project-mainact-items');
	$tables->thead=array(
									'date no'=>'วันที่',
									'title'=>'กิจกรรมหลัก/กิจกรรมย่อย',
									'money'=>'งบประมาณ<br />(บาท)',
									'amt target'=>'กลุ่มเป้าหมาย<br />(คน)',
									'amt calendar'=>'กิจกรรมย่อย<br />(ครั้ง)',
									'money budget'=>'งบกิจกรรม<br />(บาท)',
									'amt done'=>'ทำแล้ว<br />(ครั้ง)',
									'money expend -hover-parent'=>'ใช้จ่ายแล้ว<br />(บาท)',
									);
	foreach ($info->mainact as $mrs) {
		//$ret.=print_o($mrs,'$mrs');
		$mrs->no=++$j;
		$tables->rows[]=__project_calendar_show_mainactivity_item($project,$mrs,$isEdit,$isEditDetail);
		if (!$mrs->trid) continue;
		$isSubCalendar=false;
		foreach ($info->calendar[$mrs->trid] as $crs) {
			$isSubCalendar=true;
			$isEditCalendar=$isEdit && $crs->from_date>$lockReportDate;

			if ($crs->from_date==$crs->to_date) $actionDate= sg_date($crs->from_date,'ว ดด ปป');
			else if (sg_date($crs->from_date,'Y-m')==sg_date($crs->to_date,'Y-m')) $actionDate=sg_date($crs->from_date,'ว').'-'.sg_date($crs->to_date,'ว').' '.sg_date($crs->from_date,'ดด ปป');
			else $actionDate=sg_date($crs->from_date,'ว ดด ปป').'-'.sg_date($crs->to_date,'ว ดด ปป');

			$ui = new Ui();
			if ($isEditCalendar) {
				$ui->add('<a class="sg-action" href="'.url('project/calendar/'.$tpid.'/edit/'.$crs->id).'" data-rel="box" title="แก้ไขรายละเอียดกิจกรรมย่อย"><i class="icon -edit"></i></a>');
				if (!$crs->activityId) {
					$ui->add('<a class="sg-action" href="'.url('project/calendar/'.$tpid.'/remove/'.$crs->id).'" data-rel="#project-objective-wrapper" data-ret="'.url('project/calendar/'.$tpid).'" data-confirm="ลบกิจกรรมย่อย กรุณายืนยัน" title="ลบกิจกรรมย่อย"><i class="icon -delete"></i></a>');
				}
			}
			$menu .= '<nav class="nav -icons -hover">'.$ui->build().'</nav>';


			$tables->rows[]=array(
												$isEditCalendar?'<a class="sg-action inline-edit-field -fill" href="'.url('project/calendar/'.$tpid.'/edit/'.$crs->id).'" data-rel="box" data-type="link" title="คลิกเพื่อแก้ไข">'.$actionDate.'</a>':$actionDate,
												view::inlineedit(array('group'=>'calendar','fld'=>'title','tr'=>$crs->id, 'class'=>'-fill', 'value'=>$crs->title),$crs->title,$isEditCalendar),
												'',
												$crs->targetpreset,
												'',
												number_format($crs->budget,2),
												$crs->activityId?'<a href="'.url('paper/'.$tpid.'/owner#tr-'.$crs->activityId).'" title="บันทึกหมายเลข '.$crs->activityId.'">✔</a>':'',
												($crs->exp_total?number_format($crs->exp_total,2):'-')
												. $menu,
												'config'=>array('class'=>'calendar')
												);
		}
		if ($isEdit && !$isSubCalendar) $tables->rows[]=array('','<td colspan="7">ยังไม่ได้กำหนดกิจกรรมย่อย กรุณา<a class="sg-action" href="'.url('project/calendar/'.$tpid.'/add/'.$mrs->trid).'" data-rel="box">เพิ่มกิจกรรมย่อย</a>อย่างน้อย 1 กิจกรรม</td>','config'=>array('class'=>'calendar'));
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
	if ($noMainAct) $tables->rows[]='<tr><td colspan="8"><h4>กิจกรรมย่อยที่ยังไม่กำหนดกิจกรรมหลัก'.($isEdit?' (กรุณาแก้ไขโดยกำหนดกิจกรรมหลักหรือลบทิ้ง) !!!':'').'</h4></td></tr>';

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
											//$isEdit?'<span class="hover--menu iconset"><a class="sg-action" href="'.url('project/calendar/'.$tpid.'/edit/'.$rs->id).'" data-rel="box" title="แก้ไขรายละเอียดกิจกรรมย่อย"><i class="icon -edit"></i></a>'.($rs->activityId?'':' <a class="sg-action" href="'.url('project/calendar/'.$tpid.'/remove/'.$rs->id).'" data-rel="#project-objective-wrapper" data-ret="'.url('project/calendar/'.$tpid).'" data-confirm="ลบกิจกรรมย่อย กรุณายืนยัน" title="ลบกิจกรรมย่อย"><i class="icon -delete"></i></a>'):'',
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

function __project_calendar_show_mainactivity_item($project,$mrs,$isEdit=false,$isEditDetail=false) {
	if (empty($mrs->trid)) return;
	$ui=new ui();
	$ui->add('<a class="sg-action" href="'.url('project/plan/'.$mrs->tpid.'/info/'.$mrs->trid).'" data-rel="box" data-height="90%"><i class="icon -view"></i> <i class"icon -view"></i> รายละเอียดกิจกรรมหลัก</a>');
	$ui->add('<a class="sg-action" href="'.url('project/plan/'.$mrs->tpid.'/calendar/'.$mrs->trid).'" data-rel="box" data-height="90%"><i class="icon -view"></i> กิจกรรมย่อยในกิจกรรมหลัก</a>');
	if ($isEdit) {
		$ui->add('<sep>');
		$ui->add('<a class="sg-action" href="'.url('project/calendar/'.$mrs->tpid.'/add/'.$mrs->trid).'" data-rel="box" title="เพิ่มกิจกรรมย่อยในกิจกรรมหลักนี้" data-height="90%"><i class="icon -adddoc"></i> เพิ่มกิจกรรมย่อยในกิจกรรมหลักนี้</a>');
	}

	$submenu='<span class="iconset">'._NL;
	if ($isEdit) $submenu.=sg_dropbox('<form>ทำอะไร : <input type="text" /><br />เมื่อไหร่ : <input type="text" /><br />งบประมาณ : <input type="text" /></form>','{type:"box", class:"leftside -no-print add hover--menu",text:"Add", icon:"adddoc", title:"เพิ่มกิจกรรมย่อยในกิจกรรมหลักนี้", url:"'.url('project/calendar/'.$mrs->tpid.'/add/'.$mrs->trid,array('type'=>'short')).'"}')._NL;
	$submenu.=sg_dropbox($ui->build('ul'),'{type:"hover",class:"leftside submenu -no-print"}')._NL;
	$submenu.='</span>'._NL;

	// เงื่อนไขของการแก้ไขงบประมาณของกิจกรรมหลักคือ ไม่มีการพัฒนาโครงการหรือพัฒนาโครงการก่อนปี 2558 ซึ่งจะไม่มีรหัสโครงการพัฒนา devtpid
	$row=array(
		'<td style="text-align:center;font-weight:normal;"><big style="background:#f60; border-radius:50%; display:inline-block;padding:4px; width:1.4em;height:1.4em;line-height:1.4em; color:#fff;">'.$mrs->no.'</big></td>',
		$mrs->title,
		number_format($mrs->budget,2),
		number_format($mrs->target),
		'<a href="'.url('project/plan/'.$mrs->tpid.'/calendar/'.$mrs->trid).'" class="sg-action" data-rel="box" data-height="90%" title="ดูรายการกิจกรรมย่อย">'.($mrs->totalCalendar?$mrs->totalCalendar:'-').'</a>',
		$mrs->totalBudget?number_format($mrs->totalBudget,2):'-',
		'<a href="'.url('project/plan/'.$mrs->tpid.'/calendar/'.$mrs->trid).'" class="sg-action" data-rel="box" data-height="90%" title="ดูรายการกิจกรรมย่อย">'.($mrs->totalActitity?$mrs->totalActitity:'-').'</a>',
		$mrs->totalExpense?number_format($mrs->totalExpense,2):'-',
		$submenu,
		'config'=>array('class'=>'mainact'),
	);
	return $row;
}
?>