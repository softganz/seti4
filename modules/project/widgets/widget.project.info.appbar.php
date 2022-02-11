<?php
/**
* Project Widget :: Follow AppBar
* Created 2021-11-23
* Modify 	2021-11-23
*
* @param Object $projectInfo
* @return AppBar
*
* @usage new ProjectInfoAppBarWidget($projectInfo, $options)
*/

import('widget:project.follow.nav.php');

class ProjectInfoAppBarWidget extends AppBar {
	var $class = '-follow';
	var $projectId;
	var $title;
	var $leading;
	var $trailing;
	var $navigator;
	var $projectInfo;

	function __construct($projectInfo, $options = []) {
		parent::__construct();
		$this->projectInfo = $projectInfo;
		$this->projectId = $this->projectInfo->projectId;
		$this->title = $this->projectInfo->title;
		$this->leading = '<i class="icon -material">directions_run</i>';
		$this->trailing = $options['trailing'];
		$this->navigator = new ProjectFollowNavWidget(
			$this->projectInfo,
			['showPrint' => true]
		);
	}
}
?>