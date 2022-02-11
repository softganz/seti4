<?php
/**
* Org :: Student Dashboard
* Created 2021-12-05
* Modify  2021-12-05
*
* @param Object $orgInfo
* @return Widget
*
* @usage org/{id}/info.student
*/

import('widget:org.nav.php');

class OrgInfoStudentNewSerie extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		if ($serieId) {
			$data = LmsModel::getSerie($serieId)->info;
		} else {
			$data = (Object) [
				'serieNo' => mydb::select('SELECT MAX(`serieNo`) `lastSerie` FROM %lms_serie% WHERE `orgId` = :orgId LIMIT 1', [':orgId' => $this->orgId])->lastSerie + 1,
				'dateStart' => sg_date('Y-m-d'),
			];
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'รุ่นนักเรียน',
				'leading' => _HEADER_BACK,
				'boxHeader' => true,
			]),
			'body' => new Form([
				'id' => 'new-serie',
				'class' => 'sg-form',
				'action' => url('lms/serie/create'),
				'rel' => 'notify',
				'done' => 'reload',
				'children' => [
					'serieId' => ['type' => 'hidden', 'value' => $data->serieId],
					'orgId' => ['type' => 'hidden', 'value' => $this->orgId],
					'serieNo' => [
						'label' => 'รุ่นที่',
						'type' => 'text',
						'class' => '-numeric',
						'readonly' => true,
						'value' => $data->serieNo,
					],
					'dateStart' => [
						'label' => 'วันที่เริ่ม',
						'type' => 'text',
						'class' => 'sg-datepicker -date',
						'require' => true,
						'value' => sg_date($data->dateStart, 'd/m/Y'),
					],
					'save' => [
						'type' => 'button',
						'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
						'container' => '{class: "-sg-text-right"}',
					],
				], // children
			]), // Form
		]);
	}
}
?>