<?php
/**
* Project :: Set Dashboard Information
* Created 2022-02-01
* Modify  2022-02-02
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/set/{id}/info.share
*/

import('widget:project.info.appbar.php');
import('page:project.info.dashboard.php');

class ProjectSetInfoDashboard extends ProjectInfoDashboard {
	function build() {
		$widget = parent::build();
		$widget->appBar = new ProjectInfoAppBarWidget($this->projectInfo);
		return $widget;
	}
}
?>