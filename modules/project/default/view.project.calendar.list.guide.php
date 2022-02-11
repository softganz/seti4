<?php
function view_project_calendar_list_guide($tpid,$info,$isEdit) {
	$stmt='SELECT `catid`, `name`, `description` `indicator` FROM %tag% WHERE `taggroup`="project:activitygroup" ORDER BY `catid` ASC';
	$guideList=mydb::select($stmt)->items;

	$calendarList=project_model::get_calendar($tpid,$period,$owner="owner");

	$guideIdList=array();
	foreach (mydb::select('SELECT * FROM %project_actguide% WHERE `tpid`=:tpid',':tpid',$tpid)->items as $rs) {
		$guideIdList[$rs->calid][]=$rs->guideid;
	}
	//$ret.=mydb()->_query.print_o($guideIdList,'$guideIdList');

	$objectiveNo=0;
	$guideTables = new Table();
	$guideTables->colgroup=array('no'=>'width="5%"','objective'=>'width="45%"','indicator'=>'width="50%"');
	$guideTables->thead=array('','แนวทางดำเนินงาน 8 องค์ประกอบ','ตัวชี้วัด');

	$tables = new Table();
	$tables=new table('item project-mainact-items');
	$tables->thead=array('date no'=>'วันที่','title'=>'ชื่อกิจกรรม','amt target'=>'กลุ่มเป้าหมาย (คน)','amt budget'=>'งบกิจกรรม (บาท)','amt done'=>'ทำแล้ว','amt expend'=>'ใช้จ่ายแล้ว (บาท)', $isEdit?'<a class="sg-action" href="'.url('project/calendar/'.$tpid.'/add').'" data-rel="box" title="เพิ่มกิจกรรม"><i class="icon -adddoc -hidetext"></i><span class="-hidden">เพิ่มกิจกรรม</span></a>':'');
	foreach ($guideList as $guideId => $rs) {
		$subTarget=$subBudget=$subActivity=$subExpense=0;

		$tables->rows[]=array('<td colspan="7"><h4>แนวทางดำเนินงานที่ '.$rs->catid.' : '.$rs->name.'</h4>'._NL
											.'<strong>ตัวชี้วัด</strong><br />'.sg_text2html($rs->indicator).'</td>');



		foreach ($calendarList->items as $crs) {
			if (!in_array($rs->catid, $guideIdList[$crs->id])) continue;
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

	//$ret.=print_o($info,'$info');
	return $ret;
}
?>