<?php
function view_project_calendar_list_obj($tpid,$info,$isEdit) {
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
	$tables->thead=array('date no'=>'วันที่','title'=>'ชื่อกิจกรรม','amt target'=>'กลุ่มเป้าหมาย (คน)','amt budget'=>'งบกิจกรรม (บาท)','amt done'=>'ทำแล้ว','amt expend'=>'ใช้จ่ายแล้ว (บาท)', $isEdit?'<a class="sg-action" href="'.url('project/calendar/'.$tpid.'/add').'" data-rel="box" title="เพิ่มกิจกรรม"><i class="icon -adddoc -hidetext"></i><span class="-hidden">เพิ่มกิจกรรม</span></a>':'');
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
												number_format($crs->budget,2),
												$crs->activityId?'<a href="'.url('paper/'.$tpid.'/owner#tr-'.$crs->activityId).'" title="บันทึกหมายเลข '.$crs->activityId.'">✔</a>':'',
												$crs->exp_total?number_format($crs->exp_total,2):'-',
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
												number_format($subBudget,2),
												number_format($subActivity),
												number_format($subExpense,2),
												'',
												'config'=>array('class'=>'subfooter')
											);

	}
	$ret.=$tables->build();
	$ret.='<p>หมายเหตุ : งบประมาณ และ ค่าใช้จ่าย รวมทุกวัตถุประสงค์อาจจะไม่เท่ากับงบประมาณรวมได้</p>';

	//$ret.=print_o($info,'$info');
	return $ret;
}
?>