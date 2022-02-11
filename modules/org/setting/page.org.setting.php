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

class OrgSetting extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		if (!$this->orgId) return 'PROCESS ERROR';

		$isAdmin = is_admin();
		$isOrgAdmin = $this->orgInfo->is->orgadmin;

		if (!($isAdmin || $isOrgAdmin)) return message('error', 'Access Denied');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Settings :: '.$this->orgInfo->name,
				'navigator' => new OrgNavWidget($this->orgInfo),
				'trailing' => new DropBox(['children' => [
					$isAdmin ? '<a href="'.url('org/'.$this->orgId.'/info.view').'"><i class="icon -material">account_balance</i><span>ข้อมูลองค์กร</span></a>' : NULL,
					$isAdmin ? '<a href="'.url('org/'.$this->orgId.'/setting').'"><i class="icon -material">settings</i><span>Settings</span></a>' : NULL,
				],]),
			]), // AppBar
			'body' => new Column([
				'children' => [
					new ListTile([
						'title' => 'Settings',
					]),
					$isAdmin ? new Container([
						'class' => 'nav -app-menu',
						'children' => [
							'<h3>Home Page</h3>',
							new Ui([
								'children' => [
									'<a class="sg-action" href="'.url('org/'.$this->orgId.'/setting.initcmd').'" data-rel="box"><i class="icon -material">edit</i><span>Init Command</span></a>',
									'<a class="sg-action" href="'.url('org/'.$this->orgId.'/setting.homepage').'" data-rel="box"><i class="icon -material">edit</i><span>Home Page</span></a>',
									'<a class="sg-action" href="'.url('org/'.$this->orgId.'/info.setting.config').'" data-rel="box" data-width="full"><i class="icon -material">settings</i><span>Config</span></a>',
								],
							]), // Ui
						], // children
					]) : NULL, // Container
				],
			]),
		]);
	}
}
?>