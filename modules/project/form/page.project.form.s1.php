<?php
/**
 * แบบรายงานความก้าวหน้าโครงการ (ส.1)
 *
 * @param Object $topic
 * @param Object $para
 * @param String $report
 */
function project_form_s1($self,$topic,$para,$report) {
	// RELOCATE TO NEW REPORT
	location('project/'.$topic->tpid.'/operate.result/'.$para->period);

	return;

	$tpid=$topic->tpid;
	$formid='ส.1';
	$period=$para->period;
	$action=post('act');
	$order=SG\getFirst(post('order'),'mainact');

	//$ret.='tpid='.$tpid.' period='.$period.print_o(post(),'post').print_o($para,'$para');
	$isAdmin=user_access('administer projects');
	$isOwner=project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid);
	$isEdit=($topic->project->project_statuscode==1) && ($isAdmin || $isOwner);
	$isAccessExpense=user_access('access full expense') || $isOwner;

	switch ($action) {
		case 'addtr' :
			list($formid,$part)=explode(':',post('group'));
			//$ret.='FormId='.$formid.' Part='.$part;
			mydb::query('INSERT INTO %project_tr% (`tpid`, `formid`, `part`, `period`, `created`, `uid`) VALUES (:tpid, :formid, :part, :period, :created, :uid)',':tpid',$tpid, ':formid',$formid, ':part',$part, ':period',$period, ':created', date('U'), ':uid',i()->uid);
			//$ret.=mydb()->_query;
			break;

		case 'addleader' :
			$ret.=__project_form_s1_addleader($tpid,$period);
			return $ret;
			break;

		case 'removeleader' :
			if ($isEdit && ($psnid=post('id')) && SG\confirm()) {
				$ret.='Remove';
				mydb::query('DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `parent`=:psnid AND `formid`="leader"', ':tpid',$tpid, ':psnid',$psnid);
				$ret.=mydb()->_query;
			}
			return $ret;
			break;

		case 'addinno' :
			$ret.=__project_form_s1_addinno($tpid);
			return $ret;
			break;

	}

	$currentReport=mydb::select('SELECT `period`, COUNT(*) reportItems FROM %project_tr% tr WHERE tr.`tpid`=:tpid AND tr.`formid`=:formid GROUP BY `period`',':tpid',$tpid,':formid',$formid);
	$allReport=$currentReport->_num_rows;

	if (!$period) {
		if ($allReport) {
			$ret.='<p>กรุณาเลือกงวดของรายงาน'.($isEdit?'หรือสร้างรายงานงวดใหม่':'').'</p>';
			foreach ($currentReport->items as $item) {
				$ret.='<a class="btn" href="'.url('paper/'.$tpid.'/owner/s1/period/'.$item->period).'">รายงาน '.$formid.' งวดที่ '.$item->period.'</a>';
				$nextPeriod=$item->period+1;
			}
			if ($isEdit && $allReport<cfg('project.period.max')-2) $ret.=' หรือ <a class="sg-action btn -primary" href="'.url('paper/'.$tpid.'/owner/s1/period/'.$nextPeriod,'action=create').'" data-confirm="ยืนยันการสร้างรายงาน '.$formid.'?"><i class="icon -addbig -white"></i><span>สร้างรายงาน '.$formid.' งวดที่ '.$nextPeriod.'</span></a>';
		} else {
			$ret.='<p class="notify">โครงการยังไม่มีรายงาน '.$formid.'</p>';
			$nextPeriod=1;
			if ($isEdit) $ret.='<p>ต้องการสร้างรายงาน '.$formid.' หรือไม่?</p><a class="btn -primary" href="'.url('paper/'.$tpid.'/owner/s1/period/1','action=create').'" confirm="ยืนยันการสร้างรายงาน '.$formid.'?"><i class="icon -addbig -white"></i><span>สร้างรายงาน '.$formid.'</span></a> <a class="btn" href="'.url('paper/'.$tpid.'/owner').'">ไม่สร้าง</a>';
		}
		return $ret;
	}

	if ($isEdit && post('action')) {
		switch (post('action')) {
			case 'create' :
					mydb::query('INSERT INTO %project_tr% (`tpid`, `formid`, `period`, `part`, `detail1`, `uid`, `created`) VALUES (:tpid, :formid, :period, :part, :detail1, :uid, :created)', ':tpid', $tpid, ':formid', $formid, ':period', $period, ':part','title', ':detail1', date('d/m/Y'), ':uid', i()->uid, ':created', date('U'));
					location('paper/'.$tpid.'/owner/s1/period/'.$period);
					break;
			case 'remove' :
					mydb::query('DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="ส.1" AND `period`=:period',':tpid',$tpid,':period',$period);
					location('paper/'.$tpid.'/owner/s1');
					break;
		}
	}

	$rs=project_model::get_tr($tpid,$formid,$period);
	$periodInfo=project_model::get_period($tpid,$period);
	$activities=R::Model('project.activity.get.bytpid',$tpid,
								'{owner:'._PROJECT_OWNER_ACTIVITY.', period:'.$period.'}'
								);

	$locked=$periodInfo->flag>=_PROJECT_LOCKREPORT;
	if ($locked) $isEdit=false;
	//$ret.=print_o($periodInfo,'$periodInfo');
	//$ret.=print_o($activities,'$activities');

	$url='paper/'.$tpid.'/owner/s1/period/'.$period;

	// Show form toolbar
	$ui=new ui();
	$ui->add('<a class="btn -link" href="'.url($url).'">รายงานความก้าวหน้าโครงการ (ส.1) งวดที่ '.$period.'</a>');
	if ($order=='mainact') {
		$ui->add('<a class="btn -link" href="'.url($url,array('order'=>'date')).'">เรียงกิจกรรมตามวันที่</a>');
	} else {
		$ui->add('<a class="btn -link" href="'.url($url).'">เรียงกิจกรรมตามกิจกรรมหลัก</a>');
	}
	$ui->add('<a class="btn -link" href="javascript:void(0)">สถานะรายงาน : '.($locked?'Locked':'Unlock').'</a>');
	//$ui->add('<a href="'.url('paper/'.$tpid.'/owner/m1/period/'.$period,array('o'=>'word','a'=>'download')).'">ดาวน์โหลด</a>');
	$ui->add('<a class="btn -link" href="javascript:window.print()">พิมพ์</a>');
	if ($isEdit) {
		$subui=new ui();
		$subui->add('<a href="'.url($url,array('action'=>'remove')).'" class="sg-action" data-confirm="ยืนยันการลบรายงาน?">ลบรายงาน</a>');
		$ui->add(sg_dropbox($subui->build('ul')));
	}

	$ret.='<nav class="nav -page -no-print">'.$ui->build('ul').'</nav>';


	if ($isEdit) {
		$inlineAttr['data-update-url']=url('project/edit/tr');
		$inlineAttr['data-period']=$period;
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}
	$ret.='<div id="project-report-s1" class="inline-edit project__report project__report--s1" '.sg_implode_attr($inlineAttr).'>'._NL;

	$section='title';
	$irs=end($rs->items[$section]);

	$ret.='<p class="form-info">รหัสโครงการ <strong>'.$topic->project->prid.'</strong><br />สัญญาเลขที่ <strong>'.$topic->project->agrno.'</strong><br />งวดที่ <strong>'.$irs->period.'</strong></p>'._NL;
	$ret.='<h2>แบบรายงานความก้าวหน้าโครงการ (ส.1)</h2>'._NL;

	$ret.='<p>ชื่อโครงการ <strong>'.$topic->title.'</strong></p>'._NL;
	if ($topic->project->area)
		$ret.='<p>ชุมชน <strong>'.$topic->project->area.'</strong></p>'._NL;
	$ret.='<p>รหัสโครงการ <strong>'.$topic->project->prid.'</strong> เลขที่ข้อตกลง <strong>'.$topic->project->agrno.'</strong></p>'._NL;
	$ret.='<p>ระยะเวลาดำเนินงาน ตั้งแต่ <strong>'.sg_date($topic->project->date_from,'ว ดดด ปปปป').' ถึง '.sg_date($topic->project->date_end,'ว ดดด ปปปป').'</strong></p>'._NL;


	$ret.='<p>รายงานงวดที่ : <strong>'.$period.'</strong> จากเดือน <strong>'.sg_date(SG\getFirst($periodInfo->report_from_date,$periodInfo->from_date),'ดดด ปปปป').'</strong> ถึงเดือน <strong>'.sg_date(SG\getFirst($periodInfo->report_to_date,$periodInfo->to_date),'ดดด ปปปป').'</strong></p>'._NL;

	$ret.='<h3>ส่วนที่ 1 ผลการดำเนินโครงการ <span>(แสดงผลการดำเนินงานรายกิจกรรมที่แสดงผลผลิตและผลลัพธ์ที่เกิดขึ้นจริง</span></h3>'._NL;

	// Get activity from project_tr that between report date

	if ($order=='mainact') {
		$mainact=project_model::get_main_activity($tpid, 'owner', $period);

		$tables = new Table();
		$tables->addClass('project-report-s1-item');
		$tables->colgroup=array('summary'=>'width="80%"','amt output1'=>'width="10%"','amt output2'=>'width="10%"');
		$activityHead='<tr><th rowspan="2">วัตถุประสงค์ที่ตั้งไว้<br />ผลลัพธ์และตัวชี้วัดผลลัพธ์<sup>**</sup><br />กิจกรรมของโครงการ</th><th colspan="2">ผลผลิต<sup>*</sup></th></tr><tr><th>ผลผลิตที่ตั้งไว้</th><th>ผลผลิตที่เกิดขึ้นจริง</th></tr>';

		$mno=0;
		foreach ($mainact->info as $mrs) {
			//$ret.='<h3>'.$mrs->title.'</h3>';
			$tables->rows[]=array('<td colspan="3"><h3 style="text-align:left;">'.(++$mno).'. '.$mrs->title.'</h3></td>');
			$no=0;
			if (empty($mainact->activity[$mrs->trid])) {
				$tables->rows[]=array('<td colspan="3">ไม่มีกิจกรรม</td>');
				continue;
			}
			$no=0;

			foreach ($mainact->activity[$mrs->trid] as $key => $activity) {
				$tables->rows[]=$activityHead;
				unset($row);
				$summary='';

				$tables->rows[]=array('<td colspan="3"><h4 style="text-align:left;">'.$mno.'.'.(++$no).' '.$activity->title.'</h4></td>');

				$summary.='<strong>วันที่ '.sg_date($activity->action_date,'ว ดดด ปปปป').' เวลา '.$activity->action_time.' น.</strong>';
				$summary.='<h5>วัตถุประสงค์ที่ตั้งไว้</h5>';
				$summary.=sg_text2html($activity->objective);

				$summary.='<h5>ผลลัพธ์ที่ตั้งไว้</h5>'.sg_text2html($activity->presetOutputOutcome)._NL;
				$summary.='<h5>ผลลัพธ์ที่เกิดขึ้นจริง</h5>'.sg_text2html($activity->real_work)._NL;

				$summary.='<h5>กิจกรรมที่กำหนดไว้ในแผน</h5>'._NL.sg_text2html($activity->goal_do)._NL;
				$summary.='<h5>กิจกรรมที่ทำจริง</h5>'._NL.sg_text2html($activity->real_do)._NL;

				if ($activity->gallery) {
					if (debug('method')) $summary.=$activity->photos.'<br />'.print_o($activity,'$activity');
					$summary.='<div class="photo">'._NL;
					$summary.='<ul>'._NL;
					foreach (explode(',',$activity->photos) as $item) {
						list($photoid,$photo)=explode('|',$item);
						if (substr($photo,0,12)=='project_rcv_' && !$isAccessExpense) continue;
						$photo=model::get_photo_property($photo);
						$photo_alt=$item->title;
						$summary .= '<li>';
						$summary.='<img height="80" class="photo photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" ';
						$summary.=' />';
						$summary.=$item->title;
						$summary .= '</li>'._NL;
					}
					$summary.='</ul>'._NL;
					$summary.='</div><!--photo-->'._NL;
				}
				$summary.='<p>&nbsp;</p>';

				$row[]=$summary;
				$row[]=number_format($activity->targetpreset);
				$row[]=number_format($activity->targetjoin);
				$tables->rows[]=$row;
			}
		}

		$ret .= $tables->build();

	} else {
		$tables = new Table();
		$tables->addClass('project-report-s1-item');
		$tables->colgroup=array('summary'=>'width="80%"','amt output1'=>'width="10%"','amt output2'=>'width="10%"');
		$tables->thead='<thead><tr><th rowspan="2">วัตถุประสงค์ที่ตั้งไว้<br />ผลลัพธ์และตัวชี้วัดผลลัพธ์<sup>**</sup><br />กิจกรรมของโครงการ</th><th colspan="2">ผลผลิต<sup>*</sup></th></tr><tr><th>ผลผลิตที่ตั้งไว้</th><th>ผลผลิตที่เกิดขึ้นจริง</th></tr></thead>';

		$no=0;
		foreach ($activities->items as $activity) {
			unset($row);
			$summary='';

			$tables->rows[]=array('<td colspan="3"><h3 style="text-align:left;">'.(++$no).'. '.$activity->title.'</h3></td>');

			$summary.='<strong>วันที่ '.sg_date($activity->action_date,'ว ดดด ปปปป').' เวลา '.$activity->action_time.' น.</strong>';
			$summary.='<h4>วัตถุประสงค์ที่ตั้งไว้</h4>';
			$summary.=sg_text2html($activity->objective);

			$summary.='<h4>ผลลัพธ์ที่ตั้งไว้</h4>'.sg_text2html($activity->presetOutputOutcome)._NL;
			$summary.='<h4>ผลลัพธ์ที่เกิดขึ้นจริง</h4>'.sg_text2html($activity->real_work)._NL;

			$summary.='<h4>กิจกรรมที่กำหนดไว้ในแผน</h4>'._NL.sg_text2html($activity->goal_do)._NL;
			$summary.='<h4>กิจกรรมที่ทำจริง</h4>'._NL.sg_text2html($activity->real_do)._NL;

			if ($activity->gallery) {
				if (debug('method')) $summary.=$activity->photos.'<br />'.print_o($activity,'$activity');
				$summary.='<div class="photo">'._NL;
				$summary.='<ul>'._NL;
				foreach (explode(',',$activity->photos) as $item) {
					list($photoid,$photo)=explode('|',$item);
					if (substr($photo,0,12)=='project_rcv_' && !$isAccessExpense) continue;
					$photo=model::get_photo_property($photo);
					$photo_alt=$item->title;
					$summary .= '<li>';
					$summary.='<img height="80" class="photo photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" ';
					$summary.=' />';
					$summary.=$item->title;
					$summary .= '</li>'._NL;
				}
				$summary.='</ul>'._NL;
				$summary.='</div><!--photo-->'._NL;
			}
			$summary.='<p>&nbsp;</p>';

			$row[]=$summary;
			$row[]=number_format($activity->targetpreset);
			$row[]=number_format($activity->targetjoin);
			$tables->rows[]=$row;
		}

		$ret .= $tables->build();
	}

	$ret.='<p>* ผลผลิต หมายถึง ผลที่เกิดขึ้นเชิงปริมาณจากการทำกิจกรรม เช่น จำนวนผู้เข้าร่วมประชุม จำนวนผู้ผ่านการอบรม จำนวนครัวเรือนที่ปลูกผักสวนครัว เป็นต้น<br />** ผลลัพธ์ หมายถึง การเปลี่ยนแปลงที่นำไปสู่การแก้ปัญหา เช่น หลังอบรมมีผู้ปรับเปลี่ยนพฤติกรรมจำนวนกี่คน มีข้อบังคับหรือมาตรการของชุมชนที่นำไปสู่การปรับเปลี่ยนพฤติกรรมหรือสภาพแวดล้อม เป็นต้น ทั้งนี้ต้องมีข้อมูลอ้างอิงประกอบการรายงาน เช่น ข้อมูลรายชื่อแกนนำ , แบบสรุปการประเมินความรู้ , รูปภาพกิจกรรมพร้อมคำอธิบายใต้ภาพ เป็นต้น</p>'._NL;







	$ret.='<h3>ส่วนที่ 2 ประเมินความก้าวหน้าของการดำเนินงานโครงการและปัญหา/อุปสรรคในการดำเนินโครงการ</h3>';
	$ret.='<h4>ประเมินความก้าวหน้าของการดำเนินงานโครงการ</h4>';

	$totalCalendar=mydb::select('SELECT COUNT(*) total FROM %calendar% c LEFT JOIN %project_activity% a ON a.`calid`=c.`id` WHERE `tpid`=:tpid AND `calowner`=1 LIMIT 1',':tpid',$tpid,':reportdate',$periodInfo->report_to_date)->total;
	$totalActivity=mydb::select('SELECT COUNT(*) total FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="activity" AND `part`="owner" AND `date1`<=:reportdate LIMIT 1',':tpid',$tpid,':reportdate',$periodInfo->report_to_date)->total;

	$activityPercent=round($totalActivity*100/$totalCalendar);


	$totalBudget=$topic->project->budget;
	$totalSpend=mydb::select('SELECT SUM(`num7`) total FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="activity" AND `part`="owner" AND `date1`<=:reportdate LIMIT 1',':tpid',$tpid,':reportdate',$periodInfo->report_to_date)->total;
	$spendPercent=round($totalSpend*100/$totalBudget);

	$rateRs=mydb::select('SELECT SUM(`rate1`) rate,4*COUNT(*) total FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="activity" AND `part`="owner" AND `date1`<=:reportdate LIMIT 1',':tpid',$tpid,':reportdate',$periodInfo->report_to_date);
	$totalPoint=$rateRs->rate;
	$totalRate=$rateRs->total;
	$ratePercent=round($totalPoint*100/$totalRate);

	$tables = new Table();
	$tables->addClass('project__report--percent');
	$tables->thead=array('การดำเนินงานเมื่อเทียบกับการดำเนินงานทั้งโครงการ','ทั้งหมด','ทำแล้ว','10%','20%','30%','40%','50%','60%','70%','80%','90%','100%');
	$row=array();
	$row[]='การทำกิจกรรม';
	$row[]=$totalCalendar;
	$row[]=$totalActivity;
	for ($i=0;$i<100;$i=$i+10) $row[]=$activityPercent>$i && $activityPercent<=$i+10?'<span title="'.$activityPercent.'%">✔</span>':'&nbsp;';
	$tables->rows[]=$row;

	$row=array();
	$row[]='การใช้จ่ายงบประมาณ';
	$row[]=number_format($totalBudget,2);
	$row[]=number_format($totalSpend,2);
	for ($i=0;$i<100;$i=$i+10) $row[]=$spendPercent>$i && $spendPercent<=$i+10?'<span title="'.$spendPercent.'%">✔</span>':'&nbsp;';
	$tables->rows[]=$row;

	$row=array();
	$row[]='คุณภาพกิจกรรม';
	$row[]=$totalRate;
	$row[]=$totalPoint;
	for ($i=0;$i<100;$i=$i+10) $row[]=$ratePercent>$i && $ratePercent<=$i+10?'<span title="'.$ratePercent.'%">✔</span>':'&nbsp;';
	$tables->rows[]=$row;

	$ret .= $tables->build();

	$ret.='<style type="text/css">.project__report--percent td:nth-child(n+2) {text-align:center;} .project__report--percent td:nth-child(n+4) {font-size:20pt;}</style>';





	$ret.='<h4>ปัญหา/อุปสรรคในการดำเนินงานโครงการ (สรุปเป็นข้อ ๆ)</h4>'._NL;

	$tables = new Table();
	$tables->thead=array('ประเด็นปัญหา/อุปสรรค','สาเหตุเพราะ','แนวทางการแก้ไขของผู้รับทุน');
	$tables->rows[]=array(
				view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html'),$irs->text1,$isEdit,'textarea'),
				view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text5','tr'=>$irs->trid,'ret'=>'html'),$irs->text5,$isEdit,'textarea'),
				view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text6','tr'=>$irs->trid,'ret'=>'html'),$irs->text6,$isEdit,'textarea'),
				);

	$ret .= $tables->build();




	$ret.='<h3>ส่วนที่ 3 ผลลัพธ์ของโครงการที่สอดคล้องกับเป้าหมายตัวชี้วัดของแผนสร้างสรรค์โอกาสและนวัตกรรมสุขภาวะ</h3>';
	$ret.='<h4>แกนนำด้านการสร้างเสริมสุขภาพในประเด็นต่าง ๆ</h4>';
	$ret.='<p>(แกนนำด้านการสร้างเสริมสุขภาพ หมายถึง ผู้ผลักดันหรือผู้ทำให้เกิดการสร้างเสริมสุขภาพ ชักชวนผู้อื่นมาร่วมทำกิจกรรมที่ทำให้มีสุสภาพที่ดีขึ้น เช่น แกนนำเลิกเหล้า บุหรี่)</p>';
	$ret.='<p><strong>เกิดแกนนำ จำนวน คน</strong> โปรดระบุข้อมูลของแกนนำ ตามตัวอย่างตารางด้านล่าง พร้อมแนบมากับรายงาน</p>';


	$section='2.3.3';
	unset($tables);
	$ret.=__project_form_s1_listleader($tpid,$period,$isEdit);





	$ret.='<h4>นวัตกรรมสร้างเสริมสุขภาพระดับพื้นที่</h4>';
	$ret.='<p>นวัตกรรมสร้างเสริมสุขภาพระดับพื้นที่ หมายถึง การจัดการความคิด กลไก หรือกระบวนการ และ/หรือเทคโนโลยีที่เหมาะสม นำมาใช้ในการดำเนินงานโครงการหรือใช้ในการจัดการปัญหาสุขภาพของพื้นที่แล้วได้ผลดีกว่าเดิม</p>';

	$ret.=__project_form_s1_listinno($tpid,$topic,$period,$isEdit);





	$ret.='<h4>แผนงาน/กิจกรรม ที่จะดำเนินการในงวดต่อไป</h4>'._NL;

	$useNextPeriod=1;

	if ($useNextPeriod==1) {
		$allPeriod=count(project_model::get_period($tpid));
		// Do not show activity on last report
		if ($period<$allPeriod-1) {
			$nextActDbs=project_model::get_calendar($tpid,$period+1,'owner');
			$ret.='<ol>'._NL;
			foreach ($nextActDbs->items as $irs) {
				$ret.='<li>'.$irs->title.' ( '.sg_date($irs->from_date).($irs->to_date!=$irs->from_date?' - '.sg_date($irs->to_date):'').' )</li>'._NL;
			}
			$ret.='</ol>'._NL;
		}
	}

	$section='title';
	$irs=end($rs->items[$section]);

	//$ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid,'ret'=>'html'),SG\getFirst($irs->text2,'1. '._NL.'2. '._NL.'3. '),$isEdit,'textarea');

	$ret.='<div style="width:40%;float:left;margin:60px 5% 30px; text-align:center;"><p>(................................)<br />'.$topic->project->prowner.'<br />ผู้รับผิดชอบโครงการ<br />'.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail1','tr'=>$irs->trid,'ret'=>'date:ว ดดด ปปปป', 'value'=>$irs->detail1),$irs->detail1?sg_date($irs->detail1,'ว ดดด ปปปป'):'',$isEdit,'datepicker').'</p></div>'._NL;


	$ret.='</div>';

	return $ret;
}

