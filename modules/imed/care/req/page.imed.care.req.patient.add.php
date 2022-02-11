<?php
/**
* Module :: Description
* Created 2021-08-21
* Modify 	2021-08-21
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

import('model:imed.patient');
import('widget:imed.patient.form.add');

class ImedCareReqPatientAdd extends Page {
	var $keyId;

	function __construct($requestInfo) {
		$this->keyId = $requestInfo->keyId;
	}

	function build() {
		$myPatientList = PatientModel::serviceList(['userId' => i()->uid], '{debug: false, item: "*", order: "CONVERT(p.`name` USING tis620) ASC"}');
		// debugMsg($myPatientList, '$myPatientList');

		$addNewPatient = post('new');
		$addFromList = !$addNewPatient && count($myPatientList) > 0;

		return new Scaffold([
			'appBar' => new AppBar([
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
				'title' => $addFromList ? 'เลือกผู้ป่วย' : 'เพิ่มผู้ป่วย',
				'navigator' => [
					$addFromList ? '<a class="sg-action" href="'.url('imed/care/req/'.$this->keyId.'/patient.add',['new' => 'yes']).'" data-rel="parent:.box-page"><i class="icon -material">person_add_alt</i><span>เพิ่มผู้ป่วย</span></a>' : '<a class="sg-action" href="'.url('imed/care/req/'.$this->keyId.'/patient.add').'" data-rel="parent:.box-page"><i class="icon -material">contacts</i><span>รายชื่อผู้ป่วย</span></a>',
				],
			]),
			'body' => new Widget([
				'children' => [
					// '<header class="header -hidden">'._HEADER_BACK.'<h3>'.($addFromList ? 'เลือกผู้ป่วย' : 'เพิ่มผู้ป่วย').'</h3></header>',
					$addFromList ? $this->_addFromList($myPatientList) : $this->_addNewPatient(),
				], // children
			]), // Widget
		]);
	}

	function _addFromList($myPatientList) {
		return new Container([
			'children' => (function($myPatientList) {
				$result = [];
				foreach ($myPatientList as $item) {
					$result[] = new Card([
						'children' => [
							new ListTile([
								'class' => 'sg-action',
								'crossAxisAlignment' => 'center',
								'href' => url('imed/care/api/req/'.$this->keyId.'/patient.add/'.$item->psnId),
								'rel' => 'none',
								'done' => 'reload',
								'leading' => '<img class="profile-photo" src="'.imed_model::patient_photo($item->psnId).'" width="29" height="29" />',
								'title' => $item->fullname,
								'trailing' => '<i class="icon -material -gray">radio_button_checked</i>',
							]),
						],
					]);
				}
				return $result;
			})($myPatientList), // children
		]);
	}

	function _addNewPatient() {
		return new ImedPatientFormAddWidget([
			'action' => url('imed/care/api/req/'.$this->keyId.'/patient.add'),
			'class' => 'sg-form imed-care-patient-add',
			'rel' => 'none',
			'done' => 'reload',
			'patient' => (Object) ['cid' => '?'],
		]);
	}
}
?>