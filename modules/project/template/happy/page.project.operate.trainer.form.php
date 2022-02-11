<?php
/**
 * Show trainer follow form
 *
 * @param Object $self
 * @param Object $projectInfo
 * @param Integer $period
 * @return String
 */
function project_operate_trainer_form($self, $projectInfo, $period = 1) {
	if (!($tpid = $projectInfo->tpid)) return message('error', 'PROCESS ERROR:NO PROJECT');

	// Prepare data
	$rs=project_model::get_tr($tpid,'follow',$period);
	$info=project_model::get_tr($tpid,'info:objective');
	$periodInfo=project_model::get_period($tpid,$period);
	$mainact=project_model::get_main_activity($tpid);
	$mainFollow=array();

	// Get section 2.2 and set to group
	// ไม่มี parent : text1=ชื่อกิจกรรม, text2=รายละเอียดกลุ่มเป้าหมาย, text3,text4,text5=ผลที่พี่เลี้ยงบันทึก
	// มี parent แบ่งเป็น กิจกรรมย่อย (text3,text4,text5) และกิจกรรมหลัก (text1)
	$stmt='SELECT tr.*, p.`part` parentpart
					FROM %project_tr% tr
						LEFT JOIN %project_tr% p ON p.`trid`=tr.`parent`
					WHERE tr.`tpid`=:tpid AND tr.`part`="2.2" AND tr.`period`=:period';
	$dbs=mydb::select($stmt,':tpid',$tpid, ':period',$period);
	foreach ($dbs->items as $item) {
		if (empty($item->parentpart) || $item->parentpart=='owner') {
			$mainFollow['owner'][$item->trid]=$item;
		} else {
			$mainFollow[$item->parentpart][$item->parent]=$item;
		}
		if ($item->parent && $item->parentpart=='owner') $activityArray[]=$item->parent;
	}

	if ($activityArray) $activitys=project_model::get_activity($tpid,$activityArray);

	$section='title';
	$irs=end($rs->items[$section]);
	$locked=$irs->flag>=_PROJECT_LOCKREPORT;

	$is_edit = false; //($projectInfo->info->project_statuscode==1) && (user_access('administer projects') || project_model::is_trainer_of($tpid));
	if ($locked) $is_edit=false;

	$liketitle=$is_edit?'คลิกเพื่อแก้ไข':'';
	$editclass=$is_edit?'editable':'';
	$emptytext=$is_edit?'<span style="color:#999;">แก้ไข</span>':'';
	$formid='follow';

	// Part 1
	$ret.='<a name="all"></a>';
	$ret.='<a name="part1"></a>';
	$ret.='<div id="part1"><h4>ส่วนที่ 1 : ข้อมูลเบื้องต้นการติดตาม</h4>';

	$tables = new Table();
	$tables->caption='<h5>1.1 ข้อมูลเบื้องต้นการติดตาม</h5>';
	$tables->rows[]=array('ชื่อสกุลผู้ติดตาม 1',view::inlineedit(array('group'=>'follow:'.$section,'fld'=>'detail1','tr'=>$irs->trid),$irs->detail1,$is_edit));
	$tables->rows[]=array('ชื่อสกุลผู้ติดตาม 2',view::inlineedit(array('group'=>'follow:'.$section,'fld'=>'detail2','tr'=>$irs->trid),$irs->detail2,$is_edit));
	$tables->rows[]=array('วันที่ลงพื้นที่ติดตาม',view::inlineedit(array('group'=>'follow:'.$section,'fld'=>'date1','tr'=>$irs->trid,'ret'=>'date:ว ดดด ปปปป', 'value'=>sg_date(SG\getFirst($irs->date1,date()),'d/m/Y')),$irs->date1,$is_edit,'datepicker'));
	$tables->rows[]=array('วันที่ส่งรายงานถึง สสส.',view::inlineedit(array('group'=>'follow:'.$section,'fld'=>'date2','tr'=>$irs->trid,'ret'=>'date:ว ดดด ปปปป','value'=>sg_date(SG\getFirst($irs->date2,date('U')),'d/m/Y')),$irs->date2,$is_edit,'datepicker'));

	$ret .= $tables->build();

	$section='1.2';
	$no=0;

	$tables = new Table();
	$tables->caption='<h5>'.$section.' ผู้ให้ข้อมูล</h5>';
	$tables->thead=array('no'=>'ลำดับ','ชื่อ-สกุลผู้ให้ข้อมูล','ที่อยู่','หมายเลขโทรศัพท์',$is_edit?'<a href="" data-action="add" data-group="follow:'.$section.'"><i class="icon -add"></i></a>':'');
	foreach ($rs->items[$section] as $irs) {
		$tables->rows[]=array(++$no,
													view::inlineedit(array('group'=>'follow:'.$section,'fld'=>'detail1','tr'=>$irs->trid),$irs->detail1,$is_edit),
													view::inlineedit(array('group'=>'follow:'.$section,'fld'=>'detail2','tr'=>$irs->trid),$irs->detail2,$is_edit),
													view::inlineedit(array('group'=>'follow:'.$section,'fld'=>'detail3','tr'=>$irs->trid),$irs->detail3,$is_edit),
													$is_edit?'<a href="" data-action="del" data-group="follow:'.$section.'" data-tr="'.$irs->trid.'" title="ลบรายการนี้">X</a>':'');
	}

	$ret .= $tables->build();
	$ret.='</div>';

	// Part 2
	$ret.='<a name="part2"></a>';
	$ret.='<div id="part2"><h4>ส่วนที่ 2 : ข้อมูลโครงการและความก้าวหน้าการดำเนินงาน</h4>';

	$section='2.1';
	$no=0;

	$tables = new Table();
	$tables->caption='<h5>'.$section.' วัตถุประสงค์และตัวชี้วัดความสำเร็จโครงการ</h5>';
	$tables->thead=array('center'=>'ลำดับ','วัตถุประสงค์','ตัวชี้วัดความสำเร็จ');
	foreach ($info->items['objective'] as $irs) {
		$tables->rows[]=array('<p>'.++$no.'.</p>',sg_text2html($irs->text1),sg_text2html($irs->text2));
	}

	$ret .= $tables->build();

	$section='2.2';
	$no=0;

	$tables = new Table();
	$tables->addClass('project-report-follow-2-2');
	$tables->caption='<h5>'.$section.' ความก้าวหน้าของการดำเนินงานโครงการ</h5>';
	$tables->thead='<thead><tr><!-- <th rowspan="2">กิจกรรมโครงการ</th> --><th rowspan="2">กลุ่มเป้าหมาย</th><th colspan="2">งบประมาณ</th><th colspan="2">ผลการจัดกิจกรรมเชิงปริมาณ</th><th rowspan="2">ผลการจัดกิจกรรมเชิงคุณภาพ/สรุปผลงานที่ได้จากการดำเนินงานเชิงคุณภาพ</th><th rowspan="2"><!--'.($is_edit?'<a href="" action="add" group="follow:'.$section.'"><i class="icon -add"></i></a>':'').'--></th></tr><tr><th>ที่ตั้งไว้(บาท)</th><th>เกิดขึ้นจริง(บาท)</th><th>จำนวนที่ตั้งไว้(คน)</th><th>จำนวนเกิดขึ้นจริง(คน)</th></tr></thead>';

	// Show ความก้าวหน้าของการดำเนินงานโครงการ ผ่าน กิจกรรมหลัก (มี parent เป็น mainact)
	foreach ($mainact->info as $mainRs) {
		if (!$is_edit && empty($mainFollow['mainact'][$mainRs->trid]->text1)) continue;
		$sectionmain='2.2:'.$mainRs->trid;
		$mainActId=$mainRs->trid;

		// Check for period activity ; if no , not show main activity
		$isActivity=0;
		if (cfg('project.follow.activity')=='all') {
			$isActivity=count($mainact->activity[$mainRs->trid]);
		} else {
			foreach ($mainact->activity[$mainRs->trid] as $actRs) {
				if ($actRs->action_date>=$periodInfo->report_from_date && $actRs->action_date<=$periodInfo->report_to_date) {
					$isActivity++;
				}
			}
		}

		if (cfg('project.follow.mainact')=='all' || (cfg('project.follow.mainact')!='all' && $isActivity)) {
			$tables->rows[]=array('<td colspan="6"><h4>กิจกรรมหลัก : '.$mainRs->title.'<span class="sg-info" title="รายการติดตามจากกิจกรรมหลัก">i</span></h4></td>');
			$tables->rows[]=array(
														'',
														number_format($mainRs->budget,2),
														'',
														number_format($mainRs->target),
														'',
														/*
														_NL.'<strong>ผลการจัดกิจกรรมเชิงคุณภาพที่ตั้งไว้ : </strong>'.view::inlineedit(array('group'=>'follow:'.$sectionmain,'fld'=>'text1','parent'=>$mainRs->trid,'tr'=>$mainFollow[$mainRs->trid]->trid,'ret'=>'html','button'=>'yes','value'=>$mainFollow[$mainRs->trid]->text1),sg_text2html($mainFollow[$mainRs->trid]->text1),$is_edit,'textarea')._NL
														.'<strong>ผลการจัดกิจกรรมที่เกิดขึ้นจริง : </strong>'.view::inlineedit(array('group'=>$formid.':'.$sectionmain,'fld'=>'text2','parent'=>$mainRs->trid,'tr'=>$mainFollow[$mainRs->trid]->trid,'ret'=>'html','button'=>'yes','value'=>$mainFollow[$mainRs->trid]->text2),sg_text2html($mainFollow[$mainRs->trid]->text2),$is_edit,'textarea')._NL
														.*/
														'<strong>ผลผลิต</strong><br />'.view::inlineedit(array('group'=>$formid.':'.$sectionmain,'fld'=>'text2','parent'=>$mainRs->trid,'tr'=>$mainFollow['mainact'][$mainRs->trid]->trid,'ret'=>'html','button'=>'yes','value'=>$mainFollow['mainact'][$mainRs->trid]->text2),$mainFollow['mainact'][$mainRs->trid]->text2,$is_edit,'textarea').'<br /><strong>ผลลัพธ์ (สรุปผลงานที่ได้จากการดำเนินงานเชิงคุณภาพ)</strong>'.view::inlineedit(array('group'=>$formid.':'.$sectionmain,'fld'=>'text1','parent'=>$mainRs->trid,'tr'=>$mainFollow['mainact'][$mainRs->trid]->trid,'ret'=>'html','button'=>'yes','value'=>$mainFollow['mainact'][$mainRs->trid]->text1),$mainFollow['mainact'][$mainRs->trid]->text1,$is_edit,'textarea').'<p align="right"><a class="btn" href="javascript:void(0)" data-show="project-mainact-activity-'.$mainActId.'"><i class="icon -down"></i><span>กิจกรรมย่อย '.$isActivity.' ครั้ง</span></a>'._NL,
													);
			foreach ($mainact->activity[$mainRs->trid] as $actRs) {
				if (cfg('project.follow.activity')=='all') {
					// do nothing
				} else if ($actRs->action_date<=$periodInfo->report_from_date
					|| $actRs->action_date>=$periodInfo->report_to_date) {
					continue;
				}
				$tables->rows[]=array('<td colspan="7" class="project-mainact-activity-'.$mainActId.' hidden -no-print"><h5>กิจกรรมย่อย : '.sg_date($actRs->action_date,'d-m-ปปปป').' - '.$actRs->title.'</h5></td>');
				$tables->rows[]=array(sg_text2html($actRs->targetjoindetail),
															number_format($actRs->budget,2),
															number_format($actRs->exp_total,2),
															number_format($actRs->targetpreset),
															number_format($actRs->targetjoin),
															'<strong>ผลสรุปที่สำคัญของกิจกรรม</strong>'.sg_text2html($actRs->real_work),
															$is_edit?'<a class="hover-icon -tr" href="'.url('paper/'.$tpid.'/member/owner',array('trid'=>$actRs->trid)).'"><i class="icon -edit"></i></a>':'',
															'config'=>array('class'=>'project-mainact-activity-'.$mainActId.' -hidden -no-print')
														);
			}
		}
	}

	// Show ความก้าวหน้าของการดำเนินงานโครงการ ผ่านกิจกรรมย่อย (มี parent เป็น owner) และ ผ่านการป้อนแบบบันทึกเอง (ไม่มี parent)
	foreach ($mainFollow['owner'] as $irs) {
		$parent=$irs->parent;
		$tables->rows[]=array('<td colspan="6"><h4>กิจกรรมย่อย: '.SG\getFirst($activitys->items[$parent]->title,$irs->text1).'<span class="sg-info" title="'.($irs->parent?'รายการติดตามกิจกรรมย่อยจากการเลือก':'รายการติดตามกิจกรรมย่อยที่บันทึกข้อมูลเอง').'">i</span></h4></td>');
		$tables->rows[]=array(
													SG\getFirst($activitys->items[$parent]->goal_do,$irs->text2),
													number_format($activitys->items[$parent]->budget,2),
													number_format($activitys->items[$parent]->exp_total,2),
													number_format($activitys->items[$parent]->targetpreset),
													number_format($activitys->items[$parent]->targetjoin),
													'<strong>ผลการจัดกิจกรรมเชิงคุณภาพที่ตั้งไว้ : </strong>'.view::inlineedit(array('group'=>'follow:'.$section,'fld'=>'text3','tr'=>$irs->trid,'ret'=>'html','button'=>'yes','value'=>$irs->text3),$irs->text3,$is_edit,'textarea')
													.'<strong>ผลการจัดกิจกรรมที่เกิดขึ้นจริง : </strong>'.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text4','tr'=>$irs->trid,'ret'=>'html','button'=>'yes','value'=>$irs->text4),$irs->text4,$is_edit,'textarea')
													.'<strong>สรุปผลงานที่ได้จากการดำเนินงานเชิงคุณภาพ : </strong>'.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text5','tr'=>$irs->trid,'ret'=>'html','button'=>'yes','value'=>$irs->text5),$irs->text5,$is_edit,'textarea'),
													$is_edit?'<a href="" data-action="del" data-group="follow:'.$section.'" data-tr="'.$irs->trid.'" title="ลบรายการนี้">X</a><br /><a href="'.url('paper/'.$tpid.'/member/owner',array('trid'=>$irs->parent)).'">แก้ไข</a>':'');
	}

	$ret .= $tables->build();



	//$ret.=print_o($mainact,'$mainact');
	//$ret.=print_o($rs->items['2.2'],'$rs');

	$tables->caption='<h5>2.3 จุดเด่นของโครงการที่น่าสนใจ</h5>';

	$section='2.3.1';
	$no=0;

	$tables = new Table();
	$tables->caption='<h6>'.$section.' นวัตกรรมการสร้างเสริมสุขภาพ</h6><p>(นวัตกรรมคือ การจัดการความคิด กระบวนการ ผลผลิต และ/หรือเทคโนโลยีที่เหมาะสม มาใช้งานให้เกิดประสิทธิผล และ/หรือประสิทธิภาพมากกว่าเดิมอย่างชัดเจน)</p>';
	$tables->thead=array('ชื่อนวัตกรรม','คุณลักษณะ/วิธีการทำให้เกิดนวัตกรรม','ผลของนวัตกรรม/การนำไปใช้ประโยชน์',$is_edit?'<a href="" data-action="add" data-group="follow:'.$section.'"><i class="icon -add"></i></a>':'');
	foreach ($rs->items[$section] as $irs) {
		$tables->rows[]=array(	view::inlineedit(array('group'=>'follow:'.$section,'fld'=>'detail1','tr'=>$irs->trid),$irs->detail1,$is_edit),
													view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html','button'=>'yes','value'=>$irs->text1),$irs->text1,$is_edit,'textarea'),
													view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid,'ret'=>'html','button'=>'yes','value'=>$irs->text2),$irs->text2,$is_edit,'textarea'),
													$is_edit?'<a href="" data-action="del" data-group="follow:'.$section.'" data-tr="'.$irs->trid.'" title="ลบรายการนี้">X</a>':'');
	}

	$ret .= $tables->build();

	$section='2.3.2';
	$no=0;

	$tables = new Table();
	$tables->caption='<h6>'.$section.' โครงการเด่น (Best Practice)</h6><p>(โครงการเดิ่น คือ โครงการสร้างเสริมสุขภาพให้สัมฤทธิ์ผลที่เป็นรูปธรรมแล้วขยายผลอย่างยั่งยืน โดยแนวคิดกระบวนการ และผลงาน สามารถเป็นตัวอย่างที่จะนำไปขยายผลในชุมชน (Setting) อื่น ๆ ได้ การดำเนินงานมีส่วนร่วมของภาคีที่หลากหลาย มีการบริหารจัดการที่ดี โปร่งใสและตรวจสอบได้)</p>';
	$tables->thead=array('ชื่อ Best Practice','วิธีการทำให้เกิด Best Practice','ผลของ Best Practice / การนำไปใช้ประโยชน์',$is_edit?'<a href="" data-action="add" data-group="follow:'.$section.'"><i class="icon -add"></i></a>':'');
	foreach ($rs->items[$section] as $irs) {
		$tables->rows[]=array(
						view::inlineedit(array('group'=>'follow:'.$section,'fld'=>'detail1','tr'=>$irs->trid),$irs->detail1,$is_edit),
						view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>$irs->text1),$irs->text1,$is_edit,'textarea'),
						view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>$irs->text2),$irs->text2,$is_edit,'textarea'),
						$is_edit?'<a href="" data-action="del" data-group="follow:'.$section.'" data-tr="'.$irs->trid.'" title="ลบรายการนี้">X</a>':''
						);
	}

	$ret .= $tables->build();

	$section='2.3.3';
	$no=0;

	$tables = new Table();
	$tables->caption='<h6>'.$section.' เกิดแกนนำ/ผู้นำการเปลี่ยนแปลงด้านการสร้างเสริมสุขภาพในประเด็นต่าง ๆ</h6>';
	$tables->thead=array('ชื่อ-สกุล','ที่อยู่ติดต่อได้สะดวก','คุณสมบัติแกนนำ/ผู้นำการเปลี่ยนแปลง',$is_edit?'<a href="" data-action="add" data-group="follow:'.$section.'"><i class="icon -add"></i></a>':'');
	foreach ($rs->items[$section] as $irs) {
		$tables->rows[]=array(	view::inlineedit(array('group'=>'follow:'.$section,'fld'=>'detail1','tr'=>$irs->trid),$irs->detail1,$is_edit),
													view::inlineedit(array('group'=>'follow:'.$section,'fld'=>'detail2','tr'=>$irs->trid),$irs->detail2,$is_edit),
													view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>$irs->text1),$irs->text1,$is_edit,'textarea'),
													$is_edit?'<a href="" data-action="del" data-group="follow:'.$section.'" data-tr="'.$irs->trid.'" title="ลบรายการนี้">X</a>':'');
	}

	$ret .= $tables->build();

	$section='2.3.4';
	$no=0;

	$tables = new Table();
	$tables->caption='<h6>'.$section.' มีสภาพแวดล้อมและปัจจัยทางสังคมที่เอื้อต่อสุขภาพ</h6><p>เกิดการเปลี่ยนแปลงเชิงกายภาพและสังคมที่เอื้อต่อสุขภาพในชุมชนพื้นที่โครงการดังนี้</p>';
	$tables->thead=array('สถานที่/พื้นที่ ที่เปลี่ยนแปลง','รายละเอียดการเปลี่ยนแปลงที่เกิดขึ้น',$is_edit?'<a href="" data-action="add" data-group="follow:'.$section.'"><i class="icon -add"></i></a>':'');
	foreach ($rs->items[$section] as $irs) {
		$tables->rows[]=array(	view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>$irs->text1),$irs->text1,$is_edit,'textarea'),
													view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>$irs->text2),$irs->text2,$is_edit,'textarea'),
													$is_edit?'<a href="" data-action="del" data-group="follow:'.$section.'" data-tr="'.$irs->trid.'" title="ลบรายการนี้">X</a>':'');
	}

	$ret .= $tables->build();

	$ret.='</div>';

	// Part 3
	$ret.='<a name="part3"></a>';
	$ret.='<div id="part3"><h4>ส่วนที่ 3 : ปัญหาและอุปสรรคสำคัญที่มีผลกระทบต่อการดำเนินงาน</h4>';

	$section='3.1';
	$no=0;

	$tables = new Table();
	$tables->caption='<h5>'.$section.' การดำเนินงานกจิกรรม/กลุ่มเป้าหมาย/ระยะเวลาดำเนินงาน/การดำเนินงาน/งบประมาณ</h5>';
	$tables->thead=array('ประเด็นปัญหา/อุปสรรค','การแก้ไขของผู้รับทุน','ข้อเสนอแนะ/การแก้ไขปัญหาและการเสริมพลังของผู้ติดตาม',$is_edit?'<a href="" data-action="add" data-group="follow:'.$section.'"><i class="icon -add"></i></a>':'');
	foreach ($rs->items[$section] as $irs) {
		$tables->rows[]=array(	view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>$irs->text1),$irs->text1,$is_edit,'textarea'),
													view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>$irs->text2),$irs->text2,$is_edit,'textarea'),
													view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text3','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>$irs->text3),$irs->text3,$is_edit,'textarea'),
													$is_edit?'<a href="" data-action="del" data-group="follow:'.$section.'" data-tr="'.$irs->trid.'" title="ลบรายการนี้">X</a>':'');
	}

	$ret .= $tables->build();


	$section='3.2';
	$no=0;
	
	$tables = new Table();
	$tables->caption='<h5>'.$section.' การดำเนินงานกจิกรรม/กลุ่มเป้าหมาย/ระยะเวลาดำเนินงาน/การดำเนินงาน/งบประมาณ</h5>';
	$tables->colgroup=array(
													'width="30%"',
													'align="centet" width="20px"',
													'align="centet" width="20px"',
													'align="centet" width="20px"',
													'align="centet" width="20px"',
													'width="60%"',
												);
	$tables->thead='<thead><tr><th rowspan="2">ประเภทความเสี่ยง / ปัจจัยเสี่ยง</th><th colspan="4">ระดับความเสี่ยง<br />(จากมากไปหาน้อย)</th><th rowspan="2">ข้อมูล ข้อสังเกตุ และข้อคิดเห็นของผู้ติดตาม</th></tr><tr><th>3</th><th>2</th><th>1</th><th>0</th></tr></thead>';

	$risks=array('1'=>'ความเสี่ยงด้านการดำเนินงาน (Operational Risks)',
							'1.1'=>'โครงสร้างการดำเนินงาน',
							'1.2'=>'ศักยภาพและทักษะการดำเนินงาน',
							'1.3'=>'ผลลัพธ์และผลสำเร็จของการดำเนินงาน',
							'2'=>'ความเสี่ยงทางการเงิน (Financial Risks)',
							'2.1'=>'ระบบและกลไกการบริหารจัดการ',
							'2.2'=>'การใช้จ่ายเงิน',
							'2.3'=>'หลักฐานการเงิน'
							);
	foreach ($risks as $k=>$v) {
		if (strlen($k)==1) $tables->rows[]='<tr><td colspan="6"><strong>'.$k.'. '.$v.'</strong></td></tr>';
		else {
			$isection=$section.'.'.$k;
			$irs=end($rs->items[$isection]);
			$row[]=$k.' '.$v;
			for ($i=3;$i>=0;$i--) {
				$row[]='<input type="radio" '.($is_edit?' ':'disabled="disabled" ').'data-group="follow:'.$isection.'" data-fld="rate1" data-tr="'.$irs->trid.'" name="s'.$isection.'" class="inline-edit-field" data-type="radio" value="'.$i.'" '.(isset($irs->rate1) && $irs->rate1==$i ? ' checked="checked"':'').' />';
			}
			if (isset($irs->rate1)) $rates[$irs->rate1]++;
//			$ret.=print_o($irs,'$irs');
			$row[]=view::inlineedit(array('group'=>$formid.':'.$isection,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>$irs->text1),$irs->text1,$is_edit,'textarea');

			$tables->rows[]=$row;
			unset($row);
		}
	}
	$rateSum=3*$rates[3]+2*$rates[2]+1*$rates[1]+0*$rates[0];
	$tables->tfoot[]=array('<td align="right">ผลรวม</td>',3*$rates[3],2*$rates[2],1*$rates[1],0*$rates[0],'');
	$tables->tfoot[]=array('<td align="right">ผลรวมทั้งหมด</td>','<td colspan="4" align="center">'.$rateSum.'</td>','ระดับความเสี่ยง : ???<br />เกณฑ์วัดระดับความเสี่ยง ???');

	$ret .= $tables->build();

	$section='3.2.9';
	$irs=end($rs->items[$section]);
	$ret.='สรุปการแก้ไขความเสี่ยง <input type="radio" '.($is_edit?'':'disabled="disabled" ').'data-group="follow:'.$section.'" data-fld="rate1" data-tr="'.$irs->trid.'" name="s'.$section.'" class="inline-edit-field" data-type="radio" value="1" '.(isset($irs->rate1) && $irs->rate1==1 ? ' checked="checked"':'').' /> แก้ไขแล้ว <input type="radio" data-group="follow:'.$section.'" data-fld="rate1" tr="'.$irs->trid.'" name="s'.$section.'" class="inline-edit-field" data-type="radio" value="0" '.(isset($irs->rate1) && $irs->rate1==0 ? ' checked="checked"':'').' /> ยังไม่ได้แก้ไข';

	$ret.='</div>';

	// Part 4
	$ret.='<a name="part4"></a>';
	$ret.='<div id="part4"><h4>ส่วนที่ 4 : สรุปความเห็นของผู้ติดตาม</h4>';



	$tables = new Table();
	$tables->thead=array('ส่วนที่ 4','สรุปความเห็นของผู้ติดตาม');

	$section='4.1';
	$irs=end($rs->items[$section]);
	$tables->rows[]=array('<td rowspan="3">4.1 กรณีเบิกเงินงวด/ติดตามเยี่ยมชม</td>','<input type="radio" '.($is_edit?'':'disabled="disabled" ').'data-group="follow:'.$section.'" data-fld="rate1" data-tr="'.$irs->trid.'" name="s'.$section.'" class="inline-edit-field" data-type="radio" value="2" '.(isset($irs->rate1) && $irs->rate1==2 ? ' checked="checked"':'').' /> มีแนวโน้มสำเร็จตามเป้าหมายโครงการและติดตามปกติ<br />การวิเคราะห์ผลการดำเนินงานโครงการและสรุปข้อคิดเห็น'.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>$irs->text1),$irs->text1,$is_edit,'textarea'));

	$tables->rows[]=array('<input type="radio" '.($is_edit?'':'disabled="disabled" ').'data-group="follow:'.$section.'" data-fld="rate1" data-tr="'.$irs->trid.'" name="s'.$section.'" class="inline-edit-field" data-type="radio" value="1" '.(isset($irs->rate1) && $irs->rate1==1 ? ' checked="checked"':'').' /> มีแนวโน้มเสี่ยง ต้องติดตามอย่างใกล้ชิด เนื่องจาก'.view::inlineedit(array('group'=>'follow:'.$section,'fld'=>'detail1','tr'=>$irs->trid),$irs->detail1,$is_edit,'textarea').'<br />การวิเคราะห์ผลการดำเนินงานโครงการและสรุปข้อคิดเห็น'.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>$irs->text2),$irs->text2,$is_edit,'textarea'));

	$tables->rows[]=array('<input type="radio" '.($is_edit?'':'disabled="disabled" ').'data-group="follow:'.$section.'" data-fld="rate1" data-tr="'.$irs->trid.'" name="s'.$section.'" class="inline-edit-field" data-type="radio" value="0" '.(isset($irs->rate1) && $irs->rate1==0 ? ' checked="checked"':'').' /> มีความเสี่ยง ต้องยุติโครงการ เนื่องจาก<br />'.view::inlineedit(array('group'=>'follow:'.$section,'fld'=>'detail2','tr'=>$irs->trid),$irs->detail2,$is_edit,'textarea'));

	$section='4.2';
	$irs=end($rs->items[$section]);
	$tables->rows[]=array('<td rowspan="2">4.2 กรณีสรุปปิดโครงการ</td>','<input type="radio" '.($is_edit?'':'disabled="disabled" ').'data-group="follow:'.$section.'" data-fld="rate1" data-tr="'.$irs->trid.'" name="s'.$section.'" class="inline-edit-field" data-type="radio" value="1" '.(isset($irs->rate1) && $irs->rate1==1 ? ' checked="checked"':'').' /> ดำเนินงานได้ตามแผนปฏิบัติการและสามารถปิดโครงการได้<br />สรุปผลภาพรวมการดำเนินงาน-การเงินโครงการและข้อคิดเห็น'.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>$irs->text1),$irs->text1,$is_edit,'textarea'));

	$tables->rows[]=array('<input type="radio" '.($is_edit?'" ':'disabled="disabled" ').'data-group="follow:'.$section.'" data-fld="rate1" data-tr="'.$irs->trid.'" name="s'.$section.'" class="inline-edit-field" data-type="radio" value="0" '.(isset($irs->rate1) && $irs->rate1==0 ? ' checked="checked"':'').' /> ไม่เป็นไปตามแผนปฏิบัติการ ให้ดำเนินการจัดระบบการเงิน ระบบรายงานให้ถูกต้องก่อนปิดโครงการ<br />สรุปผลภาพรวมการดำเนินงาน-การเงินโครงการและข้อคิดเห็น'.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>$irs->text2),$irs->text2,$is_edit,'textarea'));

	$ret .= $tables->build();

	$ret.='</div>';

	// Part 5
	$ret.='<a name="part5"></a>';
	$ret.='<div id="part5"><h4>ส่วนที่ 5 : สรุปภาพรวมของการติดตามประจำงวด (ข้อสังเกต/สิ่งดีๆ ที่ค้นพบ/ข้อพึงระวัง/บทเรียนที่ได้)</h4>';
	$section='5.1';
	$irs=end($rs->items[$section]);
	$ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>$irs->text1),$irs->text1,$is_edit,'textarea');
	$ret.='</div>';

	$title=end($rs->items['title']);
	$ret.='<p class="noprint">สร้างรายงานโดย <img class="ownerphoto" src="'.model::user_photo($rs->username,false).'" width="32" height="32" alt="'.htmlspecialchars($rs->ownerName).'" title="'.htmlspecialchars($rs->ownerName).'" />'.$title->posterName.'</p>';

	if ($is_edit) {
			$ret.='<script type="text/javascript"><!--
			$(document).ready(function() {
				var postUrl=$(".inline-edit").attr("url");
				$("#part1, #part2, #part3, #part4, #part5").css("display","none");
				$(currentPart).css("display","block");

			});
			</script>';
	}

	return $ret;
}
?>