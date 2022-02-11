<?php
/**
 * แบบรายงานความก้าวหน้าโครงการ (ส.3)
 *
 * @param Object $topic
 * @param Object $para
 * @param String $report
 */
function project_form_s3($self,$topic,$para,$report) {
	$tpid=$topic->tpid;
	$formid='ส.3';
	$download=post('download');
	$action=post('act');
	$order=SG\getFirst(post('order'),'mainact');


	$isAdmin=user_access('administer projects');
	$isOwner=project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid);
	$isEdit=!$download && $topic->project->project_statuscode==1 && ($isAdmin || $isOwner);
	$isAccessExpense=user_access('access full expense') || $isOwner;

	$info=project_model::get_tr($tpid,'info');
	$estimate=project_model::get_tr($tpid,'ประเมิน');
	$activities=R::Model('project.activity.get.bytpid',$tpid,
								'{owner:'._PROJECT_OWNER_ACTIVITY.'}'
								);
	$rs=project_model::get_tr($tpid,$formid);

	switch ($action) {
		case 'addtr' :
			list($formid,$part)=explode(':',post('group'));
			//$ret.='FormId='.$formid.' Part='.$part;
			mydb::query('INSERT INTO %project_tr% (`tpid`, `formid`, `part`, `period`, `created`, `uid`) VALUES (:tpid, :formid, :part, :period, :created, :uid)',':tpid',$tpid, ':formid',$formid, ':part',$part, ':period',$period, ':created', date('U'), ':uid',i()->uid);
			//$ret.=mydb()->_query;
			break;

		case 'addleader' :
			$ret.=__project_form_s3_addleader($tpid);
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
			$ret.=__project_form_s3_addinno($tpid);
			return $ret;
			break;

	}

	// Show member of this project
	$trainer=mydb::select('SELECT u.uid, u.username, u.name, tu.membership FROM %topic_user% tu LEFT JOIN %users% u ON u.uid=tu.uid WHERE `tpid`=:tpid AND tu.membership="Trainer" ',':tpid',$topic->tpid)->items;

	$ret.='<a name="top"></a>';

	$url='paper/'.$tpid.'/owner/s3';
	// Show form toolbar
	$ui=new ui();
	$ui->add('<a href="'.url($url).'">รายงานฉบับสมบูรณ์ (ส.3)</a>');
	if ($order=='mainact') {
		$ui->add('<a href="'.url($url,array('order'=>'date')).'">เรียงกิจกรรมตามวันที่</a>');
	} else {
		$ui->add('<a href="'.url($url).'">เรียงกิจกรรมตามกิจกรรมหลัก</a>');
	}
	$ui->add('<a href="'.url($url,array('download'=>'word')).'" >ดาวน์โหลด</a>');
	$ui->add('<a href="javascript:window.print()">พิมพ์</a>');
	if (!post('download')) $ret.='<div class="reportbar -no-print">'.$ui->build('ul').'</div>';


	if ($isEdit) {
		$inlineAttr['data-update-url']=url('project/edit/tr');
		$inlineAttr['data-period']=$period;
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}
	$ret.='<div id="project-report-s3" class="inline-edit project__report" '.sg_implode_attr($inlineAttr).'>'._NL;

	$ret.='<h3 class="noprint">แบบรายงานการดำเนินงานฉบับสมบูรณ์ (ส.3)</h3>'._NL;

	$section='title';
	$irs=end($rs->items[$section]);

	$ret.='<div class="project-cover-page -forprint"">';
	$ret.='<p>ชื่อโครงการ<p><h3>" '.$topic->title.' "</h3>'._NL;
	$ret.='<p>หัวหน้าโครงการ<p>'.$topic->project->prowner.''._NL;
	if($topic->project->prteam) $ret.='<p>คณะทำงาน<p><ol><li>'.implode('</li><li>',explode(',',$topic->project->prteam)).'</li></ol>'._NL;
	if($topic->project->org) $ret.='<p>ชื่อหน่วยงานที่ได้รับทุน<p><ol><li>'.$topic->project->org.'</li></ol>'._NL;
	//$ret.='<p>“ได้รับการสนับสนุนโดย สำนักงานกองทุนสนับสนุนการสร้างเสริมสุขภาพ (สสส.)”</p>';
	$ret.='<p>เดือน ปี ที่พิมพ์</p><p>'.($topic->project->date_end?sg_date($topic->project->date_end,'ดดด ปปปป'):'').'</p>';
	$ret.='</div>';
	$ret.='<hr class="pagebreak" />';





	// section :: ชื่อโครงการ
	$ret.='<div class="-forprint">';
	$ret.='<p><strong>ชื่อโครงการ '.$topic->project->title.'</strong></p>';
	$ret.='<p></p>';
	$ret.='<p>ชุมชน '.$topic->project->area.' จังหวัด '.$topic->project->provname.'</p>';
	$ret.='<p></p>';
	$ret.='<p>รหัสโครงการ '.$topic->project->prid.' เลขที่ข้อตกลง '.$topic->project->agrno.'</p>';
	$ret.='<p></p>';
	$ret.='<p>ระยะเวลาดำเนินงาน ตั้งแต่ '.($topic->project->date_from?sg_date($topic->project->date_from,'ว ดดด ปปปป'):'').' ถึง '.($topic->project->date_end?sg_date($topic->project->date_end,'ว ดดด ปปปป'):'').'</p>';
	$ret.='</div>';




	$ret.='<hr class="pagebreak" />';

	$ret.='<a name="section1"></a>';

	$ret.='<div class="-forprint">';
	$ret.='<h3>คำนำ</h3>
