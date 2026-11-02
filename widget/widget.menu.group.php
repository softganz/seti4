<?php
/**
 * Widget   :: Menu Group Widget
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2022-09-07
 * Modified :: 2026-09-25
 * Version  :: 9
 *
 * @param Array $args
 *
 * @uses new MenuGroupWidget([])
 */

class MenuGroupWidget extends Widget {
	public $use = '';
	public $menu; // Object
	public $dropbox; // Array('menu' => Array, 'use' => String)
	public $variable; // Object
	public $callFrom; // Object

	function __construct($args = []) {
		parent::__construct($args);
		// debugMsg($this->use, '$this');
	}

	#[\Override]
	function build() {
		$menuItemProperty = (object) [
			'widget' => null,
			'call' => null,
			'condition' => null,
			'access' => null,
			'right' => null,
			'href' => null
		];

		return new Row([
			'children' => (function () use ($menuItemProperty) {
				$childrens = [];

				// Show menu in follow appBar config
				foreach (explode(',', $this->use) as $navKey) {
					$menuItem = (object) array_replace(
						(array) $menuItemProperty,
						(array) $this->menu->{$navKey}
					);
					// Create menu button
					if ($menuItem->widget) {
						// Menu is widget
						try {
							$widget = new $menuItem->widget($this->callFrom);
							foreach ((array) $widget->children as $button) {
								$childrens[] = $button;
							}
						} catch (Throwable $exception) {
							$childrens[] = '?';
						}
					} elseif ($menuItem->call) {
						// Menu is method
						if (
							is_object($this->callFrom) &&
							method_exists($this->callFrom, $menuItem->call)
						) {
							$callButtons = (array) $this->callFrom->{$menuItem->call}();
							foreach ($callButtons as $button) {
								$childrens[] = $button;
							}
						}
					} elseif ($button = $this->renderButton($navKey, $menuItem)) {
						// Menu is button
						$childrens[$navKey] = $button;
					}
				}

				// Show Dropbox menu in follow appBar config
				if ($this->dropbox && $this->dropbox['use']) {
					$childrens['dropbox'] = new Dropbox([
						'children' => (function () use ($menuItemProperty) {
							foreach (explode(',', $this->dropbox['use']) as $navKey) {
								$menuItem = (object) array_replace(
									(array) $menuItemProperty,
									(array) $this->dropbox['menu']->{$navKey}
								);

								if ($button = $this->renderButton($navKey, $menuItem)) {
									$childrens[$navKey] = $button;
								}
							}

							return $childrens;
						})()
					]);

					// Clear dropbox if empty
					foreach (
						(array) $childrens['dropbox']->children
						as $key => $children
					) {
						if (is_null($children)) {
							unset($childrens['dropbox']->children[$key]);
						}
					}

					if (empty($childrens['dropbox']->children)) {
						unset($childrens['dropbox']);
					}
				}

				return $childrens;
			})() // children
		]);
	}

	protected function renderButton($navKey, $menuItem) {
		if (empty($menuItem)) {
			return null;
		}

		$this->setVariable($menuItem);

		// Check right to create menu
		if ($menuItem->right) {
			if (!is_object($this->callFrom)) {
				return null;
			}

			$hasRightToCreateButton = false;

			$rights = explode(',', $menuItem->right);
			foreach ($rights as $right) {
				$right = trim($right);
				if (empty($right)) {
					continue;
				}
				if ($this->callFrom->right->{$right}) {
					$hasRightToCreateButton = true;
					break;
				}
			}
			if (!$hasRightToCreateButton) {
				return null;
			}
		}

		// Check menu condition
		if ($menuItem->condition) {
			try {
				$showCondition = eval('return ' . $menuItem->condition . ';');
			} catch (ParseError $exception) {
				return '?';
			}

			if (!$showCondition) {
				return null;
			}
		}

		// @deprecated
		// Check menu access from RIGHT
		if ($menuItem->access) {
			if (!defined($menuItem->access)) {
				return null;
			} elseif (!($this->variable->RIGHT & constant($menuItem->access))) {
				return null;
			}
		}

		// Build button
		unset($menuItem->condition);

		if ($menuItem->icon) {
			$menuItem->icon = new Icon($menuItem->icon);
		}
		if ($menuItem->href) {
			$menuItem->href = Url::link($menuItem->href);
		}
		if (isset($menuItem->attribute)) {
			$menuItem->attribute = (array) $menuItem->attribute;
		}

		return new Button((array) $menuItem);
	}

	// Replace {{variable[.variable]}} with $this->variable
	private function setVariable(&$menuValue) {
		foreach ($menuValue as $menuItemKey => $menuItemValue) {
			if (!is_string($menuItemValue)) {
				continue;
			}

			$menuValue->{$menuItemKey} = preg_replace_callback(
				'/(\{\{(.*?)\}\})/',
				function ($match) use ($menuItemKey) {
					$matchVar = explode('.', $match[2]);

					if ($menuItemKey === 'condition') {
						return '$this->variable->' . implode('->', $matchVar);
					} else {
						if (count($matchVar) === 1) {
							return $this->variable->{$matchVar[0]};
						} elseif (count($matchVar) === 2) {
							return $this->variable->{$matchVar[0]}->{$matchVar[1]};
						}
					}
				},
				$menuItemValue
			);
		}
	}

	private function setVariablex() {
		// Replace {{variable[.variable]}} with $this->variable
		foreach ($menuItem as $menuItemKey => $menuItemValue) {
			if (!is_string($menuItemValue)) {
				continue;
			}
			$menuItem->{$menuItemKey} = preg_replace_callback(
				'/(\{\{(.*?)\}\})/',
				function ($match) use ($menuItemKey, &$menuItemValue) {
					$matchVar = explode('.', $match[2]);

					if ($menuItemKey === 'condition') {
						return '$this->variable->' . implode('->', $matchVar);
					} else {
						if (count($matchVar) === 1) {
							return $this->variable->{$matchVar[0]};
						} elseif (count($matchVar) === 2) {
							return $this->variable->{$matchVar[0]}->{$matchVar[1]};
						}
					}
				},
				$menuItemValue
			);
		}
	}
}
?>
