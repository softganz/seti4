<?php
/**
* Project :: Planning Summary
* Created 2020-06-20
* Modify  2020-06-20
*
* @param Object $self
* @return String
*/

$debug = true;

class ProjectPlanningHome extends Page {
	function build() {
		return R::Page('project.planning.summary');
	}
}
?>