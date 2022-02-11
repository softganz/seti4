<?php
/**
* Org :: Planning Home Page
* Created 2021-08-10
* Modify  2021-08-11
*
* @param Object $orgInfo
* @return Widget
*
* @usage org/{id}/planning
*/

$debug = true;

import('widget:org.nav.php');
import('model:project.planning.php');

class OrgInfoPlanning extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		$isAdmin = $this->orgInfo->is->orgadmin;
		$isEdit = $isAdmin || $this->orgInfo->RIGHT & _IS_OFFICER;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แผนงาน : '.$this->orgInfo->name,
				'navigator' => new OrgNavWidget($this->orgInfo),
			]), // AppBar
			'children' => [
				new Column([
					'children' => (function() {
						$result = [];
						foreach (ProjectPlanningModel::items(['orgId' => $this->orgId]) as $item) {
							$result[] = new Card([
								'class' => 'sg-action',
								'href' => url('project/planning/'.$item->planningId),
								'children' => [
									new ListTile([
										'title' => $item->title,
										'leading' => '<img class="profile-photo" src="'.model::user_photo($item->username).'" />',
										'trailing' => '<a href=""><i class="icon -material">navigate_next</i></a>',
									]),
								],
							]);
						}
						return $result;
					})(),
				]),
				$isEdit ? new FloatingActionButton([
					'children' => [
						'<a class="sg-action btn -floating -circle" href="'.url('project/planning/new', ['org' => $this->orgId]).'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>สร้างแผนงาน</span></a>'
					],
				]) : NULL,
			],
		]);
	}
}
?>