<?php
/**
* Project :: Co-Organization by regian
* Created 2021-09-27
* Modify  2021-09-27
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.org.co
*/

$debug = true;

import('model:project.follow.php');

class ProjectInfoOrgCoZone extends Page {
	var $projectId;
	var $zone;
	var $right;
	var $projectInfo;

	function __construct($projectInfo, $zone = NULL) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->right = $projectInfo->right;
		$this->zone = $zone;
	}

	function build() {
		if (!$this->projectId) return message('error', 'PROCESS ERROR');

		$orgCo = ProjectFollowModel::getOrgCo(['projectId' => $this->projectId, 'zone' => $this->zone]);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'องค์กรร่วม '.$orgCo->count.' องค์กร',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]),
			'body' => new Widget([
				'children' => [
					new Table([
						'thead' => ['no' => '', 'องค์กร', 'จังหวัด'],
						'children' => array_map(function($item) {
							static $no = 0;
							return [
								++$no,
								'<a href="'.url('org/'.$item->orgId).'">'.$item->orgName.'</a>',
								$item->changwatName,
							];
						}, $orgCo->items),
					]),
				], // children
			]), // Widget
		]);
	}
}
?>