<?php
/**
* iMed Widget :: Render All Visit Items
* Created 2021-08-20
* Modify  2021-09-04
*
* @param Array $args
* @return Widget
*
* @usage import('model:imed.visits')
* @usage new ImedVisitsWidget([])
*/

$debug = true;

import('widget:imed.visit.item');

class ImedVisitsWidget extends Widget {
	var $refApp;
	var $urlMore;
	var $children;

	function build() {
		return new Container([
			'id' => 'imed-visits',
			'class' => 'imed-visits sg-inline-edit',
			'attribute' => ['data-update-url' => url('imed/edit/patient'), 'data-debug' => debug('inline')],
			'children' => (function() {
				$widgets = [];
				foreach ($this->children as $item) {
					$widgets[] = new ImedVisitItemWidget([
						'seqId' => $item->seqId,
						'refApp' => $this->refApp,
						'child' => $item,
					]);
				}
				if ($this->urlMore) $widgets[] = '<div id="getmore" class="-noprint" style="flex: 1 0 100%;"><a class="sg-action btn -primary" href="'.$this->urlMore.'" data-rel="replace:#getmore" style="margin: 48px 16px 32px; padding: 16px 0; display: block; text-align: center; border-radius: 36px;"><span>มีอีก</span><i class="icon -material">navigate_next</i></a></div>';
				return $widgets;
			})(), // children
		]);
	}
}
?>