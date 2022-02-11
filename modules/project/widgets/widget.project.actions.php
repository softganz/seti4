<?php
/**
* Project Widget :: Project Actions
* Created 2021-09-08
* Modify 	2021-09-08
*
* @param Array $args
* @return Widget
*
* @usage new ProjectActionsWidget([])
*/

$debug = true;

import('widget:project.action.item');

class ProjectActionsWidget extends Widget {
	var $urlMore;
	var $children;

	function build() {
		return new Container([
			'id' => 'project-actions',
			'class' => 'project-actions sg-inline-edit',
			// 'attribute' => ['data-update-url' => url('project/edit/tr')],
			'children' => (function() {
				$widgets = [];
				foreach ($this->children as $item) {
					$widgets[] = new ProjectActionItemWidget([
						'seqId' => $item->seqId,
						'refApp' => $this->refApp,
						'child' => $item,
					]);
				}
				if ($this->urlMore) $widgets[] = '<div id="getmore" style="flex: 1 0 100%;"><a class="sg-action btn -primary" href="'.$this->urlMore.'" data-rel="replace:#getmore" style="margin: 48px 16px 32px; padding: 16px 0; display: block; text-align: center; border-radius: 36px;"><span>มีอีก</span><i class="icon -material">navigate_next</i></a></div>';
				return $widgets;
			})(), // children
		]);
	}
}
?>