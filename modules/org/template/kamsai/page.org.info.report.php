<?php
/**
* Org :: Report Menu
* Created 2021-12-07
* Modify  2021-12-07
*
* @param Object $orgInfo
* @return Widget
*
* @usage org/{id}/info.report
*/

import('widget:org.nav.php');

class OrgInfoReport extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
		$this->right = (Object) [
			'edit' => $this->orgInfo->RIGHT & _IS_EDITABLE,
		];
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'รายงาน : '.$this->orgInfo->name,
				'navigator' => new OrgNavWidget($this->orgInfo),
			]),
			'body' => new Container([
				'class' => 'project-info -report -sg-paddingnorm',
				'children' => [
					'<a class="btn">รายงานฉบับสมบูรณ์</a>'
				], // children
			]), // Widget
		]);
	}
}
?>