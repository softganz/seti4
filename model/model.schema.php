<?php
/**
* Schema  :: Schema Model
* Created :: 2022-09-23
* Modify 	:: 2022-09-23
* Version :: 2
*
* @param Array $args
* @return Object
*
* @usage new SchemaModel([])
* @usage SchemaModel::function($conditions, $options)
*/

class SchemaModel {
	var $schemaName;

	function __construct($schemaName) {
		$this->schemaName = $schemaName;
		if ($schemaName) {
			foreach(self::get($schemaName) as $key => $value) {
				$this->{$key} = $value;
			}
		}
	}

	public static function get($schemaName) {
		return json_decode(R::Asset($schemaName));
	}

	public static function indicator($section) {
		foreach ($this->body as $metrix) {
			foreach ($metrix->items as $metrinItem) {
				foreach ($metrinItem->indicator as $indicator) {
					// debugMsg($indicator, '$indicator');
					if ($indicator->section == $section) return $indicator;
				}
			}
		}
		return [];
	}

	public static function bodyOnly($body) {
		$result = [];

		foreach ($body as $key => $element) {
			if (is_object($element)) {
				// Create widget
				if ($element->method) continue;
				else if ($element->widget && $element->widget != 'Children') continue;
				else if (in_array($element->type, ['textfield'])) continue;
				else if ($element->widget === 'Children') {
					$result = array_merge($result, self::bodyOnly($element->children));
				} else {
					// debugMsg($element->inputName);
					unset($element->options);
					$result[] = $element;
				}
				// if ($element->widgetName) {
				// 	$widgetName = $element->widget;
				// 	unset($element->widget);
				// 	if ($widgetName === "Children") {
				// 		foreach ($element->children as $childrenKey => $childrenValue) {
				// 			if (is_string($childrenValue) && preg_match('/^</', $childrenValue)) {
				// 				$element->children[$childrenKey] = $childrenValue;
				// 				continue;
				// 			}
				// 			$childrenValue = (Array) $childrenValue;
				// 			if ($childrenValue['type'] === "widget") {
				// 				$element->children[$childrenKey] = new $childrenValue['widget']((Array) $childrenValue);
				// 			} else {
				// 				$childrenValue['value'] = $this->data->{$childrenValue['inputName']};
				// 				$element->children[$childrenKey] = (Array) $childrenValue;
				// 			}
				// 		}
				// 	}
				// 	$schema->body[$key] = new $widgetName((Array) $element);
				// } else {
				// 	unset($schema->body[$key]);
				// }
			} else if (is_string($element)) {
				// unset($schema->body[$key]);
			} else {
			// } else if (is_array($element)) {
			// 	// Get information from $qtCodes
			// 	$elements = [];
			// 	foreach ($element as $subKey => $elementId) {
			// 		// Element start with html tag
			// 		if (preg_match('/^</', $elementId)) {
			// 			$elements[] = $elementId;
			// 			continue;
			// 		}

			// 		$elementParam = SG\getFirst($qtCodes[$elementId]->detail, (Object) []);

			// 		// Extract choice to array
			// 		if ($elementParam->choices) {
			// 			if (is_string($elementParam->choices)) {
			// 				$choices = [];
			// 				foreach (explode(',', $elementParam->choices) as $choiceText) {
			// 					$choices[$choiceText] = $choiceText;
			// 				}
			// 			} else {
			// 				$choices = $elementParam->choices;
			// 			}
			// 			$elementParam->options = (Array) $choices;
			// 			unset($elementParam->choices);
			// 		}
			// 		// unset($elementParam->attribute);
			// 		$elementParam->value = $this->data->{$elementId};

			// 		$elements[$elementId] = (Array) $elementParam;
			// 	}
			// 	// debugMsg($elements, '$elements');
			// 	$schema->body[$key] = new Children([
			// 		'tagName' => 'div',
			// 		'class' => 'widget-card personal-edit',
			// 		'children' => $elements,
			// 	]);
			// } else {
				$result[] = $element;
				// debugMsg(htmlspecialchars($element));
			}
		}
		return $result;
	}
}
?>