/**
* Add leader
* @param Integer $tpid
* @param String $formid
* @return String
*/
function __project_form_s1_addleader($tpid,$period) {
	$ret.='<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3 class="title">แกนนำ</h3></header>';

	$ret.='<h3 class="title">รายละเอียดแกนนำด้านการสร้างเสริมสุขภาพ</h3>';
	$post=(object)post('person');
	if ($post->fullname) {
		$post->tpid=$tpid;
		if (empty($post->psnid)) $post->psnid=NULL;
		$post->uid=i()->uid;
		list($post->name,$post->lname)=sg::explode_name(' ',$post->fullname);
		$addr=SG\explode_address($post->address,$post->areacode);
		$post->house=$addr['house'];
		$post->village=$addr['village'];
		$post->tambon=$addr['tambonCode'];
		$post->ampur=$addr['ampurCode'];
		$post->changwat=$addr['changwatCode'];
		$post->zip=$addr['zip'];
		$post->created=date('U');

		$stmt='INSERT INTO %db_person%
						(`psnid`, `uid`, `prename`, `name`, `lname`, `house`, `village`, `tambon`, `ampur`, `changwat`, `phone`)
					VALUES
						(:psnid, :uid, :prename, :name, :lname, :house, :village, :tambon, :ampur, :changwat, :phone)
					ON DUPLICATE KEY UPDATE `prename`=:prename, `name`=:name, `lname`=:lname, `house`=:house, `village`=:village, `tambon`=:tambon, `ampur`=:ampur, `changwat`=:changwat, `phone`=:phone';
		mydb::query($stmt,$post);
		if (!$post->psnid) $post->psnid=mydb()->insert_id;
		//$ret.=mydb()->_query.mydb()->_error.'<br />';

		if ($post->leadertype && $post->psnid) {
			mydb::query('INSERT INTO %project_tr% (`tpid`, `formid`, `parent`, `gallery`, `uid`, `created`) VALUES (:tpid, "leader", :psnid, :leadertype, :uid, :created)',$post);
			$post->orgid=mydb()->insert_id;
			//$ret.=mydb()->_query;
		}

		//$ret.=__project_form_s1_listleader($tpid,$isEdit);
		return $ret;
	}
	if ($psnid) {
		$stmt='SELECT p.`psnid`, p.`cid`, p.`prename`, CONCAT(p.`name`," ",p.`lname`) fullname,
				s.`orgid`, s.`argtype`,
				o.`name` orgname,
				p.`phone`, p.`email`,
				p.`house`, p.`village`,
				IFNULL(cosub.`subdistname`,p.`t_tambon`) subdistname,
				IFNULL(codist.`distname`,p.`t_ampur`) distname,
				IFNULL(copv.`provname`,p.`t_changwat`) provname,
				CONCAT(p.`changwat`,p.`ampur`,p.`tambon`) areacode
			FROM %db_person% p
				LEFT JOIN %org_supplier% s USING(`psnid`)
				LEFT JOIN %db_org% o USING (`orgid`)
					LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
					LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
					LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))
					LEFT JOIN %co_province% copv ON p.`changwat`=copv.`provid`
			WHERE p.`psnid`=:psnid
			LIMIT 1';
		$rs=mydb::select($stmt,':psnid',$psnid);
		$rs->address=SG\implode_address($rs);
		$post=$rs;
	}

	if (empty($post)) $post=(object)post('person');

	$form->config->variable='person';
	$form->config->method='post';
	$form->config->action=url('paper/'.$tpid.'/owner/s1/period/'.$period);
	$form->config->class='sg-form';
	$form->config->attr='data-rel="box" data-done="close" data-callback="refreshContent" data-refresh-url="'.url('paper/'.$tpid.'/owner/s1/period/'.$period).'"';

	$form->act=array('type'=>'hidden','name'=>'act','value'=>'addleader');
	$form->psnid=array('type'=>'hidden','value'=>$post->psnid);
	$form->areacode=array('type'=>'hidden','value'=>htmlspecialchars($post->areacode));

	$form->prename->type='text';
	$form->prename->label='คำนำหน้านาม';
	$form->prename->size=10;
	$form->prename->value=htmlspecialchars($post->prename);

	$form->fullname->type='text';
	$form->fullname->label='ชื่อ - นามสกุล';
	$form->fullname->require=true;
	$form->fullname->size=40;
	$form->fullname->class='sg-autocomplete';
	$form->fullname->attr='data-altfld="edit-person-psnid" data-query="'.url('org/api/person').'" data-callback="orgSupplierAddMember"';
	$form->fullname->value=htmlspecialchars($post->fullname);

	$form->address->type='text';
	$form->address->label='ที่อยู่';
	$form->address->class='sg-address';
	$form->address->attr='data-altfld="edit-person-areacode"';
	$form->address->size=40;
	$form->address->value=htmlspecialchars($post->address);

	$form->phone->type='text';
	$form->phone->label='โทรศัพท์';
	$form->phone->size=40;
	$form->phone->value=htmlspecialchars($post->phone);

	$form->leadertype->type='radio';
	$form->leadertype->label='บทบาทแกนนำ';
	$form->leadertype->options=model::get_category('project:category','catid');
	$form->leadertype->value=SG\getFirst($post->leadertype,'รายย่อย');

	$form->submit->type='submit';
	$form->submit->items->save=tr('save');
	$form->submit->posttext='หรือ <a class="sg-action" data-rel="close" href="javascript:void(0)">ยกเลิก</a>';

	$ret .= theme('form','org-add-person',$form);

	$ret.='<script type="text/javascript">
	$("#edit-person-fullname").focus()
	function orgSupplierAddMember($this,ui) {
		//notify("Callback "+ui.item.desc)
		$.getJSON(url+"org/api/person?id="+ui.item.value,function(data) {
			$("#edit-person-prename").val(data.prename)
			$("#edit-person-address").val(data.address)
			$("#edit-person-phone").val(data.phone)
			$("#edit-person-email").val(data.email)
			$("#edit-person-areacode").val(data.areacode)
			$("#edit-person-orgname").focus()
		});
	}
	</script>';
	return $ret;
}

