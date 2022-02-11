<?php
/**
* Project Widget :: Project Navigator Widget
* Created 2021-12-13
* Modify 	2021-12-13
*
* @param Array $args
* @return Widget
*
* @usage new AppBarNavWidget([])
*/

$debug = true;

class AppBarNavWidget extends Widget {
	var $configName;
	var $userSigned = false;

	function build() {
		if ($this->userSigned && !i()->ok) return NULL;

		$children = [];

		// Has property config
		if ($this->configName) {
			$children['info'] = new Row([
				'tagName' => 'ul',
				'childTagName' => 'li',
				'class' => '-info',
				'children' => (function() {
					$childrens = [];

					list($module, $navName) = explode('.', $this->configName);
					$navConfig = cfg($module)->{$navName};
					$menuItems = $navConfig->navigator;

					// Show button in follow navigator config
					foreach (explode(',', $navConfig->navigatorUse) as $navKey) {
						$menuItem = $menuItems->{$navKey};
						// if ($menuItem->access) {
						// 	if (!defined($menuItem->access)) continue;
						// 	else if (!($this->projectInfo->RIGHT & constant($menuItem->access))) continue;
						// }
						$childrens[$navKey] = '<a href="'.url($menuItem->url ? $menuItem->url : '').'" title="'.$menuItem->title.'" '.sg_implode_attr($menuItem->attribute).'>'
							. '<i class="icon -material">'.$menuItem->icon.'</i>'
							. '<span>'.$menuItem->label.'</span>'
							. '</a>';
					}
					return $childrens;
				})(), // children
			]);
		}

		// Has property children
		if ($this->children) {
			if (is_array($this->children)) {
				$children[] = new Row([
					'tagName' => 'ul',
					'childTagName' => 'li',
					// 'class' => '-info',
					'children' => $this->children
				]);
			} else if (is_object($this->children)) {
				$children[] = $this->children;
			}
		}
		// debugMsg($children, '$children');
		// debugMsg($this,'$this');
		return new Widget([
			'children' => $children,
		]);
	}
}
?>