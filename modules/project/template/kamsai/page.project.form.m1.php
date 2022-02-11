<?php
/**
 * แบบรายงานการเงินโครงการ (ง.1)
 *
 * @param Object $topic
 * @param Object $para
 * @param String $report
 */
function project_form_m1($self,$topic,$para,$report) {
	$tpid=$topic->tpid;
	$period=$para->period;
	$formid='ง.1';
	$action=post('action');

	$statusText=array(
									_PROJECT_DRAFTREPORT=>'เริ่มทำรายงาน',
									_PROJECT_COMPLETEPORT=>'ส่งรายงานจากพื้นที่',
									_PROJECT_LOCKREPORT=>'ผ่านการตรวจสอบของพี่เลี้ยงโครงการ',
									_PROJECT_PASS_HSMI=>'ผ่านการตรวจสอบของ '.cfg('project.grantpass'),
									_PROJECT_PASS_SSS=>'ผ่านการตรวจสอบของ '.cfg('project.grantby')
									);

	$isOwner=project_model::is_owner_of($tpid);
	$isTrainer=project_model::is_trainer_of($tpid);
	$isAdmin=user_access('administer projects');
	$isTeam=$isAdmin || $isOwner || $isTrainer;
	$is_edit=$topic->project->project_statuscode==1 && $isTeam;

	if (!$period) {
		$periodInfo=project_model::get_period($tpid);
		$lastPeriod=0;
		$lastPeriodLock=_PROJECT_LOCKREPORT;
		if ($periodInfo) {
			$ret.='<div class="container">';
			foreach ($periodInfo as $item) {
				if (is_null($item->flag)) break;

				$retStatus.='<div id="project-m1-status" class="row project-m1-status">'._NL;
				$retStatus.='<div class="col -md-2 -title"><a class="project-report-status -status-'.$item->flag.'" href="'.url('paper/'.$tpid.'/owner/m1/period/'.$item->period).'">รายงาน<br />'.$formid.'<br /><b>งวดที่ '.$item->period.'</b></a></div>'._NL;

				$retStatus.='<div class="col -md-2 -status-1 -pass"><span class="status-no">1</span><span class="status-text">เริ่มทำรายงาน</span></div>'._NL;
				$retStatus.='<div class="col -md-2 -status-2'.($item->flag>=_PROJECT_COMPLETEPORT?' -pass':'').'"><span class="status-no">2</span><span class="status-text">ส่งรายงานจากพื้นที่</span></div>'._NL;
				$retStatus.='<div class="col -md-2 -status-3'.($item->flag>=_PROJECT_LOCKREPORT?' -pass':'').'"><span class="status-no">3</span><span class="status-text">ผ่านการตรวจสอบของพี่เลี้ยงโครงการ</span></div>'._NL;
				$retStatus.='<div class="col -md-2 -status-4'.($item->flag>=_PROJECT_PASS_HSMI?' -pass':'').'"><span class="status-no">4</span><span class="status-text">ผ่านการตรวจสอบของ '.cfg('project.grantpass').'</span></div>'._NL;
				$retStatus.='<div class="col -md-2 -status-5'.($item->flag>=_PROJECT_PASS_SSS?' -pass':'').'"><span class="status-no">5</span><span class="status-text">ผ่านการตรวจสอบของ '.cfg('project.grantby').'</span></div>'._NL;
				$retStatus.='<br clear="all" />';
				$retStatus.='</div><!-- row -->'._NL;

				$lastPeriod=$item->period;
				$lastPeriodLock=$item->flag;
			}
			$nextPeriod=$lastPeriod+1;
			$ret.=$retStatus;
			$ret.='</div><!-- container -->';
//			if ($is_edit && $lastPeriodLock>=_PROJECT_LOCKREPORT) $ret.=' หรือ <a class="button" href="'.url('paper/'.$tpid.'/owner/m1/period/'.$nextPeriod,'action=create').'" confirm="ยืนยันการสร้างรายงาน '.$formid.' งวดที่ '.$nextPeriod.' ?">สร้างรายงาน '.$formid.' งวดที่ '.$nextPeriod.'</a>';

			if ($is_edit && $lastPeriodLock>=_PROJECT_LOCKREPORT && $nextPeriod<cfg('project.period.max')) $ret.=' <p><a class="sg-action btn -primary" href="'.url('paper/'.$tpid.'/owner/m1/period/'.$nextPeriod,'action=create').'" data-confirm="ต้องการสร้างรายงาน '.$formid.' งวดที่ '.$nextPeriod.' กรุณายืนยัน?">สร้างรายงาน '.$formid.' งวดที่ '.$nextPeriod.'</a></p>';

		} else {
			$ret.='<p>คำเตือน : โครงการนี้ยังไม่มีการการกำหนดงวด <a href="'.url('paper/'.$tpid).'">กรุณากำหนดงวด</a> '.$nextPeriod.' ของโครงการใน<a href="'.url('paper/'.$tpid).'">รายละเอียดโครงการ</a>ก่อน !!!</p>';
		}
		return $ret;
	}

	$rs=project_model::get_tr($tpid,$formid,$period);
	$periodInfo=project_model::get_period($tpid,$period);
	$locked=$periodInfo->flag>=_PROJECT_LOCKREPORT;

	//$ret.=print_o($periodInfo,'$periodInfo');//.print_o($rs,'$rs');

	$section='title';
	$irs=end($rs->items[$section]);

	if ($locked) $is_edit=false;

	$url='paper/'.$tpid.'/owner/m1/period/'.$period;
	// Show form toolbar
	$ui=new ui();
	$ui->add('<a href="'.url($url).'">รายงาน ง.1 ประจำงวดที่ '.$period.'</a>');
	$ui->add('<a href="'.url($url,array('action'=>'status')).'">สถานะรายงาน : '.$statusText[$periodInfo->flag].'</a>');
/*
				$ret.='<div id="project-notify">'._NL;
				$ret.='<ul><li><a href="#">'.$notifyM1.'</a><div><div><div class="beeperNub"></div><h3>คำเตือน</h3><ul>'.$notifyMsg.'</ul></div></div></li></ul>'._NL;
				$ret.='</div>'._NL;
*/
	if ($is_edit && $periodInfo->flag==_PROJECT_DRAFTREPORT) {
		$ui->add('<a href="'.url('paper/'.$tpid.'/owner/m1/period/'.$period,array('action'=>'pass','step'=>_PROJECT_COMPLETEPORT)).'" title="คลิกเพื่อแจ้งรายงานเสร็จสมบูรณ์">แจ้งรายงานเสร็จสมบูรณ์</a>');
	} else if ($is_edit && $periodInfo->flag==_PROJECT_COMPLETEPORT) {
		$ui->add('<a href="'.url('paper/'.$tpid.'/owner/m1/period/'.$period,array('action'=>'pass','step'>_PROJECT_DRAFTREPORT)).'" title="คลิกเพื่อยกเลิกการแจ้งรายงานเสร็จสมบูรณ์">ยกเลิกการแจ้งรายงานเสร็จสมบูรณ์</a>');
	}
//	if (user_access('administer projects')) $ui->add('คลิกเพื่อ <a href="'.url('paper/'.$tpid.'/owner/m1/period/'.$period,'lock=auto').'" title="คลิกเพื่อ'.($locked?'ปลด':'').'ล็อก">'.($locked?'Unlock':'Lock').'</a>');
	//$ui->add('<a href="'.url('paper/'.$tpid.'/owner/m1/period/'.$period,array('o'=>'word','a'=>'download')).'">ดาวน์โหลด</a>');
	$ui->add('<a href="javascript:window.print()">พิมพ์</a>');
	if ($isAdmin && $periodInfo->flag==_PROJECT_DRAFTREPORT) {
		$subui=new ui();
		$subui->add('<a class="sg-action" href="'.url('paper/'.$tpid.'/owner/m1/period/'.$period,array('action'=>'delete')).'" data-confirm="ต้องการลบรายงานนี้ใช่ไหรือไม่ กรุณายืนยัน?">ลบรายงานการเงินประจำงวดนี้</a>');
		$ui->add(sg_dropbox($subui->show('ul')));
	}

	$ret.='<div class="reportbar">'.$ui->show('ul').'</div>';

	switch ($action) {
		case 'create' :
			$periodInfo=project_model::get_period($tpid,$period);
			if ($periodInfo && $periodInfo->from_date && $periodInfo->to_date && $periodInfo->budget) {
				mydb::query('UPDATE %project_tr% SET `flag`=:flag, `detail1`=`date1`, `detail2`=`date2` WHERE `tpid`=:tpid AND `formid`="info" AND `part`="period" AND `period`=:period LIMIT 1', ':tpid', $tpid, ':period', $period, ':flag', _PROJECT_DRAFTREPORT );
				$stmt='INSERT INTO %project_tr% SET `tpid`=:tpid, `formid`="ง.1", `part`="summary", `period`=:period, `uid`=:uid, `num1`=0, `num2`=0, `num3`=0, `num4`=0, `num5`=0, `num6`=0, `created`=:created';
				mydb::query($stmt,':tpid',$tpid, ':period',$period, ':uid',i()->uid, ':created',date('U'));

				$firebase=new Firebase('sg-project-man','update');
				$data = array('tpid'=>$tpid,'tags'=>'Project M1 Create','value'=>$topic->title.' สร้างรายงาน ง.1 งวดที่ '.$period,'formid'=>'m1','period'=>$period,'url'=>_DOMAIN.url('paper/'.$tpid.'/owner/m1/period/'.$period),'time'=>array('.sv'=>'timestamp'));
				$firebase->post($data);
				//$firebase->post('/'.date('YmdHis').round(microtime(true) * 1000).'-'.$tpid.'-m1-'.$period.'.json',$data);

				location('paper/'.$tpid.'/owner/m1/period/'.$period);
			} else {
				$ret.='<p class="notify">ยังไม่มีการกำหนด <strong>งวดที่ '.$period.'</strong> ในรายละเอียดโครงการ หรือ <strong>ป้อนรายละเอียดของงวดไม่ครบถ้วน</strong> เช่น วันเริ่มงวด,วันสิ้นสุดงวด,งบประมาณ<br /> กรุณา <a href="'.url('paper/'.$tpid).'">กำหนดงวดที่ '.$period.' และรายละเอียดให้ครบถ้วนก่อน</a></p>';
				return $ret;
			}
			break;

		case 'pass' :
			project_model::lock_period($tpid,$period,post('step'));
			location($url,array('action'=>'status'));
			break;

		case 'note' :
			$noteField=array('note_owner'=>'text1', 'note_complete'=>'text2', 'note_trainer'=>'text3', 'note_hsmi'=>'text4', 'note_sss'=>'text5');
			if ($noteField[post('note')]) {
				$stmt='UPDATE %project_tr% SET `'.$noteField[post('note')].'`=:note WHERE `trid`=:trid LIMIT 1';
				mydb::query($stmt,':trid', $periodInfo->trid, ':note',post('msg'));
			}
			location($url,array('action'=>'status'));
			break;

		case 'status' :
			$ret.='<div id="project-report-status" class="container project-m1-status"><h4>สถานะรายงาน</h4>';
			$ret.='<div class="row">';
			$ret.='<div class="col -md-3 -status-1 -pass"><span class="status-no">1</span><h5>เริ่มทำรายงาน</h5>'.($isTeam?'<div class="note">'.($isOwner?'<form method="post" action="'.url($url,array('action'=>'note')).'"><input type="hidden" name="note" value="note_owner" /><textarea name="msg">'.$periodInfo->note_owner.'</textarea><button>บันทึก</button></form>':sg_text2html($periodInfo->note_owner)).'</div>':'').'</div>'._NL;
			$ret.='<div class="col -md-3 -status-2'.($periodInfo->flag>=_PROJECT_COMPLETEPORT?' -pass':'').'"><span class="status-no">2</span><h5>ส่งรายงานจากพื้นที่</h5>'.($isTeam?'<div class="note">'.($isOwner?'<form method="post" action="'.url($url,array('action'=>'note')).'"><input type="hidden" name="note" value="note_complete" /><textarea name="msg">'.$periodInfo->note_complete.'</textarea><button>บันทึก</button></form>':sg_text2html($periodInfo->note_complete)).'</div>':'').''.($is_edit && $periodInfo->flag<_PROJECT_PASS_HSMI?'<a class="project-button -send" href="'.url($url,array('action'=>'pass','step'=>_PROJECT_COMPLETEPORT)).'">ส่งรายงานจากพื้นที่</a>':'').'</div>'._NL;
			if (projectcfg::enable('trainer')) $ret.='<div class="col -md-3 -status-3'.($periodInfo->flag>=_PROJECT_LOCKREPORT?' -pass':'').'"><span class="status-no">3</span><h5>ผ่านการตรวจสอบของพี่เลี้ยงโครงการ</h5>'.($isTeam?'<div class="note">'.($isTrainer?'<form method="post" action="'.url($url,array('action'=>'note')).'"><input type="hidden" name="note" value="note_trainer" /><textarea name="msg">'.$periodInfo->note_trainer.'</textarea><button>บันทึก</button></form>':sg_text2html($periodInfo->note_trainer)).'</div>':'').(($isTrainer || $isAdmin) && $periodInfo->flag<_PROJECT_PASS_HSMI?'<a class="project-button -pass" href="'.url($url,array('action'=>'pass','step'=>_PROJECT_LOCKREPORT)).'">ผ่านการตรวจสอบ</a>':'').'</div>'._NL;
			$ret.='<div class="col -md-3 -status-4'.($periodInfo->flag>=_PROJECT_PASS_HSMI?' -pass':'').'"><span class="status-no">4</span><h5>ผ่านการตรวจสอบของ '.cfg('project.grantpass').'</h5>'.($isTeam?'<div class="note">'.($isAdmin?'<form method="post" action="'.url($url,array('action'=>'note')).'"><input type="hidden" name="note" value="note_hsmi" /><textarea name="msg">'.$periodInfo->note_hsmi.'</textarea><button>บันทึก</button></form>':sg_text2html($periodInfo->note_hsmi)).'</div>':'').($isAdmin?'<a class="project-button -pass" href="'.url($url,array('action'=>'pass','step'=>_PROJECT_PASS_HSMI)).'">ผ่านการตรวจสอบ</a><a class="project-button -reject" href="'.url($url,array('action'=>'pass','step'=>_PROJECT_DRAFTREPORT)).'">แก้ไข</a>':'').'</div>'._NL;
			$ret.='<div class="col -md-3 -status-5'.($periodInfo->flag>=_PROJECT_PASS_SSS?' -pass':'').'"><span class="status-no">5</span><h5>ผ่านการตรวจสอบของ '.cfg('project.grantby').'</h5>'.($isTeam?'<div class="note">'.($isAdmin?'<form method="post" action="'.url($url,array('action'=>'note')).'"><input type="hidden" name="note" value="note_sss" /><textarea name="msg">'.$periodInfo->note_sss.'</textarea><button>บันทึก</button></form>':sg_text2html($periodInfo->note_sss)).'</div>':'').($isAdmin?'<a class="project-button -pass" href="'.url($url,array('action'=>'pass','step'=>_PROJECT_PASS_SSS)).'">ผ่านการตรวจสอบ</a><a class="project-button -reject" href="'.url($url,array('action'=>'pass','step'=>_PROJECT_DRAFTREPORT)).'">แก้ไข</a>':'').'</div>'._NL;
			$ret.='<br clear="all" />';
			$ret.='</div><!-- row -->';
			$ret.='</div><!-- container -->';

			return $ret;

		case 'delete' :
			if ($isAdmin && $tpid && $period && $periodInfo->flag==_PROJECT_DRAFTREPORT) {
				//$ret.='Delete report';
				$stmt='DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="ง.1" AND `period`=:period';
				mydb::query($stmt,':tpid',$tpid, ':period',$period);
				//$ret.=mydb()->_query;

				$stmt='UPDATE %project_tr% SET `flag`=NULL, `detail1`=NULL, `detail2`=NULL WHERE `tpid`=:tpid AND `formid`="info" AND `part`="period" AND `period`=:period LIMIT 1';
				mydb::query($stmt,':tpid',$tpid, ':period',$period);
				//$ret.=mydb()->_query;


				//$ret.=print_o($rs,'$rs');
				//$ret.=print_o($periodInfo,'$periodInfo');
				location('paper/'.$tpid.'/owner/m1');
			}
			break;
		default:
				# code...
			break;
	}

	/*
	if ($period && $_REQUEST['act']=='create' && $is_edit) {
	} else if ($period && isset($_REQUEST['lock']) && in_array($_REQUEST['lock'],array(_PROJECT_COMPLETEPORT,_PROJECT_DRAFTREPORT)) && $is_edit) {
		project_model::lock_period($tpid,$period,post('lock'));
		location('paper/'.$tpid.'/owner/m1/period/'.$period);
	} else if ($period && isset($_REQUEST['lock']) && user_access('administer projects')) {
		project_model::lock_period($tpid,$period,post('lock'));
		location('paper/'.$tpid.'/owner/m1/period/'.$period);
	}
*/


	$ret.='<p class="form-info">รหัสโครงการ <strong>'.$topic->project->prid.'</strong><br />สัญญาเลขที่ <strong>'.$topic->project->agrno.'</strong><br />งวดที่ <strong>'.$period.'</strong></p>'._NL;

	if ($is_edit) {
		$inlineAttr['data-update-url']=url('project/edit/tr');
		$inlineAttr['data-period']=$period;
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}
	$ret.='<div id="project-report-m1" class="inline-edit project__report" '.sg_implode_attr($inlineAttr).'>'._NL;

	$ret.='<h3>แบบรายงานการเงินโครงการ ประจำงวดที่ '.$period.'</h3>'._NL;
	$ret.='<p>ตั้งแต่ <strong>'.sg_date($periodInfo->from_date,'ว ดดด ปปปป').'</strong> ถึง <strong>'.sg_date($periodInfo->to_date,'ว ดดด ปปปป').'</strong></p>'._NL;

	$ret.='<p>งวดรายงาน ตั้งแต่ '
				.view::inlineedit(array('group'=>'info:period','fld'=>'detail1','tr'=>$periodInfo->trid,'ret'=>'date:ว ดดด ปปปป', 'value'=>sg_date($periodInfo->report_from_date,'d/m/Y')),$periodInfo->report_from_date,$is_edit,'datepicker')
				.' ถึง '
				.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail2','tr'=>$periodInfo->trid,'ret'=>'date:ว ดดด ปปปป', 'value'=>sg_date($periodInfo->report_to_date,'d/m/Y')),$periodInfo->report_to_date,$is_edit,'datepicker')
				.'</p>'._NL;


	$ret.='<p>ชื่อโครงการ <strong>'.$topic->title.'</strong></p>'._NL;

	if (1 || $irs->date1 && $irs->date2) {
		$ret.='<h4>ส่วนที่ 1 แบบแจกแจงรายจ่ายแยกตามกิจกรรมของโครงการ</h4>'._NL;
		$section='activity';

		$tables=new table();
		$tables->id='project-form-m1-tr';
		$tables->colgroup=array('','','money m1'=>'','money m2'=>'','money m3'=>'','money m4'=>'','money m5'=>'','money m6'=>'','money m7'=>'');
		$tables->thead='<thead><tr><th rowspan="2"></th><th rowspan="2">กิจกรรม</th><th colspan="6">ประเภทรายจ่าย</th><th rowspan="2">รวมรายจ่าย</th></tr><tr><th>ค่าตอบแทน</th><th>ค่าจ้าง</th><th>ค่าใช้สอย</th><th>ค่าวัสดุ</th><th>ค่าสาธารณูปโภค</th><th>อื่น ๆ</th></tr></thead>';
		if (1||$irs->date1 && $irs->date2) {
			$activity=mydb::select('SELECT tr.`trid`, c.`title` activity, `num1`, `num2`, `num3`, `num4`, `num5`, `num6`, `num7` FROM %project_tr% tr LEFT JOIN %calendar% c ON c.id=tr.calid WHERE tr.`tpid`=:tpid AND tr.`formid`=:formid AND `part`="owner" AND tr.`date1` BETWEEN :start AND :end ORDER BY tr.`date1` ASC',':tpid',$topic->tpid,':formid','activity',':start',$periodInfo->report_from_date,':end',$periodInfo->report_to_date);
			foreach ($activity->items as $irs) {
				if ($irs->num7<=0) continue;
				$tables->rows[]=array(++$no.')', $irs->activity, number_format($irs->num1,2), number_format($irs->num2,2), number_format($irs->num3,2), number_format($irs->num4,2), number_format($irs->num5,2), number_format($irs->num6,2), number_format($irs->num7,2));
			}
		}
		$ret.=$tables->show();

		$section='summary';
		$irs=end($rs->items[$section]);
		$ret.='<h4>ส่วนที่ 2 แบบรายงานสรุปการใช้จ่ายเงินประจำงวดนี้</h4><p> (โปรดแนบสำเนาสมุดบัญชีเงินฝากธนาคารที่ปรับปรุงยอดล่าสุด)</p>'._NL;
		unset($tables);
		$tables->class='item';
		$tables->id='project-report-m1-summary';
		$tables->thead=array('(1) รายรับ','(2) รายจ่าย','(3) คงเหลือ');
		unset($row);
		$row[]=	'1) เงินคงเหลือยกมา (ถ้ามี) = '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num1','tr'=>$irs->trid,'ret'=>'money','callback'=>'m1_checksum'),$irs->num1,$is_edit,'money').' บาท<br />'
							.'2) เงินรับจาก '.cfg('project.grantby').' งวดนี้ = '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num2','tr'=>$irs->trid,'ret'=>'money','callback'=>'m1_checksum'),$irs->num2,$is_edit,'money').' บาท<br />'
							.'3) ดอกเบี้ย = '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num3','tr'=>$irs->trid,'ret'=>'money','callback'=>'m1_checksum'),$irs->num3,$is_edit,'money').' บาท<br />'
							.'4) เงินรับอื่น ๆ (เปิดบัญชี) = '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num4','tr'=>$irs->trid,'ret'=>'money','callback'=>'m1_checksum'),$irs->num4,$is_edit,'money').' บาท<br />'
							.'';
		$sum_income=$irs->num1+$irs->num2+$irs->num3+$irs->num4;
		$no=0;
		foreach ($activity->items as $mrs) {
			if ($mrs->num7<=0) continue;
			$act_money.=++$no.') กิจกรรม '.$no.' = '.number_format($mrs->num7,2).' บาท<br />';
			$act_money_total+=$mrs->num7;
		}
		$row[]=$act_money;
		$balance=$sum_income-$act_money_total;
		$real_balance=$irs->num5+$irs->num6;
		//$ret.='Balance='.$balance.' Real balance='.$real_balance;

		$row[]='1) เงินสดในมือ = '.view::inlineedit(array('group'=>$formid.':'.$section.'','fld'=>'num5','tr'=>$irs->trid,'ret'=>'money','callback'=>'m1_checksum'),$irs->num5,$is_edit,'money').' บาท<br />2) เงินในบัญชี = '.view::inlineedit(array('group'=>$formid.':'.$section.'','fld'=>'num6','tr'=>$irs->trid,'ret'=>'money','callback'=>'m1_checksum'),$irs->num6,$is_edit,'money').' บาท';
		$tables->rows[]=$row;

		$is_balance=(string)$balance==(string)$real_balance;
		$tables->rows[]=array('รวมรายรับ (1) = <strong id="project-m1-sum-income">'.number_format($sum_income,2).'</strong> บาท',
											'รวมรายจ่าย (2) = <strong id="project-m1-sum-expense">'.number_format($act_money_total,2).'</strong> บาท', /* New variable is $act_money_total , Old variable is $irs->num2 */
											'(1) รายรับ - (2) รายจ่าย = (3) <strong id="project-m1-sum-balance"'.($is_balance?'':' class="notbalance"').'>'.number_format($balance,2).'</strong> บาท<br /><span id="project-m1-sum-balance-msg" class="noprint '.($is_balance?'hidden':'notbalance').'">ยอด (1) - (2) ไม่เท่ากับ (3)</span>',
											);

		$ret.=theme('table',$tables);

		$nextPeriod=project_model::get_period($tpid,$period+1);
		$section='summary';
		$ret.='<h4>ส่วนที่ 3 ขอเบิกเงินสนับสนุนโครงการงวดต่อไป</h4>'._NL;
		$irs=end($rs->items[$section]);
		$useNextPeriod=$irs->flag==2?2:1;
		if ($irs->num1==0) $irs->num1='';

		//$ret.='งวดที่ <strong>'.($period+1).'</strong> เป็นจำนวนเงิน <strong>'.$irs->num1.'|'.$nextPeriod->budget.view::inlineedit(array('group'=>$formid.':'.$section.'','fld'=>'num1','tr'=>$irs->trid,'ret'=>'money','callback'=>'m1_checksum'),$irs->num1===0?$nextPeriod->budget:$irs->num1,$is_edit,'money').'</strong> บาท<br />'._NL; // เดิมอยู่ใน field $irs->num1

		if ($is_edit) {
			$ret.='<p class="noprint">';
			$ret.='<label>ขอเบิกเงินสนับสนุนโครงการงวดต่อไป :</label><br />';
			$ret.=view::inlineedit(array('group'=>$formid.':'.$section.'','fld'=>'flag','tr'=>$irs->trid,'value'=>$useNextPeriod),'1:ขอเบิก',$is_edit,'radio').'<br />';
			$ret.=view::inlineedit(array('group'=>$formid.':'.$section.'','fld'=>'flag','tr'=>$irs->trid,'value'=>$useNextPeriod),'2:ไม่ขอเบิก',$is_edit,'radio');
			$ret.='</p>';
		}

		if ($useNextPeriod==1) {
			$ret.='งวดที่ <strong>'.($period+1).'</strong> เป็นจำนวนเงิน <strong>'.view::inlineedit(array('group'=>$formid.':'.$section.'','fld'=>'num10','tr'=>$irs->trid,'ret'=>'money','callback'=>'m1_checksum'),SG\getFirst($irs->num10,number_format($nextPeriod->budget,2)),$is_edit,'money').'</strong> บาท<br />'._NL; // เดิมอยู่ใน field $irs->num1
		} else {
			$ret.='งวดที่ <strong>'.($period+1).'</strong> เป็นจำนวนเงิน <strong>0.00</strong> บาท<br />'._NL;
		}

		$ret.='<p class="sign -item"><span class="sign -pretext">ลงชื่อ </span><span class="sign -signdraw">....................................................</span><span class="sign -position"> หัวหน้าโครงการ/ผู้รับผิดชอบโครงการ</span><br />'._NL;
		$ret.='<span class="sign -prespace"></span><span class="sign -nametext">( '.$topic->project->prowner.' )</span>';
		$ret.='</p>';

		if ($useNextPeriod==1) {
			$section='nextact';
			$allPeriod=count(project_model::get_period($tpid));
			// Do not show activity on last report
			if ($period<$allPeriod-1) {
				$ret.='<p>เพื่อดำเนินกิจกรรมหลัก ดังต่อไปนี้</p>'._NL;
				$nextActDbs=project_model::get_calendar($tpid,$period+1,'owner');
				$ret.='<ol>'._NL;
				foreach ($nextActDbs->items as $irs) {
					$ret.='<li><strong>'.$irs->title.'</strong> ('.sg_date($irs->from_date).($irs->to_date!=$irs->from_date?' - '.sg_date($irs->to_date):'').')</li>'._NL;

				}
				$ret.='</ol>'._NL;
			}
		}
		/*
		$ret.='<ol>'._NL;
		foreach ($rs->items[$section] as $irs) {
			$ret.='<li>'.sg_date($irs->from_date).' - '.$irs->activity.($is_edit?' <a href="" action="del" group="'.$formid.':'.$section.'" tr="'.$irs->trid.'" title="ลบรายการนี้">X</a>':'').'</li>'._NL;
		}
		if ($is_edit) {
			$dbs=mydb::select('SELECT * FROM %calendar% c WHERE `tpid`=:tpid ORDER BY c.from_date ASC',':tpid',$tpid);
			$ret.='<li><select id="nextactivity" class="form-select"><option value="">===เลือก===</option>'._NL;
			foreach ($dbs->items as $irs) $ret.='<option value="'.$irs->id.'">'.sg_date($irs->from_date).' - '.$irs->title.'</option>'._NL;
			$ret.='</select>'._NL;
			$ret.='<a href="" class="button" action="add" group="'.$formid.':'.$section.'">+เพิ่มกิจกรรม</a></li>'._NL;
		}
		$ret.='</ol>'._NL;
*/
		$ret.='<p>ข้าพเจ้าขอรับรองว่าเงินสนับสนุนโครงการจาก '.cfg('project.grantby').' ได้นำมาใช้อย่างถูกต้อง ตรงตามแผนงาน โครงการ ที่ระบุไว้ในสัญญาทุกประการ และขอรับรองรายงานข้างต้น</p>'._NL;

		$section='summary';
		$irs=end($rs->items[$section]);

		// ผู้รับผิดชอบโครงการ
		$ret.='<p class="sign -item">';
		$ret.='<span class="sign -pretext">ลงชื่อ </span><span class="sign -signdraw">....................................................</span><span sign -position"> หัวหน้าโครงการ/ผู้รับผิดชอบโครงการ</span><br />'._NL;
		$ret.='<span class="sign -prespace"></span><span class="sign -nametext">( '.$topic->project->prowner.' )</span><br />'._NL;
		$ret.='<span class="sign -prespace"></span><span class="sign -date">'.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail1','tr'=>$irs->trid,'ret'=>'date:ว ดดด ปปปป', 'value'=>$irs->detail1?sg_date($irs->detail1,'d/m/Y'):''),$irs->detail1,$is_edit,'datepicker').'</span>';
		$ret.='</p>';

		// เจ้าหน้าที่การเงินโครงการ
		$ret.='<p class="sign -item">';
		$ret.='<span class="sign -pretext">ลงชื่อ </span><span class="sign -signdraw">....................................................</span><span class="sign -position"> เจ้าหน้าที่การเงินโครงการ</span><br />'._NL;
		$ret.='<span class="sign -prespace"></span><span class="sign -nametext">( '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail4','tr'=>$irs->trid, 'class'=>"W1"),$irs->detail4,$is_edit,'text-block').' )</span><br />'._NL;
		$ret.='<span class="sign -prespace"></span><span class="sign -date"> '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail2','tr'=>$irs->trid,'ret'=>'date:ว ดดด ปปปป', 'value'=>$irs->detail2?sg_date($irs->detail2,'d/m/Y'):''),$irs->detail2,$is_edit,'datepicker').'</span><br />'._NL;
		$ret.='</p>'._NL;
	}
	$ret.='</div>'._NL;
	$ret.='<style type="text/css">
	.sign.-item {margin: 3em 0;}
	.sign.-pretext, .sign.-prespace {width:5em; display: inline-block;}
	.sign.-signdraw, .sign.-nametext, .sign.-date {width: 20em; display: inline-block; text-align: center; vertical-align: bottom;}
	.sign.-signdraw {overflow: hidden;}
	.fixed-width {display:inline-block; width: 20em;overflow:hidden;text-align:center;}

	@media print {
		.sign.-pretext, .sign.-prespace {width:2em;}
		.sign.-signdraw, .sign.-nametext, .sign.-date {width: 10em;}

	}
	</style>'._NL;

	if ($is_edit) {
		$ret.='<script type="text/javascript">
$(document).ready(function() {
	var postUrl=$(".inline-edit").attr("data-url");
	var period=$(".inline-edit").attr("data-period");
	$(".inline-edit a[action]")
	.click(function() {
		$this=$(this);
		var action=$this.attr("action");
		var group=$this.attr("group");
		var tr=$this.attr("data-tr");
		var para={id: tpid, action: action, group: group, period: period, tr: tr};
		if (group=="ง.1:nextact" && action=="add") para.calid=$("#nextactivity").val();
		$.post(postUrl,para, function(data) {
			if (action=="del") {
				var row = $this.closest("li");
				row.remove();
			} else if (action=="add") {
				var $target=$this.closest("li");
				var conceptName = $target.find(":selected").text();
				$("<li>"+conceptName+"</li>").insertBefore($target);
				$target.find("select").val("");
			}
		}, "json");
		return false;
	});
});
</script>'._NL;
	};
	return $ret;
}
?>