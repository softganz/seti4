<?php
/**
* Project :: Follow Action Information
* Created 2019-10-25
* Modify  2021-10-27
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.action
*/

import('widget:project.info.appbar.php');

$debug = true;

class ProjectInfoAction extends Page {
	var $projectId;
	var $right;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->right = (Object) [
			'edit' => $this->projectInfo->info->isEdit,
			'add' => $this->projectInfo->info->isEdit || $this->projectInfo->info->membershipType
		];
	}

	function build() {
		if (!$this->projectId) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => new Widget([
				'children' => [
					$this->right->edit ? R::PageWidget('project.action.plan', [$this->projectId]) : NULL,
					'<span id="action-top"></span>',
					// R::Page('project.action.done', NULL, $this->projectId),

					new Widget([
						'children' => array_map(
							function($item) {
								return R::View('project.action.render',$this->projectInfo,$item);
							},
							R::Model('project.action.get',$this->projectId, '{order:"`actionDate` DESC, `actionId` DESC", includePhoto: false}')
						),
					]), // Widget
					$this->right->add && $this->projectInfo->settings->showAddNewActionButton ? new FloatingActionButton([
						'children' => [
							'<a class="sg-action btn -floating" href="'.url('project/'.$this->projectId.'/info/action.post').'" data-rel="box" data-width="640" title="เพิ่มบันทึกกิจกรรมใหม่"><i class="icon -material">add</i><span>เพิ่มกิจกรรมใหม่</span></a>'
						],
					]) : NULL,
				],
			]),
		]);
	}
}
?>