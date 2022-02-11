<?php
/**
* Org :: Student Class Weight
* Created 2021-12-06
* Modify  2021-12-06
*
* @param Object $orgInfo
* @return Widget
*
* @usage org/{id}/info.student.weight
*/

import('model:lms.php');
import('widget:org.nav.php');

class OrgInfoStudentWeight extends Page {
	var $orgId;
	var $classLevel;
	var $classNo;
	var $right;
	var $orgInfo;

	function __construct($orgInfo, $lastTime = NULL) {
		$this->orgId = $orgInfo->orgId;
		$this->classLevel = post('level');
		$this->classNo = post('class');
		$this->times = SG\getFirst(post('times'), $lastTime);
		list($this->year, $this->term, $this->period) = explode(':', $this->times);
		$this->orgInfo = $orgInfo;
		$this->right = (Object) [
			'edit' => $this->orgInfo->RIGHT & _IS_EDITABLE,
			'addWeight' => ($this->orgInfo->RIGHT & _IS_EDITABLE) && $this->classLevel,
		];
	}

	function build() {
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
			'body' => new Container([
				'id' => 'org-info-student-weight',
				'class' => 'org-info-student-weight',
				'children' => [
					new Table([
						'thead' => [
							'no' => '',
							'name -nowrap' => 'ชื่อ นามสกุล',
							'level -center -nowrap' => 'ชั้น',
							'weight -amt -nowrap' => 'น้ำหนัก',
							'height -amt -nowrap' => 'ส่วนสูง',
							''
						],
						'children' => array_map(
							function($item) {
								static $no = 0;
								// debugMsg($item, '$item');
								return [
									++$no,
									$item->preName.$item->name.' '.$item->lname,
									$item->classLevelName.'/'.$item->classNo,
									$this->right->edit ? '<a class="sg-action btn -link" href="'.url('org/'.$this->orgId.'/info.student.weight.form/'.$item->studentId).'" data-rel="box" data-width="480"><i class="icon -material">add</i></a>' : NULL,
									$this->right->edit ? '<a class="sg-action btn -link" href="'.url('org/'.$this->orgId.'/info.student.weight.form/'.$item->studentId).'" data-rel="box" data-width="480"><i class="icon -material">add</i></a>' : NULL,
								];
							},
							LmsModel::getStudentItems([
								'orgId' => $this->orgId,
								'classLevel' => $this->classLevel,
								'classNo' => $this->classNo
							])->items
						),
					]), // Table
					// new DebugMsg($this, '$this'),
				],
			]),
		]);
	}
}
?>