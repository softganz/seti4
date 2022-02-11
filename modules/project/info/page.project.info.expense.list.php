<?php
/**
* Project :: Action Expense List
* Created 2019-10-24
* Modify  2022-02-07
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.expense.list
*/

import('widget:project.info.appbar.php');

class ProjectInfoExpenseList extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => new Table([
				'thead' => [
					'no'=>'',
					'date'=>'วันที่ทำกิจกรรม<br />(ตามแผน)',
					'กิจกรรมตามแผนที่วางไว้',
					'money'=>'ค่าใช้จ่าย<br />(บาท)',
					''
				],
				'children' => array_map(
					function($actionInfo) {
						static $no = 0;
						$dropbox = new Dropbox([
							'children' => [
								$actionInfo->activityId ? '<a href="'.url('project/'.$this->projectId.'/info.expense/'.$actionInfo->actionId).'">ค่าใช้จ่าย</a>' : NULL,
								'<a href="'.url('project/'.$this->projectId.'/info.join/'.$actionInfo->calid).'">บันทึกผู้เข้าร่วมกิจกรรม</a>',
							], // children
						]);
						return [
							++$no,
							sg_date($actionInfo->actionDate,'ว ดด ปปปป'),
							$actionInfo->title,
							$actionInfo->exp_total?number_format($actionInfo->exp_total,2):'',
							$dropbox->build(),
						];
					},
					R::Model('project.action.get',$this->projectId)
				), // children
			]), // Table
		]);
	}
}
?>