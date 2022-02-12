<?php
/**
* Project :: แบบรายงานฉบับสมบูรณ์ (ส.3)
* Created 2022-02-05
* Modify  2022-02-05
*
* @param Object $projectInfo
* @param String $action
* @return Widget
*
* @usage project/{id}/eval.finalreport[/{action}]
*/

import('widget:project.info.appbar.php');

class ProjectFinalreport extends Page {
	var $projectId;
	var $action;
	var $projectInfo;

	function __construct($projectInfo, $action = NULL) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->action = $action;
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		$projectInfo = $this->projectInfo;

		$formid = 'finalreport';
		$download = post('download');
		$order = SG\getFirst(post('order'),'action');


		$finalReportTitle = project_model::get_tr($this->projectId,$formid);

		$info = project_model::get_tr($this->projectId,'info');
		$mainact = project_model::get_main_activity($this->projectId, 'owner');
		$valuation = project_model::get_tr($this->projectId,'valuation');
		$activities = R::Model(
			'project.activity.get.bytpid',
			$this->projectId,
			'{owner:'._PROJECT_OWNER_ACTIVITY.'}'
		);
		$rs = project_model::get_tr($this->projectId,$formid);
		$basicInfo = reset(SG\getFirst(project_model::get_tr($this->projectId, 'info:basic')->items['basic'], []));

		$isViewOnly = $this->action == 'view';
		$isEditable = $projectInfo->info->isRight;
		$isEdit = $projectInfo->info->isRight && $this->action == 'edit';

		$ret = '';


		$ret.='<a name="top"></a>';

		$url='project/'.$this->projectId.'/finalreport';

		$ret.='<h2 class="title -main -no-print">แบบรายงานการดำเนินงานฉบับสมบูรณ์</h3>'._NL;

		// Show form toolbar
		$ui=new ui();
		$ui->add('<a href="'.url($url).'"><i class="icon -viewdoc"></i>รายงานฉบับสมบูรณ์</a>');

		if ($order=='mainact') {
			$ui->add('<a href="'.url($url,array('order'=>'date')).'"><i class="icon -sort"></i>เรียงกิจกรรมตามวันที่</a>');
		} else {
			$ui->add('<a href="'.url($url).'"><i class="icon -sort"></i>เรียงกิจกรรมตามกิจกรรมหลัก</a>');
		}
		$ui->add('<a href="'.url($url,array('download'=>'word')).'" ><i class="icon -download"></i><span>ดาวน์โหลด</span></a>');
		$ui->add('<a href="javascript:window.print()"><i class="icon -print"></i></a>');
		if (!post('download')) $ret.='<div class="reportbar -no-print">'.$ui->build('ul').'</div>';

		/*
		if ($isEdit) {
			$inlineAttr['data-update-url']=url('project/edit/tr');
			$inlineAttr['data-period']=$period;
			if (post('debug')) $inlineAttr['data-debug']='yes';
		}



		$ret.='<div id="project-report-s3" class="inline-edit project__report project-report -s3" '.sg_implode_attr($inlineAttr).'>'._NL;
		*/

		if ($isEdit) {
			$inlineAttr['class'] = 'sg-inline-edit ';
			$inlineAttr['data-tpid'] = $this->projectId;
			$inlineAttr['data-update-url'] = url('project/edit/tr');
			if (post('debug')) $inlineAttr['data-debug'] = 'yes';
		}
		$inlineAttr['class'] .= 'project-finalreport';

		$ret.='<div id="project-finalreport" '.sg_implode_attr($inlineAttr).'>'._NL;

		$section = 'title';
		$irs = $rs->items[$section] ? end($rs->items[$section]) : (Object) [];

		$ret.='<div class="project-cover-page -forprint">';
		$ret.='รายงานฉบับสมบูรณ์<br /><h3>'.$projectInfo->info->orgName.'</h3><br /><h3>“ '.$projectInfo->title.' ”</h3>';
		$ret.=$projectInfo->info->area.'<br />';
		//$ret.='ตำบล'.$projectInfo->info->subdistname.' อำเภอ'.$projectInfo->info->distname.' จังหวัด'.$projectInfo->info->provname.'<br />';
		$ret.='<br />หัวหน้าโครงการ<br />'.$projectInfo->info->prowner.''._NL;
		$ret.='<div class="cover -footer">ได้รับการสนับสนุนโดย '.$projectInfo->info->orgName;
		$ret.='<br />'.($projectInfo->info->date_end?sg_date($projectInfo->info->date_end,'ดดด ปปปป'):'').'<br />';
		/*
		$ret.='<img src="//dekthaikamsai.com/upload/logo-thaihealth.png" height="64" /> <img src="//dekthaikamsai.com/upload/logo-kamsai.png" height="64" /> <img src="//dekthaikamsai.com/upload/logo-aof.jpg" height="64" />'
		*/
		$ret .= '</div>';
		$ret.='</div>';




