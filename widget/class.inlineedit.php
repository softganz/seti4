<?php
/**
 * Widget  :: InlineEdit
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2023-12-08
 * Modify  :: 2026-01-31
 * Version :: 24
 *
 * @param Array $args
 * @return Widget
 *
 * @usage import('widget:class.inlineedit.php')
 * @usage new InlineEdit([])
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

	function __construct($args = []) {
		parent::__construct($args);

		if ($this->editMode && !$this->useParentEditClass) $this->class .= ' '.$this->editModeClassName;
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
	function _renderChildContainerStart($childKey, $attributes = [], $child = []) {
		if (!is_array($child)) return;

		if (isset($child['widget'])) $child['type'] = 'widget';
		else if (isset($child['method'])) $child['type'] = 'method';
		if (in_array($child['type'], ['widget', 'method'])) {
			return '<span '
				. ($child['id'] ? 'id="'.$child['class'].'" ' : '')
				. 'class="'.($this->editMode ? $this->editFieldClassName : $this->viewFieldClassName)
					.' -'.$child['type']
					.($child['class'] ? ' '.$child['class'] : '')
				. '">'._NL;
		}

		$attributes['id'] = $child['id'];
		if ($this->editMode) {
			$attributes['class'] = $this->editFieldClassName;
			if ($child['action']) $attributes['data-action'] = $child['action'];
		} else {
			$attributes['class'] = $this->viewFieldClassName;
		}
		$attributes['class'] .= ' -'.$child['type'];
		if ($child['inputName']) $attributes['class'] .= ' -name-'.preg_replace_callback('/([A-Z])/', function($matches) {return '-'.strtolower($matches[1]);}, $child['inputName']);

		if ($child['class']) $attributes['class'] .= ' '.$child['class'];
		if ($child['inputClass']) $attributes['class'] .= ' -input-'.$child['inputClass'];

		$attributes['class'] = trim($attributes['class']);


		$attributes['onClick'] = '';

		if (is_string($childKey) && empty($child['inputName'])) $attributes['data-input-name'] = $childKey;

		if (!is_array($child['value'])) {
			$attributes['data-value'] = htmlspecialchars(isset($child['value']) ? $child['value'] : $child['text']);
		} else {
			$attributes['data-value'] = '';
		}

		if ($child['type'] === 'select') {
			$child['data'] = json_encode($this->processChoice(SG\getFirst($child['choices'], $child['data'])), JSON_UNESCAPED_UNICODE);
			unset($child['choices']);
		} else if ($child['choices']) {
			$child['choices'] = json_encode($child['choices'], JSON_UNESCAPED_UNICODE);
		}

		$options = (Object) SG\getFirst($child['options']);
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
			$child['description'], $child['postText']
		);

		foreach ($child as $key => $value) {
			$key = preg_replace_callback('/([A-Z]+)/', function ($word) {return '-'.strtolower($word[1]);}, $key);

			$attributes['data-'.$key] = $value;
		}

		foreach ($childAttribute as $key => $value) $attributes[$key] = $value;

		// debugMsg('$childKey = '.$childKey); debugMsg($attributes, '$attributes'); debugMsg($child, '$child');

		if (is_array($this->debug) && in_array('childContainer', $this->debug)) {
			debugMsg('$childKey = '.$childKey);
			debugMsg($attributes, '$attributes');
			debugMsg($child, '$child');
		}
		return parent::_renderChildContainerStart($childKey, $attributes, $child);
	}

	// @override
	function _renderChildContainerEnd($childKey = NULL, $child = []) {
		if (!is_array($child)) return;

		if (isset($child['widget']) || isset($child['method']) || in_array($child['type'], ['widget', 'method'])) return '</span>';

		return parent::_renderChildContainerEnd($childKey, $child);
	}

	// @override
	function _renderEachChildWidget($key, $widget, $callbackFunction = []) {
		return parent::_renderEachChildWidget($key, $widget, [
			'array' => function($key, $widget) {
				if (isset($widget['options'])) $widget['options'] = (Object) $widget['options'];
				return $this->_renderChildType($key, (Object) $widget);
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
		]);
	}

	private function _renderChildType($key, $widget = '{}') {
		if (isset($widget->widget)) $widget->type = 'widget';
		if (empty($widget->inputName) && is_string($key)) $widget->inputName = $key;
		$text = SG\getFirst($widget->value, $widget->text);
		$widget->dataType = SG\getFirst($widget->dataType, $widget->retType);
		unset($widget->retType);

		if ((is_null($text) || $text == '') && $this->editMode) $text = '<span class="placeholder -no-print">'.SG\getFirst($widget->options->placeholder, $widget->placeholder).'</span>';
		else if ($widget->dataType === 'nl2br') $text = trim(nl2br($text));
		else if ($widget->dataType === 'html') $text = trim(sg_text2html($text));
		else if ($widget->dataType === 'text') $text = trim(str_replace("\n",'<br />',$text));
		else if ($widget->dataType === 'money' && $text != '') $text = number_format(sg_strip_money($text), 2);
		else if (preg_match('/^date/i', $widget->dataType) && $text) {
			list($widget->dataType, $retFormat) = explode(':', $widget->dataType);
			if (!$retFormat) $retFormat = 'ว ดดด ปปปป';
			$text = sg_date($widget->value, $retFormat);
		}

		switch ($widget->type) {
			case "comment": break;
			case 'textfield': $ret .= $this->_renderTypeTextField($widget); break;
			case 'radio':
			case 'checkbox':
				$ret .= $this->_renderTypeRadio($widget);
				break;
			case 'select': $ret .= $this->_renderTypeSelect($widget); break;
			case 'label': $ret .= $this->_renderTypeLabel($widget); break;
			case 'widget': $ret .= $this->_renderTypeWidget($widget); break;
			case 'method': $ret .= $this->_renderTypeMethod($widget); break;
			default: $ret .= $this->_renderTypeText($widget, $text); break;
		}

		if ($widget->description) {
			$ret .= '<div class="-description">';
				$ret .= $this->_renderChildren([$widget->description]);
			$ret .= '</div>';
		}

		if (is_array($this->debug) && in_array('rawItem', $this->debug)) {
			$ret .= (new DebugMsg($widget, '$widget'))->build();
		}

		return $ret;
	}

	private function _renderLabel($widget) {
		if (empty($widget->label)) return;

		return '<label class="-label'
			. ($widget->labelClass ? ' '.$widget->labelClass : '')
			. '"'
			. ($widget->labelStyle ? ' style="'.$widget->labelStyle.'"' : '')
			. ' for=""'
			. '>'
			. ($widget->options->numbering ? '<span class="-numbering">'.(++$this->numbering).'.</san>' : '')
			. $widget->label
			. ($widget->options->labelSubfix ? '<span class="-label-subfix">'.$widget->options->labelSubfix.'</span>' : '')
			. ($widget->unit ? ' ('.$widget->unit.')' : '')
			. '</label>'._NL;
	}

	function _renderTypeTextField($widget) {
		return $this->_renderLabel($widget)
			. (isset($widget->text) ? '<span>'.self::_renderEachChildWidget(NULL, $widget->text).'</span>' : '');
	}

	function _renderTypeLabel($widget) {
		return $this->_renderLabel($widget);
	}

	function _renderTypeText($widget, $text) {
		$childEditMode = $this->editMode || $widget->editMode;

		list($type, $format) = explode(':', $widget->dataType);

		switch ($type) {
			case 'numeric':
				if (is_null($text)) break;
				// $text = preg_replace('/[^0-9\.]/', '', $text);
				// $text = number_format(floatval($text));
				break;
		}

		$ret = '';
		// $ret .= $text.'<hr>';
		// $ret .= (new DebugMsg($widget, '$widget'))->build();

		$ret .= $this->_renderLabel($widget);

		if ($childEditMode) {
			$ret .= '<span class="-for-input">'.($text == '' ? '<span class="placeholder -no-print">'.$widget->options->placeholder.'</span>' : $text).'</span>'._NL;
		} else {
			$ret .= '<span class="-for-view">'.$text.'</span>'._NL;
		}
		// if ($widget->description) $ret .= '<div class="-description">'.$widget->description.'</div>';
		return $ret;
	}

	function _renderTypeSelect($widget) {
		$childEditMode = $this->editMode || $widget->editMode;
		$widget->data = $this->processChoice(SG\getFirst($widget->choices, $widget->data));

		$ret = '';
		$ret .= $this->_renderLabel($widget, ':');

		$text = SG\getFirst($widget->data[$widget->value], $widget->options->placeholder ? '<span class="placeholder -no-print">'.$widget->options->placeholder.'</span>' : NULL);

		if ($childEditMode) {
			$ret .= '<span class="-for-input">'.$text.'</span>'._NL;
		} else {
			$ret .= '<span class="-for-view">'.$text.'</span>'._NL;
		}
		return $ret;
	}

	private function processChoice($choices) {
		$result = [];
		if (is_array($choices) || is_object($choices)) {
			$result = (Array) $choices;
		} else if (preg_match('/^\{/', $choices)) {
			$result = $choices;
		} else if (preg_match('/^BC|DC\:/', $choices, $out)) {
			preg_match('/^(BC|DC)\:([0-9a-z]*)(\.\.)([0-9a-z]*)(.*)/i', $choices, $out);
			$yearType = $out[1];
			$start = $out[2];
			$end = $out[4];
			$direction = $out[5];

			if ($end === 'NOW') $end = date('Y');
			// debugMsg($out, '$out');
			for ($choice = $start; $choice <= $end; $choice++) {
				$result[$choice] = $yearType === 'BC' ? $choice + 543 : $choice;
			}
			// debugMsg($result, '$result');
		} else if (preg_match('/\.\./', $choices)) {
			list($start, $end) = explode('..', $choices);
			for ($choice = $start; $choice <= $end; $choice++) {
				$result[$choice] = $choice;
			}
		}
		return $result;
	}

	private function _renderRadioItem($widget) {
		$ret = '';

		foreach($widget->choices as $key => $choiceText) {
			$isCheck = NULL;
			$childrens = NULL;

			if (is_string($choiceText) && preg_match('/^</', $choiceText)) {
				$ret .= $choiceText;
				continue;
			} else if (is_object($choiceText)) {
				$childrens = $choiceText;
				$choiceText = $choiceText->text;
				// && isset($choiceText->children);
			}

			if (is_array($widget->value)) {
				$isCheck = in_array($key, $widget->value);
			} else {
				$isCheck = $key == $widget->value;
			}

			$ret .= '<abbr class="'.$widget->type.' -block">'
				. '<label>'
				. '<input class="-for-input" type="'.$widget->type.'"'
				. ' name="'.$widget->inputName.'"'
				. ' value="'.$key.'"'
				. ($isCheck ? ' checked="checked"' : '')
				. ' />'
				. '<span>'.$choiceText.'</span>'
				. '</label>'
				. '</abbr>';
		}

		return $ret;
	}

	function _renderTypeRadio($widget) {
		$childEditMode = $this->editMode || $widget->editMode;

		$ret = $this->_renderLabel($widget, ':');

		if ($childEditMode) {
			$ret .= $this->_renderRadioItem($widget)._NL;
		} else {
			$ret .= '<span class="-for-view">'.$this->_renderRadioItem($widget).'</span>'._NL;
		}

		return $ret;
	}

	function _renderTypeWidget($widget) {
		$ret = $this->_renderLabel($widget);
		$ret .= $this->_renderEachChildWidget(NULL, $widget->widget);

		return $ret;
	}

	function _renderTypeMethod($widget) {
		$ret = $this->_renderLabel($widget);
		$ret .= $this->_renderEachChildWidget(NULL, $widget->method);

		return $ret;
	}

	function _renderNotField() {
		$ret = '';
		if (is_object($this) && method_exists($this, 'build')) {
		} else {
			$ret .= '<span class="inline-edit-view '
				.'-'.$this->type
				.($this->inputClass ? ' '.$this->inputClass : '').'" '
				.'>';
			if ($this->dataType === 'html') {
				$ret .= trim(sg_text2html($this->text));
			} else if ($this->dataType === 'text') {
				$ret .= trim(str_replace("\n", '<br />', $this->text));
			} else if ($input_type == "money") {
				$ret .= number_format(sg_strip_money($this->text), 2);
			} else if (in_array($input_type, array('radio', 'checkbox'))) {
				list($choice, $label, $info) = explode(':', $this->text);
				$choice = trim($choice);
				$name = getFirst($fld['name'],$fld['fld']);
				if ($label == '' && strpos($this->text, ':') == false) $label = $choice;
				$label = trim($label);
				$ret .= '<input type="'.$input_type.'" '
					.($fld['value'] == $choice ? 'checked="checked" readonly="readonly" disabled="disabled"' : 'disabled="disabled"')
					.' style="margin:0;margin-top: -1px; display:inline-block;min-width: 1em; vertical-align: middle;" /> '
					.$label;
			} else if (substr($this->dataType, 0, 4) == 'date') {
				$format = substr($this->dataType, 5);
				$ret .= $this->text ? sg_date($this->text, $format) : '';
			} else {
				$ret .= $this->text;
			}
			$ret .= '</span>';
		}
		return $ret;
	}
} // End of class InlineEdit
?>