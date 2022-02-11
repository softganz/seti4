<?php
/**
* Project :: Student Status
* Created 2021-11-14
* Modify  2021-11-14
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/proposal/{id}/info.student.status
*/

$debug = true;

import('widget:project.follow.nav.php');
import('model:lms.student.php');

class ProjectInfoStudentStatus extends Page {
	var $projectId;
	var $studentId;
	var $right;
	var $projectInfo;

	function __construct($projectInfo, $studentId) {
		$this->projectId = $projectInfo->projectId;
		$this->studentId = $studentId;
		$this->studentInfo = StudentModel::get($this->studentId);
		$this->projectInfo = $projectInfo;
		$this->right = (Object) ['edit' => $projectInfo->RIGHT & _IS_EDITABLE];
	}

	function build() {
		if (!$this->right->edit) {
			return message(['code' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);
		} else if (!$this->studentInfo) {
			return message(['code' => _HTTP_OK_NO_CONTENT, 'text' => 'ไม่มีข้อมูล']);
		} else if ($this->studentInfo->info->projectId != $this->projectId) {
			return message(['code' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'ไม่อนุญาติให้แก้ไข']);
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->studentInfo->fullname,
				'boxHeader' => true,
			]),
			'body' => new Form([
				'action' => url('project/info.nxt.api/'.$this->projectId.'/student.status/'.$this->studentId),
				'class' => 'sg-form',
				'rel' => 'notify',
				'done' => 'load | close',
				'children' => [
					'status' => [
						'type' => 'radio',
						'options' => [
							'Active' => 'ยังคงศึกษา',
							'Graduate' => 'จบการศึกษา',
							'Retired' => 'ให้ออก',
							'Quit' => 'ลาออก',
						],
						'value' => $this->studentInfo->info->status,
					],
					'save' => [
						'type' => 'button',
						'value' => '<i class="icon -material">done</i><span>{tr:SAVE}</span>',
						'container' => '{class: "-sg-text-right"}',
					],
				], // children
			]), // Container,
		]);
	}
}
?>