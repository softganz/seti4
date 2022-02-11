<?php
/**
* Project :: แบบประเมิน HIA
* Created 2020-01-07
* Modify  2022-02-05
*
* @param Object $projectInfo
* @param String $action
* @return Widget
*
* @usage project/{id}/eval.hia[/{action}]
*
* HIA-Indicator
* formid = eval-hia
* part = indicator
* text1 = ชื่อตัวชี้วัด
* text2 = สรุปผลการประเมิน (สรุปผลการประเมินสำคัญที่นำเข้าเวทีการทบทวน)
* text3 = หมายเหตุ (สรุปผลการประเมินสำคัญที่นำเข้าเวทีการทบทวน)
* text4 = สรุปผลการประเมิน (ผลการประเมิน)
* text5 = หมายเหตุ (ผลการประเมิน)
*/

import('widget:project.info.appbar.php');

class ProjectEvalHia extends Page {
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
		$formid = 'eval-hia';

		$tranValue = project_model::get_tr($this->projectId, $formid)->items;

		$url = q();

		$titleRs = isset($tranValue['title']) ? end($tranValue['title']) : NULL;

		$locked = $titleRs->flag;

		$isAdmin = i()->username == 'softganz';
		$isViewOnly = $this->action == 'view';
		$isEditable = $projectInfo->info->isRight;
		$isEdit = $projectInfo->info->isRight && $this->action == 'edit' && !$locked;
		$isDebug = false;

		if (post('lock') && $isAdmin && $titleRs->trid) {
			$locked = $titleRs->flag == _PROJECT_LOCKREPORT?NULL:_PROJECT_LOCKREPORT;
			$stmt = 'UPDATE %project_tr% SET `flag` = :flag WHERE `trid` = :trid LIMIT 1';
			mydb::query($stmt,':trid',$titleRs->trid,':flag',$locked);
			location($url);
		}


		$ui = new Ui();
		//$ui->add('<a class="btn -link" href="'.url($url,$isAdmin?array('lock'=>$locked?'no':'yes') : NULL).'" title="'.($isAdmin?'คลิกเพื่อเปลี่ยนสถานะรายงาน':'').'"><i class="icon -material">'.($locked?'lock':'lock_open').'</i></a>');

		$ret .= '<header class="header"><h3>แบบประเมินด้วยขั้นตอน HIA</h3>'.$ui->build().'</header>'._NL;

		$stepUi = new Ui(NULL, 'ui-step');
		$isStep1 = isset($tranValue[1]) ? end($tranValue[1])->flag : false;
		$isStep2 = isset($tranValue[2]) ? end($tranValue[2])->flag : false;
		$isStep3 = isset($tranValue[3]) ? end($tranValue[3])->flag : false;
		$isStep4 = isset($tranValue[4]) ? end($tranValue[4])->flag : false;
		$isStep5 = isset($tranValue[5]) ? end($tranValue[5])->flag : false;
		$isStep6 = isset($tranValue[6]) ? end($tranValue[6])->flag : false;

		$stepUi->add(
			array(
				array(
					'<a class="step -s1"><span class="step-num">1</span><span>กลั่นกรองโครงการ</span></a>',
					'{id: "step-1", class: "'.($isStep1 ? '-done' : '').'"}'
				),
				array(
					'<a class="step -s2"><span class="step-num">2</span><span>กำหนดขอบเขต</span></a>',
					'{id: "step-2", class: "'.($isStep2 ? '-done' : '').'"}'
				),
				array(
					'<a class="step -s3"><span class="step-num">3</span><span>ลงมือประเมิน</span></a>',
					'{id: "step-3", class: "'.($isStep3 ? '-done' : '').'"}'
				),
				array(
					'<a class="step -s4"><span class="step-num">4</span><span>ทบทวนรายงาน</span></a>',
					'{id: "step-4", class: "'.($isStep4 ? '-done' : '').'"}'
				),
				array(
					'<a class="step -s5"><span class="step-num">5</span><span>ปรับปรุงทบทวน</span></a>',
					'{id: "step-5", class: "'.($isStep5 ? '-done' : '').'"}'
				),
				array(
					'<a class="step -s6"><span class="step-num">6</span><span>ติดตาม</span></a>',
					'{id: "step-6", class: "'.($isStep6 ? '-done' : '').'"}'
				)
			)
		);
		$ret .= '<nav class="nav -step -no-print"><hr />'.$stepUi->build().'</nav>';


		if ($isEdit) {
			$inlineAttr['class'] = 'sg-inline-edit ';
			$inlineAttr['data-tpid'] = $this->projectId;
			$inlineAttr['data-update-url'] = url('project/edit/tr');
			if (post('debug')) $inlineAttr['data-debug'] = 'yes';
		}
		$inlineAttr['class'] .= 'project-result';

		$ret.='<div id="project-eval-hia" '.sg_implode_attr($inlineAttr).'>'._NL;


		$section = 'title';
		$irs = isset($tranValue[$section]) ? end($tranValue[$section]) : NULL;


