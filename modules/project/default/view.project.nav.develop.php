<?php
/**
* Project Development Menu
*
* @param Object $devInfo
* @param Object $options
* @return String
*/

function view_project_nav_develop($devInfo = NULL, $options = '{}') {
	return R::View('project.nav.proposal', $devInfo, $options);
}
?>