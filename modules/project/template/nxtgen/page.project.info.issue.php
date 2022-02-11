<?php
/**
* Project :: Issue Information
* Created 2021-10-26
* Modify  2021-10-26
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.issue
*/

$debug = true;

class ProjectInfoIssue extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		return NULL;
	}
}
?>