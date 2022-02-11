<?php
/**
* Project :: Documents
* Created 2021-11-28
* Modify  2021-11-28
*
* @return Widget
*
* @usage project/docs
*/

class ProjectDocs extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Project Documents',
			]),
			'body' => new Container([
				'children' => [
					'<header class="header"><h3>API</h3></header>',
					new Ui([
						'type' => 'menu',
						'children' => [
							'<a href="'.url('project/docs/api/follow/action').'">กิจกรรมติดตามโครงการ</a>'
						],
					]),
				], // children
			]), // Container
		]);
	}
}
?>