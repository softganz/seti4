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

import('widget:org.nav.php');

class OrgInfoFollow extends Page {
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
				'title' => 'โครงการ : '.$this->orgInfo->name,
				'navigator' => new OrgNavWidget($this->orgInfo),
			]), // AppBar
			'children' => [
				new Table([
					'thead' => [
						'',
						'title -fill' => 'ชื่อโครงการ',
						'budget -money -nowrap' => 'งบประมาณ'
					],
					'rows' => (function() {
						$rows = [];
						foreach (R::Model('project.follows', ['orgId' => $this->orgId])->items as $item) {
							$rows[] = [
								'<img class="profile-photo" src="'.model::user_photo($item->username).'" />',
								'<a href="'.url('project/'.$item->projectId).'">'.$item->title.'</a>',
								number_format($item->budget,2),
							];
						}
						return $rows;
					})(),
				]),
				$isEdit ? new FloatingActionButton([
					'children' => [
						'<a class="sg-action btn -floating -circle" href="'.url('project/create/',array('orgid' => $this->orgId)).'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>สร้างโครงการ</span></a>'
					],
				]) : NULL,
			],
		]);
	}
}
?>