		$outputList  = array(
			'1' => array(
				'title' => '1. การกลั่นกรองโครงการ',
				'field' => 'flag,text1,text2',
				'items' => array(
					array('title' => '1.1 วัตถุประสงค์'),
					array('section'=>'1.1.1','title'=>'1) เพื่อให้ทราบผลและบอกถึงงานที่รับผิดชอบ'),
					array('section'=>'1.1.2','title'=>'2) เพื่อให้เห็นแนวโน้ม เพื่อเตือนภัย'),
					array('section'=>'1.1.3','title'=>'3) เพื่อเปลี่ยนพฤติกรรม และเข้าใจกระบวนการ'),
					array('section'=>'1.1.4','title'=>'4) เพื่อลำดับความสำคัญ แปลง ปรับกลยุทธ์ สู่การปฎิบัติ'),
					array('section'=>'1.1.5','title'=>'5) เพื่อเป็นเครื่องมือในการจัดการ ช่วยการควบคุม'),
					array('section'=>'1.1.6','title'=>'6) เพื่อจัดสรรทรัพยากร'),
					array('section'=>'1.1.7','title'=>'7) เพื่อการเรียนรู้ และรู้ขีดความสามารถ'),
					array('section'=>'1.1.8','title'=>'8) เพื่อเปรียบเทียบ ปรับปรุงและพัฒนา'),
					array('section'=>'1.1.9','title'=>'9) เพื่อช่วยเปลี่ยนวัฒนธรรมองค์กร'),
					array('section'=>'1.1.10','title'=>'10) เพื่อให้รางวัล เพื่อจูงใจ'),
					array('section'=>'1.1.99','title'=>'11) อื่นๆ (ระบุรายละเอียด)', 'detail'=>1),
					array('title' => '1.2 วิธีการ (ระบุรายละเอียดทำอะไร กับใคร กี่คน ผลสรุป)'),
					array('section'=>'1.2.1','title'=>'1) ประชุมทีมประเมิน', 'detail'=>1),
					array('section'=>'1.2.2','title'=>'2) ประชุมร่วมกับโครงการ', 'detail'=>1),
					array('section'=>'1.2.3','title'=>'3) ประชุมร่วมกับโครงการและผู้มีส่วนเกี่ยวข้อง', 'detail'=>1),
					array('section'=>'1.2.99','title'=>'4) อื่นๆ (ระบุรายละเอียด)', 'detail'=>1),
					array('title' => '1.3 เครื่องมือ'),
					array('section'=>'1.3.1','title'=>'1) เครื่องมือที่ใช้ในขั้นตอนการกลั่นกรอง (ระบุรายละเอียดเครื่องมือ เช่น แนวคำถามในการประชุมกลุ่ม)', 'detail'=>1),
					array('title' => '1.4 ผลที่ได้'),
					array('section'=>'1.4.1','title'=>'1) ผลที่ได้ในขั้นตอนการกลั่นกรอง', 'detail'=>1),
				)
			),

			'2' => array(
				'title'=>'2. การกำหนดขอบเขต',
				'field'=>'flag,text1,text2',
				'items'=>array(
					array('section'=>'2.1','title'=>'1) วิธีการในการกำหนดขอบเขต', 'detail'=>1),
					array('section'=>'2.2','title'=>'2) ผู้เข้าร่วมกำหนดขอบเขต', 'detail'=>1),
					array('section'=>'2.3','title'=>'3) เครื่องมือที่ใช้', 'detail'=>1),
					array('title'=>'4) กรอบแนวคิด'),
					array('section'=>'2.4.1','title'=>'1) ใช้กรอบ Ottawa charter', 'detail'=>2),
					array('section'=>'2.4.2','title'=>'2) ใช้กรอบ ปัจจัยกำหนดสุขภาพ', 'detail'=>2),
					array('section'=>'2.4.3','title'=>'3) ใช้กรอบ Balance Score Card', 'detail'=>2),
					array('section'=>'2.4.4','title'=>'4) ใช้กรอบ CIPP', 'detail'=>2),
					array('section'=>'2.4.99','title'=>'5) อื่นๆ', 'detail'=>2),
				),
			),

			'3' => array(
				'title'=>'3. ลงมือประเมิน',
				'field'=>'flag,text1,text2',
				'items'=>array(
					array('section'=>'3.1','title'=>'1) กระบวนการเก็บข้อมูลโดยการมีส่วนร่วม (ระบุรายละเอียด)', 'detail'=>1),
					array('section'=>'3.2','title'=>'2) กระบวนการวิเคราะห์และสังเคราะห์ข้อมูล (ระบุรายละเอียด)', 'detail'=>1),
					array('section'=>'3.3','title'=>'3) ผู้มีส่วนร่วมในการประเมิน (ระบุรายละเอียด)', 'detail'=>1),
					array('section'=>'3.4','title'=>'4) ผลการประเมิน (เรียงตามตัวชี้วัดที่กำหนดในขั้นตอน scoping)', 'detail'=>3),
					array('section'=>'3.99','title'=>'5) อื่นๆ', 'detail'=>1),
				)
			),

			'4' => array(
				'title'=>'4. ทบทวนรายงานการประเมินว่าถูกต้องหรือไม่?',
				'field'=>'flag,text1,text2',
				'items'=>array(
					array('section'=>'4.1','title'=>'1) กระบวนการในการทบทวนรายงาน', 'detail'=>1),
					array('section'=>'4.2','title'=>'2) ผู้มีส่วนร่วมในการทบทวนรายงาน', 'detail'=>1),
					// สลับลำดับการแสดงของ 4.3 กับ 4.4
					array('section'=>'4.4','title'=>'3) ผลการทบทวนร่างรายงาน', 'detail'=>1),
					array('section'=>'4.3','title'=>'4) สรุปผลการประเมินสำคัญที่นำเข้าเวทีการทบทวน', 'detail'=>4),
					array('section'=>'4.99','title'=>'5) อื่นๆ', 'detail'=>1),
				)
			),

			'5' => array(
				'title'=>'5. การปรับปรุงทบทวนโครงการ',
				'field'=>'flag,text1,text2',
				'items'=>array(
					array('section'=>'5.1','title'=>'1) ข้อเสนอเพื่อการทบทวนโครงการ', 'detail'=>1),
					array('section'=>'5.99','title'=>'2) อื่นๆ', 'detail'=>1),
				)
			),

			'6' => array(
				'title'=>'6. ได้มีการปรับปรุงทบทวนโครงการตามข้อ 5 หรือไม่?',
				'field'=>'flag,text1,text2',
				'items'=>array(
					array('section'=>'6.1','title'=>'1) กลไกในการติดตามการปรับปรุงโครงการตามข้อเสนอแนะ', 'detail'=>1),
					array('section'=>'6.2','title'=>'2) วิธีการติดตาม การปรับปรุงโครงการตามข้อเสนอแนะ', 'detail'=>1),
					array('section'=>'6.99','title'=>'3) อื่นๆ', 'detail'=>1),
				)
			),
		);




		// Project Abstract
		$finalReportTitle = end(project_model::get_tr($this->projectId,'finalreport:title')->items['title']);

		$ret .= '<section class="project-result-abstract -sg-box">';

			// ดึงค่า default จากรายละเอียดโครงการ
			$preAbstract = 'โครงการนี้มีวัตถุประสงค์เพื่อ';
			if ($projectInfo->objective) {
				$oi = 0;
				foreach ($projectInfo->objective as $rs) {
					$preAbstract .= ' ('.(++$oi).') '.$rs->title;
				}
			} else {
				$ret .= $projectInfo->info->objective;
			}
			$preAbstract .= _NL._NL;

			$preAbstract .= 'ผลการดำเนินงานที่สำคัญ ได้แก่';

			$oi = 0;
			foreach ($projectInfo->activity as $rs) {
				$preAbstract .= ' ('.(++$oi).') '.$rs->title;
			}

			$preAbstract .= _NL._NL;
			$preAbstract .= 'ข้อเสนอแนะ ได้แก่ (1) ...';