		$ret.='<hr class="pagebreak" />';




		// section :: ชื่อโครงการ
		$ret.='<div class="-forprint">';
		$ret.='<p><strong>ชื่อโครงการ '.$projectInfo->info->title.'</strong></p>';
		$ret.='<p></p>';
		$ret.='<p>ที่อยู่ '.$projectInfo->info->area.' จังหวัด '.$projectInfo->info->provname.'</p>';
		$ret.='<p></p>';
		$ret.='<p>รหัสโครงการ '.$projectInfo->info->prid.' เลขที่ข้อตกลง '.$projectInfo->info->agrno.'</p>';
		$ret.='<p></p>';
		$ret.='<p>ระยะเวลาดำเนินงาน ตั้งแต่ '.($projectInfo->info->date_from?sg_date($projectInfo->info->date_from,'ว ดดด ปปปป'):'').' ถึง '.($projectInfo->info->date_end?sg_date($projectInfo->info->date_end,'ว ดดด ปปปป'):'').'</p>';
		$ret.='</div>';




		$ret.='<hr class="pagebreak" />';








		$ret.='<a name="section2"></a>';

		$ret.='<div class="-forprint">';
		$ret.='<h3>กิตติกรรมประกาศ</h3>';
		$ret.='<p><strong>"'.$projectInfo->title.' จังหวัด'.$projectInfo->info->provname.'"</strong> สำเร็จได้ด้วยดี ด้วยความร่วมมือจาก สมาชิกในชุมชน '.$projectInfo->info->area.'</p>';
		$ret.='<p>คณะทำงานโครงการฯ ขอขอบคุณ <b>'.$projectInfo->info->orgName.'</b> ที่ให้การสนับสนุนงบประมาณในการดำเนินโครงการฯ รวมทั้ง ภาคีเครือข่ายที่สำคัญระดับพื้นที่ ที่ให้การสนับสนุน ช่วยเหลือ ชี้แนะ สุดท้ายขอขอบคุณผู้เกี่ยวข้องที่มิได้ระบุชื่อไว้ในที่นี้ ซึ่งมีส่วนสำคัญในการขับเคลื่อนการดำเนินงานให้มีความยั่งยืนในพื้นที่ต่อไป</p>';
		$ret.='<p style="margin:3em;display:inline-block;white-space:nowrap;float:right;text-align:center;">
	คณะทำงานโครงการ<br />'.$projectInfo->title.'</p>';
		$ret.='<br clear="all" />';
		$ret.='</div>';





		$ret.='<hr class="pagebreak" />';






		$ret.='<a name="section3"></a>';

		$ret.='<div class="-forprint">';
		$ret.='<h3>บทคัดย่อ</h3>';


		$abstract = $finalReportTitle->items['title'] ? end($finalReportTitle->items['title'])->text2 : '';


		if (empty($abstract)) {
			$ret.='<p><strong>โครงการ " '.$projectInfo->title.' "</strong> ดำเนินการในพื้นที่ <strong>'.$projectInfo->info->area.'</strong> รหัสโครงการ <strong>'.$projectInfo->info->prid.'</strong> ระยะเวลาการดำเนินงาน <strong>'.($projectInfo->info->date_from?sg_date($projectInfo->info->date_from,'ว ดดด ปปปป'):'ยังไม่ระบุ').' - '.($projectInfo->info->date_end?sg_date($projectInfo->info->date_end,'ว ดดด ปปปป'):'(ยังไม่ระบุ)').'</strong> ได้รับการสนับสนุนงบประมาณจำนวน <strong>'.number_format($projectInfo->info->budget,2).'</strong> บาท จาก '.$projectInfo->info->orgName.' เพื่อใช้ในการดำเนินกิจกรรมโครงการ โดยมีกลุ่มเป้าหมายเป็นสมาชิกในชุมชนจำนวน <strong>'.$projectInfo->info->totaltarget.'</strong> คน หลังจากสิ้นสุดระยะเวลาโครงการ ผลที่เกิดขึ้นจากการดำเนินงานปรากฏดังนี้</p>';
			$ret .= '<p class="notify -no-print">โครงการนี้ยังไม่มีการเขียนหรือแก้ไขบทคัดย่อ</p>';
		} else {
			$ret .= sg_text2html($abstract);
		}

