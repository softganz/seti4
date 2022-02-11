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

import('widget:imed.visit.render');

class ImedVisitItemWidget extends Widget {
	var $seqId;
	var $refApp;
	var $child;

	function build() {
		return new Card([
			'id' => 'imed-visit-'.$this->child->seqId,
			'class' => 'ui-item',
			'child' => new ImedVisitRenderWidget(
				$this->child,
				['refApp' => $this->refApp]
			),
		]);
	}
}
?>