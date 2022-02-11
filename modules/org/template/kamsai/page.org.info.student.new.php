<?php
/**
* Org :: New Student Serie Form
* Created 2021-12-05
* Modify  2021-12-05
*
* @param Object $orgInfo
* @return Widget
*
* @usage org/{id}/info.student.new/{serieNo}
*/

import('model:lms.php');
import('widget:org.nav.php');

class OrgInfoStudentNew extends Page {
	var $orgId;
	var $serieNo;
	var $orgInfo;

	function __construct($orgInfo, $serieNo) {
		$this->orgId = $orgInfo->orgId;
		$this->serieNo = $serieNo;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		$serieInfo = LmsModel::getSerie(['orgId' => $this->orgId, 'serieNo' => $this->serieNo]);
		if (!$serieInfo->serieId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลตามที่ระบุ']);

		$mostClassLevel = mydb::select(
			'SELECT student.`classLevel`, COUNT(*) `totalStudent`
			FROM %lms_student% student
			WHERE student.`serieId` = :serieId
			GROUP BY student.`classLevel`
			ORDER BY `totalStudent` DESC
			LIMIT 1',
			[':serieId' => $serieInfo->serieId]
		)->classLevel;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'เพิ่มนักเรียนรุ่น '.$this->serieNo,
				'leading' => _HEADER_BACK,
				'boxHeader' => true,
			]),
			'body' => new Form([
				'class' => 'sg-form',
				'action' => url('lms/serie/api/'.$serieInfo->serieId.'/student.save'),
				'rel' => 'notify',
				'done' => 'load:#main:'.url('org/'.$this->orgId.'/info.student.serie/'.$this->serieNo).' | close',
				'checkValid' => true,
				'children' => [
					'classLevel' => [
						'label' => 'ชั้นเรียน',
						'type' => 'select',
						'class' => '-fill',
						'require' => true,
						'value' => $mostClassLevel,
						'options' => ['' => '==เลือกชั้นเรียน==']
							+ mydb::select(
								'SELECT `qtno`, `question` FROM %qt% WHERE `qtgroup` = "schoolclass" ORDER BY `qtno` ASC;
								-- {key: "qtno", value: "question"}'
							)->items,
					],
					'classNo' => [
						'label' => 'ห้องเรียน',
						'type' => 'select',
						'require' => true,
						'class' => '-fill',
						'options' => '1..20',
					],
					'prename' => [
						'label' => 'คำนำหน้านาม',
						'type' => 'text',
						'class' => '-fill',
						'require' => true,
					],
					'name' => [
						'label' => 'ชื่อ นามสกุล',
						'type' => 'text',
						'class' => '-fill',
						'require' => true,
					],
					'cid' => [
						'label' => 'เลขประจำตัวประชาชน',
						'type' => 'text',
						'class' => '-fill',
						// 'require' => true,
						'maxlength' => 13,
					],
					'birth' => [
						'label' => 'วันเกิด',
						'type' => 'date',
						'require' => true,
						'year' => (Object) ['type' => 'BC', 'range' => (2000).','.(date('Y')-2000).',1'],
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