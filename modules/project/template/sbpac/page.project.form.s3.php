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
	$formid='รายงานฉบับสมบูรณ์';

	$is_edit=($topic->project->project_statuscode==1) && (user_access('administer projects') || project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid));

	$info=project_model::get_tr($tpid,'info');
	$estimate=project_model::get_tr($tpid,'ประเมิน');
	$activities=R::Model('project.activity.get.bytpid',$tpid,
								'{owner:'._PROJECT_OWNER_ACTIVITY.'}'
								);
	$rs=project_model::get_tr($tpid,$formid);
	// Show member of this project
	$trainer=mydb::select('SELECT u.uid, u.username, u.name, tu.membership FROM %topic_user% tu LEFT JOIN %users% u ON u.uid=tu.uid WHERE `tpid`=:tpid AND tu.membership="Trainer" ',':tpid',$topic->tpid)->items;

	$ret.='<a name="top"></a>';

	$ui=new ui();
	$ui->add('<a href="'.url('paper/'.$tpid.'/member/owner/post/s3').'">รายงาน ส.3</a>');
	$ui->add('<a href="'.url('paper/'.$tpid.'/member/owner/post/s3',array('o'=>'word','a'=>'download')).'" >ดาวน์โหลด</a>');
	if (!post('a')) $ret.='<div class="reportbar">'.$ui->build('ul').'</div>';


	if ($is_edit) {
		$inlineAttr['data-update-url']=url('project/edit/tr');
		$inlineAttr['data-period']=$period;
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}
	$ret.='<div id="project-report-s3" class="inline-edit project__report" '.sg_implode_attr($inlineAttr).'>'._NL;

	$ret.='<h3 class="noprint">แบบรายงานการดำเนินงานฉบับสมบูรณ์</h3>'._NL;

	$section='title';
	$irs=end($rs->items[$section]);

	$ret.='<div class="project-cover-page">';
	$ret.='<p>ชื่อโครงการ<p><h3>" '.$topic->title.' "</h3>'._NL;
	$ret.='<p>หัวหน้าโครงการ<p>'.$topic->project->prowner.''._NL;
	if($topic->project->prteam) $ret.='<p>คณะทำงาน<p><ol><li>'.implode('</li><li>',explode(',',$topic->project->prteam)).'</li></ol>'._NL;
	if($topic->project->org) $ret.='<p>ชื่อหน่วยงานที่ได้รับทุน<p><ol><li>'.$topic->project->org.'</li></ol>'._NL;
	$ret.='<p>“ได้รับการสนับสนุนโดย ศูนย์อำนวยการบริหารจังหวัดชายแดนภาคใต้ (ศอ.บต.)”</p>';
	$ret.='<p>เดือน ปี ที่พิมพ์</p><p>'.($topic->project->date_end?sg_date($topic->project->date_end,'ดดด ปปปป'):'').'</p>';
	$ret.='</div>';
	$ret.='<hr class="pagebreak" />';

	$ret.='<a name="section1"></a>';

	$ret.='<h3>คำนำ</h3>';
	/*
	$ret.='
<p>ชุมชน/ท้องถิ่นเป็นฐานหลักของสังคม แนวคิดการพัฒนาระดับชุมชนและท้องถิ่นให้เข้มแข็ง น่าอยู่ ปลอดภัย มีพัฒนาการพึ่งพาตนเองได้อย่างยั่งยืน ทั้งด้านสังคม การศึกษา วัฒนธรรม เศรษฐกิจ สิ่งแวดล้อม จะส่งผลให้ประชาชนในชุมชนมีสุขภาวะที่ดี และหากทุกชุมชน/ท้องถิ่นมุ่งมั่นดำเนินการอย่างกว้างขวาง ประเทศไทยก็จะพัฒนาอย่างมีทิศทาง เป็นประเทศที่น่าอยู่ที่สุด</p>
<p>ชุมชนจะเข้มแข็ง น่าอยู่ และพึ่งตนเองได้ ต้องอาศัยความรับผิดชอบ การเรียนรู้ ร่วมคิดร่วมทำ ตลอดจนอาศัยความร่วมมือและข้อมูลทางวิชาการจากหน่วยงานในท้องถิ่นไม่ว่าจะเป็นหน่วยงานภาครัฐหรือเอกชน  เพื่อปรับเปลี่ยนวิธีคิด ปรับพฤติกรรมการผลิตและการบริโภคโดยยึดหลักปรัชญาเศรษฐกิจพอเพียง รักษาสิ่งแวดล้อม เน้นการพึ่งตนเอง เป็นสังคมเอื้ออาทร พึ่งพาอาศัยกันได้ และไม่ทอดทิ้งกัน</p>
<p>รายงานฉบับสมบูรณ์ <strong>โครงการ " '.$topic->title.' "</strong> จัดทำขึ้นเพื่อแสดงให้เห็นถึงกระบวนการชุมชนที่ผสมผสานการทำงานและใช้แนวคิดหลักการข้างต้นเพื่อพัฒนาทุนปรับปรุงปัจจัยที่ส่งผลกระทบต่อชุมชน ในการมุ่งให้ประชาชนในชุมชนมีสุขภาวะครบ 4 มิติ ทั้งทางกาย ใจ สังคม และ ปัญญา</p>
<p>ทางคณะทำงานโครงการหวังเป็นอย่างยิ่งว่ารายงานฉบับนี้จะมีประโยชน์ต่อผู้เกี่ยวข้องตั้งแต่ สำนักงานกองทุนสนับสนุนการสร้างเสริมสุขภาพ สถาบันการจัดการระบบสุขภาพ มอ. คณะทำงาน ตลอดจนสมาชิกในชุมชน และผู้ที่สนใจในการขับเคลื่อนงานชุมชนสุขภาวะ</p>
<p align="right">คณะทำงานโครงการ " '.$topic->title.' "</p>
<p align="right">วันที่ '.($topic->project->date_end?sg_date($topic->project->date_end,'ว ดดด ปปปป'):'').'</p>';
*/
	$ret.='<hr class="pagebreak" />';

	$ret.='<a name="section2"></a>';

	$ret.='<h3>กิตติกรรมประกาศ</h3>';
	/*
	$ret.='<p><strong>โครงการ " '.$topic->title.' "</strong> สำเร็จลุล่วงไปได้ด้วยดีด้วยความร่วมมือร่วมใจของ สมาชิกในชุมชน <strong>'.$topic->project->area.'</strong></p>
<p>ทางคณะทำงานโครงการขอขอบคุณ <strong>'.$topic->project->prtrainer.'</strong><!-- <span class="noprint"> ( ';
	foreach ($trainer as $trs) $ret.=$trs->name.' ';
	$ret.=' ) (นาย/นางสาว/นาง/พี่เลี้ยง)</span>--> พี่เลี้ยงโครงการที่ได้ให้ข้อเสนอแนะ แนวคิด ตลอดจนช่วยแก้ไขข้อบกพร่องการดำเนินงานมาโดยตลอด สถาบันการจัดการระบบสุขภาพภาคใต้ มหาวิทยาลัยสงขลานครินทร์ที่ได้ให้คำปรึกษาและจัดกระบวนการพบปะพูดคุยแลกเปลี่ยนทั้งภายในและระหว่างพื้นที่ โดยเฉพาะขอขอบคุณสำนักสร้างสรรค์โอกาสและนวัตกรรม สำนักงานกองทุนสนับสนุนการสร้างเสริมสุขภาพ ที่ให้การสนับสนุนงบประมาณในการดำเนินงาน สุดท้ายขอขอบคุณผู้เกี่ยวข้องที่มิได้ระบุชื่อไว้ในที่นี้ซึ่งมีส่วนสำคัญทำให้การดำเนินงานขับเคลื่อนไปสู่ชุมชนสุขภาวะเป็นไปอย่างราบรื่น</p>
<p></p>
<p>คณะทำงานโครงการ " '.$topic->title.' "</p>';
	*/
	$ret.='<hr class="pagebreak" />';

	$ret.='<a name="section3"></a>';

	$ret.='<h3>บทสรุปคัดย่อการดำเนินงาน</h3>';
	$abstract=end($estimate->items['5.7.1'])->text1;

	/*
	$ret.='<p><strong>โครงการ " '.$topic->title.' "</strong> ดำเนินการในพื้นที่ <strong>'.$topic->project->area.'</strong> รหัสโครงการ <strong>'.$topic->project->prid.'</strong> ระยะเวลาการดำเนินงาน <strong>'.($topic->project->date_from?sg_date($topic->project->date_from,'ว ดดด ปปปป'):'ยังไม่ระบุ').' - '.($topic->project->date_end?sg_date($topic->project->date_end,'ว ดดด ปปปป'):'(ยังไม่ระบุ)').'</strong> ได้รับการสนับสนุนงบประมาณจำนวน <strong>'.number_format($topic->project->budget,2).'</strong> บาท จากสำนักงานกองทุนสนับสนุนการสร้างเสริมสุขภาพ เพื่อใช้ในการดำเนินกิจกรรมโครงการ โดยมีกลุ่มเป้าหมายเป็นสมาชิกในชุมชนจำนวน <strong>'.$topic->project->totaltarget.'</strong> คน หลังจากสิ้นสุดระยะเวลาโครงการ ผลที่เกิดขึ้นจากการดำเนินงานปรากฏดังนี้</p>';
	*/
	$ret.=sg_text2html($abstract);
	$ret.='<p class="notes -no-print" style="margin: 20px 0; padding: 10px; background: #ccc;">หมายเหตุ : รายละเอียดของบทสรุปคัดย่อการดำเนินงาน ให้พี่เลี้ยงโครงการเป็นผู้เขียนสรุปภาพรวมของโครงการใน <a href="'.url('project/'.$tpid.'/eval.valuation').'">"แบบฟอร์มการเก็บข้อมูลเพื่อการประเมินแบบมีโครงสร้าง"</a> หัวข้อ 7. สรุปภาพรวมโครงการ ช่องรายละเอียด</p>';

	$property=property('project::'.$tpid);

	$ret.='<hr class="pagebreak" />';

	$ret.='<a name="section4"></a>';

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
	$ret.='<hr class="pagebreak" />';

	$ret.='<a name="section5"></a>';

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
	$ret.='<h3>กลุ่มเป้าหมาย</h3>
