<?php
/**
* Project :: Show Action Information
* Created 2022-02-07
* Modify  2022-02-07
*
* @param Object $projectId
* @param Int $actionId
* @return Widget
*
* @usage module/{id}/method
*/

import('widget:project.info.appbar.php');

class ProjectActionView extends Page {
	var $projectId;
	var $actionId;
	var $projectInfo;

	function __construct($projectInfo, $actionId = NULL) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->actionId = $actionId;
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		$actionInfo = R::Model('project.action.get', ['projectId' => $this->projectId, 'actionId' => $this->actionId]);

		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => new Widget([
				'children' => [
					'<header class="header -box -hidden"><h3>'.$actionInfo->title.'</h3></header>',
					R::View('project.action.render', $this->projectInfo, $actionInfo),
				], // children
			]), // Widget
		]);
	}
}
?>