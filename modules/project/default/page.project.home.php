<?php
/**
* Project :: Home Page
* Created 2021-01-18
* Modify  2021-01-18
*
* @return Widget
*
* @usage project
*/

class ProjectHome extends Page {
	function build() {
		return R::PageWidget('project.tree');
	}
}
?>