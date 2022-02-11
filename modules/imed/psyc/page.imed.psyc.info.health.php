<?php
/**
* iMed : Patient Health Information
* Created 2021-05-27
* Modify  2021-05-31
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/psyc/{id}/info.health
*/

$debug = true;

class ImedPsycInfoHealth {
	var $patientInfo;

	function __construct($patientInfo) {
		$this->patientInfo = $patientInfo;
	}

	function build() {
		// Data Model
		$psnInfo = $this->patientInfo;
		$psnId = $this->patientInfo->psnId;

		$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
		$isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

		if (!$psnId) return message('error','ไม่มีข้อมูล');
		if (!$isAccess) return message('error',$psnInfo->error);

		include_once 'modules/imed/assets/qt.individual.php';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ข้อมูลสุขภาพทั่วไป - '.$psnInfo->info->fullname,
				'removeOnApp' => true,
			]), // AppBar
			'body' => new Container([
				'id' => 'imed-care-individual',
				'class' => 'imed-qt'.($isEdit ? ' sg-inline-edit' : ''),
				'attribute' => $isEdit ? [
					'data-update-url' => url('imed/edit/patient'),
					'data-psnid' =>  $psnId,
					'data-debug' => debug('inline') ? 'inline' : NULL,
				] : NULL,
				'children' => [
					$this->_addItem('โรคประจำตัว:', imed_model::qt('HLTH.2.4',$qt,$psnInfo->qt,$isEdit)),
					$this->_addItem('โรคประจำตัว:',
						imed_model::qt('โรคประจำตัว-ความดันโลหิตสูง',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('โรคประจำตัว-เบาหวาน',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('โรคประจำตัว-ไขมันในเลือดสูง',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('โรคประจำตัว-โรคอ้วน',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('โรคประจำตัว-โรคลมชัก',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('โรคประจำตัว-พาร์กินสัน',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('โรคประจำตัว-อื่นๆ',$qt,$psnInfo->qt,$isEdit)._NL
					),
					$this->_addItem(
						'สิทธิในการรับการรักษาพยาบาล:',
						imed_model::qt('PSNL.1.10.1',$qt,$psnInfo->qt,$isEdit)
						.imed_model::qt('PSNL.1.10.3',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.'รายละเอียดสิทธิ์ '.'<br />'._NL
						.imed_model::qt('PSNL.1.10.2',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('PSNL.RIGHT.OFFICE',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('PSNL.RIGHT.SOCIALSECURITY.NO',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('PSNL.RIGHT.EMPTY.CAUSE',$qt,$psnInfo->qt,$isEdit)
					),
					$this->_addItem(
						'ประวัติการรักษาพยาบาล:',
						imed_model::qt('HLTH.2.5.1',$qt,$psnInfo->qt,$isEdit)
						.imed_model::qt('HLTH.2.5.1.1',$qt,$psnInfo->qt,$isEdit)
					),
					$this->_addItem(
						'สถานที่รับการรักษา/รับยา:',
						 imed_model::qt('HLTH.2.5.2.1',$qt,$psnInfo->qt,$isEdit).'<br />'
						.imed_model::qt('HLTH.2.5.2.2',$qt,$psnInfo->qt,$isEdit).'<br />'
						.imed_model::qt('HLTH.2.5.2.3',$qt,$psnInfo->qt,$isEdit).'<br />'
						.imed_model::qt('HLTH.2.5.2.4',$qt,$psnInfo->qt,$isEdit).'<br />'
						.imed_model::qt('HLTH.2.5.2.5',$qt,$psnInfo->qt,$isEdit).'<br />'
					),
					$this->_addItem(
						'การรักษาต่อเนื่อง:',
						imed_model::qt('HLTH.2.5.3',$qt,$psnInfo->qt,$isEdit)
					),
					$this->_addItem(
						'ประวัติการแพ้ยา:',
						imed_model::qt('HLTH.2.5.4',$qt,$psnInfo->qt,$isEdit)
						.imed_model::qt('HLTH.2.5.4.1',$qt,$psnInfo->qt,$isEdit)
					),
					$this->_addItem(
						'ประวัติการแพ้อาหาร:',
						imed_model::qt('HLTH.2.5.5',$qt,$psnInfo->qt,$isEdit)
						.imed_model::qt('HLTH.2.5.5.1',$qt,$psnInfo->qt,$isEdit)
					),
					$this->_addItem(
						'สุขอนามัยส่วนตัวคนพิการ',
						imed_model::qt('OTHR.5.8.2',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('OTHR.5.8.2.1',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
					),
					$this->_addItem(
						'สภาพสิ่งแวดล้อมในบ้าน',
						imed_model::qt('OTHR.5.8.3',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('OTHR.5.8.3.1',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
					),
					$this->_addItem(
						'ความปลอดภัยของที่อยู่อาศัย',
						imed_model::qt('HLTH.ความปลอดภัยของที่อยู่อาศัย',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('HLTH.ความปลอดภัยของที่อยู่อาศัย.รายละเอียด',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
					),
					$this->_addItem(
						'ความมั่นคงของที่อยู่อาศัย',
						imed_model::qt('HLTH.ความมั่นคงของที่อยู่อาศัย',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('HLTH.ความมั่นคงของที่อยู่อาศัย.รายละเอียด',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
					),
					$this->_addItem(
						'สิ่งอำนวยความสะดวกให้สามารถดำรงชีวิตในบ้าน',
						imed_model::qt('HLTH.สิ่งอำนวยความสะดวกให้สามารถดำรงชีวิตในบ้าน',$qt,$psnInfo->qt,$isEdit)._NL
						.imed_model::qt('HLTH.สิ่งอำนวยความสะดวกให้สามารถดำรงชีวิตในบ้าน.รายละเอียด',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
					),
					$this->_addItem(
						'การรับบริการด้านสุขภาพ',
						imed_model::qt('OTHR.5.2.5',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('OTHR.5.2.4',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('OTHR.5.2.2',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('OTHR.5.2.3',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('OTHR.5.2.1',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('OTHR.5.2.6',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
						.imed_model::qt('OTHR.5.2.6.VISIT',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
					),
					$this->_addItem(
						'อุปกรณ์ความช่วยเหลือ',
						imed_model::qt('HLTH.PROSTHETIC',$qt,$psnInfo->qt,$isEdit)._NL
					),
					new Card([
						'children' => [
							new ListTile(['title' => 'กายอุปกรณ์',]),
							R::Page('imed.patient.po', NULL, $psnInfo),
							new Row([
								'class' => '-sg-paddingmore',
								'mainAxisAlignment' => 'end',
								'children' => [
									'<a class="sg-action btn" href="'.url('imed/patient/po/'.$psnId.'/add').'" data-rel="box" data-width="480" data-max-height="80%"><i class="icon -material">add_circle_outline</i><span>เพิ่มกายอุปกรณ์</span></a>',
								],
							]),
							'<style type="text/css">.btn-floating.-po-add {display: none;}</style>'
						],
					]),
					$this->_addItem(
						'การฟื้นฟูสมรรถภาพ',
						imed_model::qt('DSBL.3.3',$qt,$psnInfo->qt,$isEdit)
						.imed_model::qt('DSBL.3.3.1',$qt,$psnInfo->qt,$isEdit)
					),
					$this->_addItem(
						'ปัญหาด้านสุขภาพที่ต้องการให้แก้ไข',
						imed_model::qt('problem',$qt,$psnInfo->disabled->problem,$isEdit).'<br />'.'กรุณาระบุรายการละ 1 บรรทัด'
					),
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
}
?>