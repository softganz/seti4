<?php
/**
* Project :: My Follow
* Created 2021-12-13
* Modify  2021-12-13
*
* @return Widget
*
* @usage project/my/info/proposal
*/

import('model:project.follow.php');
import('widget:appbar.nav.php');

class ProjectMyFollow extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Follow@'.i()->name,
				'leading' => '<img class="profile-photo" src="'.model::user_photo(i()->username).'" width="32" height="32" />',
				'navigator' => new AppBarNavWidget(['configName' => 'project.my', 'userSigned' => true]),
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