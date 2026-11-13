<?php
/**
 * Dashboard :: Dashboard Widget
 * Author    :: Little Bear<softganz@gmail.com>
 * Created   :: 2023-12-16
 * Modified  :: 2026-11-12
 * Version   :: 4
 *
 * @param Array $args
 *
 * @uses new DashboardWidget([])
 */

class DashboardWidget extends Widget {
	protected string $widgetName = 'Dashboard';
	public $tagName = 'div';

	function __construct($args = []) {
		parent::__construct($args);
	}

	#[\Override]
	protected function renderEachChildWidget(
		$widget,
		$key = null,
		$callbackFunction = [],
		$options = []
	) {
		return parent::renderEachChildWidget($widget, $key, [
			'array' => function ($key, $widget) {
				return $this->renderChildType($key, (object) $widget);
			},
			'text' => function ($key, $text) {
				return $text . _NL;
			}
		]);
	}

	protected function renderChildType($key, $widget = '{}') {
		$widget = (object) array_replace(
			[
				'class' => null, // String
				'title' => null, // String
				'leading' => null, // String,Widget
				'trailing' => null, // String,Widget
				'value' => null, // String
				'unit' => null, // String
				'chart' => null // Object
			],
			(array) $widget
		);
		return new Container([
			'class' => $widget->class,
			'children' => [
				$widget->title
					? '<span class="-title">'
						. ($widget->leading
							? parent::renderEachChildWidget($widget->leading)
							: '')
						. '<span>'
						. parent::renderEachChildWidget($widget->title)
						. '</span>'
						. ($widget->trailing
							? parent::renderEachChildWidget($widget->trailing)
							: '')
						. '</span>'
					: null,
				isset($widget->value)
					? '<span class="-value">'
						. parent::renderEachChildWidget($widget->value)
						. '</span>'
					: null,
				$widget->unit
					? '<span class="-unit">'
						. parent::renderEachChildWidget($widget->unit)
						. '</span>'
					: null,
				$widget->chart ? $this->drawChart($widget) : null
			] // children
		])->build();

		switch ($widget->type) {
			// case 'textfield': $ret .= $this->renderTypeTextField($text); break;
			// case 'radio':
			// case 'checkbox': $ret .= $this->renderTypeRadio($widget); break;
			// case 'select': $ret .= $this->renderTypeSelect($text); break;
			default:
				$ret .= $this->renderTypeText($text, $widget);
				break;
		}

		return $ret;
	}
}
?>
