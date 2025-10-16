<?php
/**
 * Dashboard :: Dashboard Widget
 * Created   :: 2023-12-16
 * Modify    :: 2023-12-16
 * Version   :: 1
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

	// @override
	function _renderEachChildWidget($key, $widget, $callbackFunction = []) {
		return parent::_renderEachChildWidget(
			$key,
			$widget,
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
					. ($widget->leading ? self::_renderEachChildWidget(NULL, $widget->leading) : '')
					. '<span>'.self::_renderEachChildWidget(NULL, $widget->title).'</span>'
					. ($widget->trailing ? self::_renderEachChildWidget(NULL, $widget->trailing) : '')
					. '</span>' : NULL,
				isset($widget->value) ? '<span class="-value">'.self::_renderEachChildWidget(NULL, $widget->value).'</span>' : NULL,
				$widget->unit ? '<span class="-unit">'.self::_renderEachChildWidget(NULL, $widget->unit).'</span>' : NULL,
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