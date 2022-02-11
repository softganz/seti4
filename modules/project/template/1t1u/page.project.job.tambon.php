<?php
/**
* Project :: Tambon Level
* Created 2022-01-29
* Modify  2022-01-29
*
* @return Widget
*
* @usage project/tambon
*/

import('model:project.follow.php');

class ProjectJobTambon extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'กิจกรรมตำบล',
				'leading' => '<i class="icon -material">people</i>',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Table([
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
									'projectType' => 'ชุดโครงการ',
									'ownerType' => _PROJECT_OWNERTYPE_TAMBON
								],
								[
									'debug' => false,
									'items' => '*',
								]
							)->items
						)
					]), // Table
				], // children
			]), // Widget
		]);
	}
}
?>