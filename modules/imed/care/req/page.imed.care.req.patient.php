<?php
/**
* iMed :: Request Patient
* Created 2021-08-21
* Modify 	2021-08-21
*
* @param String $arg1
* @return Widget
*
* @usage imed/care/req/{id}/patient
*/

$debug = true;

import('model:imed.patient');
import('model:imed.visit');
import('page:imed.visits');

class ImedCareReqPatient extends Page {
	var $seqId;
	var $keyId;

	function __construct($requestInfo) {
		$this->seqId = $requestInfo->seqId;
		$this->keyId = $requestInfo->keyId;
		$this->requestInfo = $requestInfo;
	}

	function build() {
		$isEdit = is_admin('imed care') || $this->requestInfo->takerId == i()->uid;
		$patientInfo = PatientModel::get($this->requestInfo->psnId);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ข้อมูลผู้ป่วย',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
				// 'navigator' => [
				// 	'<a class="sg-action" href="'.url('imed/care/req/'.$this->keyId.'/patient.add',['new' => 'yes']).'" data-rel="parent:.box-page"><i class="icon -material">person_add_alt</i><span>เพิ่มผู้ป่วย</span></a>',
				// 	'<a class="sg-action" href="'.url('imed/care/req/'.$this->keyId.'/patient.add').'" data-rel="parent:.box-page"><i class="icon -material">contacts</i><span>รายชื่อผู้ป่วย</span></a>',
				// ],
				// 'trailing' => new Row([
				// 	new DropBox([
				// 		'children' => [
				// 		],
				// 	]),
				// ]), // Row
				'navigator' => [
					$isEdit ? '<a class="sg-action" href="'.url('imed/care/req/'.$this->keyId.'/patient.add').'" data-rel="box"><i class="icon -material">contacts</i><span>เปลี่ยนชื่อผู้ป่วย</span></a>' : NULL,
				],
			]),
			'body' => new Container([
				'children' => [
					$this->patientProfileWidget($patientInfo),
					$this->PatientInfoWidget($patientInfo),
					$this->PatientVisitWidget($patientInfo),
					// new Row([
					// 	'class' => 'imed-care-menu -imed-info',
					// 	'children' => [
					// 		'<a class="sg-action" href="'.url('imed/care/req/'.$this->keyId.'/patient.info').'" data-rel="box" data-width="full"><i class="icon -material">badge</i><span>ข้อมูลทั่วไป</span></a>',
					// 		'<a class="sg-action" href="'.url('imed/care/req/'.$this->keyId.'/patient.visit').'" data-rel="box" data-width="full"><i class="icon -material">history</i><span>ประวัติการรักษา</span></a>',
					// 	], // children
					// ]), // Row

					// new DebugMsg($this->requestInfo, '$this->requestInfo'),
					// new DebugMsg($patientInfo, '$patientInfo'),
				], // children
			]), // Container
		]);
	}

	function patientProfileWidget($patientInfo) {
		$psnId = $patientInfo->psnId;
		return new Card([
			'class' => 'imed-patient-photo-wrapper',
			'children' => [
				// Show patient photo
				'<div id="imed-patient-photo" class="-patient-photo">'
					. '<img src="'.imed_model::patient_photo($psnId).'" width="100%" height="100%" />'
					. '</div>',
				// $ret .= '<form class="sg-form -upload" method="post" enctype="multipart/form-data" action="'.url('imed/patient/'.$psnId.'/info/photo.upload').'" data-rel="#imed-patient-photo" x-data-done="load:#main:'.url('imed/app/'.$psnId).'" style="position: absolute; top: 16px; right: calc(50% - 112px); padding: 0; margin: 0; background-color: transparent;"><span class="fileinput-button"><i class="icon -material">add_a_photo</i><input type="file" name="photo" class="inline-upload" accept="image/*;capture=camcorder" onchange=\'$(this).closest(form).submit(); return false;\' /></span></form>',

				'<div class="-sg-text-center"><b>'.$patientInfo->fullname.'</b><br />เพศ '.SG\getFirst($patientInfo->info->sex,'ไม่ระบุ').'<br />',
				'ที่อยู่ '.SG\implode_address($patientInfo->info, 'short'),
				'</div>',
				'</div>',
			],
		]);
	}

	function PatientInfoWidget($patientInfo) {
		return new Container([
			'children' => [
				// Information
				new ListTile([
					'title' => 'ข้อมูลทั่วไป',
					'leading' => '<i class="icon -material">person</i>',
				]), // ListTile
				new Card([
					'style' => 'overflow: hidden',
					'child' => new Table([
						'children' => [
							['ชื่อ-นามสกุล', $patientInfo->fullname],
							['ชื่อเล่น', $patientInfo->info->nickname],
							['อายุ', $patientInfo->info->birth?' อายุ '.(date('Y')-sg_date($patientInfo->info->birth,'Y')).' ปี':''],
							['ที่อยู่', $patientInfo->info->address],
							['โทรศัพท์', $patientInfo->info->phone],
						]
					]), // child
				]), // Card
			], // children
		]);
	}

	function PatientVisitWidget($patientInfo) {
		return new Container([
			'children' => [
				// Visit history
				new ListTile([
					'title' => 'ประวัติการรักษา',
					'leading' => '<i class="icon -material">medical_services</i>',
				]), // ListTile
				new Container([
					'children' => (function() {
						$widgets = [];
						foreach (ImedVisitModel::items(['psnId' => $this->requestInfo->psnId],'{items: 20}')->items as $item) {
							$widgets[] = new Card([
								'children' => [
									new ListTile([
										'title' => $item->ownerName,
										'leading' => '<img class="profile-photo" src="'.model::user_photo($item->username).'" />',
										'subtitle' => 'เมื่อ '.sg_date($item->timedata, 'ว ดด ปปปป'),
									]), // ListTile
									new Container([
										'class' => '-sg-paddingnorm',
										'child' => nl2br($item->visitDetail),
									]), // Container
									// new DebugMsg($item,'$item'),
								],
							]);
						}
						return $widgets;
					})(),
				]), // Container
			], // children
		]);
	}

}
?>