			$ret .= '<h3 class="noprint">บทคัดย่อ/บทนำ</h3>';
			$ret .= '<h5>บทคัดย่อ</h5>';
			$ret .= View::inlineedit(
				array(
					'group' => 'finalreport:title',
					'fld' => 'text2',
					'tr' => $finalReportTitle->trid,
					'ret' => 'html',
					'button' => 'yes',
					'value' => trim(SG\getFirst($finalReportTitle->text2,$preAbstract))
				),
				SG\getFirst($finalReportTitle->text2,$preAbstract),
				$isEdit,
				'textarea'
			);

			$ret .= '<h5>คำสำคัญ</h5>';
			$ret .= View::inlineedit(
				array(
					'group' => 'finalreport:title',
					'fld' => 'detail2',
					'tr' => $finalReportTitle->trid,
					'rows' => 32,
					'ret' => 'html',
					'button' => 'yes',
					'value' => trim($finalReportTitle->detail2)
				),
				$finalReportTitle->detail2,
				$isEdit,
				'textarea'
			);

			$ret .= '<h5>บทนำ</h5>';
			$ret .= View::inlineedit(
				array(
					'group' => 'finalreport:title',
					'fld' => 'text8',
					'tr' => $finalReportTitle->trid,
					'rows' => 32,
					'ret' => 'html',
					'button' => 'yes',
					'value' => trim($finalReportTitle->text8)
				),
				$finalReportTitle->text8,
				$isEdit,
				'textarea'
			);

			//$ret .= print_o($finalReportTitle,'$finalReportTitle');

			$ret .= '<div class="-no-print"><em>หมายเหตุ<ul><li><strong>บทคัดย่อ/บทนำ</strong> จะนำไปใส่ในส่วนบทคัดย่อของรายงานฉบับสมบูรณ์</li><li>หากต้องการใช้ค่าเริ่มต้นของบทคัดย่อ ให้ลบข้อความในช่องบทคัดย่อ ทั้งหมด แล้วกดปุ่ม Refresh</li></ul></em></div>';
		$ret .= '</section><!-- project-result-abstract -->';



		$ret .= '<section id="project-info-link" class="project-info-link -sg-box" data-url="'.url('project/'.$this->projectId.'/info.link').'">';
		$ret .= '<h3>รายชื่อโครงการที่ประเมิน</h3>';
		$tables = new Table();

		foreach ($projectInfo->link as $rs) {
			$tables->rows[] = array(
				'title -hover-parent' => '<a href="'.$rs->url.'" target="_blank">'.SG\getFirst($rs->title,$rs->url).'</a>'
					. ($isEdit ? '<nav class="nav -icons -hover"><a class="sg-action" href="'.url('project/'.$this->projectId.'/info/link.remove/'.$rs->linkId).'" data-rel="notify" data-done="remove:parent tr" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a></nav>' : ''),
			);
		}

		$ret .= $tables->build();
		if ($isEdit) {
			$ret .= '<nav class="nav -icons -sg-text-right -no-print"><a class="sg-action btn -link" href="'.url('project/'.$this->projectId.'/info.link.form').'" data-rel="box" data-width="480"><i class="icon -material">add_circle</i><span>เพิ่มโครงการ</span></a></nav>';
		}
		//$ret .= print_o($projectInfo->link, '$link');
		$ret .= '</section>';



		$ret .= '<section class="project-result-hia -sg-box">';
		$ret .= '<h3 class="-no-print">แบบประเมิน HIA</h3>';

