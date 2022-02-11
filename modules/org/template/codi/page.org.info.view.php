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

class OrgInfoView extends Page {
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
				'title' => $this->orgInfo->name,
				'navigator' => new OrgNavWidget($this->orgInfo),
				'trailing' => new DropBox(['children' => [
					$isAdmin ? '<a href="'.url('org/'.$this->orgId.'/setting').'"><i class="icon -material">settings</i><span>Settings</span></a>' : NULL,
				],]),
			]), // AppBar
			'children' => [
				'CODI',
			],
		]);
	}
}
?>