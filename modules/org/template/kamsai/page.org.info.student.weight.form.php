<?php
/**
* Org :: Weight & Height Form
* Created 2021-12-05
* Modify  2021-12-06
*
* @param Object $orgInfo
* @return Widget
*
* @usage org/{id}/info.student.weight.form/{studentId}
*/

import('model:lms.php');
import('widget:org.nav.php');

class OrgInfoStudentWeightForm extends Page {
	var $orgId;
	var $studentId;
	var $orgInfo;

	function __construct($orgInfo, $studentId) {
		$this->orgId = $orgInfo->orgId;
		$this->studentId = $studentId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		$studentInfo = LmsModel::getStudent(['orgId' => $this->orgId, 'studentId' => $this->studentId]);
		if (!$studentInfo->studentId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลตามที่ระบุ']);

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
				'title' => 'บันทึกน้ำหนัก-ส่วนสูง :: '.$studentInfo->fullName,
				'leading' => _HEADER_BACK,
				'boxHeader' => true,
			]),
			'body' => new Container([
				'children' => [
					new Form([
						'class' => 'sg-form',
						'action' => url('lms/student/api/'.$this->studentId.'/weight.save'),
						'rel' => 'notify',
						'done' => 'load:#main:'.url('org/'.$this->orgId.'/info.student.serie',['level' => $studentInfo->info->classLevel, 'class'=> $studentInfo->info->classNo]).' | close',
						'checkValid' => true,
						'children' => [
							'classLevel' => ['type' => 'hidden', 'value' => $studentInfo->info->classLevel],
							'date' => [
								'label' => 'วันที่เก็บข้อมูล',
								'type' => 'text',
								'class' => 'sg-datepicker -date -fill',
								'require' => true,
								'value' => date('d/m/Y'),
							],
							'year' => [
								'label' => 'ปีการศึกษา',
								'type' => 'select',
								'require' => true,
								'class' => '-fill',
								'options' => (function() {
									$options = ['' => '==เลือกปีการศึกษา'];
									for ($year=date('Y')+1; $year > 2020 ; $year--) {
										$options[$year] = 'ปีการศึกษา '.($year + 543);
									}
									return $options;
								})(),
							],
							'period' => [
								'label' => 'ภาคการศึกษา',
								'type' => 'select',
								'class' => '-fill',
								'require' => true,
								'options' => ['' => '==เลือกภาคการศึกษา=='] + [
									'1:1' => 'ภาคการศึกษา 1 ต้นเทอม',
									'1:2' => 'ภาคการศึกษา 1 ปลายเทอม',
									'2:1' => 'ภาคการศึกษา 2 ต้นเทอม',
									'2:2' => 'ภาคการศึกษา 2 ปลายเทอม'
								],
							],
							'weight' => [
								'label' => 'น้ำหนัก (กิโลกรัม)',
								'type' => 'text',
								'class' => '-fill',
								'require' => true,
							],
							'height' => [
								'label' => 'ส่วนสูง (เซ็นติเมตร)',
								'type' => 'text',
								'class' => '-fill',
								'require' => true,
							],
							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
								'container' => '{class: "-sg-text-right"}',
							],
						], // children
					]), // Form
					new ScrollView([
						'child' => $this->weightHistory(),
					]),
					// new DebugMsg(mydb()->_query),
				], // children
			]), // Container
		]);
	}

	function weightHistory() {
		return new Table([
			'class' => '-center -nowrap',
			'thead' => [
				'record' => 'วันที่เก็บข้อมูล',
				'year' => 'ปีการศึกษา',
				'term' => 'ภาค',
				'class' => 'ชั้นเรียน',
				'weight' => 'น้ำหนัก',
				'height' => 'ส่วนสูง',
			],
			'children' => array_map(
				function($item) {
					return [
						sg_date($item->recordDate, 'ว ดด ปปปป'),
						$item->year + 543,
						$item->term.':'.$item->period,
						$item->classLevelName,
						$item->weight,
						$item->height,
					];
				},
				mydb::select(
					'SELECT w.*, level.`className` `classLevelName`
					FROM %lms_weight% w
						LEFT JOIN %lms_code_classlevel% level ON level.`classLevel` = w.`classLevel`
					WHERE `studentId` = :studentId
					ORDER BY `recordDate` DESC',
					[':studentId' => $this->studentId]
				)->items
			), // children
		]);
	}
}
?>