/**
* List leader name
* @param Integer $tpid
* @param Boolean $isEdit
* @return String
*/
function __project_form_s1_listleader($tpid,$period,$isEdit) {
	$stmt='SELECT l.`tpid`, l.`trid`, l.`parent` psnid, l.`gallery` catid
			, CONCAT(p.`prename`," ",p.`name`," ",p.`lname`) fullname
			, p.`house`, p.`village`
			, IFNULL(cosub.`subdistname`,p.`t_tambon`) subdistname
			, IFNULL(codist.`distname`,p.`t_ampur`) distname
			, IFNULL(copv.`provname`,p.`t_changwat`) provname
			, CONCAT(p.`changwat`,p.`ampur`,p.`tambon`) areacode
			, GROUP_CONCAT(c.`name`) leaderType
		FROM %project_tr% l
			LEFT JOIN %db_person% p ON p.`psnid`=l.`parent`
			LEFT JOIN %tag% c ON c.`taggroup`="project:category" AND c.`catid`=l.`gallery`
			LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
			LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
			LEFT JOIN %co_province% copv ON p.`changwat`=copv.`provid`
		WHERE `tpid`=:tpid AND `formid`="leader"
		GROUP BY `psnid`';
	$dbs=mydb::select($stmt,':tpid',$tpid);

	$tables = new Table();
	$tables->thead=array('ชื่อ-สกุล','ที่อยู่ติดต่อได้สะดวก','บทบาทแกนนำ','center'=>$isEdit?'<a class="sg-action" href="'.url('project/form/'.$tpid.'/s1/period/'.$period,array('act'=>'addleader')).'" data-rel="box" title="เพิ่มรายชื่อแกนนำ"><i class="icon -add"></i></a>':'');

	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			$rs->fullname,
			SG\implode_address($rs),
			$rs->leaderType,
			$isEdit?'<a class="sg-action hover--menu" href="'.url('project/form/'.$tpid.'/s1',array('act'=>'removeleader','id'=>$rs->psnid)).'" data-rel="this" data-confirm="ลบรายการนี้ กรุณายืนยัน" data-removeparent="tr">X</a>':''
			);
	}

	if ($isEdit) {
		$tables->rows[]=array('<td colspan="4" class="noprint" style="text-align:center;"><a class="sg-action btn" href="'.url('project/form/'.$tpid.'/s1/period/'.$period,array('act'=>'addleader')).'" data-rel="box" title="เพิ่มรายชื่อแกนนำ"><i class="icon -person-add"></i>เพิ่มรายชื่อแกนนำ</a></td>');
	}

	//$rs=project_model::get_tr($tpid,'follow');

	$no=0;
	//$tables->thead=array('ชื่อ-สกุล','ที่อยู่ติดต่อได้สะดวก','คุณสมบัติแกนนำ/ผู้นำการเปลี่ยนแปลง',$isEdit?'<a href="" data-action="add" data-group="follow:'.$section.'">+เพิ่ม</a>':'');
	foreach ($rs->items[$section] as $irs) {
		$tables->rows[]=array(
			view::inlineedit(array('group'=>'follow:'.$section,'fld'=>'detail1','tr'=>$irs->trid,'class'=>'w-9'),$irs->detail1,$isEdit),
			view::inlineedit(array('group'=>'follow:'.$section,'fld'=>'detail2','tr'=>$irs->trid,'class'=>'w-9'),$irs->detail2,$isEdit),
			view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>$irs->text1),$irs->text1,$isEdit,'textarea'),
			$isEdit?'<a href="" data-action="del" data-group="follow:'.$section.'" data-tr="'.$irs->trid.'" title="ลบรายชื่อแกนนำ">X</a>':''
		);
	}

	$ret .= $tables->build();

	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}