		foreach ($outputList as $mainKey => $mainOutput) {
			$evalRs = isset($tranValue[$mainKey]) ? end($tranValue[$mainKey]) : NULL;
			$ret .= '<div class="item'.($evalRs->flag ? ' -active' : '').'" data-eval="'.$mainKey.'">'._NL;

			$ret .= '<h3>'.$mainOutput['title'].'</h3>'._NL;

			// Show Check Icon
			$ret .= view::inlineedit(
				array(
					'group'=>$formid.':'.$mainKey,
					'fld'=>'flag',
					'name'=>'rate'.$mainKey,
					'tr'=>$evalRs->trid,
					'value'=>$evalRs->flag,
					'options' => '{class: "-evalcheck", callback: "evalRadioCallback"}',
				),
				'1:<i class="icon -material -sg-32 -no-print">'.($evalRs->flag ? 'check_circle' : ($isEdit ? 'check_circle' : '')).'</i>',
				$isEdit,
				'checkbox'
			);



			$ret .= '<div class="detail">'._NL;
			$ret .= view::inlineedit(
				array(
					'group' => $formid.':'.$mainKey,
					'fld' => 'text1',
					'tr' => $evalRs->trid,
					'ret' => 'html',
					'value' => trim($evalRs->text1),
					'options' => '{placeholder: "ระบุรายละเอียด"}',
				),
				$evalRs->text1,
				$isEdit,
				'textarea'
			);

			$sectionNo = 0;

			foreach ($mainOutput['items'] as $k => $outputItem) {
				if (empty($outputItem['section'])) {
					$ret .= '<div class="item -sub"><h4>'.$outputItem['title'].'</h4></div>';
					continue;
				}

				$section = $outputItem['section'];
				$irs = isset($tranValue[$section]) ? end($tranValue[$section]) : NULL;

				$ret .= '<div class="item -sub'.($irs->flag ? ' -active' : '').'" data-eval="'.$section.'"">'._NL;
				$ret .= '<h5>'.$outputItem['title'].($isAdmin && $isDebug ? ' ('.$mainKey.':'.$section.')' : '').'</h5>';

				if ($mainKey == 2 && $outputItem['detail'] === 2) {
					$ret .= '<span class="inline-edit-item -checkbox -no-print">';
					if ($irs->flag) {
						$ret .= '<i class="icon -material -sg-32 -no-print">check_circle</i>';
					} else if ($isEdit) {
						$ret .= '<i class="icon -material -sg-32 -no-print" onclick=\'$(this).closest(".item").find(".detail").show()\'>check_circle</i>';
					}
					$ret .= '</span>';
				} else {
					$ret .= view::inlineedit(
						array(
							'group'=>$formid.':'.$section,
							'fld'=>'flag',
							'name'=>'rate'.$section,
							'tr'=>$irs->trid,
							'value'=>$irs->flag,
							'removeempty' => 'yes',
							'options' => '{class: "-evalcheck", callback: "evalRadioCallback"}',
						),
						'1:<i class="icon -material -sg-32 -no-print">'.($irs->flag ? 'check_circle' : ($isEdit ? 'check_circle' : '')).'</i>',
						$isEdit,
						'checkbox'
					);
				}




				if ($outputItem['detail']) {
					$ret .= '<div class="detail">'._NL;
					switch ($outputItem['detail']) {
						case 1: $ret .= $this->_detail1($formid, $section, $projectInfo, $irs, $isEdit); break;

						case 2: $ret .= $this->_detail2($formid, $section, $projectInfo, $outputList, $tranValue, $outputItem, $isEdit); break;

						case 3: $ret .= $this->_detail3($formid, $section, $projectInfo, $outputList, $tranValue, $outputItem, $isEdit); break;

						case 4: $ret .= $this->_detail4($formid, $section, $projectInfo, $outputList, $tranValue, $outputItem, $isEdit); break;
					}

					$ret .= '</div><!-- detail -->'._NL;
				}

				$ret .= '</div><!-- item -sub -->'._NL;

			}

			// Show Photo
			$stmt = 'SELECT * FROM %topic_files% WHERE `tagname` = "PROJECT,EVAL-HIA" AND `refid` = :refid';
			$photoDbs = mydb::select($stmt, ':refid', $evalRs->trid);

			$photoStr = '';
			foreach ($photoDbs->items as $item) {
				$photoStrItem = '';
				$ui = new Ui('span');

				if ($item->type == 'photo') {
					//$ret.=print_o($item,'$item');
					$photoInfo=model::get_photo_property($item->file);

					if ($isEdit) {
						$ui->add('<a class="sg-action" href="'.url('project/'.$this->projectId.'/info/photo.delete/'.$item->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -cancel"></i></a>');
					}

					$photo_alt = $item->title;
					$photoStrItem .= '<li class="ui-item -hover-parent">';

					$photoStrItem .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';

					$photoStrItem .= '<a class="sg-action" data-group="photo'.$actionId.'" href="'.$photoInfo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
					$photoStrItem .= '<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" width="200" />';
					//$photoStrItem .= '<img class="photo -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" ';
					//$photoStrItem .= ' />';
					$photoStrItem .= '</a>';

					$photoStrItem .= '</li>'._NL;

					$photoStr .= $photoStrItem;

				} else if ($item->type == 'doc') {
					$photoStrItem .= '<li class="ui-item -hover-parent">';
					$photoStrItem .= '<a href="'.cfg('paper.upload.document.url').$item->file.'" title="'.htmlspecialchars($photo_alt).'" target="_blank">';
					$photoStrItem .= '<img class="photoitem -doc -pdf" src="//img.softganz.com/icon/icon-file.png" width="63" height="63" />';
					$photoStrItem .= '<span class="title">'.$item->title.'</span>';
					$photoStrItem .= '</a>';

					if ($isEdit) {
						$ui->add('<a class="sg-action" href="'.url('project/'.$this->projectId.'/info/docs.delete/'.$item->fid).'" title="ลบไฟล์นี้" data-confirm="ยืนยันว่าจะลบไฟล์รายงานนี้จริง?"  data-rel="this" data-removeparent="li"><i class="icon -cancel -gray"></i></a>');
					}
					$photoStrItem .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';
					$photoStrItem .= '</li>';
					$photoStr .= $photoStrItem;
				}
			}

			$ret .= '<div class="-photolist -action">'._NL
				. '<ul id="project-eval-hia-photo" class="ui-album">'._NL
				. $photoStr
				. '</ul>'._NL
				. '</div>'._NL;

			if ($isEdit) {
				$navUi = new Ui();

				$navUi->add(
					'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('project/info/'.$this->projectId.'/photo.upload/'.$evalRs->trid).'" data-rel="#project-eval-hia-photo" data-append="li">'
					. '<input type="hidden" name="tagname" value="EVAL-HIA" />'
					. '<span class="btn fileinput-button"><i class="icon -material">add_a_photo</i><span class="-sg-is-desktop">อัพโหลดภาพ</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span>'
					. '<input class="-hidden" type="submit" value="upload" />'
					. '</form>'
				);
				$navUi->add(
					'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('project/info/'.$this->projectId.'/photo.upload/'.$evalRs->trid).'" data-rel="#project-eval-hia-photo" data-append="li">'
					. '<input type="hidden" name="tagname" value="EVAL-HIA" />'
					. '<span class="btn fileinput-button"><i class="icon -material">add_a_photo</i><span class="-sg-is-desktop">อัพโหลดไฟล์เอกสาร</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span>'
					. '<input class="-hidden" type="submit" value="upload" />'
					. '</form>'
				);

				if ($navUi->count()) $ret .= '<nav class="nav -card -no-print" style="padding: 32px 0;">'.$navUi->build().'</nav>';
			}

			$ret .= '</div><!-- detail -->'._NL;

			$ret .= '</div><!-- item -->'._NL;
		}

		$ret .= '</section>';







		$ret .= '<section class="project-result-summary -sg-box">';

			$ret .= '<h3>สรุป</h3>';
			$ret .= '<h5>อภิปรายผลและข้อเสนอแนะ</h5>';
			$ret .= View::inlineedit(
				array(
					'group' => 'finalreport:title',
					'fld' => 'text3',
					'tr' => $finalReportTitle->trid,
					'ret' => 'html',
					'button' => 'yes',
					'value' => trim($finalReportTitle->text3)
				),
				trim($finalReportTitle->text3),
				$isEdit,
				'textarea'
			);

			$ret .= '<h5>เอกสารอ้างอิง</h5>';
			$ret .= View::inlineedit(
				array(
					'group' => 'finalreport:title',
					'fld' => 'text10',
					'tr' => $finalReportTitle->trid,
					'ret' => 'html',
					'button' => 'yes',
					'value' => trim($finalReportTitle->text10)
				),
				trim($finalReportTitle->text10),
				$isEdit,
				'textarea'
			);

		$ret .= '</section><!-- project-result-summary -->';





		$ret .='<section id="project-docs" class="project-docs box -no-print">'._NL;
		$ret .= '<h3>เอกสารประกอบโครงการ</h3>';
		$ret .= R::PageWidget('project.info.docs', [$projectInfo])->build();
		$ret .= '</section><!-- project-docs -->'._NL._NL;


		$ret .= '<nav class="nav -icons -sg-text-center -no-print"><a class="btn" href="javascript:window.print()"><i class="icon -print"></i><span>พิมพ์</span></a></nav>';


		if ($isViewOnly) {
			// Do nothing
		} else if ($isEdit) {
			$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -primary -circle48" href="'.url('project/'.$this->projectId.'/eval.hia',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -save -white"></i></a></div>';
		} else if ($isEditable) {
			$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/'.$this->projectId.'/eval.hia/edit',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
		}

		$ret .= '</div><!-- project-eval-hia -->';


		$ret .= $this->_script();

		//$ret.=print_o($tranValue,'$tranValue');
		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => new Widget([
				'children' => [
					$ret,
					// $this->_script(),
					// new DebugMsg($valuationTr,'$valuationTr'),
				], // children
			]), // Widget
		]);
	}

	function _detail1($formid, $section, $projectInfo, $irs, $isEdit) {
		$ret .= view::inlineedit(
			array(
				'group' => $formid.':'.$section,
				'fld' => 'text1',
				'tr' => $irs->trid,
				'ret' => 'html',
				'value' => trim($irs->text1),
				'options' => '{placeholder: "ระบุรายละเอียด"}',
			),
			$irs->text1,
			$isEdit,
			'textarea'
		);
		return $ret;
	}

	function _detail2($formid, $section, $projectInfo, $outputList, $tranValue, $outputItem, $isEdit) {
		$subItemNo = 0;

		$tables = new Table();
		$tables->addClass('-hia-concept');
		$tables->thead = array(
			'title -hover-parent' => 'ประเด็นการประเมิน',
			'ตัวชี้วัด',
			'เครื่องมือ',
			'กลุ่มผู้ให้ข้อมูล'
		);

		foreach ($tranValue[$section] as $subItem) {
			unset($row);
			$row[] = view::inlineedit(
					array(
						'group' => $formid.':'.$section,
						'fld' => 'text1',
						'tr' => $subItem->trid,
						'value' => trim($subItem->text1),
						'options' => '{class: "-fill", placeholder: "ระบุประเด็นเนื้อหา"}',
					),
					$subItem->text1,
					$isEdit,
					'text'
				)
			. ($isEdit ? '<nav class="nav -icons -hover"><a class="sg-action btn -link -no-print" href="'.url('project/'.$this->projectId.'/info/hia.indicator.delete/'.$subItem->trid).'" data-rel="notify" data-done="remove: parent tr | load" data-title="ลบประเด็นการประเมิน" data-confirm="ต้องการลบประเด็นการประเมินนี้ (พร้อมตัวชี้วัด,เครื่องมือ,กลุ่มผู้ให้ข้อมูล) กรุณายืนยัน?"><i class="icon -material -gray">delete</i></a></nav>' : '');

			$itemUi = new Ui('ol', 'ui-ol');
			foreach ($tranValue['indicator'] as $indicatorItem) {
				if ($indicatorItem->parent != $subItem->trid) continue;
				$itemUi->add(
					view::inlineedit(
						array(
							'group' => $formid.':'.$section,
							'fld' => 'text1',
							'tr' => $indicatorItem->trid,
							'value' => trim($indicatorItem->text1),
							'options' => '{class: "-fill", placeholder: "ระบุตัวชี้วัด"}',
						),
						$indicatorItem->text1,
						$isEdit,
						'text'
					)
					. ($isEdit ? '<nav class="nav -icons -hover -top-right"><a class="sg-action btn -link -no-print" href="'.url('project/'.$this->projectId.'/info/tran.remove/'.$indicatorItem->trid).'" data-rel="notify" data-done="remove:parent .ui-item | load" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a></nav>' : ''),
					'{class: "-hover-parent"}'
				);
			}
			if ($isEdit) {
				$itemUi->add(
					'<header class="header">'._HEADER_BACK.'<h3>เพิ่มตัวชี้วัด</h3></header>'
					. '<form class="sg-form" action="'.url('project/'.$this->projectId.'/info/tran.add/'.$formid.',indicator',array('name'=>'project.info', 'fld'=>'hia.indicator', 'ref'=>$subItem->trid)).'" data-rel="notify" data-done="close | load:#main:'.url('project/'.$this->projectId.'/eval.hia/edit').'">'
					. '<input type="hidden" name="tran[parent]" value="'.$subItem->trid.'" />'
					. '<div class="form-item"><label>ตัวชี้วัด</label><input type="text" class="form-text -fill" name="tran[text1]" placeholder="ระบุตัวชี้วัด" /></div>'
					. '<div class="form-item -sg-text-right"><button class="btn -primary" type="submit"><i class="icon -material">done</i><span>เพิ่มตัวชี้วัด</span></button>'
					. '</form>',
					'{class: "-hidden", id: "hia-indicator-'.$subItem->trid.'"}'
				);
			}

			$row['indicator'] = $itemUi->build()
				. ($isEdit ? '<nav class="nav -framework-add"><a class="sg-action btn -link -no-print" href="#hia-indicator-'.$subItem->trid.'" data-rel="box" data-width="480"><i class="icon -material">add_circle_outline</i></a></nav>' : '');

			$itemUi = new Ui('ol', 'ui-ol');

			foreach ($tranValue['tool'] as $toolItem) {
				if ($toolItem->parent != $subItem->trid) continue;
				$itemUi->add(
					view::inlineedit(
						array(
							'group' => $formid.':'.$section,
							'fld' => 'text1',
							'tr' => $toolItem->trid,
							'value' => trim($toolItem->text1),
							'options' => '{class: "-fill", placeholder: "ระบุเครื่องมือ"}',
						),
						$toolItem->text1,
						$isEdit,
						'text'
					)
					. ($isEdit ? '<nav class="nav -icons -hover -top-right"><a class="sg-action btn -link -no-print" href="'.url('project/'.$this->projectId.'/info/tran.remove/'.$toolItem->trid).'" data-rel="notify" data-done="remove:parent .ui-item" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a></nav>' : ''),
					'{class: "-hover-parent"}'
				);
			}

			if ($isEdit) {
				$itemUi->add(
					'<header class="header">'._HEADER_BACK.'<h3>เพิ่มเครื่องมือ</h3></header>'
					. '<form class="sg-form" action="'.url('project/'.$this->projectId.'/info/tran.add/'.$formid.',tool').'" data-rel="notify" data-done="close | load:#main:'.url('project/'.$this->projectId.'/eval.hia/edit').'">'
					. '<input type="hidden" name="tran[parent]" value="'.$subItem->trid.'" />'
					. '<div class="form-item"><label>เครื่องมือ</label><input type="text" class="form-text -fill" name="tran[text1]" placeholder="ระบุเครื่องมือ" /></div>'
					. '<div class="form-item -sg-text-right"><button class="btn -primary" type="submit"><i class="icon -material">done</i><span>เพิ่มเครื่องมือ</span></button>'
					. '</form>',
					'{class: "-hidden", id: "hia-tool-'.$subItem->trid.'"}'
				);
			}

			$row['tool'] = $itemUi->build()
				. ($isEdit ? '<nav class="nav -framework-add"><a class="sg-action btn -link -no-print" href="#hia-tool-'.$subItem->trid.'" data-rel="box" data-width="480"><i class="icon -material">add_circle_outline</i></a></nav>' : '');


			$itemUi = new Ui('ol', 'ui-ol');

			foreach ($tranValue['stakeholder'] as $stakeholderItem) {
				if ($stakeholderItem->parent != $subItem->trid) continue;
				$itemUi->add(
					view::inlineedit(
						array(
							'group' => $formid.':'.$section,
							'fld' => 'text1',
							'tr' => $stakeholderItem->trid,
							'value' => trim($stakeholderItem->text1),
							'options' => '{class: "-fill", placeholder: "ระบุกลุ่มผู้ให้ข้อมูล"}',
						),
						$stakeholderItem->text1,
						$isEdit,
						'text'
					)
					. ($isEdit ? '<nav class="nav -icons -hover -top-right"><a class="sg-action btn -link -no-print" href="'.url('project/'.$this->projectId.'/info/tran.remove/'.$stakeholderItem->trid).'" data-rel="notify" data-done="remove:parent .ui-item" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a></nav>' : ''),
					'{class: "-hover-parent"}'
				);
			}

			if ($isEdit) {
				$itemUi->add(
					'<header class="header">'._HEADER_BACK.'<h3>เพิ่มกลุ่มผู้ให้ข้อมูล</h3></header>'
					. '<form class="sg-form" action="'.url('project/'.$this->projectId.'/info/tran.add/'.$formid.',stakeholder').'" data-rel="notify" data-done="close | load:#main:'.url('project/'.$this->projectId.'/eval.hia/edit').'">'
					. '<input type="hidden" name="tran[parent]" value="'.$subItem->trid.'" />'
					. '<div class="form-item"><label>กลุ่มผู้ให้ข้อมูล</label><input type="text" class="form-text -fill" name="tran[text1]" placeholder="ระบุกลุ่มผู้ให้ข้อมูล" /></div>'
					. '<div class="form-item -sg-text-right"><button class="btn -primary" type="submit"><i class="icon -material">done</i><span>เพิ่มกลุ่มผู้ให้ข้อมูล</span></button>'
					. '</form>',
					'{class: "-hidden", id: "hia-stakeholder-'.$subItem->trid.'"}'
				);
			}

			$row['stakeholder'] = $itemUi->build()
				. ($isEdit ? '<nav class="nav -framework-add"><a class="sg-action btn -link -no-print" href="#hia-stakeholder-'.$subItem->trid.'" data-rel="box" data-width="480"><i class="icon -material">add_circle_outline</i></a></nav>' : '');

			$tables->rows[] = $row;
		}

		if ($isEdit) {
			$tables->rows[] = array(
				'<td class="-issue-add" colspan="4">'
				. '<div id="hia-issue-form" class="-no-print">'
				. '<header class="header"><h5>เพิ่มประเด็นการประเมิน ('.$outputItem['title'].')</h5></header>'
				. '<form class="sg-form" action="'.url('project/'.$this->projectId.'/info/tran.add/'.$formid.','.$section).'" data-rel="notify" data-checkvalid="1" data-done="close | load:#main:'.url('project/'.$this->projectId.'/eval.hia/edit').'">'
				. '<input type="hidden" name="tran[flag]" value="1" />'
				. '<div class="form-item -group"><span class="form-group">'
				. '<input type="text" class="form-text -fill -require" name="tran[text1]" placeholder="ระบุประเด็นการประเมิน" />'
				. '<div class="input-append"><span><button><i class="icon -material">add_circle_outline</i></button></span></div>'
				. '</div>'
				. '</form>'
				. '</div>'
				. '</td>'
			);
		}
		$ret .= $tables->build();

		//$ret .= print_o($tranValue[$section],'$tranValue');
		//$ret .= print_o($tranValue['indicator'],'$tranValue[indicator]');

		return $ret;








		/*
		$cardUi = new Ui(NULL, 'ui-card');
		$cardUi->header('<h5>ประเด็นเนื้อหา '.($isEdit ? '<a class="sg-action btn -link -hidden -no-print" href="#hia-issue-form" data-rel="box" data-width="480"><i class="icon -material">add_circle_outline</i></a>' : '').'</h5>');

		foreach ($tranValue[$section] as $subItem) {
			$cardStr = '<header class="header"><h5>'
				. '<span>'.(++$subItemNo).'. </span>'
				. view::inlineedit(
						array(
							'group' => $formid.':'.$section,
							'fld' => 'text1',
							'tr' => $subItem->trid,
							'value' => trim($subItem->text1),
							'options' => '{class: "", placeholder: "ระบุประเด็นเนื้อหา"}',
						),
						$subItem->text1,
						$isEdit,
						'text'
					)
				. '</h5></header>';


			$itemUi = new Ui('ol', 'ui-ol');

			$cardStr .= '<p><b>ตัวชี้วัด</b> '.($isEdit ? '<a class="sg-action btn -link -no-print" href="#hia-indicator-'.$subItem->trid.'" data-rel="box" data-width="480"><i class="icon -material">add_circle_outline</i></a>' : '');

			foreach ($tranValue['indicator'] as $indicatorItem) {
				if ($indicatorItem->parent != $subItem->trid) continue;
				$itemUi->add(
					view::inlineedit(
						array(
							'group' => $formid.':'.$section,
							'fld' => 'text1',
							'tr' => $indicatorItem->trid,
							'value' => trim($indicatorItem->text1),
							'options' => '{placeholder: "ระบุตัวชี้วัด"}',
						),
						$indicatorItem->text1,
						$isEdit,
						'text'
					)
					. ($isEdit ? ' <a class="sg-action btn -link -no-print" href="'.url('project/'.$this->projectId.'/info/tran.remove/'.$indicatorItem->trid).'" data-rel="notify" data-done="remove:parent .ui-item" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>' : '')
				);
			}
			if ($isEdit) {
				$itemUi->add(
					'<div id="hia-indicator-'.$subItem->trid.'">'
					. '<header class="header">'._HEADER_BACK.'<h3>เพิ่มตัวชี้วัด</h3></header>'
					. '<form class="sg-form" action="'.url('project/'.$this->projectId.'/info/tran.add/'.$formid.',indicator',array('name'=>'project.info', 'fld'=>'hia.indicator', 'ref'=>$subItem->trid)).'" data-rel="notify" data-done="close | load:#main:'.url('project/'.$this->projectId.'/eval.hia/edit').'">'
					. '<input type="hidden" name="tran[parent]" value="'.$subItem->trid.'" />'
					. '<div class="form-item"><label>ตัวชี้วัด</label><input type="text" class="form-text -fill" name="tran[text1]" placeholder="ระบุตัวชี้วัด" /></div>'
					. '<div class="form-item -sg-text-right"><button class="btn -primary" type="submit"><i class="icon -material">done</i><span>เพิ่มตัวชี้วัด</span></button>'
					. '</form>'
					. '</div>',
					'{class: "-hidden"}'
				);
			}
			$cardStr .= $itemUi->build();
			$cardStr .= '</p>';


			$itemUi = new Ui('ol', 'ui-ol');

			$cardStr .= '<p><b>เครื่องมือ</b> '.($isEdit ? '<a class="sg-action btn -link -no-print" href="#hia-tool-'.$subItem->trid.'" data-rel="box" data-width="480"><i class="icon -material">add_circle_outline</i></a>' : '');

			foreach ($tranValue['tool'] as $toolItem) {
				if ($toolItem->parent != $subItem->trid) continue;
				$itemUi->add(
					view::inlineedit(
						array(
							'group' => $formid.':'.$section,
							'fld' => 'text1',
							'tr' => $toolItem->trid,
							'value' => trim($toolItem->text1),
							'options' => '{placeholder: "ระบุเครื่องมือ"}',
						),
						$toolItem->text1,
						$isEdit,
						'text'
					)
					. ($isEdit ? ' <a class="sg-action btn -link -no-print" href="'.url('project/'.$this->projectId.'/info/tran.remove/'.$toolItem->trid).'" data-rel="notify" data-done="remove:parent .ui-item" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>' : '')
				);
			}

			if ($isEdit) {
				$itemUi->add(
					'<div id="hia-tool-'.$subItem->trid.'">'
					. '<header class="header">'._HEADER_BACK.'<h3>เพิ่มเครื่องมือ</h3></header>'
					. '<form class="sg-form" action="'.url('project/'.$this->projectId.'/info/tran.add/'.$formid.',tool').'" data-rel="notify" data-done="close | load:#main:'.url('project/'.$this->projectId.'/eval.hia/edit').'">'
					. '<input type="hidden" name="tran[parent]" value="'.$subItem->trid.'" />'
					. '<div class="form-item"><label>เครื่องมือ</label><input type="text" class="form-text -fill" name="tran[text1]" placeholder="ระบุเครื่องมือ" /></div>'
					. '<div class="form-item -sg-text-right"><button class="btn -primary" type="submit"><i class="icon -material">done</i><span>เพิ่มเครื่องมือ</span></button>'
					. '</form>'
					. '</div>',
					'{class: "-hidden"}'
				);
			}

			$cardStr .= $itemUi->build();
			$cardStr .= '</p>';


			$itemUi = new Ui('ol', 'ui-ol');

			$cardStr .= '<p><b>ผู้มีส่วนได้ส่วนเสีย</b> '.($isEdit ? '<a class="sg-action btn -link -no-print" href="#hia-stakeholder-'.$subItem->trid.'" data-rel="box" data-width="480"><i class="icon -material">add_circle_outline</i></a>' : '');

			foreach ($tranValue['stakeholder'] as $stakeholderItem) {
				if ($stakeholderItem->parent != $subItem->trid) continue;
				$itemUi->add(
					view::inlineedit(
						array(
							'group' => $formid.':'.$section,
							'fld' => 'text1',
							'tr' => $stakeholderItem->trid,
							'value' => trim($stakeholderItem->text1),
							'options' => '{placeholder: "ระบุเครื่องมือ"}',
						),
						$stakeholderItem->text1,
						$isEdit,
						'text'
					)
					. ($isEdit ? ' <a class="sg-action btn -link -no-print" href="'.url('project/'.$this->projectId.'/info/tran.remove/'.$stakeholderItem->trid).'" data-rel="notify" data-done="remove:parent .ui-item" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>' : '')
				);
			}

			if ($isEdit) {
				$itemUi->add(
					'<div id="hia-stakeholder-'.$subItem->trid.'">'
					. '<header class="header">'._HEADER_BACK.'<h3>เพิ่มผู้มีส่วนได้ส่วนเสีย</h3></header>'
					. '<form class="sg-form" action="'.url('project/'.$this->projectId.'/info/tran.add/'.$formid.',stakeholder').'" data-rel="notify" data-done="close | load:#main:'.url('project/'.$this->projectId.'/eval.hia/edit').'">'
					. '<input type="hidden" name="tran[parent]" value="'.$subItem->trid.'" />'
					. '<div class="form-item"><label>ผู้มีส่วนได้ส่วนเสีย</label><input type="text" class="form-text -fill" name="tran[text1]" placeholder="ระบุผู้มีส่วนได้ส่วนเสีย" /></div>'
					. '<div class="form-item -sg-text-right"><button class="btn -primary" type="submit"><i class="icon -material">done</i><span>เพิ่มผู้มีส่วนได้ส่วนเสีย</span></button>'
					. '</form>'
					. '</div>',
					'{class: "-hidden"}'
				);
			}

			$cardStr .= $itemUi->build();
			$cardStr .= '</p>';

			//$cardStr .= print_o($subItem,'$subItem');
			$cardUi->add($cardStr);
		}

		if ($isEdit) {
			$cardUi->add('<div id="hia-issue-form" class="-no-print"><header class="header"><h5>เพิ่มประเด็นเนื้อหา</h5></header><form class="sg-form" action="'.url('project/'.$this->projectId.'/info/tran.add/'.$formid.','.$section).'" data-rel="notify" data-done="close | load:#main:'.url('project/'.$this->projectId.'/eval.hia/edit').'"><input type="hidden" name="tran[flag]" value="1" /><div class="form-item -group"><span class="form-group"><input type="text" class="form-text -fill" name="tran[text1]" placeholder="ระบุประเด็นเนื้อหาเพิ่ม" /><div class="input-append"><span><button><i class="icon -material">add_circle_outline</i></button></span></div></div></form></div>');
		}
		$ret .= $cardUi->build();
		*/
	}

	function _detail3($formid, $section, $projectInfo, $outputList, $tranValue, $outputItem, $isEdit) {
		//$ret .= $section.print_o($outputItem,'$outputItem');
		//$ret .= print_o($tranValue[$section], '$tranValue');

		$sectionRs = isset($tranValue[$section]) ? end($tranValue[$section]) : NULL;
		if ($sectionRs->text1) {
			$ret .= view::inlineedit(
				array(
					'group' => $formid.':'.$section,
					'fld' => 'text1',
					'tr' => $sectionRs->trid,
					'ret' => 'html',
					'value' => trim($sectionRs->text1),
					'options' => '{placeholder: "ระบุรายละเอียด"}',
				),
				$sectionRs->text1,
				$isEdit,
				'textarea'
			);
		}

		$subItemNo = 0;
		$tables = new Table();
		$tables->addClass('-hia-summary');
		$tables->thead = array('ประเด็นการประเมิน', 'ตัวชี้วัด', 'ผลการประเมิน', 'หมายเหตุ');
		foreach ($outputList[2]['items'] as $outputItem) {
			if ($outputItem['detail'] != 2) continue;
			$outputSection = $outputItem['section'];

			foreach ($tranValue[$outputSection] as $subItem) {
				foreach ($tranValue['indicator'] as $indicatorItem) {
					if ($indicatorItem->parent != $subItem->trid) continue;

					unset($row);
					$row[] = '<b>'.(++$subItemNo).'. '.$subItem->text1.'</b> <em>('.$outputItem['title'].')</em>';
					$row[] = $indicatorItem->text1;
					$row[] = view::inlineedit(
							array(
								'group' => $formid.':'.$section,
								'fld' => 'text4',
								'tr' => $indicatorItem->trid,
								'ret' => 'html',
								'value' => trim($indicatorItem->text4),
								'options' => '{class: "-fill", placeholder: "ระบุผลของตัวชี้วัด"}',
							),
							$indicatorItem->text4,
							$isEdit,
							'textarea'
						);
					$row[] = view::inlineedit(
							array(
								'group' => $formid.':'.$section,
								'fld' => 'text5',
								'tr' => $indicatorItem->trid,
								'ret' => 'html',
								'value' => trim($indicatorItem->text5),
								'options' => '{class: "-fill", placeholder: "ระบุหมายเหตุ"}',
							),
							$indicatorItem->text5,
							$isEdit,
							'textarea'
						);

					$tables->rows[] = $row;
				}
			}
		}

		$ret .= $tables->build();

		return $ret;
	}

	function _detail4($formid, $section, $projectInfo, $outputList, $tranValue, $outputItem, $isEdit) {
		$subItemNo = 0;
		$tables = new Table();
		$tables->addClass('-hia-summary');
		$tables->thead = array('ประเด็นการประเมิน', 'ตัวชี้วัด', 'สรุปผลการประเมิน', 'หมายเหตุ');
		foreach ($outputList[2]['items'] as $outputItem) {
			if ($outputItem['detail'] != 2) continue;
			$outputSection = $outputItem['section'];

			foreach ($tranValue[$outputSection] as $subItem) {
				foreach ($tranValue['indicator'] as $indicatorItem) {
					if ($indicatorItem->parent != $subItem->trid) continue;

					unset($row);
					$row[] = '<b>'.(++$subItemNo).'. '.$subItem->text1.'</b> <em>('.$outputItem['title'].')</em>';
					$row[] = $indicatorItem->text1;
					$row[] = view::inlineedit(
							array(
								'group' => $formid.':'.$section,
								'fld' => 'text2',
								'tr' => $indicatorItem->trid,
								'ret' => 'html',
								'value' => trim($indicatorItem->text2),
								'options' => '{class: "-fill", placeholder: "ระบุผลของตัวชี้วัด"}',
							),
							$indicatorItem->text2,
							$isEdit,
							'textarea'
						);
					$row[] = view::inlineedit(
							array(
								'group' => $formid.':'.$section,
								'fld' => 'text3',
								'tr' => $indicatorItem->trid,
								'ret' => 'html',
								'value' => trim($indicatorItem->text3),
								'options' => '{class: "-fill", placeholder: "ระบุหมายเหตุ"}',
							),
							$indicatorItem->text3,
							$isEdit,
							'textarea'
						);

					$tables->rows[] = $row;
				}
			}
		}

		$ret .= $tables->build();

		return $ret;
	}

	function _script() {
		head('
		<style>
		.page.-main>header>h3 {text-align: center; font-size: 2.0em;}
		.project-result-hia .item {position: relative;}
		.project-result-hia>.item {margin-bottom: 32px; position: relative;}
		.project-result-hia>.item>h3 {margin-bottom: 8px;}
		.project-result-hia>.item>.detail {padding: 0 16px;}
		.inline-edit-item.-checkbox {position: absolute; top: 10px; right: 8px;}
		.inline-edit-item.-checkbox .icon {color: #ddd; margin: 0; cursor: pointer;}
		.inline-edit-item.-checkbox .-evalcheck {display: none;}
		.inline-edit-item.-checkbox .inline-edit-view.-checkbox.-evalcheck {display: block;}
		.inline-edit-item.-checkbox .inline-edit-view.-checkbox.-evalcheck>input {display: none !Important;}
		.inline-edit-item.-checkbox .inline-edit-view.-checkbox.-evalcheck .icon {cursor: default;}

		.item.-sub {padding-left: 16px;}
		.item>h3 {padding: 16px;}
		.item>h5 {padding: 18px 0; margin: 0;}
		.item>.detail {display: none;}
		.item.-active>.detail {display: block;}
		.item.-active>h3 {background-color: #c5ffc5;}
		.item.-active>.inline-edit-item.-checkbox .icon {color: green;}
		.item>.detail>.ui-card {background-color: #f5f5f5; padding: 8px; margin: 0 8px; width: auto;}
		.item>.detail>.ui-card>.ui-item {background-color: #fff;}
		.item.-hia-concept {border: 1px #ccc solid;}
		.item.-hia-concept td {width: 25%; position: relative;}
		.item.-hia-concept>tbody>tr:last-child>td {border-bottom: none;}
		.item.-hia-concept .nav.-framework-add {position: absolute; bottom: 4px; right: 4px;}

		.item.-hia-summary td {width: 25%;}

		.sg-inline-edit .item.-hia-concept td {padding-bottom: 36px; }
		.sg-inline-edit .item.-hia-concept td.-issue-add {padding: 4px;}
		</style>'
		);


		$ret .= '<script type="text/javascript">
		$(".inline-edit-field.-radio:checked").each(function() {
			var $icon = $(this).parent("label").find(".icon")
			$(this).closest(".item").toggleClass("-active")
			//console.log($icon)
		})

		function evalRadioCallback($this,data,$parent) {
			//$this.prop("checked", false)
			var $parentDom = $this.closest(".item")
			$parentDom.toggleClass("-active")
			$("#step-"+$parentDom.data("eval")).toggleClass("-done")
		}
		</script>';
		return $ret;
	}
}
?>