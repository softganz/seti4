<?php
/**
* Org :: List of Children Organization
* Created 2021-08-12
* Modify  2021-08-13
*
* @param Object $orgInfo
* @return Widget
*
* @usage org/{id}/children
*/

$debug = true;

import('package:org/models/model.org.php');

class OrgChild extends Page {
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

		// if (!($isAdmin || $isOrgAdmin)) return message('error', 'Access Denied');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->orgInfo->name,
			]), // AppBar
			'body' => new Table([
				'children' => (function() {
					$rows = [];
					foreach (OrgModel::items(['childOf' => $this->orgId], '{debug: false}') as $item) {
						$rows[] = [
							'<a href="'.url('org/'.$item->orgId).'">'.$item->name.'</a>',
						];
						$rows[] = '<td colspan="10">'.print_o($item, '$item').'</td>';
					}
					return $rows;
				})(),
				// [
				// 	['AAAAA'],
				// 	[print_o($this->orgInfo)],
				// ],
			]),
		]);
	}
}
?>