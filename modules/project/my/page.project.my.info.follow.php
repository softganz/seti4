<?php
/**
* Project :: My Follow Information
* Created 2021-12-13
* Modify  2021-12-13
*
* @return Widget
*
* @usage project/my/info/proposal
*/

import('model:project.follow.php');

class ProjectMyInfoFollow extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Follow',
			]), // AppBar
			'body' => new Container([
				'children' => [
					new ScrollView([
						'child' => new Table([
							'thead' => [
								'',
								'title -fill' => 'ชื่อโครงการ',
								'budget -money -nowrap' => 'งบประมาณ',
								'status -center -nowrap' => 'สถานะ',
							],
							'children' => (function() {
								$rows = [];
								foreach (ProjectFollowModel::items(['userId' => 'member'])->items as $item) {
									$rows[] = [
										'<img class="profile-photo -sg-24" src="'.model::user_photo($item->username).'" />',
										'<a href="'.url('project/'.$item->projectId).'">'.SG\getFirst($item->title, '???').'</a>',
										number_format($item->budget,2),
										$item->project_status,
									];
								}
								return $rows;
							})(),
						]), // Table
					]), // ScrollView
				], // children
			]), // Container
		]);
	}
}
?>