<?php
/**
 * Schema   :: Schema Model
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2022-09-23
 * Modified :: 2026-08-30
 * Version  :: 4
 *
 * @param Array $args
 * @return Object
 *
 * @uses new SchemaModel([])
 * @uses SchemaModel::function($conditions, $options)
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

	/**
	 * Add value from data to each input name
	 *
	 * @param array $body
	 * @param object $data
	 * @return array
	 */
	public static function addValue(array &$body, ?object $data): array {
		foreach ($body as $key => $element) {
			if (!is_object($element)) continue;
			if ($element->widget === 'Children') {
				self::addValue($element->children, $data);
				continue;
			}
			if ($element->inputName) {
				$body[$key]->value = self::getNestedValue($data, $element->inputName);
			}
		}

		return $body;
	}

	public static function indicator($schema, $section) {
		foreach ($schema->body as $metrix) {
			foreach ($metrix->items as $metrinItem) {
				foreach ($metrinItem->indicator as $indicator) {
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
					unset($element->options);
					$result[] = $element;
				}
			} else if (is_string($element)) {
			} else {
				$result[] = $element;
			}
		}
		return $result;
	}

	/**
	 * Get nested value from object by dot-separated path
	 *
	 * @param object $data
	 * @param string $path e.g. "2.1" or "var.sub"
	 * @return mixed
	 */
	private static function getNestedValue(?object $data, string $path) {
		$value = $data;
		foreach (explode('.', $path) as $segment) {
			if (!is_object($value) || !isset($value->{$segment})) return null;
			$value = $value->{$segment};
		}
		return $value;
	}
}
?>