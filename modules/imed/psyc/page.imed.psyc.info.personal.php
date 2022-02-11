<?php
/**
* iMed : Patient Personal Information
* Created 2021-05-27
* Modify  2021-05-31
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/psyc/{id}/info.personal
*/

$debug = true;

class ImedPsycInfoPersonal {
	var $psnId;
	var $patientInfo;

	function __construct($patientInfo) {
		$this->patientInfo = $patientInfo;
		$this->psnId = $patientInfo->psnId;
	}

	function build() {
		//R::View('imed.toolbar',$self,'ข้อมูลผู้ป่วยรอการฟื้นฟู','none',$psnInfo);

		$psnInfo = $this->patientInfo;
		$psnId = $this->patientInfo->psnId;

		if (!$psnId) return message('error','ไม่มีข้อมูล');

		$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
		$this->isEdit = $isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

		if (!$isAccess) return message('error',$psnInfo->error);

		$currentUrl = url('imed/psyc/'.$psnId.'/info.personal');

		include_once 'modules/imed/assets/qt.individual.php';

		/*
			// $inlineAttr = array();
			// $inlineAttr['class'] = 'imed-qt -personal';
			// if ($isEdit) {
			// 	$inlineAttr['class'] .= ' sg-inline-edit';
			// 	$inlineAttr['data-update-url'] = url('imed/edit/patient');
			// 	$inlineAttr['data-psnid'] = $psnId;
			// 	$inlineAttr['data-url'] = $currentUrl;
			// 	if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
			// }


			// $ret.='<div id="imed-care-personal" '.sg_implode_attr($inlineAttr).'>'._NL;


			// Patient menu
			// $ret .= '<header class="header"><h3>ข้อมูลส่วนบุคคล'.($psnInfo->info->dischar == 1 ? ' (เสียชีวิต)' : '').'</h3></header>';

			// $tables = new Table();
			// $tables->addId('imed-patient-individual');
			// $tables->caption='ข้อมูลส่วนบุคคล';
			// if ($isEdit) {
			// 	$tables->rows[] = array(
			// 		'คำนำหน้าชื่อ <a class="sg-info" href="http://th.wiktionary.org/wiki/รายชื่ออักษรย่อในภาษาไทย" target="_blank" 	data-tooltip="คลิกเพื่อดูรายการคำย่อของคำนำหน้าชื่อ">i</a>',
			// 		'<strong>'.imed_model::qt('prename',$qt,$psnInfo->info->prename,$isEdit).'</strong>'
			// 	);

			// 	$tables->rows[] = array(
			// 		'ชื่อ - นามสกุล',
			// 		'<strong>'.imed_model::qt('name',$qt,$psnInfo->info->name.' '.$psnInfo->info->lname,$isEdit).'</strong>'
			// 	);

			// 	$tables->rows[] = array(
			// 		'ชื่อเล่น',
			// 		imed_model::qt('nickname',$qt,$psnInfo->info->nickname,$isEdit)
			// 	);

			// } else {
			// 	$tables->rows[] = array(
			// 		'ชื่อ-นามสกุล',
			// 		'<strong>'.$psnInfo->info->prename.' '.$psnInfo->info->name.' '.$psnInfo->info->lname.' ('.$psnInfo->info->nickname.')</strong>'
			// 	);
			// }

			// $tables->rows[] = array(
			// 	'หมายเลขบัตรประชาชน',
			// 	imed_model::qt('cid',$qt,$psnInfo->info->cid,$isEdit)
			// );

			// $tables->rows[] = array(
			// 	'เพศ',
			// 	imed_model::qt('sex',$qt,$psnInfo->info->sex,$isEdit)
			// );

			// $tables->rows[] = array(
			// 	'วันเกิด <sup class="sg-tooltip" data-url="'.url('imed').'">?</sup>',
			// 	view::inlineedit(
			// 		array('group'=>'person','fld'=>'birth','ret'=>'date:ว ดดด ปปปป','value'=>$psnInfo->info->birth),
			// 		$psnInfo->info->birth ? sg_date($psnInfo->info->birth,'ว ดดด ปปปป') : null,
			// 		$isEdit
			// 		,'datepicker'
			// 	)
			// 	.($psnInfo->info->birth?' อายุ '.(date('Y')-sg_date($psnInfo->info->birth,'Y')).' ปี':'')
			// );

			// $tables->rows[] = array(
			// 	'เชื้อชาติ',
			// 	imed_model::qt('PSNL.1.5.1',$qt,$psnInfo->qt,$isEdit)
			// );

			// $tables->rows[] = array(
			// 	'สัญชาติ',
			// 	imed_model::qt('PSNL.1.5.2',$qt,$psnInfo->qt,$isEdit)
			// );

			// $tables->rows[] = array(
			// 	'ศาสนา',
			// 	imed_model::qt('PSNL.1.5.3',$qt,$psnInfo->qt,$isEdit)
			// );

			// $tables->rows[] = array(
			// 	'สถานภาพสมรส',
			// 	imed_model::qt('mstatus',$qt,$psnInfo->info->mstatus,$isEdit,$psnInfo->info->mstatus_desc)
			// 	.imed_model::qt('PSNL.1.6.2',$qt,$psnInfo->qt,$isEdit)
			// );

			// $tables->rows[] = array(
			// 	'ระดับการศึกษาสูงสุด',
			// 	imed_model::qt('educate',$qt,$psnInfo->info->educate,$isEdit,$psnInfo->info->edu_desc)
			// 	.imed_model::qt('PSNL.EDU.OTHER',$qt,$psnInfo->qt,$isEdit).'<br />'
			// );

			// $tables->rows[] = array(
			// 	'ที่อยู่ปัจจุบัน <sup class="sg-tooltip" data-url="'.url('imed/help/input/address').'">?</sup>',
			// 	view::inlineedit(
			// 		array(
			// 			'group'=>'person',
			// 			'fld'=>'address',
			// 			'areacode'=>$psnInfo->info->areacode,
			// 			'options' => '{
			// 				class: "-fill'.(empty($psnInfo->info->areacode) ? ' -incomplete' : '').'",
			// 					onblur: "none",
			// 					autocomplete: {
			// 						minLength: 5,
			// 						target: "areacode",
			// 						query: "'.url('api/address').'"
			// 					},
			// 				placeholder: "0 ซอย ถนน ม.0 ต.ตัวอย่าง แล้วเลือกจากรายการแสดง"
			// 			}',
			// 			),
			// 		$psnInfo->info->address,
			// 		$isEdit,
			// 		'autocomplete'
			// 	)
			// );

			// $tables->rows[] = array(
			// 	'ที่อยู่ตามทะเบียนบ้าน <sup class="sg-tooltip" data-url="'.url('imed/help/input/address').'">?</sup>',
			// 	view::inlineedit(
			// 		array(
			// 			'group'=>'person',
			// 			'fld'=>'raddress',
			// 			'areacode'=>$psnInfo->info->rareacode,
			// 			'options' => '{
			// 				class: "-fill'.(empty($psnInfo->info->rareacode) ? ' -incomplete' : '').'",
			// 					onblur: "none",
			// 					autocomplete: {
			// 						minLength: 5,
			// 						target: "areacode",
			// 						query: "'.url('api/address').'"
			// 					},
			// 				placeholder: "0 ซอย ถนน ม.0 ต.ตัวอย่าง แล้วเลือกจากรายการแสดง"
			// 			}',
			// 		),
			// 		$psnInfo->info->raddress,
			// 		$isEdit,
			// 		'autocomplete'
			// 	)
			// );

			// $tables->rows[] = array('สถานะของที่พักอาศัย',
			// 	imed_model::qt('OTHR.5.5',$qt,$psnInfo->qt,$isEdit).'<br />'
			// 	.imed_model::qt('OTHR.5.5.1',$qt,$psnInfo->qt,$isEdit)
			// );

			// $tables->rows[] = array('สภาพบ้าน',
			// 	imed_model::qt('PSNL.HOUSECONDITION',$qt,$psnInfo->qt,$isEdit).'<br />'
			// 	.imed_model::qt('PSNL.HOUSECONDITION.OTHER',$qt,$psnInfo->qt,$isEdit)
			// );

			// $tables->rows[] = array(
			// 	'โทรศัพท์',
			// 	imed_model::qt('phone',$qt,$psnInfo->info->phone,$isEdit)
			// );

			// $tables->rows[] = array(
			// 	'อีเมล์',
			// 	imed_model::qt('email',$qt,$psnInfo->info->email,$isEdit)
			// );

			// $tables->rows[] = array(
			// 	'อาชีพ',
			// 	imed_model::qt('occupa',$qt,$psnInfo->info->occu_desc,$isEdit)
			// );

			// $tables->rows[] = array(
			// 	'รายได้',
			// 	imed_model::qt('ECON.4.5',$qt,$psnInfo->qt,$isEdit)
			// );

			// $ret .= $tables->build();



			// ผู้ดูแล :: ชื่อ/ความสัมพันธ์/โทรศัพท์/รายได้



			// $ret .= $this->_addItem('หมายเหตุ', imed_model::qt('remark',$qt,$psnInfo->info->remark,$isEdit));
			// $ret .= '<p><small>สร้างโดย '.$psnInfo->info->created_by.' เมื่อ '.sg_date($psnInfo->info->created_date,'ว ดด ปปปป H:i').($psnInfo->info->modify?' แก้ไขล่าสุดโดย '.$psnInfo->info->modify_by.' เมื่อ '.sg_date($psnInfo->info->modify,'ว ดด ปปปป H:i'):'').'</small></p>';

			// $ret.='</div>';
		*/

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $psnInfo->info->fullname,
				'removeOnApp' => true,
			]), // AppBar
			'body' => new Container([
				'id' => 'imed-care-personal',
				'class' => 'imed-qt'.($isEdit ? ' sg-inline-edit' : ''),
				'attribute' => $isEdit ? [
					'data-update-url' => url('imed/edit/patient'),
					'data-url' => $currentUrl,
					'data-psnid' =>  $psnId,
					'data-debug' => debug('inline') ? 'inline' : NULL,
				] : NULL,
				'children' => [
					$isEdit ? $this->_addItem(
						'คำนำหน้าชื่อ <a class="sg-info" href="http://th.wiktionary.org/wiki/รายชื่ออักษรย่อในภาษาไทย" target="_blank" 	data-tooltip="คลิกเพื่อดูรายการคำย่อของคำนำหน้าชื่อ">i</a>',
						'<strong>'.imed_model::qt('prename',$qt,$psnInfo->info->prename,$isEdit).'</strong>'
					) : NULL,
					$isEdit ? $this->_addItem(
						'ชื่อ - นามสกุล',
						'<strong>'.imed_model::qt('name',$qt,$psnInfo->info->name.' '.$psnInfo->info->lname,$isEdit).'</strong>'
					) : $this->_addItem(
						'ชื่อ-นามสกุล',
						'<strong>'.$psnInfo->info->prename.' '.$psnInfo->info->name.' '.$psnInfo->info->lname.' ('.$psnInfo->info->nickname.')</strong>'
					),
					$isEdit ? $this->_addItem(
						'ชื่อเล่น',
						imed_model::qt('nickname',$qt,$psnInfo->info->nickname,$isEdit)
					) : NULL,
					$this->_addItem(
						'หมายเลขบัตรประชาชน',
						imed_model::qt('cid',$qt,$psnInfo->info->cid,$isEdit)
					),
					$this->_addItem(
						'เพศ',
						imed_model::qt('sex',$qt,$psnInfo->info->sex,$isEdit)
					),
					$this->_addItem(
						'วันเกิด <sup class="sg-tooltip" data-url="'.url('imed').'">?</sup>',
						view::inlineedit(
							array('group'=>'person','fld'=>'birth','ret'=>'date:ว ดดด ปปปป','value'=>$psnInfo->info->birth),
							$psnInfo->info->birth ? sg_date($psnInfo->info->birth,'ว ดดด ปปปป') : null,
							$isEdit
							,'datepicker'
						)
						.($psnInfo->info->birth?' อายุ '.(date('Y')-sg_date($psnInfo->info->birth,'Y')).' ปี':'')
					),
					$this->_addItem(
						'เชื้อชาติ',
						imed_model::qt('PSNL.1.5.1',$qt,$psnInfo->qt,$isEdit)
					),
					$this->_addItem(
						'สัญชาติ',
						imed_model::qt('PSNL.1.5.2',$qt,$psnInfo->qt,$isEdit)
					),
					$this->_addItem(
						'ศาสนา',
						imed_model::qt('PSNL.1.5.3',$qt,$psnInfo->qt,$isEdit)
					),
					$this->_addItem(
						'สถานภาพสมรส',
						imed_model::qt('mstatus',$qt,$psnInfo->info->mstatus,$isEdit,$psnInfo->info->mstatus_desc)
						.imed_model::qt('PSNL.1.6.2',$qt,$psnInfo->qt,$isEdit)
					),
					$this->_addItem(
						'ระดับการศึกษาสูงสุด',
						imed_model::qt('educate',$qt,$psnInfo->info->educate,$isEdit,$psnInfo->info->edu_desc)
						.imed_model::qt('PSNL.EDU.OTHER',$qt,$psnInfo->qt,$isEdit).'<br />'
					),
					$this->_addItem(
						'ที่อยู่ปัจจุบัน <sup class="sg-tooltip" data-url="'.url('imed/help/input/address').'">?</sup>',
						view::inlineedit(
							array(
								'group'=>'person',
								'fld'=>'address',
								'areacode'=>$psnInfo->info->areacode,
								'options' => '{
									class: "-fill'.(empty($psnInfo->info->areacode) ? ' -incomplete' : '').'",
										onblur: "none",
										autocomplete: {
											minLength: 5,
											target: "areacode",
											query: "'.url('api/address').'"
										},
									placeholder: "0 ซอย ถนน ม.0 ต.ตัวอย่าง แล้วเลือกจากรายการแสดง"
								}',
								),
							$psnInfo->info->address,
							$isEdit,
							'autocomplete'
						)
					),
					$this->_addItem(
						'ที่อยู่ตามทะเบียนบ้าน <sup class="sg-tooltip" data-url="'.url('imed/help/input/address').'">?</sup>',
						view::inlineedit(
							array(
								'group'=>'person',
								'fld'=>'raddress',
								'areacode'=>$psnInfo->info->rareacode,
								'options' => '{
									class: "-fill'.(empty($psnInfo->info->rareacode) ? ' -incomplete' : '').'",
										onblur: "none",
										autocomplete: {
											minLength: 5,
											target: "areacode",
											query: "'.url('api/address').'"
										},
									placeholder: "0 ซอย ถนน ม.0 ต.ตัวอย่าง แล้วเลือกจากรายการแสดง"
								}',
							),
							$psnInfo->info->raddress,
							$isEdit,
							'autocomplete'
						)
					),
					$this->_addItem('สถานะของที่พักอาศัย',
						imed_model::qt('OTHR.5.5',$qt,$psnInfo->qt,$isEdit).'<br />'
						.imed_model::qt('OTHR.5.5.1',$qt,$psnInfo->qt,$isEdit)
					),

					$this->_addItem('สภาพบ้าน',
						imed_model::qt('PSNL.HOUSECONDITION',$qt,$psnInfo->qt,$isEdit).'<br />'
						.imed_model::qt('PSNL.HOUSECONDITION.OTHER',$qt,$psnInfo->qt,$isEdit)
					),

					$this->_addItem(
						'โทรศัพท์',
						imed_model::qt('phone',$qt,$psnInfo->info->phone,$isEdit)
					),

					$this->_addItem(
						'อีเมล์',
						imed_model::qt('email',$qt,$psnInfo->info->email,$isEdit)
					),

					$this->_addItem(
						'อาชีพ',
						imed_model::qt('occupa',$qt,$psnInfo->info->occu_desc,$isEdit)
					),

					$this->_addItem(
						'รายได้',
						imed_model::qt('ECON.4.5',$qt,$psnInfo->qt,$isEdit)
					),

					new Card([
						'children' => [
							new ListTile([
								'title' => 'ผู้ดูแล',
								// 'trailing' => $this->isEdit ? '<a class="sg-action btn" href="'.url('imed/patient/'.$this->psnId.'/info/carer.add').'" data-rel="none" data-done="load->replace:#imed-care-personal"><i class="icon -material">add_circle_outline</i><span>เพิ่มผู้ดูแล</span></a>' : '',
							]),
							// new CarerWidget([
							// 	'psnInfo' => $psnInfo
							// ]),
							// $isEdit ? '<nav class="nav -sg-text-right"><a class="sg-action btn" href="'.url('imed/patient/'.$psnId.'/info/carer.add').'" data-rel="none" data-done="load->replace:#imed-care-personal"><i class="icon -material">add_circle_outline</i><span>เพิ่มผู้ดูแล</span></a></nav>' : '',

							$this->_carer(),
							$this->isEdit ? new Row([
								'class' => '-sg-paddingmore',
								'mainAxisAlignment' => 'end',
								'children' => [
									'<a class="sg-action btn" href="'.url('imed/patient/'.$this->psnId.'/info/carer.add').'" data-rel="none" data-done="load->replace:#imed-care-personal"><i class="icon -material">add_circle_outline</i><span>เพิ่มผู้ดูแล</span></a>',
								]
							]) : '',
						],
					]),

					$this->_addItem('หมายเหตุ', imed_model::qt('remark',$qt,$psnInfo->info->remark,$isEdit)),
					'<p><small>สร้างโดย '.$psnInfo->info->created_by.' เมื่อ '.sg_date($psnInfo->info->created_date,'ว ดด ปปปป H:i').($psnInfo->info->modify?' แก้ไขล่าสุดโดย '.$psnInfo->info->modify_by.' เมื่อ '.sg_date($psnInfo->info->modify,'ว ดด ปปปป H:i'):'').'</small></p>',
				],
			]), // Container
		]); // Scaffold
	}

	function _addItem($label = NULL, $value = NULL) {
		return '<div class="qt-item">'
			. '<label class="label">'.$label.'</label>'._NL
			. '<span class="value">'.$value.'</span>'._NL
			. '</div>';
	}

	function _carer() {
		$tables2 = new Table();
		$tables2->id = 'imed-patient-carer';
		$tables2->caption = 'ผู้ดูแล';

		$tables2->thead = array(
			'no'=>'',
			'ประเภท',
			'ชื่อ-สกุล',
			'วันที่เริ่ม',
			'สถานะ',
			'tool -hover-parent' => '',//$this->isEdit ? '<a class="sg-action btn" href="'.url('imed/patient/'.$this->psnId.'/info/carer.add').'" data-rel="none" data-done="load->replace:#imed-care-personal"><i class="icon -material">add_circle_outline</i><span>เพิ่มผู้ดูแล</span></a>' : '',
			''
		);

		$no = 0;

		foreach ($this->patientInfo->carer as $item) {
			$tables2->rows[]=array(
				++$no,
				'<!--ประเภท-->'.view::inlineedit(array('group'=>'carer','fld'=>'cat_id','tr'=>$item->tr_id),$item->cat_id_name,$this->isEdit,'select',imed_model::get_category('carer')),
				'<!--ชื่อ สกุล-->'.view::inlineedit(array('group'=>'carer','fld'=>'detail1','tr'=>$item->tr_id),$item->detail1,$this->isEdit,'text'),
				'<!--วันที่เริ่ม-->'.view::inlineedit(array('group'=>'carer','fld'=>'created','tr'=>$item->tr_id,'class'=>'w-2','ret'=>'date:ว ดด ปปปป'),sg_date($item->created,'d/m/Y'),$this->isEdit,'datepicker'),
				'<!--สถานะ-->'.view::inlineedit(array('group'=>'carer','fld'=>'status','tr'=>$item->tr_id),$item->status_name,$this->isEdit,'select',imed_model::get_category('carerstate')),
				($this->isEdit ? '<nav class="nav -icons -hover"><a class="sg-action" href="'.url('imed/patient/'.$this->psnId.'/info/carer.remove/'.$item->tr_id).'" data-rel="notify" data-done="remove:tr.carer-'.$item->tr_id.'" data-title="ลบผู้ดูแล" data-confirm="ต้องการลบรายการผู้ดูแล รายการนี้จริงหรือ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a></nav>' : ''),
				'<a href="javascript:void(0)" title="post by '.$item->poster.'">?</a>',
				'config' => array('class'=>'carer-'.$item->tr_id)
			);

			$tables2->rows[]=array(
				'<td></td>',
				'<td colspan="6">'
				.'อายุ '.view::inlineedit(array('group'=>'carer', 'fld'=>'ref_id1', 'tr'=>$item->tr_id, 'class'=>'w-1'), $item->ref_id1, $this->isEdit, 'text').' ปี<br />'
				.'ความสัมพันธ์ '.view::inlineedit(array('group'=>'carer', 'fld'=>'detail2', 'tr'=>$item->tr_id, 'class'=>'w-6'), $item->detail2, $this->isEdit, 'text').'<br />'
				.'การศึกษา '.view::inlineedit(array('group'=>'carer','fld'=>'detail3','tr'=>$item->tr_id,'class'=>'w-6'),$item->detail3,$this->isEdit,'text').'<br />'
				.'โทรศัพท์ '.view::inlineedit(array('group'=>'carer','fld'=>'remark','tr'=>$item->tr_id,'class'=>'w-6'),$item->remark,$this->isEdit,'text').'<br />'
				.'อาชีพ '.view::inlineedit(array('group'=>'carer','fld'=>'detail4','tr'=>$item->tr_id,'class'=>'w-6'),$item->detail4,$this->isEdit,'text').'<br />'
				.'รายได้ '.view::inlineedit(array('group'=>'carer','fld'=>'detail5','tr'=>$item->tr_id,'class'=>'w-1'),$item->detail5,$this->isEdit,'text').' บาท/เดือน<br />',
				'config' => array('class'=>'carer-'.$item->tr_id)
			);
		}
		$ret .= $tables2->build();
		$ret .= '<p><ul><li>กรณีไม่มีผู้ดูแลหลัก ใส่ชื่อผู้ใหญ่บ้าน / อสม.ที่ดูแล</li><li>กรณีมีผู้ช่วยดูแลคนพิการ บุคคลที่ผ่านการอบรมและรับรองจาก พม./อสม./เจ้าหน้าที่สาธารณสุข ฯลฯ</li></ul></p>';
		return $ret;
	}
}

