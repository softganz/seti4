<?php
/**
* Project :: Paid Doc View
* Created 2019-09-01
* Modify  2019-12-22
*
* @param Object $projectInfo
* @return Widget
*/

import('model:file.php');

Class ProjectInfoPaiddocView extends Page {
	var $projectId;
	var $paidId;
	var $right;
	var $projectInfo;

	function __construct($projectInfo, $paidId = NULL) {
		$this->projectId = $projectInfo->projectId;
		$this->paidId = $paidId;
		$this->projectInfo = $projectInfo;
		$this->right = (Object) [
			'edit' => $projectInfo->info->isEdit,
		];
	}

	function build() {
		$projectInfo = $this->projectInfo;
		$projectId = $this->projectId;
		$paidId = $this->paidId;

		if (!$projectId) return ErrorMessage('error', 'PROCESS ERROR');

		$numLang = 'EN';

		$fundInfo = R::Model('project.fund.get', $projectInfo->orgid);

		$paiddocInfo = R::Model('project.paiddoc.get',$projectId, $paidId);

		if (!$paiddocInfo) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลใบจ่ายเงินตามที่ระบุ']);

		$isLock = $paiddocInfo->paiddate <= $fundInfo->finclosemonth;
		$isRight = $projectInfo->info->isRight;
		$isEdit = $projectInfo->info->isEdit && !$isLock;


		if (post('print')) $isEdit = false;
		$noDocNo = substr($paiddocInfo->docno,0,1) == '?';

		/*
		ชื่อนายก			paiddoc:title text1
		เรียน นายก		paiddoc:title	text2
		ผู้ขอเบิก			paiddoc:title text3
		ตำแหน่ง 			paiddoc:title text4
		จ่ายให้				paiddoc:title text5
		เงินคงเหลือ	paiddoc:title num2
								detail3
								detail4
								text6
								text7
								text8
		ธนาคาร			paiddoc:name1 detail1
		บัญชีเลขที่		paiddoc:name1 detail2
		เลขที่เช็ค		paiddoc:name1	detail3
		ลงวันที่
		ผู้ตรวจสอบ 	paiddoc:name1 text1 -> detail4
		ตำแหน่ง			(ผู้ตรวจสอบและควบคุมงบประมาณ) -> detail5
		งานคลัง			paiddoc:name1 text2 -> detail6
		ตำแหน่ง			(หัวหน้าหน่วยงานคลัง) -> text1
		ปลัด 				paiddoc:name1 text3 -> text2
		ตำแหน่ง			(ปลัดองค์กรปกครองส่วนท้องถิ่น) -> text3
		ลงนาม1			paiddoc:name1 text4
		ตำแหน่ง1			paiddoc:name1 text5
		ลงนาม2			paiddoc:name1 text6
		ตำแหน่ง2			paiddoc:name1 text7
		ผู้จ่ายเงิน		paiddoc:name1 text8
		ตำแหน่ง 			paiddoc:name1 text9
								text10
		INSERT INTO `sgz_project_paiddoc`
		SELECT NULL,ti.`tpid`,ti.`uid`,ti.`date1`,ti.`num1`,ti.`num2`,ti.`detail1`,ti.`detail2`,
		ti.`gallery`,
		ti.`text1`, ti.`text2`, ti.`text3`, ti.`text4`, ti.`text5`,
		n.`detail1`, n.`detail2`, n.`detail3`,NULL,
		n.`text1`, NULL,
		n.`text2`, NULL,
		n.`text3`, NULL,
		n.`text4`, n.`text5`,
		n.`text6`, n.`text7`,
		n.`text8`, n.`text9`,
		ti.`text9`, ti.`text10`,
		ti.`created`
		FROM sgz_project_tr ti
		LEFT JOIN sgz_project_tr n ON n.`formid`="paiddoc" AND n.`part`="name1" AND n.`parent`=ti.`trid`
		WHERE ti.`formid`="paiddoc" AND ti.`part`="title"
		*/

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Paiddoc',
			]), // AppBar
			'body' => new Container([
				'children' => [
					new Column([
						'id' => 'project-paiddoc',
						'class' => 'project-paiddoc -forprint'.($isEdit ? ' sg-inline-edit' : ''),
						'attribute' => $isEdit ? [
							'data-tpid' => $projectId,
							'data-update-url' => url('project/edit/tr'),
							'data-debug' => debug('inline') ? 'inline' : NULL,
						] : NULL,
						'children' => [
							'<!-- แบบฟอร์มสำหรับพิมพ์ใบเบิกเงินเพื่อนำไปเซ็นต์ชื่อ -->',
							'<h2>ใบเบิกเงิน</h2>',
							'<h3>'.$fundInfo->name.'</h3>',
							new Row([
								'mainAxisAlignment' => 'spacebetween',
								'children' => [
									'ที่ <span class="sign -filldata -auto"><b class="'.($noDocNo?'noprint':'').'">'.sg_num2thai($paiddocInfo->docno,$numLang).'</b></span>',
									'วันที่ <span class="sign -filldata" style="white-space: nowrap; padding-right: 16px;"><b class="'.($noDocNo?'noprint':'').'">'.sg_num2thai(sg_date($paiddocInfo->paiddate,'ว ดดด ปปปป'),$numLang).'</b></span>',
								], // children
							]), // Row

							'<p style="padding-top: 1.0rem;">เรียน '
								.view::inlineedit(array('group'=>'paiddoc','fld'=>'positionnayok','tr'=>$paiddocInfo->paidid,'class'=>'sign -filldata -tonayok','callback'=>'replaceNayok'),$paiddocInfo->positionnayok,$isEdit).'</p>',

							'<p class="paragraph" style="padding-top: 1.0rem;">ตามที่คณะกรรมการ <b>'.$fundInfo->name.'</b> ได้อนุมัติแผนงาน/โครงการ/กิจกรรม <b>'.$projectInfo->title.'</b> ให้แก่ หน่วยงาน/องค์กร/กลุ่มคน <span class="noprint">(ระบุชื่อ) </span><b>'.$projectInfo->info->orgnamedo.'</b> จำนวน <b>'.sg_num2thai(number_format($projectInfo->info->budget,2),$numLang).' บาท</b> ('.sg_money2bath($projectInfo->info->budget,2).') นั้น หน่วยงาน/องค์กร/กลุ่มคน <span class="noprint">(ระบุชื่อ) </span><b>'.$projectInfo->info->orgnamedo.'</b> มีความประสงค์จะขอเบิกเงินจำนวน <b>'.sg_num2thai(number_format($paiddocInfo->amount,2),$numLang).' บาท</b> ('.sg_money2bath($paiddocInfo->amount,2).') เพื่อนำไปดำเนินการตามแผนงาน/โครงการ/กิจกรรมดังกล่าว  พร้อมนี้ได้แนบ เอกสาร หลักฐาน ประกอบการขอเบิกเงิน จำนวน 1 ฉบับ มาให้พิจารณาด้วยแล้ว ทั้งนี้ในการรับเงิน <span class="noprint">(ระบุชื่อผู้เสนอแผนงาน/โครงการ/กิจกรรม หรือตัวแทน)</span> <b><span id="namepaid">'.$paiddocInfo->namereceive.'</span></b> จะเป็นผู้รับเงิน</p>',

							// เจ้าหน้าที่ผู้ขอเบิก
							new Row([
								'class' => 'sign-wrapper',
								'style' => 'margin: 1rem 32px 1rem auto; width: 400px;',
								'children' => [
									'ลงชื่อ',
									new Column([
										'children' => [
											'<span class="sign -signdraw -fill">&nbsp;</span><!-- (เจ้าหน้าที่ที่ได้รับมอบหมาย)-->',
											new Row([
												'children' => [
													'(',
													view::inlineedit(array('group'=>'paiddoc','fld'=>'namewithdraw','tr'=>$paiddocInfo->paidid, 'options' => ['class'=>'sign -signdraw', 'placeholder' => 'ชื่อ นามสกุล']),$paiddocInfo->namewithdraw,$isEdit),
													')'
												], // children
											]), // Row
											view::inlineedit(array('group'=>'paiddoc','fld'=>'positionwithdraw','tr'=>$paiddocInfo->paidid, 'options' => ['class'=>'sign -signdraw', 'placeholder' => 'ตำแหน่ง']),$paiddocInfo->positionwithdraw,$isEdit),

										], // children
									]), // Column
									'ผู้ขอเบิก<!-- (เจ้าหน้าที่ อปท. ที่ได้รับมอบหมาย)-->',
								], // children
							]), // Row

							// ตรวจสอบเอกสาร
							new Row([
								'class' => 'sign-row',
								'children' => [
									new Column([
										'children' => [
											'<p>ได้ตรวจสอบเอกสารและงบประมาณที่ได้รับ มีความครบถ้วนถูกต้อง เห็นควรดำเนินการต่อไป รวมทั้งได้หักรายการที่ขอเบิกในครั้งนี้เรียบร้อยแล้ว มียอดเงินคงเหลือ '.view::inlineedit(array('group'=>'paiddoc','fld'=>'fundbalance','tr'=>$paiddocInfo->paidid,'ret'=>'money'),$paiddocInfo->fundbalance,$isEdit,'money').' บาท ('.sg_money2bath($paiddocInfo->fundbalance,2).')</p>',
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'ลงชื่อ',
													new Column([
														'children' => [
															'<span class="sign -signdraw">&nbsp;</span><!-- (เจ้าหน้าที่ที่ได้รับมอบหมาย)-->',
															new Row([
																'children' => [
																	'(',
																	view::inlineedit(array('group'=>'paiddoc','fld'=>'nameproof','tr'=>$paiddocInfo->paidid,'class'=>'sign -filldata','class'=>'sign -signdraw'),$paiddocInfo->nameproof,$isEdit)
																	.view::inlineedit(array('group'=>'paiddoc','fld'=>'positionproof','tr'=>$paiddocInfo->paidid,'class'=>'sign -filldata','class'=>'sign -signdraw'),$paiddocInfo->positionproof,$isEdit),
																	')'
																], // children
															]), // Row
														], // children
													]), // Column
												], // children
											]), // Row
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'วันที่',
													'<span class="sign -signdraw">&nbsp;</span>',
												], // children
											]), // Row
										], // children
									]), // Column
									new Column([
										'children' => [
											'เรียน ปลัดองค์กรปกครองส่วนท้องถิ่น<br />เห็นควรให้เบิกจ่าย<br />จำนวน <span class="sign -text">'.sg_num2thai(number_format($paiddocInfo->amount,2),$numLang).'</span> บาท<br /><br />',
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'ลงชื่อ',
													new Column([
														'children' => [
															'<span class="sign -signdraw">&nbsp;</span>',
															new Row([
																'children' => [
																	'(',
																	view::inlineedit(array('group'=>'paiddoc','fld'=>'namefinance','tr'=>$paiddocInfo->paidid,'class'=>'sign -filldata','class'=>'sign -signdraw'),$paiddocInfo->namefinance,$isEdit)
																	. view::inlineedit(array('group'=>'paiddoc','fld'=>'positionfinance','tr'=>$paiddocInfo->paidid,'class'=>'sign -filldata','class'=>'sign -signdraw'),$paiddocInfo->positionfinance,$isEdit),
																	')'
																], // children
															]), // Row
														], // children
													]), // Column
												], // children
											]), // Row
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'วันที่',
													'<span class="sign -signdraw">&nbsp;</span>',
												], // children
											]), // Row
										], // children
									]), // Column
								], // children
							]), // Row

							// ผู้อนุมัติ
							new Row([
								'class' => 'sign-row',
								'children' => [
									// <div class="box -b1 col -md-6">
									new Column([
										'children' => [
											'เรียน <span class="nayokposition">'.$paiddocInfo->positionnayok.'</span><br />เห็นควรอนุมัติให้เบิกจ่ายได้ จำนวน <span class="sign -text">'.sg_num2thai(number_format($paiddocInfo->amount,2),$numLang).'</span> บาท',
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'ลงชื่อ',
													new Column([
														'children' => [
															'<span class="sign -signdraw">&nbsp;</span><!-- (เจ้าหน้าที่ที่ได้รับมอบหมาย)-->',
															new Row([
																'children' => [
																	'(',
																	view::inlineedit(array('group'=>'paiddoc','fld'=>'namepalad','tr'=>$paiddocInfo->paidid,'class'=>'sign -filldata','class'=>'sign -signdraw'),$paiddocInfo->namepalad,$isEdit)
																	. view::inlineedit(array('group'=>'paiddoc','fld'=>'positionpalad','tr'=>$paiddocInfo->paidid,'class'=>'sign -filldata','class'=>'sign -signdraw'),$paiddocInfo->positionpalad,$isEdit),
																	')'
																], // children
															]), // Row
														], // children
													]), // Column
												], // children
											]), // Row
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'วันที่',
													'<span class="sign -signdraw">&nbsp;</span>',
												], // children
											]), // Row

										], // children
									]), // Column
									new Column([
										'children' => [
											'อนุมัติให้เบิกจ่ายได้<br />จำนวนเงิน <span class="sign -text">'.sg_num2thai(number_format($paiddocInfo->amount,2),$numLang).'</span> บาท',
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'ลงชื่อ',
													new Column([
														'children' => [
															'<span class="sign -signdraw">&nbsp;</span>',
															new Row([
																'children' => [
																	'(',
																	view::inlineedit(array('group'=>'paiddoc','fld'=>'namenayok','tr'=>$paiddocInfo->paidid,'class'=>'sign -filldata','class'=>'sign -signdraw'),$paiddocInfo->namenayok,$isEdit)
																	. view::inlineedit(array('group'=>'paiddoc','fld'=>'positionallow','tr'=>$paiddocInfo->paidid,'class'=>'sign -filldata','class'=>'sign -signdraw'),$paiddocInfo->positionallow,$isEdit),
																	')'
																], // children
															]), // Row
														], // children
													]), // Column
												], // children
											]), // Row
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'วันที่',
													'<span class="sign -signdraw">&nbsp;</span>',
												], // children
											]), // Row
										], // children
									]), // Column
								], // children
							]), // Row

							// จ่ายเป็น
							new Row([
								'class' => 'sign-row',
								'children' => [
									// <div class="box -b1 col -md-6">
									new Column([
										'children' => [
										'จ่ายเป็น',
										'∆ เช็คขีดคร่อม/ตั๋วแลกเงิน/ธนาณัติ',
										'∆ เงินสด (ไม่เกิน 5,000 บาท)',
										'∆ ทางธนาคาร '.$fundInfo->info->accbank,
										'บัญชีเลขที่ '.$fundInfo->info->accno,
										'เลขที่เช็ค '
										.view::inlineedit(array('group'=>'paiddoc','fld'=>'bankcheque','tr'=>$paiddocInfo->paidid,'class'=>'sign -filldata'),$paiddocInfo->bankcheque,$isEdit)
										.'ลงวันที่ ............<br />'
										.'จำนวนเงิน <span class="sign -text">'.sg_num2thai(number_format($paiddocInfo->amount,2),$numLang).'</span> บาท ('.sg_money2bath($paiddocInfo->amount,2).')<br />'
										.'จ่ายให้<!-- (ชื่อผู้รับเงิน)--> '
										.view::inlineedit(array('group'=>'paiddoc','fld'=>'namereceive','tr'=>$paiddocInfo->paidid,'class'=>'sign -filldata -namereceive','callback'=>'replaceNamePaid'),$paiddocInfo->namereceive,$isEdit)
										], // children
									]), // Column
									new Column([
										'children' => [
											'ผู้มีอำนาจลงนามในใบถอน/เช็คธนาคาร',
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'ลงชื่อ',
													new Column([
														'children' => [
															'<span class="sign -signdraw">&nbsp;</span>',
															new Row([
																'children' => [
																	'(',
																	view::inlineedit(array('group'=>'paiddoc','fld'=>'namesign1','tr'=>$paiddocInfo->paidid,'class'=>'sign -filldata','class'=>'sign -signdraw'),$paiddocInfo->namesign1,$isEdit),
																	')'
																], // children
															]), // Row
														], // children
													]), // Column
													'ผู้มีอำนาจลงนาม<!-- (กลุ่มหนึ่ง)-->',
												], // children
											]), // Row
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'',
													view::inlineedit(array('group'=>'paiddoc','fld'=>'positionallow','tr'=>$paiddocInfo->paidid,'class'=>'sign -filldata','class'=>'sign -signdraw'),$paiddocInfo->positionallow,$isEdit),
												], // children
											]), // Row

											'<br />',
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'ลงชื่อ',
													new Column([
														'children' => [
															'<span class="sign -signdraw">&nbsp;</span>',
															new Row([
																'children' => [
																	'(',
																	view::inlineedit(array('group'=>'paiddoc','fld'=>'namesign2','tr'=>$paiddocInfo->paidid,'class'=>'sign -filldata','class'=>'sign -signdraw'),$paiddocInfo->namesign2,$isEdit),
																	')'
																], // children
															]), // Row
														], // children
													]), // Column
													'ผู้มีอำนาจลงนาม<!-- (กลุ่มสอง)-->',
												], // children
											]), // Row
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'',
													view::inlineedit(array('group'=>'paiddoc','fld'=>'positionsign2','tr'=>$paiddocInfo->paidid,'class'=>'sign -filldata','class'=>'sign -signdraw'),$paiddocInfo->positionsign2,$isEdit),
												], // children
											]), // Row
										], // children
									]), // Column
								], // children
							]), // Row

							new ListTile(['title' => 'หลักฐานการเบิกจ่ายเงิน']),

							// ผู้รับเงิน
							new Row([
								'class' => 'sign-row',
								'children' => [
									new Column([
										'children' => [
											'ได้รับเงินจำนวน <span class="sign -text">'.sg_num2thai(number_format($paiddocInfo->amount,2),$numLang).'</span> บาท',
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'ลงชื่อ',
													new Column([
														'children' => [
															'<span class="sign -signdraw">&nbsp;</span>',
															new Row([
																'children' => [
																	'(',
																	'<span class="sign -signdraw">&nbsp;</span>',
																	')'
																], // children
															]), // Row
														], // children
													]), // Column
													'ผู้รับเงิน (1)',
												], // children
											]), // Row
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'ตำแหน่ง',
													'<span class="sign -signdraw">&nbsp;</span>',
												], // children
											]), // Row
											'<br />',
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'ลงชื่อ',
													new Column([
														'children' => [
															'<span class="sign -signdraw">&nbsp;</span>',
															new Row([
																'children' => [
																	'(',
																	'<span class="sign -signdraw">&nbsp;</span>',
																	')'
																], // children
															]), // Row
														], // children
													]), // Column
													'ผู้รับเงิน (2)',
												], // children
											]), // Row
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'ตำแหน่ง',
													'<span class="sign -signdraw">&nbsp;</span>',
												], // children
											]), // Row
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'วันที่',
													'<span class="sign -signdraw">&nbsp;</span>',
												], // children
											]), // Row
										], // children
									]), // Column
									new Column([
										'children' => [
											'ได้จ่ายเงินเรียบร้อยแล้ว จำนวน <span class="sign -text">'.sg_num2thai(number_format($paiddocInfo->amount,2),$numLang).'</span> บาท<br /><br />',
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'ลงชื่อ',
													new Column([
														'children' => [
															'<span class="sign -signdraw">&nbsp;</span>',
															new Row([
																'children' => [
																	'(',
																	view::inlineedit(array('group'=>'paiddoc','fld'=>'namepaid','tr'=>$paiddocInfo->paidid,'class'=>'sign -filldata','class'=>'sign -signdraw'),$paiddocInfo->namepaid,$isEdit)
																	. view::inlineedit(array('group'=>'paiddoc','fld'=>'positionpaid','tr'=>$paiddocInfo->paidid,'class'=>'sign -filldata','class'=>'sign -signdraw'),$paiddocInfo->positionpaid,$isEdit),
																	')'
																], // children
															]), // Row
															'<br />',
														], // children
													]), // Column
													'ผู้จ่ายเงิน',
												], // children
											]), // Row
											new Row([
												'class' => 'sign-wrapper',
												'children' => [
													'วันที่',
													'<span class="sign -signdraw">&nbsp;</span>',
												], // children
											]), // Row
										], // children
									]), // Column
								], // children
							]), // Row

							'<p>หมายเหตุ  (1) ให้แนบสำเนาบัตรประจำตัวประชาชน ของผู้รับเงิน /ใบมอบอำนาจพร้อมหลักฐานประกอบ</p>',
							'<strong>หมายเหตุ : </strong>'
							. view::inlineedit(array('group'=>'paiddoc','fld'=>'paidremark','tr'=>$paiddocInfo->paidid,'class'=>'sign -remark'),$paiddocInfo->paidremark,$isEdit,'textarea'),

							'<div class="-no-print">'
							. '<strong>หมายเหตุผู้จัดการระบบ : </strong>(ไม่พิมพ์ในใบเบิกเงิน)'
							. view::inlineedit(array('group'=>'paiddoc','fld'=>'adminremark','tr'=>$paiddocInfo->paidid,'class'=>'sign'),$paiddocInfo->adminremark,$isEdit,'textarea')
							. '</div>',

							$ret,
							$this->_script(),
						], // children
					]), // Column

					$this->_updoc($projectInfo,$paiddocInfo),

					// new DebugMsg($paiddocInfo, '$paiddocInfo'),
				], // children
			]), // Container
		]);
	}

	function _updoc($projectInfo, $paiddocInfo) {
		return new Card([
			'class' => 'project-expense -rcvphoto -no-print',
			'children' => [
				new ListTile(['title' => 'ไฟล์เอกสารใบเบิกเงิน']),
				$this->right->edit ? new Row([
					'crossAxisAlignment' => 'center',
					'class' => '-upload -sg-text-center',
					'children' => [
						'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('project/'.$this->projectId.'/info/paiddoc.upload/'.$this->paidId).'" data-rel="#projectrcv-photo" data-prepend="li">'
							. '<span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>อัพโหลดใบเบิกเงิน</span><input type="file" name="photo[]" multiple="true" class="inline-upload" /></span>'
							. '<input class="-hidden" type="submit" value="upload" />'
							. '</form>',
						'<img src="https://chart.googleapis.com/chart?cht=qr&chl='._DOMAIN.urlencode(url('project/'.$this->projectId.'/info.paiddoc/'.$this->paidId)).'&chs=160x160&choe=UTF-8&chld=L|2" alt="" />',
						'<p>อัพโหลดเอกสารด้วยการถ่ายภาพจากสมาร์ทโฟน โดยใช้สมาร์ทโฟนสแกนคิวอาร์โค๊ดนี้ แล้วกดปุ่ม "อัพโหลดใบเบิกเงิน" เลือกกล้องถ่ายรูป</p>',
					], // children
				]) : NULL, // Row

				new Ui([
					'type' => 'album',
					'id' => 'projectrcv-photo',
					'forceBuild' => true,
					'children' => array_map(
						function($item) {
							$this->right->edit = $this->projectInfo->info->isEdit;

							if ($item->type == 'photo') {
								$photo = FileModel::photoProperty($item->file);
								return [
									'text' => '<a class="sg-action" data-group="photo" href="'.$photo->src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">'
									. '<img class="photoitem" src="'.$photo->src.'" alt="photo '.$photo_alt.'" '
									. ' />'
									. '</a>'
									. ($this->right->edit ? '<nav class="nav -icons -hover"><a class="sg-action" href="'.url('project/'.$this->projectId.'/info/photo.delete/'.$item->fid).'" data-title="ลบภาพ" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="notify" data-done="remove:parent li"><i class="icon -material -gray">cancel</i></a></nav>' : NULL),
									'options' => ['class' => '-hover-parent']
								];
							} else if ($item->type == 'doc') {
								$doc = FileModel::docProperty($item->file);
								return [
									'text' => '<a href="'.$doc->src.'" title="ไฟล์เอกสารใบเบิกเงิน" target="_blank">'
									. '<img class="photoitem" src="//img.softganz.com/icon/pdf-icon.png" alt="ไฟล์เอกสารใบเบิกเงิน" '
									. ' />'
									. '</a>'
									. ($this->right->edit ? '<nav class="nav -icons -hover"><a class="sg-action" href="'.url('project/'.$this->projectId.'/info/docs.delete/'.$item->fid).'" data-title="ลบไฟล์เอกสาร" data-confirm="ยืนยันว่าจะไฟล์เอกสารนี้จริง?" data-rel="notify" data-done="remove:parent li"><i class="icon -material -gray">cancel</i></a></nav>' : NULL),
									'options' => ['class' => '-hover-parent']
								];
							} else {
								return NULL;
							}
						},
						$dbs = mydb::select(
							'SELECT `fid`, `refId` `paidId`, `type`, `file`
							FROM %topic_files% f
								WHERE f.`tpid` = :projectId AND f.`tagname` = "project,paiddoc" AND f.`refid` = :paidId ORDER BY f.`fid` DESC',
							[':projectId' => $this->projectId, ':paidId' => $this->paidId]
						)->items
					), // children
				]),
			], // children
		]);
	}

	function _script() {
		return '<style type="text/css">
		.-forprint h2, .-forprint h3 {text-align: center;}
		.paragraph {text-indent: 1cm;}

		.sign-row>.-item {border: 1px #ccc solid; padding: 4px; margin: 4px; flex: 0 0 calc(50% - 14px);}
		.sign-row>.-item:first-child {margin-left: 0;}
		.sign-row>.-item:last-child {margin-right: 0;}

		.sign-wrapper>.-item {display: flex;}
		.sign-wrapper>.-item:nth-child(2) {flex: 1; padding: 0 8px;}
		.sign-wrapper>.-item:nth-child(2)>* {width: 100%;}
		.sign-wrapper>.-item:nth-child(2) .widget-row>.-item:nth-child(2) {flex: 1;}
		.sign-wrapper>.-item:nth-child(3) {white-space: wrap; font-size: 1.2rem;}

		.sign {display: inline-block; border-bottom:1px #000 dotted;}
		.sign.-item {margin: 3em 0;}
		.sign.-text {}
		.sign.-filldata {width:9em; display: inline-block;}
		.sign.-signdraw, .sign.-nametext, .sign.-date {display: block; text-align: center; vertical-align: bottom; border-bottom:1px #000 dotted; white-space:nowrap;}
		.sign.-signdraw {}
		.sign.-moneytext {width:10em;}
		.sign.-right {text-align: right;}
		.fixed-width {display:inline-block; width: 20em;overflow:hidden;text-align:center;}
		.sign.-filldata.-tonayok {width:auto;}
		.sign.-filldata.-namereceive {width: auto; display: block;}
		.sign.-auto {width:auto;}

		.project-expense.-rcvphoto .widget-row.-upload>.-item {flex: 0 0 33%;}

		@media print {
			body {}
			.project-paiddoc {font-size: 1.6rem; line-height: 1.7rem;}
			.sign-row>.-item {border: 1px #999 solid; flex: 0 0 calc(50% - 17px);}
			.sign.-signdraw, .sign.-nametext, .sign.-date {}
			.sign.-filldata.-tonayok {width:auto;}
			.sign.-fill.-remark {height:auto; display: inline;border-bottom:none;}
			.project-paiddoc .box.-b1, .box.-b2 {}
			.project-paiddoc .box.-b3, .box.-b4 {height:8em;}
			.project-paiddoc .box.-b7, .box.-b8 {height:10em;}
			.-forprint h4 {margin:0; padding:0;}
		}
		</style>

		<script type="text/javascript">
		function replaceNayok($this,data,$parent) {
			$(".nayokposition").text(data.value)
		}
		function replaceNamePaid($this,data,$parent) {
			$("#namepaid").text(data.value)
		}
		</script>';
	}
}
?>