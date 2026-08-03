<?php
/**
 * Widget   :: InlineEdit
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2023-12-08
 * Modified :: 2026-08-03
 * Version  :: 33
 *
 * @param Array $args
 *
 * @uses new InlineEdit([])
 */

class InlineEdit extends Widget {
	var $widgetName = 'InlineEdit';
	var $tagName = 'div';
	var $version = '0.04';
	var $childTagName = 'span';

	// Parent propoty
	var $id;
	var $class;
	var $editMode = false;
	var $action;
	var $useParentEditClass = false;

	var $children = []; // For multiple edit items
	var $debug = []; // For debug message

	private $editModeClassName = 'sg-inlineedit';
	private $editFieldClassName = 'inlineedit-field';
	private $viewFieldClassName = 'inlineedit-view';

	private static string $camelToDashRegex = '/([A-Z]+)/';
	private static function camelToDash(string $str): string {
		return preg_replace_callback(self::$camelToDashRegex, fn($m) => '-' . strtolower($m[1]), $str);
	}

	function __construct($args = []) {
		parent::__construct($args);

		if ($this->editMode && !$this->useParentEditClass) $this->class .= ' ' . $this->editModeClassName;
		if ($this->editMode && $this->action) $this->attribute['data-action'] = $this->action;
	}

	#[\Override]
	protected function initWidget() {
		parent::initWidget();
		unset($this->child);
		unset($this->mainAxisAlignment, $this->crossAxisAlignment, $this->href, $this->dataUrl, $this->webview);
		unset($this->rel, $this->done);
		unset($this->childContainer, $this->attributeText, $this->config);
	}

	// @override
	protected function renderChildContainerStart($key, $attributes = [], $child = []) {
		if (!is_array($child)) return;

		if (isset($child['widget'])) $child['type'] = 'widget';
		else if (isset($child['method'])) $child['type'] = 'method';
		if (in_array($child['type'], ['widget', 'method'])) {
			$parts = ['<span '];
			if ($child['id']) $parts[] = 'id="' . $child['class'] . '" ';
			$parts[] = 'class="' . ($this->editMode ? $this->editFieldClassName : $this->viewFieldClassName);
			$parts[] = ' -' . $child['type'];
			if ($child['class']) $parts[] = ' ' . $child['class'];
			$parts[] = '">' . _NL;
			return implode('', $parts);
		}

		$attributes['id'] = $child['id'];

		$cls = $this->editMode ? $this->editFieldClassName : $this->viewFieldClassName;
		$cls .= ' -' . $child['type'];
		if ($child['inputName']) $cls .= ' -name-' . self::camelToDash($child['inputName']);
		if ($child['class']) $cls .= ' ' . $child['class'];
		if ($child['inputClass']) $cls .= ' -input-' . $child['inputClass'];
		$attributes['class'] = $cls;

		if ($this->editMode && $child['action']) $attributes['data-action'] = $child['action'];

		$attributes['onClick'] = '';

		if (is_string($key) && empty($child['inputName'])) $attributes['data-input-name'] = $key;

		if (!is_array($child['value'])) {
			$attributes['data-value'] = htmlspecialchars(isset($child['value']) ? $child['value'] : $child['text']);
		} else {
			$attributes['data-value'] = '';
		}

		if ($child['type'] === 'select') {
			$child['data'] = json_encode($this->processChoice(\SG\getFirst($child['choices'], $child['data'])), JSON_UNESCAPED_UNICODE);
			unset($child['choices']);
		} else if ($child['choices']) {
			$child['choices'] = json_encode($child['choices'], JSON_UNESCAPED_UNICODE);
		}

		$options = (Object) \SG\getFirst($child['options']);
		if ($child['placeholder']) $options->placeholder = $child['placeholder'];
		if ($child['onBlur']) $options->onblur = $child['onBlur'];
		if ($child['type'] === 'textarea' && $options->button !== false) $options->button = 'yes';

		$attributes['data-options'] = (Array) $options;

		$childAttribute = $child['attribute'];

		unset(
			$child['id'], $child['class'],
			$child['action'], $child['options'],
			$child['placeholder'], $child['inputClass'],
			$child['editMode'], $child['text'], $child['value'], $child['label'],
			$child['onClick'], $child['onBlur'], $child['attribute'],
			$child['description']
		);

		foreach ($child as $key => $value) {
			$attributes['data-' . self::camelToDash($key)] = $value;
		}

		foreach ($childAttribute as $key => $value) $attributes[$key] = $value;

		if (is_array($this->debug) && in_array('childContainer', $this->debug)) {
			debugMsg('$key = ' . $key);
			debugMsg($attributes, '$attributes');
			debugMsg($child, '$child');
		}
		return parent::renderChildContainerStart($key, $attributes, $child) . _NL;
	}

