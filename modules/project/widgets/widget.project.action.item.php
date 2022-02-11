<?php
/**
* iMed Widget :: Render Single Visit Item
* Created 2021-08-20
* Modify  2021-09-04
*
* @param Array $args
* @return Widget
*
* @usage import('model:imed.visit.item')
* @usage new ImedVisitItemWidget([])
*/

$debug = true;

import('widget:project.action.render');

class ProjectActionItemWidget extends Widget {
	var $child;

	function build() {
		return new Card([
			'id' => 'project-action-'.$this->child->actionId,
			'class' => 'ui-item project-action',
			'child' => new ProjectActionRenderWidget(
				$this->child,
				['refApp' => $this->refApp]
			),
		]);
	}
}
?>