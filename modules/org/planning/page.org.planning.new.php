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

class OrgPlanningNew extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		$isAdmin = $this->orgInfo->is->orgadmin;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สร้างแผนงาน : '.$this->orgInfo->name,
				'navigator' => new OrgNavWidget($this->orgInfo),
			]), // AppBar
			'children' => [
				'<header class="header">'._HEADER_BACK.'<h3>เพิ่มแผนงาน</h3></header>',
				new Form([
					'action' => url('project/planning/create'),
					'class' => 'sg-form',
					'checkValid' => true,
					'children' => [
						'oid' => ['type' => 'hidden', 'value' => $this->orgId],
						'sid' => [
							'type' => 'select',
							'label' => 'แผนงานประเด็น:',
							'class' => '-fill',
							'require' => true,
							'options' => R::Model('category.get', 'project:planning', 'catid', '{selectText: "== เลือกแผนงาน =="}'),
						],
						'yr' => [
							'type' => 'select',
							'label' => 'ประจำปี:',
							'require' => true,
							'options' => (function() {
								$result = ['' => '== เลือกปี =='];
								for ($i=date('Y')-1; $i <= date('Y')+1 ; $i++) $result[$i] = 'พ.ศ.'.($i+543);
								return $result;
							})(),
						],
						'save' => [
							'type' => 'button',
							'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
							'container' => '{class: "-sg-text-right"}',
						]
					],
				]),
			],
		]);
	}
}
?>