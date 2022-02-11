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

// import('model:org.nav.php');
import('package:org/models/model.org.php');

class OrgChildMyOrg extends Page {
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
				'title' => 'องค์กรของฉัน : '.$this->orgInfo->name,
				// 'navigator' => new OrgNavWidget($this->orgInfo),
			]), // AppBar
			'children' => [
				new Table([
					'thead' => ['', 'title -fill' => 'ชื่อองค์กร'],
					'rows' => (function() {
						$rows = [];
						foreach (OrgModel::items(['childOf' => $this->orgId, 'userId' => 'member'], '{debug: false}') as $item) {
							$rows[] = [
								'<img class="profile-photo" src="'.model::user_photo($item->username).'" />',
								'<a href="'.url('org/'.$item->orgId).'">'.$item->name.'</a>',
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