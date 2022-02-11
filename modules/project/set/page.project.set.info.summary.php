<?php
/**
* Project :: Set Summary Information
* Created 2022-02-04
* Modify  2022-02-04
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/set/{id}/info.summary
*/

import('widget:project.info.appbar.php');
import('page:project.info.summary.php');

class ProjectSetInfoSummary extends ProjectInfoSummary {
	function build() {
		$widget = parent::build();
		$widget->appBar = new ProjectInfoAppBarWidget($this->projectInfo);
		return $widget;
	}
}
?>