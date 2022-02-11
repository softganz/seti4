<?php
/**
* Project :: Set Action Information
* Created 2022-02-03
* Modify  2022-02-03
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/set/{id}/info.action
*/

import('widget:project.info.appbar.php');
import('page:project.info.action.php');

class ProjectSetInfoAction extends ProjectInfoAction {
	function build() {
		$widget = parent::build();
		$widget->appBar = new ProjectInfoAppBarWidget($this->projectInfo);
		return $widget;
	}
}
?>