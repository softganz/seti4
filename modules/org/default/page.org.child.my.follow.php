<?php
/**
* Module :: Description
* Created 2021-08-01
* Modify  2021-08-01
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

import('widget:org.nav.php');
import('model:project.php');

class OrgChildMyFollow extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		if (!i()->ok) return R::View('signform');

		$isAdmin = $this->orgInfo->is->orgadmin;
		$isEdit = $isAdmin || $this->orgInfo->RIGHT & _IS_OFFICER;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'โครงการของฉัน',
				// 'navigator' => new OrgNavWidget($this->orgInfo),
			]), // AppBar
			'children' => [
				new Table([
					'thead' => ['', 'title -fill' => 'ชื่อโครงการ', 'budget -money -nowrap' => 'งบประมาณ'],
					'rows' => (function() {
						$rows = [];
						foreach (ProjectModel::items(['childOfOrg' => $this->orgId, 'userId' => 'member'], '{debug: false}') as $item) {
							$rows[] = [
								'<img class="profile-photo" src="'.model::user_photo($item->username).'" />',
								'<a href="'.url('project/'.$item->projectId).'">'.$item->title.'</a>',
								number_format($item->budget,2),
							];
						}
						return $rows;
					})(),
				]),
			],
		]);
	}
}
?>