<p>ชุมชน/ท้องถิ่นเป็นฐานหลักของสังคม แนวคิดการพัฒนาระดับชุมชนและท้องถิ่นให้เข้มแข็ง น่าอยู่ ปลอดภัย มีพัฒนาการพึ่งพาตนเองได้อย่างยั่งยืน ทั้งด้านสังคม การศึกษา วัฒนธรรม เศรษฐกิจ สิ่งแวดล้อม จะส่งผลให้ประชาชนในชุมชนมีสุขภาวะที่ดี และหากทุกชุมชน/ท้องถิ่นมุ่งมั่นดำเนินการอย่างกว้างขวาง ประเทศไทยก็จะพัฒนาอย่างมีทิศทาง เป็นประเทศที่น่าอยู่ที่สุด</p>
<p>ชุมชนจะเข้มแข็ง น่าอยู่ และพึ่งตนเองได้ ต้องอาศัยความรับผิดชอบ การเรียนรู้ ร่วมคิดร่วมทำ ตลอดจนอาศัยความร่วมมือและข้อมูลทางวิชาการจากหน่วยงานในท้องถิ่นไม่ว่าจะเป็นหน่วยงานภาครัฐหรือเอกชน  เพื่อปรับเปลี่ยนวิธีคิด ปรับพฤติกรรมการผลิตและการบริโภคโดยยึดหลักปรัชญาเศรษฐกิจพอเพียง รักษาสิ่งแวดล้อม เน้นการพึ่งตนเอง เป็นสังคมเอื้ออาทร พึ่งพาอาศัยกันได้ และไม่ทอดทิ้งกัน</p>
<p>รายงานฉบับสมบูรณ์ <strong>โครงการ " '.$topic->title.' "</strong> จัดทำขึ้นเพื่อแสดงให้เห็นถึงกระบวนการชุมชนที่ผสมผสานการทำงานและใช้แนวคิดหลักการข้างต้นเพื่อพัฒนาทุนปรับปรุงปัจจัยที่ส่งผลกระทบต่อชุมชน ในการมุ่งให้ประชาชนในชุมชนมีสุขภาวะครบ 4 มิติ ทั้งทางกาย ใจ สังคม และ ปัญญา</p>
<p>ทางคณะทำงานโครงการหวังเป็นอย่างยิ่งว่ารายงานฉบับนี้จะมีประโยชน์ต่อผู้เกี่ยวข้องตั้งแต่ สำนักงานกองทุนสนับสนุนการสร้างเสริมสุขภาพ สถาบันการจัดการระบบสุขภาพ มอ. คณะทำงาน ตลอดจนสมาชิกในชุมชน และผู้ที่สนใจในการขับเคลื่อนงานชุมชนสุขภาวะ</p>
<p align="right">คณะทำงานโครงการ " '.$topic->title.' "</p>
<p align="right">วันที่ '.($topic->project->date_end?sg_date($topic->project->date_end,'ว ดดด ปปปป'):'').'</p>';
	$ret.='</div>';
	$ret.='<hr class="pagebreak" />';

	$ret.='<a name="section2"></a>';

	$ret.='<div class="-forprint">';
	$ret.='<h3>กิตติกรรมประกาศ</h3>