	// @override
	protected function renderChildContainerEnd($child = [], $key = NULL) {
		if (!is_array($child)) return;

		if (isset($child['widget']) || isset($child['method']) || in_array($child['type'], ['widget', 'method'])) {
			return '</span>';
		}

		return parent::renderChildContainerEnd($child, $key);
	}

	#[\Override]
	protected function renderEachChildWidget($widget, $key = NULL, $callbackFunction = [], $options = []) {
		return parent::renderEachChildWidget(
			$widget,
			$key,
			[
				'array' => function($key, $widget) {
					if (isset($widget['options'])) $widget['options'] = (Object) $widget['options'];
					return $this->renderChildType($key, (Object) $widget);
				},
				'object' => function($key, $widget) {
					while (is_object($widget) && method_exists($widget, 'build')) {
						$widget = $widget->build();
						if (!is_object($widget)) return $widget;
					}
				},
				'text' => function($key, $text) {
					return $text._NL;
				}
			]
		);
	}

	protected function renderChildType($key, $widget = '{}') {
		if (isset($widget->widget)) $widget->type = 'widget';
		if (empty($widget->inputName) && is_string($key)) $widget->inputName = $key;
		$text = \SG\getFirst($widget->value, $widget->text);
		$widget->dataType = \SG\getFirst($widget->dataType, $widget->retType);
		unset($widget->retType);

		$text = $this->formatTextByDataType($widget, $text);

		$ret = '';
		switch ($widget->type) {
			case 'comment': break;
			case 'textfield': $ret .= $this->renderTypeTextField($widget); break;
			case 'radio':
			case 'checkbox':
				$ret .= $this->renderTypeRadio($widget);
				break;
			case 'select': $ret .= $this->renderTypeSelect($widget); break;
			case 'label': $ret .= $this->renderTypeLabel($widget); break;
			case 'widget': $ret .= $this->renderTypeWidget($widget); break;
			case 'method': $ret .= $this->renderTypeMethod($widget); break;
			default: $ret .= $this->renderTypeText($widget, $text); break;
		}

		if (isset($widget->postText)) $ret .= '<span class="-post-text">' . $widget->postText . '</span>';
		if ($widget->description) {
			$ret .= '<div class="-description">';
			$ret .= $this->renderChildren([$widget->description]);
			$ret .= '</div>';
		}

		if (is_array($this->debug) && in_array('rawItem', $this->debug)) {
			$ret .= (new DebugMsg($widget, '$widget'))->build();
		}

		return $ret;
	}

	private function formatTextByDataType($widget, $text) {
		if ((is_null($text) || $text === '') && $this->editMode) {
			return '<span class="placeholder -no-print">'
				. \SG\getFirst($widget->options->placeholder, $widget->placeholder)
				. '</span>';
		}

		return match ($widget->dataType) {
			'nl2br' => trim(nl2br($text)),
			'html' => trim(sg_text2html($text)),
			'text' => trim(str_replace("\n", '<br />', $text)),
			'money' => $text !== '' ? number_format(sg_strip_money($text), 2) : $text,
			default => (preg_match('/^date/i', $widget->dataType) && $text)
				? $this->formatDateText($widget, $text)
				: $text,
		};
	}

	private function formatDateText($widget, $text) {
		$retFormat = 'ว ดดด ปปปป';
		if (str_contains($widget->dataType, ':')) {
			[, $retFormat] = explode(':', $widget->dataType, 2);
		}
		return sg_date($widget->value, $retFormat);
	}

	protected function renderLabel($widget, $postfix = '') {
		if (empty($widget->label)) return;

		$opts = $widget->options;
		$parts = ['<label class="-label'];
		if ($widget->labelClass) $parts[] = ' ' . $widget->labelClass;
		$parts[] = '"';
		if ($widget->labelStyle) $parts[] = ' style="' . $widget->labelStyle . '"';
		$parts[] = ' for=""' . '>';

		if ($opts->numbering) $parts[] = '<span class="-numbering">' . (++$this->numbering) . '.</span>';
		if ($opts->labelPrefix) $parts[] = '<span class="-label-prefix">' . $opts->labelPrefix . '</span>';

		$parts[] = '<span class="-label-text">' . $widget->label . '</span>';

		if ($opts->labelSuffix) $parts[] = '<span class="-label-subfix">' . $opts->labelSuffix . '</span>';
		if ($widget->unit) $parts[] = '<span class="-unit"> (' . $widget->unit . ')</span>';

		$parts[] = '<span class="-postfix">' . $postfix . '</span>';
		$parts[] = '</label>' . _NL;

		return implode('', $parts);
	}