class CarerWidget extends Widget {
	var $psnId;
	var $psnInfo;
	var $isEdit = false;

	function __construct($args = []) {
		parent::__construct($args);
		$this->psnId = $this->psnInfo->psnId;
		$this->isEdit = $this->psnInfo->RIGHT & _IS_EDITABLE;
		debugMsg($this->isEdit ? 'EDIT' : 'NO EDIT');
	}

	function build() {
		return new Column([
			'children' => (function() {
				$result = [];
				$no = 0;
				foreach ($this->psnInfo->carer as $item) {
					$result[] = new Card([
						'children' => [
							new ListTile([
								'leading' => ++$no.'.',
								'title' => $item->detail1,
								'trailing' => $this->isEdit ? '<nav class="nav -icons"><a class="sg-action" href="'.url('imed/patient/'.$this->psnId.'/info/carer.remove/'.$item->tr_id).'" data-rel="notify" data-done="remove:parent .widget-card" data-title="ลบผู้ดูแล" data-confirm="ต้องการลบรายการผู้ดูแล รายการนี้จริงหรือ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a></nav>' : 'NO',
							]),
							print_o($item, '$item'),
						],
					]);
				}
				// $result[] = print_o($this->psnInfo, '$psnInfo');
				return $result;
			})(),
		]);
	}
}
?>