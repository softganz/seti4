<?php

/**
 * แบบรายงานความก้าวหน้าโครงการ (ส.2)
 *
 * @param Object $topic
 * @param Object $para
 * @param String $report
 */
function project_form_s2($self,$topic,$para,$report) {
	$tpid=$topic->tpid;
	$period=$para->period;
	$formid='ส.2';

	$isAdmin=user_access('administer projects');
	$is_edit=($topic->project->project_statuscode==1) && (user_access('administer projects') || project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid));

	if (!$period) {
		$currentReport=mydb::select('SELECT `period`, COUNT(*) amt FROM %project_tr% tr WHERE tr.`tpid`=:tpid AND tr.`formid`=:formid GROUP BY `period`',':tpid',$tpid,':formid',$formid);
		$allReport=$currentReport->_num_rows;

		if ($currentReport->_num_rows) {
			$ret.='<p>กรุณาเลือกงวดของรายงาน'.($is_edit?'หรือสร้างรายงานงวดใหม่':'').'</p>';
			foreach ($currentReport->items as $item) {
				$ret.='<a class="btn" href="'.url('paper/'.$tpid.'/owner/s2/period/'.$item->period).'">รายงาน '.$formid.' งวดที่ '.$item->period.'</a>';
				$nextPeriod=$item->period+1;
			}
			if ($is_edit && $allReport<cfg('project.period.max')-1) $ret.=' หรือ <a class="btn -primary" href="'.url('paper/'.$tpid.'/owner/s2/period/'.$nextPeriod,'action=create').'" confirm="ยืนยันการสร้างรายงาน '.$formid.'?">สร้างรายงาน '.$formid.' งวดที่ '.$nextPeriod.'</a>';
		} else {
			$ret.='<p>ยังไม่มีรายงาน '.$formid.'</p>';
			$nextPeriod=1;
			if ($is_edit) $ret.='<p>ต้องการสร้างรายงาน '.$formid.' หรือไม่?</p><a class="btn -primary" href="'.url('paper/'.$tpid.'/owner/s2/period/1','action=create').'" confirm="ยืนยันการสร้างรายงาน '.$formid.'?">สร้างรายงาน '.$formid.'</a> <a class="btn" href="'.url('paper/'.$tpid.'/owner').'">ไม่สร้าง</a>';
		}
		return $ret;
	}

	if ($is_edit && post('action')) {
		switch (post('action')) {
			case 'create' :
				mydb::query('INSERT INTO %project_tr% (`tpid`, `formid`, `period`, `part`,  `uid`, `created`) VALUES (:tpid, :formid, :period, :part, :uid, :created)', ':tpid', $tpid, ':formid', $formid, ':period', $period, ':part','title', ':uid', i()->uid, ':created', date('U'));
					location('paper/'.$tpid.'/owner/s2/period/'.$period);
					break;
			case 'remove' :
				mydb::query('DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="ส.2" AND `period`=:period ',':tpid',$tpid, ':period',$period);
					location('paper/'.$tpid.'/owner/s2');
				break;
		}
	}

	$rs=project_model::get_tr($tpid,$formid,$period);
	$periodInfo=project_model::get_period($tpid,$period);
	$activities=R::Model('project.activity.get.bytpid',$tpid,
								'{owner:'._PROJECT_OWNER_ACTIVITY.', period:'.$period.'}'
								);

/*
	$locked=property('project:'.$formid.'.'.$period.'.locked:'.$tpid);
	if ($_REQUEST['lock'] && user_access('administer projects')) $locked=property('project:'.$formid.'.'.$period.'.locked:'.$tpid,$_REQUEST['lock']);

	if ($locked=='yes') $is_edit=false;
*/

	$locked=$periodInfo->flag>=_PROJECT_LOCKREPORT;
	if ($locked) $is_edit=false;

	$url='paper/'.$tpid.'/owner/s2/period/'.$period;

	// Show form toolbar
	$ui=new ui();
	$ui->add('<a href="'.url($url).'">รายงาน ส.2 ประจำงวดที่ '.$period.'</a>');
	$ui->add('สถานะรายงาน : '.($locked?'Locked':'Unlock'));
	if (	$is_edit) $ui->add('<a href="'.url($url,array('action'=>'remove')).'" class="sg-action" data-confirm="ยืนยันการลบรายงาน?">ลบรายงาน</a>');
	//$ui->add('<a href="'.url('paper/'.$tpid.'/owner/m1/period/'.$period,array('o'=>'word','a'=>'download')).'">ดาวน์โหลด</a>');
	$ui->add('<a href="javascript:window.print()">พิมพ์</a>');
	$ret.='<div class="reportbar" -no-print>'.$ui->build('ul').'</div>';

	if ($is_edit) {
		$inlineAttr['data-update-url']=url('project/edit/tr');
		$inlineAttr['data-period']=$period;
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}
	$ret.='<div id="project-report-s2" class="inline-edit project__report" '.sg_implode_attr($inlineAttr).'>'._NL;

	$ret.='<p class="form-info">รหัสโครงการ <strong>'.$topic->project->prid.'</strong><br />สัญญาเลขที่ <strong>'.$topic->project->agrno.'</strong><br />งวดที่ <strong>'.$period.'</strong></p>'._NL;
	$ret.='<h3>แบบรายงานการติดตามสนับสนุนโครงการ (ส.2)</h3>'._NL;

	$section='title';
	$irs=end($rs->items[$section]);
	$ret.='<p>ชื่อโครงการ <strong>'.$topic->title.'</strong></p>'._NL;

	$section='title';
	$irs=end($rs->items[$section]);
	$ret.='<p>จากเดือน <strong>'.sg_date($periodInfo->report_from_date,'ว ดดด ปปปป').'</strong> ถึงเดือน <strong>'.sg_date($periodInfo->report_to_date,'ว ดดด ปปปป').'</strong></p>'._NL;

	$tables = new Table();
	$tables->thead=array('no'=>'ลำดับ','ชื่อกิจกรรม','คำแนะนำจากเจ้าหน้าที่ติดตามในพื้นที่','ชื่อผู้ติดตามในพื้นที่ของ สสส.');
	foreach ($activities->items as $irs) {
		unset($row);
		$tables->rows[]=array(++$no,
			$irs->title,
			view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text8','tr'=>$irs->trid,'ret'=>'html','button'=>'yes'),$irs->followerrecommendation,$is_edit,'textarea'),
			view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail2','tr'=>$irs->trid),$irs->followername,$is_edit,'text'),
		);
	}

	$ret .= $tables->build();

	$ret.='<p><strong>ข้อเสนอแนะอื่น ๆ ต่อ สสส. เพื่อการสนับสนุนโครงการ</strong></p>'._NL;
	$section='title';
	$irs=end($rs->items[$section]);
	$ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html','button'=>'yes'),$irs->text1,$is_edit,'textarea');

	$ret.='<p><strong>ความเห็นลับสำหรับการติดตาม</strong></p>'._NL;
	$ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid,'ret'=>'html','button'=>'yes'),$irs->text2,$is_edit,'textarea');

	$ret.='<p><br /><br />............................................<br />( '.$topic->project->prowner.' )<br /><strong>หัวหน้าโครงการ/ผู้รับผิดชอบ</strong><br />(ผู้บันทึก)</p>'._NL;

	$ret.='<p><u>หมายเหตุ</u> กรุณาส่งแบบรายงานการติดตามฯ ฉบับนี้มาพร้อมกับรายงานความก้าวหน้า/รายงานปิดโครงการ</p>';

		$ret.='</div>';

	return $ret;
}
?>