<p><strong>โครงการ " '.$topic->title.' "</strong> สำเร็จลุล่วงไปได้ด้วยดีด้วยความร่วมมือร่วมใจของ สมาชิกในชุมชน <strong>'.$topic->project->area.'</strong></p>
<p>ทางคณะทำงานโครงการขอขอบคุณ <strong>'.$topic->project->prtrainer.'</strong><!-- <span class="noprint"> ( ';
	foreach ($trainer as $trs) $ret.=$trs->name.' ';
	$ret.=' ) (นาย/นางสาว/นาง/พี่เลี้ยง)</span>--> พี่เลี้ยงโครงการที่ได้ให้ข้อเสนอแนะ แนวคิด ตลอดจนช่วยแก้ไขข้อบกพร่องการดำเนินงานมาโดยตลอด สถาบันการจัดการระบบสุขภาพภาคใต้ มหาวิทยาลัยสงขลานครินทร์ที่ได้ให้คำปรึกษาและจัดกระบวนการพบปะพูดคุยแลกเปลี่ยนทั้งภายในและระหว่างพื้นที่ โดยเฉพาะขอขอบคุณสำนักสร้างสรรค์โอกาสและนวัตกรรม สำนักงานกองทุนสนับสนุนการสร้างเสริมสุขภาพ ที่ให้การสนับสนุนงบประมาณในการดำเนินงาน สุดท้ายขอขอบคุณผู้เกี่ยวข้องที่มิได้ระบุชื่อไว้ในที่นี้ซึ่งมีส่วนสำคัญทำให้การดำเนินงานขับเคลื่อนไปสู่ชุมชนสุขภาวะเป็นไปอย่างราบรื่น</p>
<p></p>
<p>คณะทำงานโครงการ " '.$topic->title.' "</p>';
	$ret.='</div>';
	$ret.='<hr class="pagebreak" />';

	$ret.='<a name="section3"></a>';

	$ret.='<div class="-forprint">';
	$ret.='<h3>บทสรุปคัดย่อการดำเนินงาน</h3>';
	$abstract=end($estimate->items['5.7.1'])->text1;

	$ret.='<p><strong>โครงการ " '.$topic->title.' "</strong> ดำเนินการในพื้นที่ <strong>'.$topic->project->area.'</strong> รหัสโครงการ <strong>'.$topic->project->prid.'</strong> ระยะเวลาการดำเนินงาน <strong>'.($topic->project->date_from?sg_date($topic->project->date_from,'ว ดดด ปปปป'):'ยังไม่ระบุ').' - '.($topic->project->date_end?sg_date($topic->project->date_end,'ว ดดด ปปปป'):'(ยังไม่ระบุ)').'</strong> ได้รับการสนับสนุนงบประมาณจำนวน <strong>'.number_format($topic->project->budget,2).'</strong> บาท จากสำนักงานกองทุนสนับสนุนการสร้างเสริมสุขภาพ เพื่อใช้ในการดำเนินกิจกรรมโครงการ โดยมีกลุ่มเป้าหมายเป็นสมาชิกในชุมชนจำนวน <strong>'.$topic->project->totaltarget.'</strong> คน หลังจากสิ้นสุดระยะเวลาโครงการ ผลที่เกิดขึ้นจากการดำเนินงานปรากฏดังนี้</p>';

	$ret.=sg_text2html($abstract);
	$ret.='<p class="notes -no-print" style="margin: 20px 0; padding: 10px; background: #ccc;">หมายเหตุ : รายละเอียดของบทสรุปคัดย่อการดำเนินงาน ให้พี่เลี้ยงโครงการเป็นผู้เขียนสรุปภาพรวมของโครงการใน <a href="'.url('project/'.$tpid.'/eval.valuation').'">"แบบฟอร์มการเก็บข้อมูลเพื่อการประเมินแบบมีโครงสร้าง"</a> หัวข้อ 7. สรุปภาพรวมโครงการ ช่องรายละเอียด</p>';
	$ret.='</div>';




	$property=property('project::'.$tpid);

	$ret.='<hr class="pagebreak" />';

	$ret.='<a name="section4"></a>';

	$ret.='<div class="-forprint">';
	$ret.='<h3>สารบัญ</h3>
<table border="0">
<tr><td>คำนำ</td><td><a href="#section1" class="noprint">&raquo;</a></td></tr>
<tr><td>กิตติกรรมประกาศ</td><td><a href="#section2" class="noprint">&raquo;</a></td></tr>
<tr><td>บทสรุปคัดย่อการดำเนินงาน</td><td><a href="#section3" class="noprint">&raquo;</a></td></tr>
<tr><td>&nbsp;&nbsp;&nbsp;ความเป็นมา/หลักการเหตุผล</td><td><a href="#section5" class="noprint">&raquo;</a></td></tr>
<tr><td>&nbsp;&nbsp;&nbsp;วัตถุประสงค์โครงการ</td><td><a href="#section6" class="noprint">&raquo;</a></td></tr>
<tr><td>&nbsp;&nbsp;&nbsp;กิจกรรม/การดำเนินงาน</td><td><a href="#section7" class="noprint">&raquo;</a></td></tr>
<tr><td>&nbsp;&nbsp;&nbsp;กลุ่มเป้าหมาย</td><td><a href="#section8" class="noprint">&raquo;</a></td></tr>
<tr><td>&nbsp;&nbsp;&nbsp;ผลลัพธ์ที่ได้</td><td><a href="#section9" class="noprint">&raquo;</a></td></tr>
<tr><td>&nbsp;&nbsp;&nbsp;การประเมินผล</td><td><a href="#section10" class="noprint">&raquo;</a></td></tr>
<tr><td>&nbsp;&nbsp;&nbsp;ปัญหาและอุปสรรค</td><td><a href="#section11" class="noprint">&raquo;</a></td></tr>
<tr><td>&nbsp;&nbsp;&nbsp;ข้อเสนอแนะ</td><td><a href="#section12" class="noprint">&raquo;</a></td></tr>
<tr><td>&nbsp;&nbsp;&nbsp;เอกสารประกอบอื่นๆ ได้แก่ เอกสาร สิ่งพิมพ์ หรือสื่อที่ใช้ในกิจกรรมโครงการ รวมทั้งภาพถ่ายกิจกรรม ไม่เกิน 10 ภาพ พร้อมทั้งคำบรรยายใต้ภาพ</td><td><a href="#section13" class="noprint">&raquo;</a></td></tr>
</table>';
	$ret.='</div>';
	$ret.='<hr class="pagebreak" />';

	$ret.='<a name="section5"></a>';

	$ret.='<div class="-forprint">';
	$ret.='<h3>ความเป็นมา/หลักการเหตุผล</h3>	';
	$ret.='<h4>สถานการณ์</h4>
<h5>สถานการณ์สุขภาวะ</h5>'.sg_text2html($property['SITUATION']).'
<h5>ปัจจัยที่เป็นสาเหตุที่เกี่ยวข้องกับ</h5>
<ul>
<li>คน:'.sg_text2html($property['PEOPLE']).'</li>
<li>สภาพแวดล้อม:'.sg_text2html($property['ENVIRONMENT']).'</li>
<li>กลไก:'.sg_text2html($property['MECHANISM']).'</li>
</ul>';

	$ret.='<a name="section6"></a>';

	$ret.='<ul>
<li>จุดหมาย/วัตถุประสงค์/เป้าหมาย:'.sg_text2html($property['OBJECTIVE']).'</li>
<li>ปัจจัยสำคัญที่เอื้อต่อความสำเร็จ/ตัวชี้วัด:'.sg_text2html($property['INDICATOR']).'</li>
<li>วิธีการสำคัญ:'.sg_text2html($property['METHOD']).'</li>
</ul>';

	$ret.='<h4>ปัจจัยนำเข้า</h4>
<h5>ทุนของชุมชน</h5>'.sg_text2html($property['CAPTITAL']).'
<h5>งบประมาณ</h5>'.sg_text2html($property['BUDGET']).'
<h5>บุคลากร</h5>'.sg_text2html($property['PERSONNEL']).'
<h5>ทรัพยากรอื่น</h5>'.sg_text2html($property['OTHERRESOURCE']).'
<h4>ขั้นตอนทำงาน</h4>'.sg_text2html($property['PROCESS']).'
<h4>ผลผลิต</h4>'.sg_text2html($property['OUTPUT']).'
<h4>ผลลัพธ์</h4>'.sg_text2html($property['OUTCOME']).'
<h4>ผลกระทบ</h4>'.sg_text2html($property['IMPACT']).'
<h4>กลไกและวิธีการติดตามของชุมชน</h4>'.sg_text2html($property['TRACKING']).'
<h4>กลไกและวิธีการประเมินผลของชุมชน</h4>'.sg_text2html($property['EVALUATION']);

	$ret.='<h3>วัตถุประสงค์โครงการ</h3>	';
	if ($info->items['objective']) {
		$ret.='<ol>';
		foreach ($info->items['objective'] as $irs) {
			$ret.='<li>'.$irs->text1.'</li>';
		}
		$ret.='</ol>';
	} else $ret.=sg_text2html($topic->project->objective);

	$ret.='<a name="section7"></a>';
	$ret.='<h3>กิจกรรม/การดำเนินงาน</h3>';
	if ($info->items['mainact']) {
		$ret.='<ol>';
		foreach ($info->items['mainact'] as $irs) {
			$ret.='<li>'.$irs->detail1.'</li>';
		}
		$ret.='</ol>';
	} else $ret.=sg_text2html($topic->project->activity);

	$ret.='<a name="section8"></a>';
	$ret.='<h3>กลุ่มเป้าหมาย</h3>'.sg_text2html($topic->project->target);
	$ret.='</div>';



	$ret.='<a name="section9"></a>';

	$ret.='<div class="-forprint">';
	$ret.='<h3>ส่วนที่ 1 ผลการดำเนินโครงการ <span>(แสดงผลการดำเนินงานรายกิจกรรมที่แสดงผลผลิตและผลลัพธ์ที่เกิดขึ้นจริง</span></h3>'._NL;

	if ($order=='mainact') {
		$mainact=project_model::get_main_activity($tpid, 'owner');

		$tables = new Table();
		$tables->addClass('project-report-s1-item');
		$tables->colgroup=array('summary'=>'width="80%"','amt output1'=>'width="10%"','amt output2'=>'width="10%"');
		$activityHead='<tr><th rowspan="2">วัตถุประสงค์ที่ตั้งไว้<br />ผลลัพธ์และตัวชี้วัดผลลัพธ์<sup>**</sup><br />กิจกรรมของโครงการ</th><th colspan="2">ผลผลิต<sup>*</sup></th></tr><tr><th>ผลผลิตที่ตั้งไว้</th><th>ผลผลิตที่เกิดขึ้นจริง</th></tr>';

		$mno=0;
		foreach ($mainact->info as $mrs) {
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

				if ($activity->gallery && !$download) {
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
		$ret.=$tables->build();
	} else {
		// Get activity from project_tr that between report date
		$tables = new Table();
		$tables->addClass('project-report-s1-item');
		$tables->colgroup=array('summary'=>'width="80%"','amt output1'=>'width="10%"','amt output2'=>'width="10%"');
		$tables->thead='<thead><tr><th rowspan="2">วัตถุประสงค์ที่ตั้งไว้<br />ผลลัพธ์และตัวชี้วัดผลลัพธ์<sup>**</sup><br />กิจกรรมของโครงการ</th><th colspan="2">ผลผลิต<sup>*</sup></th></tr><tr><th>ผลผลิตที่ตั้งไว้</th><th>ผลผลิตที่เกิดขึ้นจริง</th></tr></thead>';

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

			if ($activity->gallery && !$download) {
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

	$ret.='<div><p>* ผลผลิต หมายถึง ผลที่เกิดขึ้นเชิงปริมาณจากการทำกิจกรรม เช่น จำนวนผู้เข้าร่วมประชุม จำนวนผู้ผ่านการอบรม จำนวนครัวเรือนที่ปลูกผักสวนครัว เป็นต้น<br />** ผลลัพธ์ หมายถึง การเปลี่ยนแปลงที่นำไปสู่การแก้ปัญหา เช่น หลังอบรมมีผู้ปรับเปลี่ยนพฤติกรรมจำนวนกี่คน มีข้อบังคับหรือมาตรการของชุมชนที่นำไปสู่การปรับเปลี่ยนพฤติกรรมหรือสภาพแวดล้อม เป็นต้น ทั้งนี้ต้องมีข้อมูลอ้างอิงประกอบการรายงาน เช่น ข้อมูลรายชื่อแกนนำ , แบบสรุปการประเมินความรู้ , รูปภาพกิจกรรมพร้อมคำอธิบายใต้ภาพ เป็นต้น</p></div>'._NL;

	$section='title';
	$irs=end($rs->items[$section]);

	$ret.='<h4>การเปลี่ยนแปลงที่เกิดขึ้นนอกเหนือวัตถุประสงค์</h4>';
	$ret.=view::inlineedit(array('group'=>$formid.':title','fld'=>'text1','tr'=>$irs->trid,'ret'=>'html','button'=>'yes'),$irs->text1,$isEdit,'textarea');

	$ret.='</div>';
	$ret.='<hr class="pagebreak" />';




	$ret.='<div class="-forprint">';
	$ret.='<h3>ส่วนที่ 2 ประเมินความก้าวหน้าของการดำเนินงานโครงการและปัญหา/อุปสรรคในการดำเนินโครงการ</h3>';
	$ret.='<h4>ประเมินความก้าวหน้าของการดำเนินงานโครงการ</h4>';

	$totalCalendar=mydb::select('SELECT COUNT(*) total FROM %calendar% c LEFT JOIN %project_activity% a ON a.`calid`=c.`id` WHERE `tpid`=:tpid AND `calowner`=1 LIMIT 1',':tpid',$tpid,':reportdate',$periodInfo->report_to_date)->total;
	$totalActivity=mydb::select('SELECT COUNT(*) total FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="activity" AND `part`="owner" LIMIT 1',':tpid',$tpid,':reportdate',$periodInfo->report_to_date)->total;

	$activityPercent=round($totalActivity*100/$totalCalendar);


	$totalBudget=$topic->project->budget;
	$totalSpend=mydb::select('SELECT SUM(`num7`) total FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="activity" AND `part`="owner" LIMIT 1',':tpid',$tpid,':reportdate',$periodInfo->report_to_date)->total;
	$spendPercent=round($totalSpend*100/$totalBudget);

	$rateRs=mydb::select('SELECT SUM(`rate1`) rate,4*COUNT(*) total FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="activity" AND `part`="owner" LIMIT 1',':tpid',$tpid,':reportdate',$periodInfo->report_to_date);
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


	$section='title';
	$irs=end($rs->items[$section]);

	$ret.='<a name="section10"></a>';
	$ret.='<h4>ปัญหาอุปสรรคสำคัญที่มีผลกระทบต่อการดำเนินงาน</h4>';
	$tables = new Table();
	$tables->thead=array('ปัญหาและอุปสรรค','สาเหตุเพราะ','แนวทางแก้ไขของผู้รับทุน');
	$tables->rows[]=array(
		view::inlineedit(array('group'=>$formid.':title','fld'=>'text3','tr'=>$irs->trid,'ret'=>'html','button'=>'yes'),$irs->text3,$isEdit,'textarea'),
		view::inlineedit(array('group'=>$formid.':title','fld'=>'text5','tr'=>$irs->trid,'ret'=>'html','button'=>'yes'),$irs->text5,$isEdit,'textarea'),
		view::inlineedit(array('group'=>$formid.':title','fld'=>'text4','tr'=>$irs->trid,'ret'=>'html','button'=>'yes'),$irs->text4,$isEdit,'textarea')
	);
	$ret.=$tables->build();
	$ret.='<p></p>';




	$ret.='<a name="section9"></a>';

	$ret.='<h4>การประเมินผลตามตัวชี้วัด</h4>';

	$tables = new Table();
	$tables->addClass('project-s3-estimate');
	$tables->colgroup=array('width="50%"','width="50%"');
	$tables->thead=array('ตัวชี้วัด','การประเมินผลตามตัวชี้วัด');
	foreach ($info->items['objective'] as $irs) {
		$tables->rows[]=array(
			sg_text2html($irs->text2),
			view::inlineedit(array('group'=>$formid.':objective','fld'=>'text3','tr'=>$irs->trid,'ret'=>'html','button'=>'yes'),$irs->text3,$isEdit,'textarea',NULL,'กรุณาระบุรายละเอียดการประเมินผลตามตัวชี้วัด'),
		);
	}

	$ret .= $tables->build();
	$ret.='</div>';




	$ret.='<div class="-forprint">';
	$ret.='<h3>ส่วนที่ 3 ผลลัพธ์ของโครงการที่สอดคล้องเป้าหมายตัวชี้วัดของแผนสร้างสรรค์โอกาสและนวัตกรรมสุขภาวะ</h3>';
	$ret.='<h4>แกนนำด้านการสร้างเสริมสุขภาพในประเด็นต่าง ๆ</h4>';
	$ret.='<div><p>(แกนนำด้านการสร้างเสริมสุขภาพ หมายถึง ผู้ผลักดันหรือผู้ทำให้เกิดการสร้างเสริมสุขภาพ ชักชวนผู้อื่นมาร่วมทำกิจกรรมที่ทำให้มีสุขภาพดียิ่งขึ้น เช่น แกนนำเลิกเหล้า บุหรี่)</p></div>';

	//$ret.='<div><p><strong>เกิดแกนนำ จำนวน คน</strong> โปรดระบุข้อมูลของแกนนำ ตามตัวอย่างตารางด้านล่าง พร้อมแนบมากับรายงาน</p></div>';
	$ret.=__project_form_s3_listleader($tpid,$isEdit);


	$section='title';
	$irs=end($rs->items[$section]);
	$ret.='<h4>จำนวนผู้ได้รับประโยชน์จากโครงการนี้</h4>';
	$ret.='<div><p>(ผู้ได้รับประโยชน์โดยตรง หมายถึง ผู้ที่มีส่วนร่วมในโครงการ/ผู้ปฏิบัติ/ผู้ลงมือทำ/กลุ่มเป้าหมายหลักของโครงการ<br />ผู้ได้รับประโยชน์ทางอ้อม หมายถึง ผู้ที่ไม่ได้เป็นกลุ่มเป้าหมายที่ลงมือทำแต่ได้รับผลประโยชน์จากที่มีโครงการดังกล่าว)</p></div>';
	$tables = new Table();
	$tables->thead=array('ผู้ได้รับประโยชน์ (คน)','amt'=>'จำนวนผู้ได้รับประโยชน์ (คน)<br />(ไม่นับรายชื่อซ้ำ)','พฤติกรรมที่ส่งเสริมสุขภาพ');
	$tables->rows[]=array(
		'ทางตรง',
		view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num1','tr'=>$irs->trid,'ret'=>'numeric','placeholder'=>'0'),number_format($irs->num1,0),$isEdit,NULL,NULL,'0'),
		view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text6','tr'=>$irs->trid,'ret'=>'html','button'=>'yes'),$irs->text6,$isEdit,'textarea')
	);
	$tables->rows[]=array(
		'ทางอ้อม',
		view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num2','tr'=>$irs->trid,'ret'=>'numeric','placeholder'=>'0'),number_format($irs->num2,0),$isEdit,NULL,NULL,'0'),
		view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text7','tr'=>$irs->trid,'ret'=>'html','button'=>'yes'),$irs->text7,$isEdit,'textarea')
	);
	$ret.=$tables->build();


	$section='outcome';
	$irs=end($rs->items[$section]);
	$ret.='<h4>เกิดสภาพแวดล้อมและปัจจัยทางสังคมที่เอื้อต่อสุขภาพ</h4>';
	$ret.='<div><p>(สภาพแวดล้อมหรือปัจจัยทางสังคมที่เอื้อต่อสุขภาพ เช่น 1) เกิดกฎ กติกา ระเบียบ หรือมาตรการชุมชน เช่น มาตรการลดละเลิกเหล้าในงานศพ/งานบุญ ข้อตกลงการจัดการขยะ ขยายพื้นที่ปลอดบุหรี่ 2) เกิดกลไก ระบบ หรือโครงสร้างชุมชนที่พัฒนาขึ้นใหม่ เช่น กลไกสภาผู้นำ, เกิดกองทุนของชุมชน, ระบบเตือนภัย/เฝ้าระวังภัยในชุมชน, การัดพื้นที่เรียนรู้ในชุมชน, การจัดสภาพแวดล้อมให้เอื้อต่อการใช้จักรยานในชุมน, เกิดกลุ่มแกนนำอาสาสมัครดูแลผู้สูงอายุในชุมชน 3) เกิดต้นแบบ พ้นที่เรียนรู้ หรือแหล่งเรียนรู้ในระดับชุมชน)</p></div>';

	$tables = new Table();
	$tables->thead=array('','ประเภท','รายละเอียดของสิ่งที่เกิดขึ้น (ทั้งเชิงปริมาณและคุณภาพ)');
	$tables->rows[]=array(
		'1)',
		'เกิดกฏ กติกา ระเบียบ หรือมาตรการชุมชน',
		view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html','button'=>'yes'),$irs->text1,$isEdit,'textarea')
	);
	$tables->rows[]=array(
		'2)',
		'เกิดกลไก ระบบ หรือโครงสร้างชุมชนที่พัฒนาขึ้นใหม่',
		view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid,'ret'=>'html','button'=>'yes'),$irs->text2,$isEdit,'textarea')
	);
	$tables->rows[]=array(
		'3)',
		'เกิดต้นแบบ พื้นที่เรียนรู้ หรือแหล่งเรียนรู้ในระดับชุมชน',
		view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text3','tr'=>$irs->trid,'ret'=>'html','button'=>'yes'),$irs->text3,$isEdit,'textarea')
	);
	$ret.=$tables->build();





	$ret.='<h4>นวัตกรรมสร้างเสริมสุขภาพ</h4>';
	$ret.='<div><p>นวัตกรรมสร้างเสริมสุขภาพระดับพื้นที่ หมายถึง การจัดการความคิด กลไก หรือกระบวนการ และ/หรือ เทคโนโลยีที่เหมาะสม นำมาใช้ประโยชน์ในการดำเนินงานโครงการหรือใช้ในการจัดการปัญหาสุขภาพของพื้นที่แล้วได้ผลดีกว่าเดิม</p></div>';

	$ret.=__project_form_s3_listinno($tpid,$isEdit);
	$ret.='</div>';





	$ret.='<a name="section12"></a>';

	$ret.='<div class="-forprint">';
	$ret.='<h3>เอกสารประกอบอื่นๆ</h3><div><p class="noprint">ได้แก่ เอกสาร สิ่งพิมพ์ หรือสื่อที่ใช้ในกิจกรรมโครงการ รวมทั้งภาพถ่ายกิจกรรม ไม่เกิน 10 ภาพ พร้อมทั้งคำบรรยายใต้ภาพ</p></div>';
	$ret.='<p></p>';
	$ret.='</div>';



	$section='title';
	$irs=end($rs->items[$section]);
	$signdate=SG\getFirst($irs->detail1,$topic->project->date_end);
	$ret.='<div style="width:40%;float:left;margin:60px 5% 30px; text-align:center;"><p>................................<br />( '.$topic->project->prowner.' )<br />ผู้รับผิดชอบโครงการ<br />'.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail1','tr'=>$irs->trid,'ret'=>'date:ว ดดด ปปปป', 'value'=>$irs->detail1),$signdate?sg_date($signdate,'ว ดดด ปปปป'):'',$isEdit,'datepicker').'</p></div>'._NL;

	$ret.='<div style="width:40%;float:right;margin:60px 5% 30px;text-align:center;"><p>................................<br />(................................)<br />ทีมสนับสนุนวิชาการ ส.น. 6<br />......./............/.......</p></div>'._NL;




	$ret.='</div>';

	$ret.='<a href="#top" class="noprint">ไปบนสุด</a>';

	if ($download) {
		sendheader('application/octet-stream');
		mb_internal_encoding("UTF-8");
		header("Content-type: application/vnd.ms-word");
		header('Content-Disposition: attachment; filename="'.mb_substr($topic->title,0,50).'-ส3-'.date('Y-m-d').'.doc"');
		// move style tag to head section
		$body=$ret;
		if (preg_match_all('/<style.*?>.*?<\/style>/si',$body,$out)) {
			foreach ($out[0] as $style) $styles.=$style._NL;
			$body=preg_replace('/(<style.*?>.*?<\/style>)/si','',$body);
		}
		$ret='<HTML>
		<HEAD>
		<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=utf-8">
		<TITLE>'.$topic->title.'</TITLE>
		'.$styles.'
		</HEAD>
		<BODY>
		'.$body.'
		</BODY>
		</HTML>';
		die($ret);
	}
	return $ret;
}

/**
* Add leader
* @param Integer $tpid
* @param String $formid
* @return String
*/
function __project_form_s3_addleader($tpid,$period) {
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
						(`psnid`, `uid`, `prename`, `name`, `lname`, `house`, `village`, `tambon`, `ampur`, `changwat`, `phone`, `email`)
					VALUES
						(:psnid, :uid, :prename, :name, :lname, :house, :village, :tambon, :ampur, :changwat, :phone, :email)
					ON DUPLICATE KEY UPDATE `prename`=:prename, `name`=:name, `lname`=:lname, `house`=:house, `village`=:village, `tambon`=:tambon, `ampur`=:ampur, `changwat`=:changwat, `phone`=:phone, `email`=:email ';
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

	$url='project/form/'.$tpid.'/s3';

	$form->config->variable='person';
	$form->config->method='post';
	$form->config->action=url($url);
	$form->config->class='sg-form';
	$form->config->attr='data-rel="box" data-done="close" data-callback="refreshContent" data-refresh-url="'.url($url).'"';

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

	$form->email->type='text';
	$form->email->label='อีเมล์';
	$form->email->size=100;
	$form->email->value=htmlspecialchars($post->email);

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
			$("#edit-person-fullname").attr("readonly",true)
			$("#edit-person-fullname").focus()
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
function __project_form_s3_listleader($tpid,$isEdit) {
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

	$ret.='<div><p><strong>เกิดแกนนำ จำนวน '.$dbs->_num_rows.' คน</strong></p></div>';

	$tables = new Table();
	$tables->thead=array('ชื่อ-สกุล','ที่อยู่ติดต่อได้สะดวก','บทบาทแกนนำ','center'=>$isEdit?'<a class="sg-action -no-print" href="'.url('project/form/'.$tpid.'/s3',array('act'=>'addleader')).'" data-rel="box" title="เพิ่มรายชื่อแกนนำ"><i class="icon -person-add"></i></a>':'');

	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			$rs->fullname,
			SG\implode_address($rs),
			$rs->leaderType,
			$isEdit?'<a class="sg-action hover--menu" href="'.url('project/form/'.$tpid.'/s3',array('act'=>'removeleader','id'=>$rs->psnid)).'" data-rel="this" data-confirm="ลบรายการนี้ กรุณายืนยัน" data-removeparent="tr" title="ลบรายชื่อแกนนำ">X</a>':''
		);
	}
	if ($isEdit) {
		$tables->rows[]=array('<td colspan="4" class="noprint" style="text-align:center;"><a class="sg-action btn" href="'.url('project/form/'.$tpid.'/s3',array('act'=>'addleader')).'" data-rel="box" title="เพิ่มรายชื่อแกนนำ"><i class="icon -person-add"></i>เพิ่มรายชื่อแกนนำ</a></td>');
	}

	$no=0;
	//$tables->thead=array('ชื่อ-สกุล','ที่อยู่ติดต่อได้สะดวก','คุณสมบัติแกนนำ/ผู้นำการเปลี่ยนแปลง',$isEdit?'<a href="" data-action="add" data-group="follow:'.$section.'">+เพิ่ม</a>':'');
	foreach ($rs->items[$section] as $irs) {
		$tables->rows[]=array(
			view::inlineedit(array('group'=>'follow:'.$section,'fld'=>'detail1','tr'=>$irs->trid,'class'=>'w-9'),$irs->detail1,$isEdit),
			view::inlineedit(array('group'=>'follow:'.$section,'fld'=>'detail2','tr'=>$irs->trid,'class'=>'w-9'),$irs->detail2,$isEdit),
			view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>$irs->text1),$irs->text1,$isEdit,'textarea'),
			$isEdit?'<a href="" data-action="del" data-group="follow:'.$section.'" data-tr="'.$irs->trid.'" title="ลบรายการนี้">X</a>':''
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
function __project_form_s3_addinno($tpid,$trid) {
	$ret.='<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3 class="title">นวัตกรรมสร้างเสริมสุขภาพ</h3></header>';

	$post=(object)post('inno');
	if ($post->title) {
		$post->tpid=$tpid;
		$post->trid=SG\getFirst($post->trid,NULL);
		$post->uid=i()->uid;
		$post->created=date('U');
		$post->modified=date('U');
		$post->modifyby=i()->uid;

		$stmt='INSERT INTO %project_tr%
						(`trid`, `tpid`, `uid`, `formid`, `part`, `detail1`, `detail2`, `text1`, `created`)
					VALUES
						(:trid, :tpid, :uid, "ส.3", "innovation", :title, :innotype, :innouse, :created)
					ON DUPLICATE KEY UPDATE `detail1`=:title, `detail2`=:innotype, `text1`=:innouse, `modified`=:modified, `modifyby`=:modifyby ';
		mydb::query($stmt,$post);
		//$ret.=mydb()->_query;
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

	$url='project/form/'.$tpid.'/s3';
	$form->config->variable='inno';
	$form->config->method='post';
	$form->config->action=url($url);
	$form->config->class='sg-form';
	$form->config->checkvalid=true;
	$form->config->attr='data-rel="box" data-done="close" data-callback="refreshContent" data-refresh-url="'.url($url).'"';

	$form->act=array('type'=>'hidden','name'=>'act','value'=>'addinno');

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
function __project_form_s3_listinno($tpid,$isEdit) {
	$stmt='SELECT *
					FROM %project_tr%
					WHERE `tpid`=:tpid AND `part`="innovation"
					ORDER BY `trid` ASC';
	$dbs=mydb::select($stmt,':tpid',$tpid);

	$tables = new Table();
	$tables->thead=array(
		'ชื่อนวัตกรรม',
		'center 1'=>'การพัฒนาความรู้ใหม่จากการวิจัยและพัฒนา',
		'center 2'=>'การนำสิ่งที่มีอยู่ในชุมชนอื่นมาปรับใช้ในชุมชนตนเอง',
		'center 3'=>'การนำสิ่งที่มีอยู่มาปรับกระบวนทัศน์ใหม่หรือทำด้วยวิธีใหม่',
		'center 4'=>'การรื้อฟื้นสิ่งดีๆ ที่เคยมีในชุมชนมาปรับให้สอดคล้องกับสถานการณ์ปัจจุบัน',
		'การนำนวัตกรรมไปใช้ประโยชน์<br />(แสดงให้เห็นว่านวัตกรรมตามที่ระบุมานั้นได้นำไปใช้ในการดำเนินงานโครงการแล้วทำให้เกิดการจัดการปัญหาของพื้นที่แล้วได้ผลดีกว่าเดิมอย่างไร)',
		$isEdit?'<a class="sg-action -no-print" href="'.url('project/form/'.$tpid.'/s3',array('act'=>'addinno')).'" data-rel="box" title="เพิ่มรายชื่อนวัตกรรม"><i class="icon -add"></i></a>':''
	);

	$section='innovation';
	foreach ($dbs->items as $rs) {
		$formid=$rs->formid;
		$isEditable=$formid=='ส.3';
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
		$tables->rows[]=array('<td colspan="7" class="noprint" style="text-align:center;"><a class="sg-action btn" href="'.url('project/form/'.$tpid.'/s3',array('act'=>'addinno')).'" data-rel="box" title="เพิ่มรายชื่อนวัตกรรม"><i class="icon -add"></i>เพิ่มรายชื่อนวัตกรรม</a>');
	}
	$ret.=$tables->build();
	return $ret;
}
?>