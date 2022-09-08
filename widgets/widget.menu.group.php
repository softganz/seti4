<?php
/**
* Widget  :: Menu Group Widget
* Created :: 2022-09-07
* Modify  :: 2022-09-07
* Version :: 1
*
* @param Array $args
* @return Widget
*
* @usage new MenuGroupWidget([])
*/

class MenuGroupWidget extends Widget {
	var $use = '';
	var $menu; // Object
	var $dropbox;
	var $variable; // Object

	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
		// debugMsg($this, '$this');
		return new Row([
			'children' => (function() {
				$childrens = [];

				// Show menu in follow appBar config
				foreach (explode(',', $this->use) as $navKey) {
					$menuItem = $this->menu->{$navKey};

					if ($button = $this->_renderButton($menuItem, $this->variable)) {
						$childrens[$navKey] = $button;
					}
				}

				// Show DropBox menu in follow appBar config
				if ($this->dropbox) {
					$childrens['dropbox'] = new DropBox([
						'children' => (function() {
							foreach (explode(',', $this->dropbox['use']) as $navKey) {
								$menuItem = $this->dropbox['menu']->{$navKey};

								if ($button = $this->_renderButton($menuItem, $this->variable)) {
									$childrens[$navKey] = $button;
								}
							}

							return $childrens;
						})(),
					]);
				}

				return $childrens;
			})(), // children
		]);
	}

	private function _renderButton($menuItem, $info) {
		if (empty($menuItem)) return NULL;
		if ($menuItem->access) {
			if (!defined($menuItem->access)) return NULL;
			else if (!($info->RIGHT & constant($menuItem->access))) return NULL;
		}
		if ($menuItem->icon) $menuItem->icon = new Icon($menuItem->icon);
		if ($menuItem->href) $menuItem->href = url($menuItem->href);
		return new Button($menuItem, $info);
	}

}
?>