		$ret.='<p class="notes -no-print" style="margin: 20px 0; padding: 10px; background: #ccc;">หมายเหตุ : รายละเอียดของบทสรุปคัดย่อการดำเนินงาน ให้ผู้รับผิดชอบโครงการเป็นผู้เขียนสรุปภาพรวมของโครงการใน <a href="'.url('project/'.$this->projectId.'/info.result').'">"ผลลัพธ์โครงการ"</a></p>';
		$ret.='</div>';




		$property=property('project::'.$this->projectId);




		$ret.='<hr class="pagebreak" />';





		$ret.='<a name="section4"></a>';

		$ret.='<div class="-forprint">';
		$ret.='<h3>สารบัญ</h3>
	<table border="0">
	<tr><td>กิตติกรรมประกาศ</td><td><a href="#section2" class="noprint">&raquo;</a></td></tr>
	<tr><td>บทคัดย่อ</td><td><a href="#section3" class="noprint">&raquo;</a></td></tr>
	<tr><td>&nbsp;&nbsp;&nbsp;ความเป็นมา/หลักการเหตุผล</td><td><a href="#section5" class="noprint">&raquo;</a></td></tr>
	<tr><td>&nbsp;&nbsp;&nbsp;วัตถุประสงค์โครงการ</td><td><a href="#section6" class="noprint">&raquo;</a></td></tr>
	<tr><td>&nbsp;&nbsp;&nbsp;กิจกรรม/การดำเนินงาน</td><td><a href="#section7" class="noprint">&raquo;</a></td></tr>
	<tr><td>&nbsp;&nbsp;&nbsp;กลุ่มเป้าหมาย</td><td><a href="#section8" class="noprint">&raquo;</a></td></tr>
	<tr><td>&nbsp;&nbsp;&nbsp;ผลลัพธ์ที่ได้</td><td><a href="#section9" class="noprint">&raquo;</a></td></tr>
	<tr><td>&nbsp;&nbsp;&nbsp;การประเมินผล</td><td><a href="#section10" class="noprint">&raquo;</a></td></tr>
	<tr><td>&nbsp;&nbsp;&nbsp;ปัญหาและอุปสรรค</td><td><a href="#section11" class="noprint">&raquo;</a></td></tr>
	<tr><td>&nbsp;&nbsp;&nbsp;ข้อเสนอแนะ</td><td><a href="#section12" class="noprint">&raquo;</a></td></tr>
	<tr><td>&nbsp;&nbsp;&nbsp;เอกสารประกอบอื่นๆ</td><td><a href="#section13" class="noprint">&raquo;</a></td></tr>
	</table>';
		$ret.='</div>';




		$ret.='<hr class="pagebreak" />';





		$ret.='<a name="section5"></a>';


		$ret.='<div class="-forprint">';
		$ret.='<h3>ความเป็นมา/หลักการเหตุผล</h3>	';
		$ret.=sg_text2html($basicInfo->text1);

		$ret.='<h3>สถานการณ์</h3>	';
		//$ret.=sg_text2html($basicInfo->text6);


		$ret.='<a name="section6"></a>';

		$ret.='<h3>วัตถุประสงค์โครงการ</h3>	';
		if ($info->items['objective']) {
			$ret.='<ol>';
			foreach ($info->items['objective'] as $irs) {
				$ret.='<li>'.$irs->text1.'</li>';
			}
			$ret.='</ol>';
		} else $ret.=sg_text2html($projectInfo->info->objective);


		$ret.='<a name="section7"></a>';
		$ret.='<h3>กิจกรรม/การดำเนินงาน</h3>';
		$ret.='<ol>';
		foreach ($projectInfo->activity as $rs) {
			$ret.='<li>';
			$ret.=$rs->title;
			$ret.='</li>';
		}
		$ret.='</ol>';

		$ret.='<a name="section8"></a>';
		$ret.='<h3>กลุ่มเป้าหมาย</h3>';

