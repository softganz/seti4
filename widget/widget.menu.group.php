<?php
/**
 * Widget  :: Menu Group Widget
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2022-09-07
 * Modify  :: 2026-01-18
 * Version :: 4
 *
 * @param Array $args
 * @return Widget
 *
 * @usage new MenuGroupWidget([])
 */

class MenuGroupWidget extends Widget {
	var $use = '';
	var $menu; // Object
	var $dropbox; // Array('menu' => Array, 'use' => String)
	var $variable; // Object
	var $callFrom; // Object

	function __construct($args = []) {
		parent::__construct($args);
	}

	#[\Override]
	function build() {
		return new Row([
			'children' => (function() {
				$childrens = [];

				// Show menu in follow appBar config
				foreach (explode(',', $this->use) as $navKey) {
					$menuItem = $this->menu->{$navKey};

					// Replace {{ variable }} with $this->variable
					foreach ($menuItem as $menuItemKey => $menuItemValue) {
						if (!is_string($menuItemValue)) continue;
						$menuItem->{$menuItemKey} = preg_replace_callback(
							'/(\{\{(.*?)\}\})/',
							function($match) {
								return $this->variable->{$match[2]};
							},
							$menuItemValue
						);
					}

					// Create menu button
					if ($menuItem->widget) {
						// Menu is widget
						try {
							$widget = new $menuItem->widget($this->callFrom);
							foreach((Array) $widget->children as $button) {
								$childrens[] = $button;
							}
						} catch (Throwable $exception) {
							$childrens[] = '?';
						}
					} else if ($menuItem->call) {
						// Menu is method
						if (is_object($this->callFrom) && method_exists($this->callFrom, $menuItem->call)) {
							$callButtons = (Array) $this->callFrom->{$menuItem->call}();
							foreach($callButtons as $button) {
								$childrens[] = $button;
							}
						}
					} else if ($button = $this->_renderButton($menuItem, $this->variable)) {
						// Menu is button
						$childrens[$navKey] = $button;
					}
				}

				// Show Dropbox menu in follow appBar config
				if ($this->dropbox && $this->dropbox['use']) {
					$childrens['dropbox'] = new Dropbox([
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

					// Clear dropbox if empty
					foreach ((Array) $childrens['dropbox']->children as $key => $children) {
						if (is_null($children)) unset($childrens['dropbox']->children[$key]);
					}

					if (empty($childrens['dropbox']->children)) unset($childrens['dropbox']);
				}

				return $childrens;
			})(), // children
		]);
	}

	private function _renderButton($menuItem, $info) {
		if (empty($menuItem)) return NULL;

		// Check right to create menu
		if ($menuItem->right) {
			if (!is_object($this->callFrom)) return NULL;

			$hasRightToCreateButton = false;

			$rights = explode(',', $menuItem->right);
			foreach ($rights as $right) {
				$right = trim($right);
				if (empty($right)) continue;
				if ($this->callFrom->right->{$right}) {
					$hasRightToCreateButton = true;
					break;
				}
			}
			if (!$hasRightToCreateButton) return NULL;
		} else {
			// @deprecated
			if ($menuItem->access) {
				if (!defined($menuItem->access)) return NULL;
				else if (!($info->RIGHT & constant($menuItem->access))) return NULL;
			}
		}

		foreach ($menuItem as $key => $value) {
			if (!is_string($value)) continue;

			$menuItem->{$key} = preg_replace_callback(
				'/(\{\{(.*?)\}\})/',
				function($match) {
					return $this->variable->{$match[2]};
				},
				$value
			);			
		}

		if ($menuItem->icon) $menuItem->icon = new Icon($menuItem->icon);
		if ($menuItem->href) $menuItem->href = url($menuItem->href);

		return new Button($menuItem, $info);
	}

}
?>