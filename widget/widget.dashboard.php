<?php
/**
 * Dashboard :: Dashboard Widget
 * Author  :: Little Bear<softganz@gmail.com>
 * Created   :: 2023-12-16
 * Modify    :: 2026-04-26
 * Version   :: 2
 *
 * @param Array $args
 * @return Widget
 *
 * @usage import('widget:dashboard.php')
 * @usage new DashboardWidget([])
 */

class DashboardWidget extends Widget {
	var $widgetName = 'Dashboard';
	var $tagName = 'div';

	function __construct($args = []) {
		parent::__construct($args);
	}

	#[\Override]
	protected function _renderEachChildWidget($widget, $key = NULL, $callbackFunction = [], $options = []) {
		return parent::_renderEachChildWidget(
			$widget,
			$key,
			[
				'array' => function($key, $widget) {
					return $this->_renderChildType($key, (Object) $widget);
				},
				'text' => function($key, $text) {
					return $text._NL;
				}
			]
		);
	}

	private function _renderChildType($key, $widget = '{}') {
		$widget = (Object) array_replace(
			[
				'class' => NULL, // String
				'title' => NULL, // String
				'leading' => NULL, // String,Widget
				'trailing' => NULL, // String,Widget
				'value' => NULL, // String
				'unit' => NULL, // String
				'chart' => NULL, // Object
			],
			(Array) $widget
		);
		return (new Container([
			'class' => $widget->class,
			'children' => [
				$widget->title ? '<span class="-title">'
					. ($widget->leading ? parent::_renderEachChildWidget($widget->leading) : '')
					. '<span>' . parent::_renderEachChildWidget($widget->title) . '</span>'
					. ($widget->trailing ? parent::_renderEachChildWidget($widget->trailing) : '')
					. '</span>' : NULL,
				isset($widget->value) ? '<span class="-value">' . parent::_renderEachChildWidget($widget->value) . '</span>' : NULL,
				$widget->unit ? '<span class="-unit">' . parent::_renderEachChildWidget($widget->unit) . '</span>' : NULL,
				$widget->chart ? $this->drawChart($widget) : NULL,
			], // children
		]))->build();

		switch ($widget->type) {
			// case 'textfield': $ret .= $this->_renderTypeTextField($text); break;
			// case 'radio':
			// case 'checkbox': $ret .= $this->_renderTypeRadio($widget); break;
			// case 'select': $ret .= $this->_renderTypeSelect($text); break;
			default: $ret .= $this->_renderTypeText($text, $widget); break;
		}

		return $ret;
	}
}
?>