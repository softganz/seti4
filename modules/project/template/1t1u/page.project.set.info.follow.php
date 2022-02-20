<?php
/**
* Project :: Set Child Follow
* Created 2022-02-01
* Modify  2022-02-01
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/set/{id}/info.follow
*/

import('model:project.follow.php');
import('widget:project.info.appbar.php');

class ProjectSetInfoFollow extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->right = (Object) [
			'admin' => $this->projectInfo->RIGHT & _IS_ADMIN,
			'edit' => $this->projectInfo->RIGHT & _IS_EDITABLE,
		];
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => $this->projectInfo->info->ischild ? new Container([
				'id' => 'project-child',
				'class' => 'project-child',
				'children' => [
				], // children
			]) : NULL, // Container
		]);
	}
}
?>