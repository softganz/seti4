<?php
/**
 * Dashboard :: Dashboard Widget
 * Author    :: Little Bear<softganz@gmail.com>
 * Created   :: 2023-12-16
 * Modified  :: 2026-07-29
 * Version   :: 3
 *
 * @param Array $args
 *
 * @uses new DashboardWidget([])
 */

class DashboardWidget extends Widget {
	var $widgetName = 'Dashboard';
	var $tagName = 'div';

	function __construct($args = []) {
		parent::__construct($args);
	}

	#[\Override]
	protected function renderEachChildWidget($widget, $key = NULL, $callbackFunction = [], $options = []) {
		return parent::renderEachChildWidget(
			$widget,
			$key,
			[
				'array' => function($key, $widget) {
					return $this->renderChildType($key, (Object) $widget);
				},
				'text' => function($key, $text) {
					return $text._NL;
				}
			]
		);
	}

	protected function renderChildType($key, $widget = '{}') {
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
					. ($widget->leading ? parent::renderEachChildWidget($widget->leading) : '')
					. '<span>' . parent::renderEachChildWidget($widget->title) . '</span>'
					. ($widget->trailing ? parent::renderEachChildWidget($widget->trailing) : '')
					. '</span>' : NULL,
				isset($widget->value) ? '<span class="-value">' . parent::renderEachChildWidget($widget->value) . '</span>' : NULL,
				$widget->unit ? '<span class="-unit">' . parent::renderEachChildWidget($widget->unit) . '</span>' : NULL,
				$widget->chart ? $this->drawChart($widget) : NULL,
			], // children
		]))->build();

		switch ($widget->type) {
			// case 'textfield': $ret .= $this->renderTypeTextField($text); break;
			// case 'radio':
			// case 'checkbox': $ret .= $this->renderTypeRadio($widget); break;
			// case 'select': $ret .= $this->renderTypeSelect($text); break;
			default: $ret .= $this->renderTypeText($text, $widget); break;
		}

		return $ret;
	}
}
?>