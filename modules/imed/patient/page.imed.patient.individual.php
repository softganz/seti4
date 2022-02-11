<?php
/**
* Vew personal individual information
*
* @param Integer $psnId
* @return String
*/
function imed_patient_individual($self, $psnId = NULL) {
	$psnId = SG\getFirst($psnId,post('id'));

	$psnInfo = is_object($psnId) ? $psnId : R::Model('imed.patient.get', $psnId, '{}');
	$psnId = $psnInfo->psnId;

	if (empty($psnInfo)) return message('error','ไม่มีข้อมูล');

	$ret = '';

	$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
	$isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

	//if (!$isAccess) return message('error',$psnInfo->error);


	$inlineAttr = array();
	$inlineAttr['class'] = 'imed-care-individual';
	if ($isEdit) {
		$inlineAttr['class'] .= ' sg-inline-edit';
		$inlineAttr['data-update-url'] = url('imed/edit/patient');
		$inlineAttr['data-psnid'] = $psnId;
		if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
	}

	include_once 'modules/imed/assets/qt.individual.php';

	$ret.='<div id="imed-care-individual" '.sg_implode_attr($inlineAttr).'>'._NL;


	// Patient menu
	$ui = new Ui();
	$dropUi = new Ui();
	if ($psnInfo->info->dischar != 1) {
		$ui->add('<a class="sg-action btn -primary" href="'.url('imed/patient/'.$psnId.'/group.add').'" data-rel="box" data-width="480" data-max-height="80%"><i class="icon -material -white">group_add</i><span>Add to Group</span></a>');
	}
	$dropUi->add('<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/group.in').'" data-rel="box" data-width="480" data-webview="สมาชิกของกลุ่ม"><i class="icon -material">groups</i><span>สมาชิกของกลุ่ม</span></a>');
		$dropUi->add('<sep>');
	if (user_access('administrator imeds')) {
		$isRehab = mydb::select('SELECT * FROM %imed_care% WHERE `pid` = :pid AND `careid` = :careid LIMIT 1', ':pid',$psnId, ':careid',_IMED_CARE_REHAB)->pid;
		if ($isRehab) $dropUi->add('<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/info/rehab.remove').'" data-rel="#imed-app" data-ret="'.url('imed/patient/rehab/'.$psnId).'" data-title="ลบรายชื่อออกจากผู้ป่วยรอการฟื้นฟู" data-confirm="กรุณายืนยัน?"><i class="icon -cancel -gray"></i><span>ลบออกจากกลุ่มผู้ป่วยรอการฟื้นฟู</span></a>');

		$isElder = mydb::select('SELECT * FROM %imed_care% WHERE `pid`=:pid AND `careid`=:careid LIMIT 1', ':pid',$psnId, ':careid',_IMED_CARE_ELDER)->pid;

		if ($isElder) $dropUi->add('<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/info/elder.remove').'" data-rel="#imed-app" data-title="ลบรายชื่อออกจากกลุ่มผู้สูงอายุ" data-confirm="กรุณายืนยัน?" data-ret="'.url('imed/patient/elder/'.$psnId).'"><i class="icon -cancel -gray"></i><span>ลบออกจากกลุ่มผู้สูงอายุ</span></a>');

		$dropUi->add('<sep>');

		$dropUi->add('<a class="sg-action" href="'.url('imed/qt/view/'.$psnId).'" data-rel="box"><i class="icon -material">checklist</i><span>รายการแบบสอบถาม</span></a>');

		if ($psnInfo->info->dischar == 1) {
			$dropUi->add('<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/dead').'" data-rel="notify" title="ยกเลิกบันทึกการเสียชีวิต" data-title="ยกเลิกบันทึกการเสียชีวิต" data-confirm="ผู้ป่วยรายนี้ยังคงมีชีวิตอยู่ กรุณายืนยัน?" data-done="load->replace:#imed-care-individual:'.url('imed/patient/individual/'.$psnId).'"><i class="icon -material">archive</i><span>ยกเลิกบันทึกการเสียชีวิต</span></a>');
		} else {
			$dropUi->add('<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/dead').'" data-rel="notify" title="บันทึกการเสียชีวิต" data-title="บันทึกการเสียชีวิต" data-confirm="ผู้ป่วยรายนี้ยังคงมีชีวิตอยู่ กรุณายืนยัน?" data-done="load->replace:#imed-care-individual:'.url('imed/patient/individual/'.$psnId).'"><i class="icon -material">archive</i><span>บันทึกการเสียชีวิต</span></a>');
		}


		$dropUi->add('<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/info/delete').'" data-rel="notify" data-title="ลบรายชื่อออกจากระบบ" data-confirm="ลบรายชื่อออกจากระบบ กรุณายืนยัน"><i class="icon -delete"></i><span>ลบรายชื่อออกจากระบบ</span></a>');
	}
	$ui->add(sg_dropbox($dropUi->build()));

	$ret .= '<header class="header"><h3>ข้อมูลส่วนบุคคล'.($psnInfo->info->dischar == 1 ? ' (เสียชีวิต)' : '').'</h3><nav class="nav -page -sg-text-right">'.$ui->build().'</nav></header>';

	$tables = new Table();
	$tables->addId('imed-patient-individual');
	//$tables->caption='ข้อมูลส่วนบุคคล';
	if ($isEdit) {
		$tables->rows[] = array(
			'คำนำหน้าชื่อ <a class="sg-info" href="http://th.wiktionary.org/wiki/รายชื่ออักษรย่อในภาษาไทย" target="_blank" 	data-tooltip="คลิกเพื่อดูรายการคำย่อของคำนำหน้าชื่อ">i</a>',
			'<strong>'.imed_model::qt('prename',$qt,$psnInfo->info->prename,$isEdit).'</strong>'
		);

		$tables->rows[] = array(
			'ชื่อ - นามสกุล',
			'<strong>'.imed_model::qt('name',$qt,$psnInfo->info->name.' '.$psnInfo->info->lname,$isEdit).'</strong>'
			.($isEdit?'<a href="'.url('imed/patient/photo/'.$psnId).'" title="คลิกเพื่อเปลี่ยนภาพถ่าย" target="_blank"><img src="'.imed_model::patient_photo($psnId).'" id="patient-info-photo" /></a>':'<a href="javascript:void(0)" title="คลิกดูเปลี่ยนภาพถ่าย" target="_blank"><img src="'.imed_model::patient_photo($psnId).'" id="patient-info-photo" /></a>')
		);

		$tables->rows[] = array(
			'ชื่อเล่น',
			imed_model::qt('nickname',$qt,$psnInfo->info->nickname,$isEdit)
		);

	} else {
		$tables->rows[] = array(
			'ชื่อ-นามสกุล',
			'<strong>'.$psnInfo->info->prename.' '.$psnInfo->info->name.' '.$psnInfo->info->lname.' ('.$psnInfo->info->nickname.')</strong>'
		);
	}

	$tables->rows[] = array(
		'หมายเลขบัตรประชาชน',
		imed_model::qt('cid',$qt,$psnInfo->info->cid,$isEdit)
	);

	$tables->rows[] = array(
		'เพศ',
		imed_model::qt('sex',$qt,$psnInfo->info->sex,$isEdit)
	);

	$tables->rows[] = array(
		'วันเกิด <sup class="sg-tooltip" data-url="'.url('imed').'">?</sup>',
		view::inlineedit(
			array('group'=>'person','fld'=>'birth','ret'=>'date:ว ดดด ปปปป','value'=>$psnInfo->info->birth),
			$psnInfo->info->birth ? sg_date($psnInfo->info->birth,'ว ดดด ปปปป') : null,
			$isEdit
			,'datepicker'
		)
		.($psnInfo->info->birth?' อายุ '.(date('Y')-sg_date($psnInfo->info->birth,'Y')).' ปี':'')
	);

	$tables->rows[] = array(
		'เชื้อชาติ',
		imed_model::qt('PSNL.1.5.1',$qt,$psnInfo->qt,$isEdit)
	);

	$tables->rows[] = array(
		'สัญชาติ',
		imed_model::qt('PSNL.1.5.2',$qt,$psnInfo->qt,$isEdit)
	);

	$tables->rows[] = array(
		'ศาสนา',
		imed_model::qt('PSNL.1.5.3',$qt,$psnInfo->qt,$isEdit)
	);

	$tables->rows[] = array(
		'สถานภาพสมรส',
		imed_model::qt('mstatus',$qt,$psnInfo->info->mstatus,$isEdit,$psnInfo->info->mstatus_desc)
		.imed_model::qt('PSNL.1.6.2',$qt,$psnInfo->qt,$isEdit)
	);

	$tables->rows[] = array(
		'ระดับการศึกษาสูงสุด',
		imed_model::qt('educate',$qt,$psnInfo->info->educate,$isEdit,$psnInfo->info->edu_desc)
		.imed_model::qt('PSNL.EDU.OTHER',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('PSNL.EDU.GRADE',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('PSNL.EDU.DEPART',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('PSNL.EDU.FACULTY',$qt,$psnInfo->qt,$isEdit)
	);
	$tables->rows[] = array(
		'การศึกษาต่อ <sup class="tooltip" title="PSNL.1.8.1">?</sup>',
		imed_model::qt('PSNL.1.8.1',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('PSNL.1.8.2',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('PSNL.1.8.3',$qt,$psnInfo->qt,$isEdit)
	);

	$tables->rows[] = array(
		'ชื่อชุมชน',
		view::inlineedit(
			array(
				'group' => 'person',
				'fld' => 'commune',
				'options' => '{
					class: "-fill",
					autocomplete: {
						minLength: 2,
						query: "'.url('api/commune').'"
					}
				}',
			),
			$psnInfo->info->commune,
			$isEdit,
			'autocomplete'
		)
	);

	$tables->rows[] = array(
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
	);

	$tables->rows[] = array(
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
	);

	$tables->rows[] = array('สถานะของที่พักอาศัย',
		imed_model::qt('OTHR.5.5',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.5.1',$qt,$psnInfo->qt,$isEdit)
	);

	$tables->rows[] = array('สภาพบ้าน',
		imed_model::qt('PSNL.HOUSECONDITION',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('PSNL.HOUSECONDITION.OTHER',$qt,$psnInfo->qt,$isEdit)
	);

	$tables->rows[] = array(
		'โทรศัพท์',
		imed_model::qt('phone',$qt,$psnInfo->info->phone,$isEdit)
	);

	$tables->rows[] = array(
		'อีเมล์',
		imed_model::qt('email',$qt,$psnInfo->info->email,$isEdit)
	);

	$tables->rows[] = array(
		'อาชีพ',
		imed_model::qt('occupa',$qt,$psnInfo->info->occu_desc,$isEdit)
	);

	//$tables->rows[] = array('อาชีพ',view::show_field(array('group'=>'person','fld'=>'occupa','button'=>$button),$psnInfo->info->occu_desc,$isEdit,'select',imed_model::get_category('occupation')));

	$tables->rows[] = array(
		'ความสามารถในการทำงาน',
		imed_model::qt('aptitude',$qt,$psnInfo->info->aptitude,$isEdit)
	);

	$tables->rows[] = array(
		'ความสามารถพิเศษ',
		imed_model::qt('interest',$qt,$psnInfo->info->interest,$isEdit)
	);

	$tables->rows[] = array(
		'หมายเหตุ',
		imed_model::qt('remark',$qt,$psnInfo->info->remark,$isEdit)
	);


	$ret .= $tables->build();

	$ret.='</div>';

	$ret.='<p><small>สร้างโดย '.$psnInfo->info->created_by.' เมื่อ '.sg_date($psnInfo->info->created_date,'ว ดด ปปปป H:i').($psnInfo->info->modify?' แก้ไขล่าสุดโดย '.$psnInfo->info->modify_by.' เมื่อ '.sg_date($psnInfo->info->modify,'ว ดด ปปปป H:i'):'').'</small></p>';
	//		$ret.=print_o($psnInfo,'$psnInfo');
	//$ret.=__set_patient_type($psnId);
	return $ret;
}

function __set_patient_type($psnId) {
	$type=imed_model::get_patient_type($psnId);
	//if (i()->username=='softganz') $ret.=print_o($type,'$type');
	$ret.='<script type="text/javascript">$(document).ready(function() {';
	if ($type['chronic']) $ret.='$(".patient--type--chronic").addClass("active");';
	if ($type['disabled']) $ret.='$(".patient--type--disabled").addClass("active");';
	if ($type['elder']) $ret.='$(".patient--type--elder").addClass("active");';
	$ret.='});</script>';
	return $ret;
}
?>