		$targetTables = new Table();
		$targetTables->addClass('-target');
		$targetTables->thead=array('กลุ่มเป้าหมาย','amt -target'=>'จำนวนที่วางไว้');
		foreach ($projectInfo->target as $targetGroup) {
			$h=reset($targetGroup);

			$targetTables->rows[]=array('<b>'.$h->parentName.'</b>','','');
			foreach ($targetGroup as $key=>$targetItem) {
				$targetTables->rows[]=array(
						$targetItem->targetName,
						$targetItem->amount ? number_format($targetItem->amount) : '',
					);
			}
		}
		$ret.=$targetTables->build();

		$ret.='<h3>ผลที่คาดว่าจะได้รับ</h3>	';
		$ret.=sg_text2html($basicInfo->text5);

		$ret.='</div>';





		$ret.='<hr class="pagebreak" />';





		$ret.='<a name="section9"></a>';

		$ret.='<div class="-forprint">';
		$ret.='<h3 class="header -section">ส่วนที่ 1 ผลการดำเนินงาน</h3>'._NL;

		if ($order=='mainact') {
			unset($tables);

			$tables = new Table();
			$tables->class='item project-report-s3-item';
			$tables->colgroup=array('summary'=>'width="80%"','amt output1'=>'width="10%"','amt output2'=>'width="10%"');
			$tables->thead='<thead><tr><th rowspan="2">ผลลัพธ์และตัวชี้วัด<sup>**</sup>/กิจกรรมของโครงการ</th><th colspan="2">ผลผลิต<sup>*</sup></th></tr><tr><th>ตั้งไว้</th><th>เกิดขึ้นจริง</th></tr></thead>';

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
					unset($row);
					$summary='';

					$tables->rows[]=array('<td colspan="3"><h4 style="text-align:left;">'.$mno.'.'.(++$no).' '.$activity->title.'</h4></td>');
					$tables->rows[]='<header>';

					$summary.='<strong>วันที่ '.sg_date($activity->action_date,'ว ดดด ปปปป').' เวลา '.$activity->action_time.' น.</strong>';
					//$summary.='<h5>ผลลัพธ์ที่ตั้งไว้</h5>'.sg_text2html($activity->presetOutputOutcome)._NL;
					$summary.='<h5>กิจกรรมที่กำหนดไว้ในแผน</h5>'._NL.sg_text2html($activity->goal_do)._NL;
					$summary.='<h5>กิจกรรมที่ทำจริง</h5>'._NL.sg_text2html($activity->real_do)._NL;
					$summary.='<h5>ผลลัพธ์ที่เกิดขึ้น</h5>'.sg_text2html($activity->real_work)._NL;

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
							$summary.='<img height="120" class="photo photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="//dekthaikamsai.com/upload/pics/'.$photo->file.'" alt="photo '.$photo_alt.'" ';
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

				$summary .= '<strong>วันที่ '.sg_date($activity->action_date,'ว ดดด ปปปป').'</strong>'
									. ($activity->action_time ? '<strong> เวลา '.$activity->action_time.' น.</strong>' : '');
				//$summary.='<h4>วัตถุประสงค์ที่ตั้งไว้</h4>';
				//$summary.=sg_text2html($activity->objective);

				//$summary.='<h4>ผลลัพธ์ที่ตั้งไว้</h4>'.sg_text2html($activity->presetOutputOutcome)._NL;
				//$summary.='<h4>กิจกรรมที่กำหนดไว้ในแผน</h4>'._NL.sg_text2html($activity->goal_do)._NL;
				$summary.='<h4>กิจกรรมที่ทำ</h4>'._NL.sg_text2html($activity->real_do)._NL;
				$summary.='<h4>ผลผลิต/ผลลัพธ์ที่เกิดขึ้น</h4>'.sg_text2html($activity->real_work)._NL;

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

		$ret.='<p><em>* ผลผลิต หมายถึง ผลที่เกิดขึ้นเชิงปริมาณจากการทำกิจกรรม เช่น จำนวนผู้เข้าร่วมประชุม จำนวนผู้ผ่านการอบรม จำนวนครัวเรือนที่ปลูกผักสวนครัว เป็นต้น<br />** ผลลัพธ์ หมายถึง การเปลี่ยนแปลงที่นำไปสู่การแก้ปัญหา เช่น หลังอบรมมีผู้ปรับเปลี่ยนพฤติกรรมจำนวนกี่คน มีข้อบังคับหรือมาตรการของชุมชนที่นำไปสู่การปรับเปลี่ยนพฤติกรรมหรือสภาพแวดล้อม เป็นต้น ทั้งนี้ต้องมีข้อมูลอ้างอิงประกอบการรายงาน เช่น ข้อมูลรายชื่อแกนนำ , แบบสรุปการประเมินความรู้ , รูปภาพกิจกรรมพร้อมคำอธิบายใต้ภาพ เป็นต้น</em></p>'._NL;

		$ret.='</div>';





		$ret.='<hr class="pagebreak" />';




		$ret.='<section class="section-2 -forprint">';
		$ret.='<h3 class="header -section">ส่วนที่ 2 ประเมินความพึงพอใจต่อความสำเร็จและปัญหาอุปสรรคในการดำเนินโครงการในภาพรวม</h3>';

		$ret .= R::PageWidget('project.info.result', [$this->projectInfo, 'view'])->build();


		$ret.='<a name="section9"></a>';


		$section='title';
		$irs = $finalReportTitle->items[$section] ? end($finalReportTitle->items[$section]) : (Object) [];

		//$ret.='<h4>การเปลี่ยนแปลงที่เกิดขึ้นนอกเหนือวัตถุประสงค์</h4>';
		//$ret.=view::inlineedit(array('group'=>$formid.':title','fld'=>'text1','tr'=>$irs->trid,'ret'=>'html'),$irs->text1,$isEdit,'textarea');

		$ret.='<a name="section10"></a>';
		$ret.='<h4>ปัญหาอุปสรรคและข้อเสนอแนะ</h4>';
		$tables = new Table();
		$tables->thead=array('ปัญหาและอุปสรรค','สาเหตุ','ข้อเสนอแนะ');
		$tables->rows[]=array(
			view::inlineedit(array('group'=>$formid.':title','fld'=>'text3','tr'=>$irs->trid,'ret'=>'html'),$irs->text3,$isEdit,'textarea'),
			view::inlineedit(array('group'=>$formid.':title','fld'=>'text5','tr'=>$irs->trid,'ret'=>'html'),$irs->text5,$isEdit,'textarea'),
			view::inlineedit(array('group'=>$formid.':title','fld'=>'text4','tr'=>$irs->trid,'ret'=>'html'),$irs->text4,$isEdit,'textarea')
		);
		$ret .= $tables->build();
		$ret.='</section><!-- section-2 -->';


		$ret.='<hr class="pagebreak" />';


		$ret .= '<div class="section-3 -forprint">';
		$ret .= '<h3 class="header -section">ส่วนที่ 3 ประเมินคุณค่าโครงการ</h3>';
		$ret .= '<div class="sg-load" data-url="'.url('project/'.$this->projectId.'/eval.valuation/view').'"></div>';
		$ret .= R::PageWidget('project.eval.valuation', [$this->projectInfo, 'view'])->build();


		$ret .= '</div><!-- section 3 -->';


		$section='title';
		$irs = $finalReportTitle->items[$section] ? end($finalReportTitle->items[$section]) : (Object) [];
		$signdate=SG\getFirst($irs->detail1,$projectInfo->info->date_end);
		$ret.='<div class="container signpage -forprint">';
		$ret.='<p></p>';
		$ret.='<p>'.$projectInfo->title.' จังหวัด '.$projectInfo->info->provname.'</p>';
		$ret.='<p>รหัสโครงการ '.$projectInfo->info->prid.'</p>';
		$ret.='<p>ได้ดำเนินกิจกรรมตามที่เสนอไว้เสร็จสมบูรณ์เรียบร้อยแล้ว</p>';

		$ret.='<div class="row signall -flex">';
		$ret.='<div class="col -md-6">';
		$ret.='................................<br />( '.$projectInfo->info->prowner.' )<br />ผู้รับผิดชอบโครงการ<br />......./............/.......';
		$ret.='</div>'._NL;
		$ret.='</div>';
		$ret.='</div>';

		$ret.='<hr class="pagebreak" />';


		$ret.='<a name="section12"></a>';

		/*
		$ret.='<div class="-forprint">';
		$ret.='<h3>เอกสารประกอบอื่นๆ</h3><div><p class="noprint">ได้แก่ เอกสาร สิ่งพิมพ์ หรือสื่อที่ใช้ในกิจกรรมโครงการ รวมทั้งภาพถ่ายกิจกรรม ไม่เกิน 10 ภาพ พร้อมทั้งคำบรรยายใต้ภาพ</p></div>';
		$ret.='<p></p>';
		$ret.='</div>';
		*/


		if ($isViewOnly) {
			// Do nothing
		} else if ($isEdit) {
			$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -primary -circle48" href="'.url('project/'.$this->projectId.'/finalreport',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -save -white"></i></a></div>';
		} else if ($isEditable) {
			$ret.='<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/'.$this->projectId.'/finalreport/edit',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
		}

		$ret.='</div>';

		$ret.='<p class="noprint" align="right"><a class="btn" href="#project"><i class="icon -material">vertical_align_top</i><span>ไปบนสุด</span></a></p>';

		if ($download) {
			sendheader('application/octet-stream');
			mb_internal_encoding("UTF-8");
			header("Content-type: application/vnd.ms-word");
			header('Content-Disposition: attachment; filename="'.mb_substr($projectInfo->title,0,50).'-ส3-'.date('Y-m-d').'.doc"');
			// move style tag to head section
			$body=$ret;
			if (preg_match_all('/<style.*?>.*?<\/style>/si',$body,$out)) {
				foreach ($out[0] as $style) $styles.=$style._NL;
				$body=preg_replace('/(<style.*?>.*?<\/style>)/si','',$body);
			}
			$ret='<HTML>
			<HEAD>
			<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=utf-8">
			<TITLE>'.$projectInfo->title.'</TITLE>
			'.$styles.'
			</HEAD>
			<BODY>
			'.$body.'
			</BODY>
			</HTML>';
			die($ret);
		}

		$ret .= '<style type="text/css">
		.section-2 .title.-main {display: none;}
		.section-2 .abstract {display: none;}
		.section-3 .reportbar {display: none;}
		.section-3 .section-7 {display: none;}
		.project-result-abstract {display:none;}
		</style>';


		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => new Widget([
				'children' => [
					$ret,
				], // children
			]), // Widget
		]);
	}
}


