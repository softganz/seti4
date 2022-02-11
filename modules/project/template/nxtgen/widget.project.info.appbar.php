<?php
/**
* Project Widget :: Follow AppBar
* Created 2021-11-23
* Modify 	2021-12-21
*
* @param Object $projectInfo
* @return AppBar
*
* @usage new ProjectInfoAppBarWidget($projectInfo, $options)
*/

import('widget:project.follow.nav.php');

class ProjectInfoAppBarWidget extends AppBar {
	var $class = '-follow';
	var $projectInfo;

	function __construct($projectInfo, $options = []) {
		parent::__construct();
		$this->projectInfo = $projectInfo;
		$this->title = $this->projectInfo->title.' ('.$this->projectInfo->info->parentTitle.')';
		$this->leading = '<i class="icon -material">directions_run</i>';
		$this->trailing = $options['trailing'];
		$this->navigator = new ProjectFollowNavWidget($this->projectInfo, ['showPrint' => true]);
	}
}
?>