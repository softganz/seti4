<?php
/**
* Project :: Network Level
* Created 2022-01-29
* Modify  2022-01-29
*
* @return Widget
*
* @usage project/network
*/

import('model:project.follow.php');

class ProjectNetwork extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ระดับเครือข่าย',
				'leading' => '<i class="icon -material">device_hub</i>',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Table([
						'children' => array_map(
							function ($item) {
								return [
									'<a href="'.url('project/'.$item->projectId).'">'.$item->title.'</a>'
								];
							},
							ProjectFollowModel::items(
								[
									'projectType' => 'ชุดโครงการ',
									'ownerType' => _PROJECT_OWNERTYPE_NETWORK
								],
								['debug' => false]
							)->items
						)
					]), // Table
				], // children
			]), // Widget
		]);
	}
}
?>