/**
* Add innovation
* @param Integer $tpid
* @return String
*/
function __project_form_s1_addinno($tpid,$trid) {
	$period=post('period');

	$ret.='<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3 class="title">นวัตกรรมสร้างเสริมสุขภาพ</h3></header>';

	$ret.='<h3 class="title">นวัตกรรมสร้างเสริมสุขภาพที่เกิดขึ้นในงวดที่ '.$period.'</h3>';
	$post=(object)post('inno');
	if ($post->title) {
		$post->tpid=$tpid;
		$post->trid=SG\getFirst($post->trid,NULL);
		$post->uid=i()->uid;
		$post->period=$period;
		$post->created=date('U');
		$post->modified=date('U');
		$post->modifyby=i()->uid;

		$stmt='INSERT INTO %project_tr%
						(`trid`, `tpid`, `uid`, `formid`, `part`, `period`, `detail1`, `detail2`, `text1`, `created`)
					VALUES
						(:trid, :tpid, :uid, "ส.1", "innovation", :period, :title, :innotype, :innouse, :created)
					ON DUPLICATE KEY UPDATE `detail1`=:title, `detail2`=:innotype, `text1`=:innouse, `modified`=:modified, `modifyby`=:modifyby ';
		mydb::query($stmt,$post);
		$ret.=mydb()->_query;
		//$ret.=print_o($post,'$post');
		return $ret;
	}
	if ($trid) {
		$stmt='SELECT `trid`, `tpid`, `uid`, `formid`, `part`, `detail` `title`, `detail2` `innotype`, `text1` `innoused`
			FROM %project_tr% tr
			WHERE `trid`=:trid AND `tpid`=:tpid
			LIMIT 1';
		$rs=mydb::select($stmt,':trid',$trid,':tpid',$tpid);
		$post=$rs;
	}

	if (empty($post)) $post=(object)post('inno');

	$form->config->variable='inno';
	$form->config->method='post';
	$form->config->action=url('paper/'.$tpid.'/owner/s1');
	$form->config->class='sg-form';
	$form->config->attr='data-rel="box" data-done="close" data-callback="refreshContent" data-refresh-url="'.url('paper/'.$tpid.'/owner/s1/period/'.$period).'"';

	$form->act=array('type'=>'hidden','name'=>'act','value'=>'addinno');
	$form->period=array('type'=>'hidden','name'=>'period','value'=>$period);

	$form->title->type='text';
	$form->title->label='ชื่อนวัตกรรม';
	$form->title->class='w-9';
	$form->title->size=50;
	$form->title->require=true;
	$form->title->value=htmlspecialchars($post->title);

	$form->innotype->type='radio';
	$form->innotype->label='ลักษณะนวัตกรรม';
	$form->innotype->require=true;
	$form->innotype->options=array(
														'1'=>'การพัฒนาความรู้ใหม่จากการวิจัยและพัฒนา',
														'2'=>'การนำสิ่งที่มีอยู่ในชุมชนอื่นมาปรับใช้ในชุมชนตนเอง',
														'3'=>'การนำสิ่งที่มีอยู่มาปรับกระบวนทัศน์ใหม่หรือทำด้วยวิธีใหม่',
														'4'=>'การรื้อฟื้นสิ่งดีๆ ที่เคยมีในชุมชนมาปรับให้สอดคล้องกับสถานการณ์ปัจจุบัน'
														);
	$form->innotype->value=htmlspecialchars($post->innotype);

	$form->innouse->type='textarea';
	$form->innouse->label='การนำนวัตกรรมไปใช้ประโยชน์';
	$form->innouse->class='w-9';
	$form->innouse->value=htmlspecialchars($post->innouse);
	$form->innouse->require=true;
	$form->innouse->description='แสดงให้เห็นว่านวัตกรรมตามที่ระบุมานั้นได้นำไปใช้ในการดำเนินงานโครงการแล้วทำให้เกิดการจัดการปัญหาของพื้นที่แล้วได้ผลดีกว่าเดิมอย่างไร';

	$form->submit->type='submit';
	$form->submit->items->save=tr('save');
	$form->submit->posttext='หรือ <a class="sg-action" data-rel="close" href="javascript:void(0)">ยกเลิก</a>';

	$ret .= theme('form','org-add-person',$form);

	$ret.='<script type="text/javascript">
	$("#edit-inno-title").focus()
	</script>';
	return $ret;
}

