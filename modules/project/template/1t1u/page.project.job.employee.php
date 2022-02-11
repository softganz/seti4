<?php
/**
* Project :: Employee Level
* Created 2022-01-29
* Modify  2022-01-29
*
* @return Widget
*
* @usage project/employee
*/

import('model:project.follow.php');

class ProjectJobEmployee extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'กิจกรรมผู้รับจ้าง',
				'leading' => '<i class="icon -material">groups</i>',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					$this->listEmployee(_PROJECT_OWNERTYPE_GRADUATE),
					$this->listEmployee(_PROJECT_OWNERTYPE_STUDENT),
					$this->listEmployee(_PROJECT_OWNERTYPE_PEOPLE),
				], // children
			]), // Widget
		]);
	}

	function listEmployee($type) {
		return new Table([
			'children' => array_map(
				function ($item) {
					return [
						'<a href="'.url('project/app/follow/'.$item->projectId).'">'.$item->title.'</a>',
						$item->parentTitle,
						$item->project_status
					];
				},
				ProjectFollowModel::items(
					[
						'projectType' => 'โครงการ',
						'ownerType' => $type
					],
					[
						'debug' => false,
						'items' => '*',
					]
				)->items
			)
		]);
	}
}
?>