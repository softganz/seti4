<?php
/**
* Org :: Student Serie
* Created 2021-12-05
* Modify  2021-12-05
*
* @param Object $orgInfo
* @return Widget
*
* @usage org/{id}/info.student.serie
*/

import('model:lms.php');
import('widget:org.nav.php');

class OrgInfoStudentSerie extends Page {
	var $orgId;
	var $serieNo;
	var $classLevel;
	var $classNo;
	var $right;
	var $orgInfo;

	function __construct($orgInfo, $serieNo = NULL) {
		$this->orgId = $orgInfo->orgId;
		$this->serieNo = $serieNo;
		$this->classLevel = post('level');
		$this->classNo = post('class');
		$this->orgInfo = $orgInfo;
		$this->right = (Object) [
			'edit' => $this->orgInfo->RIGHT & _IS_EDITABLE,
			'addWeight' => ($this->orgInfo->RIGHT & _IS_EDITABLE) && $this->classLevel,
		];
	}

	function build() {
		// if ($this->classNo) {
		// 	$this->serieNo = LmsModel::getSerie(['orgId' => $this->orgId, 'classLevel' => $this->classLevel, 'classNo' => $this->classNo])->serieId;
		// }
		// if (post('class')) {
		// 	$this->serieId = LmsModel::getSerie(['orgId' => $this->orgId, 'classNo' => post('class')])->serieNo;
		// 	debugMsg($this, '$this');
		// }
		$title = 'รายชื่อนักเรียน ';
		if ($this->classLevel) {
			$classLevelName = mydb::select('SELECT `className` FROM %lms_code_classlevel% WHERE `classLevel` = :classLevel LIMIT 1', [':classLevel' => $this->classLevel])->className;
			$title .= $classLevelName.'/'.$this->classNo;
		} else {
			$title .= 'รุ่น '.$this->serieNo;
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'นักเรียนรุ่น : '.$this->orgInfo->name,
				'navigator' => new OrgNavWidget($this->orgInfo),
			]),
			'body' => new Widget([
				'id' => 'org-info-student-serie',
				'class' => 'org-info-student-serie',
				'children' => [
					new ListTile([
						'class' => '-sg-paddingmore',
						'title' => $title,
						'leading' => '<i class="icon -material">groups</i>',
						'trailing' => new Row([
							'children' => [
								'<a class="btn -link"><i class="icon -material">navigate_before</i></a>',
								'<a class="btn -link">ครั้งที่ 1</a>',
								'<a class="btn -link"><i class="icon -material">navigate_next</i></a>',
								'<a class="btn -link"><i class="icon -material">more_time</i><span>รอบใหม่</span></a>',
								new DropBox([
									'children' => [
										$this->right->edit ? '<a><i class="icon -material">arrow_circle_up</i><span>เลื่อนชั้น</span></a>' : NULL,
									],
								]), // DropBox
							], // children
						]), // Row
					]), // ListTile
					$this->classLevel ? $this->listByClass() : $this->listBySerie(),
					$this->serieNo && $this->right->edit ? new FloatingActionButton([
						'children' => [
							'<a class="sg-action btn -floating" href="'.url('org/'.$this->orgId.'/info.student.new/'.$this->serieNo).'" data-rel="box" data-width="480"><i class="icon -material">person_add</i><span>เพิ่มนักเรียน</a>'
						],
					]) : NULL,
				],
			]),
		]);
	}

	function listByClass() {
		$weightRecord = mydb::select(
			'SELECT serie.`orgId`, w.`classLevel`, student.`classNo`, w.`year`, w.`term`, w.`period`
			FROM %lms_serie% serie
				LEFT JOIN %lms_student% student ON student.`serieId` = serie.`serieId`
				LEFT JOIN %lms_weight% w ON w.`studentId` = student.`studentId`
			WHERE serie.`orgId` = :orgId AND student.`classLevel` = :classLevel AND student.`classNo` = :classNo AND w.`studentId` IS NOT NULL
			GROUP BY `year`, `term`, `period`
			ORDER BY `year`, `term`, `period`
			',
			[
				':orgId' => $this->orgId,
				':classLevel' => $this->classLevel,
				':classNo' => $this->classNo,
			]
		);

		$lastRecord = $weightRecord->items ? end($weightRecord->items) : NULL;
		$lastTime = $lastRecord ? $lastRecord->year.':'.$lastRecord->term.':'.$lastRecord->period : NULL;
		// debugMsg($lastRecord, '$lastRecord');
		// debugMsg('$lastTime = '.$lastTime);

		// debugMsg($weightRecord, '$weightRecord');

		return new Container([
			'children' => [
				new Form([
					'class' => 'sg-form form-report',
					'action' => url('org/'.$this->orgId.'/info.student.weight', ['level' => $this->classLevel, 'class' => $this->classNo]),
					'rel' => 'replace:#org-info-student-weight',
					'children' => [
						'times' => [
							'type' => 'select',
							'onChange' => '$(this).closest("form").submit()',
							'style' => 'max-width: none;',
							'options' => (function($items) {
								$options = [];
								foreach ($items as $item) {
									$options[$item->year.':'.$item->term.':'.$item->period] = 'ปีการศึกษา '.($item->year + 543).' ภาคการศึกษา '.$item->term.' ครั้งที่ '.$item->period;
								}
								return $options;
							})($weightRecord->items)
						],
					], // children
				]), // Form
				R::PageWidget('org.info.student.weight', [$this->orgInfo, $lastTime]),
				// new Table([
				// 	'thead' => [
				// 		'no' => '',
				// 		'name -nowrap' => 'ชื่อ นามสกุล',
				// 		'level -center -nowrap' => 'ชั้น',
				// 		'weight -amt -nowrap' => 'น้ำหนัก',
				// 		'height -amt -nowrap' => 'ส่วนสูง',
				// 		''
				// 	],
				// 	'children' => array_map(
				// 		function($item) {
				// 			static $no = 0;
				// 			// debugMsg($item, '$item');
				// 			return [
				// 				++$no,
				// 				$item->preName.$item->name.' '.$item->lname,
				// 				$item->classLevelName.'/'.$item->classNo,
				// 				$this->right->edit ? '<a class="sg-action btn -link" href="'.url('org/'.$this->orgId.'/info.student.weight.form/'.$item->studentId).'" data-rel="box" data-width="480"><i class="icon -material">add</i></a>' : NULL,
				// 				$this->right->edit ? '<a class="sg-action btn -link" href="'.url('org/'.$this->orgId.'/info.student.weight.form/'.$item->studentId).'" data-rel="box" data-width="480"><i class="icon -material">add</i></a>' : NULL,
				// 			];
				// 		},
				// 		LmsModel::getStudentItems([
				// 			'orgId' => $this->orgId,
				// 			'serieNo' => $this->serieNo,
				// 			'classLevel' => $this->classLevel,
				// 			'classNo' => $this->classNo
				// 		])->items
				// 	),
				// ]), // Table
			], // children
		]);
	}

	function listBySerie() {
		return new Table([
			'thead' => [
				'no' => '',
				'name -nowrap' => 'ชื่อ นามสกุล',
				'level -center -nowrap' => 'ชั้น',
				// 'weight -amt -nowrap' => 'น้ำหนัก',
				// 'height -amt -nowrap' => 'ส่วนสูง',
				// ''
			],
			'children' => array_map(
				function($item) {
					static $no = 0;
					// debugMsg($item, '$item');
					return [
						++$no,
						$item->preName.$item->name.' '.$item->lname,
						$item->classLevelName.'/'.$item->classNo,
						// $this->right->edit ? '<a class="sg-action btn -link" href="'.url('org/'.$this->orgId.'/info.student.weight.form/'.$item->studentId).'" data-rel="box" data-width="480"><i class="icon -material">add</i></a>' : NULL,
						// $this->right->edit ? '<a class="sg-action btn -link" href="'.url('org/'.$this->orgId.'/info.student.weight.form/'.$item->studentId).'" data-rel="box" data-width="480"><i class="icon -material">add</i></a>' : NULL,
					];
				},
				LmsModel::getStudentItems([
					'orgId' => $this->orgId,
					'serieNo' => $this->serieNo,
					'classLevel' => $this->classLevel,
					'classNo' => $this->classNo
				])->items
			),
		]);
	}

}
?>