/**
* List innovation
* @param Integer $tpid
* @param Boolean $isEdit
* @return String
*/
function __project_form_s1_listinno($tpid,$topic,$period,$isEdit) {
	$stmt='SELECT *
					FROM %project_tr%
					WHERE `tpid`=:tpid AND `part`="innovation" AND `period`=:period
					ORDER BY `trid` ASC';
	$dbs=mydb::select($stmt,':tpid',$tpid, ':period',$period);
	$tables = new Table();
	$tables->thead=array(
									'ชื่อนวัตกรรม',
									'center 1'=>'การพัฒนาความรู้ใหม่จากการวิจัยและพัฒนา',
									'center 2'=>'การนำสิ่งที่มีอยู่ในชุมชนอื่นมาปรับใช้ในชุมชนตนเอง',
									'center 3'=>'การนำสิ่งที่มีอยู่มาปรับกระบวนทัศน์ใหม่หรือทำด้วยวิธีใหม่',
									'center 4'=>'การรื้อฟื้นสิ่งดีๆ ที่เคยมีในชุมชนมาปรับให้สอดคล้องกับสถานการณ์ปัจจุบัน',
									'การนำนวัตกรรมไปใช้ประโยชน์<br />(แสดงให้เห็นว่านวัตกรรมตามที่ระบุมานั้นได้นำไปใช้ในการดำเนินงานโครงการแล้วทำให้เกิดการจัดการปัญหาของพื้นที่แล้วได้ผลดีกว่าเดิมอย่างไร)',
									$isEdit?'<a class="sg-action -no-print" href="'.url('project/form/'.$tpid.'/s1',array('act'=>'addinno','period'=>$period)).'" title="เพิ่มรายชื่อนวัตกรรม"><i class="icon -add"></i></a>':''
									);

	$section='innovation';
	foreach ($dbs->items as $rs) {
		$formid=$rs->formid;
		$isEditable=$formid=='ส.1' && $rs->period==$period ;
		$isTrEditable=$isEdit && $isEditable;
		$tables->rows[]=array(
											view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail1','tr'=>$rs->trid),$rs->detail1,$isTrEditable),
											view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail2','tr'=>$rs->trid,'name'=>'innotype-'.$rs->trid,'value'=>$rs->detail2),'1::',$isTrEditable,'radio'),
											view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail2','tr'=>$rs->trid,'name'=>'innotype-'.$rs->trid,'value'=>$rs->detail2),'2::',$isTrEditable,'radio'),
											view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail2','tr'=>$rs->trid,'name'=>'innotype-'.$rs->trid,'value'=>$rs->detail2),'3::',$isTrEditable,'radio'),
											view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail2','tr'=>$rs->trid,'name'=>'innotype-'.$rs->trid,'value'=>$rs->detail2),'4::',$isTrEditable,'radio'),
											view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$rs->trid,'ret'=>'html','button'=>'yes'),$rs->text1,$isTrEditable,'textarea'),
											);
	}
	if ($isEdit) {
		$tables->rows[]=array('<td colspan="7" class="noprint" style="text-align:center;"><a class="sg-action btn" href="'.url('project/form/'.$tpid.'/s1',array('act'=>'addinno','period'=>$period)).'" data-rel="box" title="เพิ่มรายชื่อนวัตกรรม"><i class="icon -add"></i>เพิ่มรายชื่อนวัตกรรม</a>');
	}
	$ret.=$tables->build();
	//$ret.=print_o($topic,'$topic');
	return $ret;
}
?>