	protected function renderTypeTextField($widget) {
		return $this->renderLabel($widget)
			. (isset($widget->text) ? '<span>' . parent::renderEachChildWidget($widget->text) . '</span>' : '');
	}

	protected function renderTypeLabel($widget) {
		return $this->renderLabel($widget);
	}

	protected function renderTypeText($widget, $text) {
		$childEditMode = $this->editMode || $widget->editMode;

		$ret = $this->renderLabel($widget);

		if ($childEditMode) {
			$ret .= '<span class="-for-input">'
				. ($text === ''
					? '<span class="placeholder -no-print">' . ($widget->options->placeholder ?? '') . '</span>'
					: $text)
				. '</span>' . _NL;
		} else {
			$ret .= '<span class="-for-view">' . $text . '</span>' . _NL;
		}
		return $ret;
	}

	protected function renderTypeSelect($widget) {
		$childEditMode = $this->editMode || $widget->editMode;
		$widget->data = $this->processChoice(\SG\getFirst($widget->choices, $widget->data));

		$ret = $this->renderLabel($widget, ':');

		$placeholder = $widget->options->placeholder ?? null;
		$value = $widget->data[$widget->value] ?? null;
		$text = $value ?? ($placeholder ? '<span class="placeholder -no-print">' . $placeholder . '</span>' : null);

		$ret .= '<span class="' . ($childEditMode ? '-for-input' : '-for-view') . '">' . $text . '</span>' . _NL;
		return $ret;
	}

	private function processChoice($choices) {
		if (is_array($choices) || is_object($choices)) {
			return (Array) $choices;
		}

		if (preg_match('/^\{/', $choices)) {
			return $choices;
		}

		if (preg_match('/^(BC|DC):([0-9a-z]*)\.\.([0-9a-z]*)/i', $choices, $m)) {
			$yearType = $m[1];
			$start = (int) $m[2];
			$end = $m[3] === 'NOW' ? (int) date('Y') : (int) $m[3];
			$offset = $yearType === 'BC' ? 543 : 0;
			$result = [];
			for ($i = $start; $i <= $end; $i++) {
				$result[$i] = $i + $offset;
			}
			return $result;
		}

		if (str_contains($choices, '..')) {
			[$start, $end] = explode('..', $choices, 2);
			$result = range((int) $start, (int) $end);
			return array_combine($result, $result);
		}

		return [];
	}

	protected function renderRadioItem($widget) {
		$valueIsArray = is_array($widget->value);
		$widgetValue = $widget->value;
		$type = $widget->type;
		$inputName = $widget->inputName;

		$parts = [];
		foreach ($widget->choices as $key => $choiceText) {
			if (is_string($choiceText) && $choiceText !== '' && $choiceText[0] === '<') {
				$parts[] = $choiceText;
				continue;
			}

			if (is_object($choiceText)) {
				$choiceText = $choiceText->text;
			}

			$isCheck = $valueIsArray
				? in_array($key, $widgetValue)
				: $key == $widgetValue;

			$parts[] = '<abbr class="' . $type . ' -block">'
				. '<label>'
				. '<input class="-for-input" type="' . $type . '"'
				. ' name="' . $inputName . '"'
				. ' value="' . $key . '"'
				. ($isCheck ? ' checked="checked"' : '')
				. ' />'
				. '<span>' . $choiceText . '</span>'
				. '</label>'
				. '</abbr>';
		}

		return implode('', $parts);
	}

	protected function renderTypeRadio($widget) {
		$childEditMode = $this->editMode || $widget->editMode;
		$items = $this->renderRadioItem($widget);

		return $this->renderLabel($widget, ':')
			. ($childEditMode
				? $items . _NL
				: '<span class="-for-view">' . $items . '</span>' . _NL);
	}

	protected function renderTypeWidget($widget) {
		$ret = $this->renderLabel($widget);
		$ret .= $this->renderEachChildWidget($widget->widget);

		return $ret;
	}

	protected function renderTypeMethod($widget) {
		$ret = $this->renderLabel($widget);
		$ret .= $this->renderEachChildWidget($widget->method);

		return $ret;
	}

	protected function renderNotField() {
		$ret = '<span class="inline-edit-view -' . $this->type
			. ($this->inputClass ? ' ' . $this->inputClass : '')
			. '">';

		$ret .= match ($this->dataType) {
			'html' => trim(sg_text2html($this->text)),
			'text' => trim(str_replace("\n", '<br />', $this->text)),
			default => (str_starts_with((string) $this->dataType, 'date'))
				? ($this->text ? sg_date($this->text, substr($this->dataType, 5)) : '')
				: $this->text,
		};

		$ret .= '</span>';
		return $ret;
	}
} // End of class InlineEdit
?>