'.sg_text2html($topic->project->target);

	$ret.='<a name="section9"></a>';

	$ret.='<h3>ผลลัพธ์ที่ได้</h3>	';

	$tables = new Table();
	$tables->addClass('project-report-s1-item');

		foreach ($activities->items as $activity) {
			unset($row);
			$tables->rows[]=array('<h3 style="text-align:left;">กิจกรรมที่ '.(++$no).' '.$activity->title.'</h3><h4>วันที่ '.sg_date($activity->action_date,'ว ดดด ปปปป').' เวลา '.$activity->action_time.'</h4>');

			$summary='';
			$summary.='<h4>วัตถุประสงค์</h4>'.sg_text2html($activity->objective)._NL;
			$summary.='<h4>กิจกรรมตามแผน</h4>'
								.'<p>จำนวนกลุ่มเป้าหมายที่ตั้งไว้ <strong>'.number_format($activity->targetpreset).'</strong> คน</p>'._NL
								.'<p><strong>รายละเอียดกลุ่มเป้าหมายที่ตั้งไว้</strong></p>'.sg_text2html($activity->targetPresetDetail)._NL
								.'<p><strong>รายละเอียดกิจกรรมตามแผน</strong></p>'.$activity->goal_do._NL;
			$summary.='<h4>กิจกรรมที่ปฎิบัติจริง</h4>'
								.'<p>จำนวนคน/ผู้เข้าร่วมกิจกรรมจริง <strong>'.number_format($activity->targetjoin).'</strong> คน</p>'._NL
								.'<p><strong>รายละเอียดกลุ่มเป้าหมายที่เข้าร่วม</strong></p>'.sg_text2html($activity->targetjoindetail)._NL
								.'<p><strong>รายละเอียดขั้นตอน กระบวนการ กิจกรรมปฎิบัติจริง</strong></p>'.sg_text2html($activity->real_do)._NL;

			$summary.='<h4>ผลที่เกิดขึ้นจริง (ผลสรุปที่สำคัญของกิจกรรม)</h4>'.sg_text2html($activity->real_work)._NL;

			$summary.='<h4>ปัญหา/แนวทางแก้ไข</h4>'.sg_text2html($activity->problem)._NL;
			$summary.='<h4>ข้อเสนอแนะต่อ ศอ.บต.</h4>'.sg_text2html($activity->recommendation)._NL;
			$summary.='<h4>ความต้องการสนับสนุนจากพี่เลี้ยงและ ศอ.บต.</h4>'.sg_text2html($activity->support)._NL;
			$summary.='<h4>ชื่อผู้ติดตามในพื้นที่ของ ศอ.บต.</h4>'.sg_text2html($activity->followername)._NL;

			if ($activity->gallery) {
				$summary.='<div class="photo">'._NL;
				$summary.='<ul>'._NL;
				if (debug('method')) $summary.=$rs->photos.print_o($rs,'$rs');
				foreach (explode(',',$activity->photos) as $item) {
					list($photoid,$photo)=explode('|',$item);
					$photo=model::get_photo_property($photo);
					$photo_alt=$item->title;
					$summary .= '<li>';
//					$summary.=print_o($photo,'$photo');
					$summary.='<img height="80" class="photo photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_url.'" alt="photo '.$photo_alt.'" ';
					$summary.=' />';
					$summary.=$item->title;
					$summary .= '</li>'._NL;
				}
				$summary.='</ul>'._NL;
				$summary.='</div><!--photo-->'._NL;
			}
			$extimateList=array(4=>'บรรลุผลมากกว่าเป้าหมาย',3=>'บรรลุผลตามเป้าหมาย',2=>'เกือบได้ตามเป้าหมาย',1=>'ได้น้อยกว่าเป้าหมายมาก');
			$summary.='<p>ประเมินคุณภาพกิจกิจกรรม <strong>'.$activity->rate.' : '.$extimateList[$activity->rate].'</strong></p>';

			$row[]=$summary;
			$tables->rows[]=$row;
		}

	$ret .= $tables->build();

	$ret.='<hr class="pagebreak" />';

	$ret.='<a name="section9"></a>';

	$ret.='<h3>การประเมินผล</h3>	';

	$tables = new Table();
	$tables->thead=array('ตัวชี้วัด','การประเมินผลตามตัวชี้วัด');
	foreach ($info->items['objective'] as $irs) {
		$tables->rows[]=array(sg_text2html($irs->text2),
			view::inlineedit(array('group'=>$formid.':objective','fld'=>'text3','tr'=>$irs->trid,'ret'=>'html','button'=>'yes'),$irs->text3,$is_edit,'textarea',NULL,'กรุณาระบุรายละเอียดการประเมินผลตามตัวชี้วัด')
		);
	}

	$ret .= $tables->build();

	$section='title';
	$irs=end($rs->items[$section]);

	$ret.='<a name="section10"></a>';

	$ret.='<h3>ปัญหาและอุปสรรค</h3>	';
	$ret.=view::inlineedit(array('group'=>$formid.':title','fld'=>'text3','tr'=>$irs->trid,'ret'=>'html','button'=>'yes'),$irs->text3,$is_edit,'textarea',NULL,'กรุณาระบุปัญหาและอุปสรรค');

	$ret.='<a name="section11"></a>';

	$ret.='<h3>ข้อเสนอแนะ</h3>	';
	$ret.=view::inlineedit(array('group'=>$formid.':title','fld'=>'text4','tr'=>$irs->trid,'ret'=>'html','button'=>'yes'),$irs->text4,$is_edit,'textarea',NULL,'กรุณาระบุข้อเสนอแนะ');
	$ret.='<p></p>';

	$ret.='<a name="section12"></a>';

	$ret.='<h3>เอกสารประกอบอื่นๆ</h3><p class="noprint">ได้แก่ เอกสาร สิ่งพิมพ์ หรือสื่อที่ใช้ในกิจกรรมโครงการ รวมทั้งภาพถ่ายกิจกรรม ไม่เกิน 10 ภาพ พร้อมทั้งคำบรรยายใต้ภาพ</p>	';
	$ret.='<p></p>';


	$ret.='</div>';

	$ret.='<a href="#top" class="noprint">ไปบนสุด</a>';

		if (post('a')=='download') {
			sendheader('application/octet-stream');
			header('Content-Disposition: attachment; filename="'.$topic->title.'-ส.3-'.date('Y-m-d').'.doc"');
		}
		if (post('o')=='word') {
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
?>