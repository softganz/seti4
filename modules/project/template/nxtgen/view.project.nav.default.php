<?php
/**
* Project detail
*
* @param Object $projectInfo
* @param Object $options
* @return String
*/

import('widget:project.follow.nav.php');

function view_project_nav_default($projectInfo, $options = NULL) {
	$nav = new ProjectFollowNavWidget($projectInfo, ['showPrint' => $options->showPrint]);
	return $nav->build()->build();
}
?>