// function project_finalreport($self, $this->projectId, $action = NULL, $actionId = NULL) {
// 	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid, '{initTemplate: true}');
// 	$tpid = $projectInfo->tpid;

// 	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo);

// 	if (!$projectInfo) return message('error', 'No Project');

// 	//$ret .= print_o($projectInfo);


// 	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
// 	$isOwner = $projectInfo->RIGHT & _IS_OWNER;
// 	$isEdit = !$download && $projectInfo->info->project_statuscode == 1 && ($isAdmin || $isOwner);
// 	$isAccessExpense = user_access('access full expense') || $isOwner;

// 	switch ($action) {
// 		case 'edit' :
// 			$ret .= R::View('project.finalreport', $projectInfo, 'edit');
// 			break;

// 		case 'addtr' :
// 			list($formid,$part)=explode(':',post('group'));
// 			//$ret.='FormId='.$formid.' Part='.$part;
// 			mydb::query('INSERT INTO %project_tr% (`tpid`, `formid`, `part`, `period`, `created`, `uid`) VALUES (:tpid, :formid, :part, :period, :created, :uid)',':tpid',$tpid, ':formid',$formid, ':part',$part, ':period',$period, ':created', date('U'), ':uid',i()->uid);
// 			//$ret.=mydb()->_query;
// 			break;

// 		case 'addleader' :
// 			$ret.=__project_form_s3_addleader($tpid);
// 			return $ret;
// 			break;

// 		case 'removeleader' :
// 			if ($isEdit && ($psnid = post('id')) && SG\confirm()) {
// 				$ret.='Remove';
// 				mydb::query('DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `parent`=:psnid AND `formid`="leader"', ':tpid',$tpid, ':psnid',$psnid);
// 				$ret.=mydb()->_query;
// 			}
// 			return $ret;
// 			break;

// 		case 'addinno' :
// 			$ret.=__project_form_s3_addinno($tpid);
// 			return $ret;
// 			break;

// 		default :
// 			if ($action)
// 				$ret .= R::View('project.finalreport.'.$action, $projectInfo, $actionId);
// 			else
// 				$ret .= R::View('project.finalreport', $projectInfo);
// 			break;
// 	}

// 	return $ret;
// }
?>