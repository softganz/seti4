<?php
/**
* Widget  :: InlineEdit
* Created :: 2023-12-08
* Modify  :: 2024-05-31
* Version :: 4
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
	var $version = '0.03';
	var $childTagName = 'span';

	// Parent propoty
	var $class;
	var $editMode = false;
	var $action;
	var $updateUrl;
	var $useParentEditClass = false;

	// Child propoty
	var $type = 'text';
	var $text;
	var $value;
	var $label;
	var $group;
	var $field;
	var $tranId;
	var $retType;
	var $inputClass = NULL;
	var $inputName;
	var $title = 'คลิกเพื่อแก้ไข';
	var $placeholder = '...';
	var $onBlur;
	var $selectOptions = [];
	var $options = []; // debug,place
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

	// @override
	function _renderChildContainerStart($childKey, $attributes = [], $child = []) {
		if (!is_array($child)) return;
		// debugMsg($childKey, '$childKey');

		if ($this->editMode) {
			$attributes['class'] = $this->editFieldClassName;
			if ($child['action']) $attributes['data-action'] = $child['action'];
		} else {
			$attributes['class'] = $this->viewFieldClassName;
		}
		$attributes['class'] .= ' -'.$child['type'];
		if ($child['class']) $attributes['class'] .= ' '.$child['class'];
		if ($child['inputClass']) $attributes['class'] .= ' -input-'.$child['inputClass'];

		$attributes['class'] = trim($attributes['class']);

		// if ($child['type']) $attributes['data-type'] = $child['type'];

		$attributes['onClick'] = '';

		if (is_string($childKey) && empty($child['inputName'])) $attributes['data-input-name'] = $childKey;
		// if ($child['inputName']) $attributes['data-name'] = $child['inputName'];
		// 	. ($child->group ? ' data-group="'.$child->group.'"'._NL : '')
		// 	. ($child->field ? ' data-fld="'.$child->field.'"'._NL : '')

		// 	. ' class="inlineedit-field inline-edit-field -'.$child->type.($child->inputClass ? ' '.$child->inputClass : '').'"'._NL
		// 	. ' data-tr="'.$child->tranId.'"'._NL
		// 	. ($child->retType ? ' data-ret="'.$child->retType.'"'._NL : '')
		// 	. ' data-value="'.htmlspecialchars(SG\getFirst($child->value, $child->text)).'"'._NL
		// 	. ($selectOptions ? ' data-data="'.htmlspecialchars(\json_encode($selectOptions)).'"' : '')
		// 	. ' title="'.$child->title.'"'._NL
		// 	. ($child->attribute && is_array($child->attribute) ? ' '.sg_implode_attr($child->attribute)._NL : '')
		// 	. ($options ? ' data-options=\''.json_encode($options).'\''._NL : '')

		if (!is_array($child['value'])) {
			$attributes['data-value'] = htmlspecialchars(isset($child['value']) ? $child['value'] : $child['text']);
		} else {
			$attributes['data-value'] = '';
		}

		$options = SG\getFirst($child['choices'], $child['options']);
		if ($child['placeholder']) $options['placeholder'] = $child['placeholder'];
		if ($child['onBlur']) $options['onblur'] = $child['onBlur'];
		if ($child['type'] === 'textarea' && $options['button'] !== false) $options['button'] = 'yes';

		$attributes['data-options'] = $options;

		$childAttribute = $child['attribute'];

		// if ($child['type'] === 'radio') {
		// 	$this->childTagName = 'input';
		// 	$attributes['type'] = 'radio';
		// }

		unset(
			$child['action'], $child['class'], $child['options'],
			$child['placeholder'], $child['inputClass'],
			$child['editMode'], $child['text'], $child['value'], $child['label'],
			$child['onClick'], $child['onBlur'], $child['attribute']
		);

		foreach ($child as $key => $value) {
			$key = preg_replace_callback('/([A-Z]+)/', function ($word) {return '-'.strtolower($word[1]);}, $key);

			$attributes['data-'.$key] = $value;
		}

		foreach ($childAttribute as $key => $value) $attributes[$key] = $value;

		// debugMsg('$childKey = '.$childKey); debugMsg($attributes, '$attributes'); debugMsg($child, '$child');

		if (in_array('childContainer', $this->debug)) {
			debugMsg('$childKey = '.$childKey);
			debugMsg($attributes, '$attributes');
			debugMsg($child, '$child');
		}
		return parent::_renderChildContainerStart($childKey, $attributes, $child);
	}

	// @override
	function _renderChildContainerEnd($childKey = NULL, $child = []) {
		if (!is_array($child)) return;
		return parent::_renderChildContainerEnd($childKey, $child);
	}

	// @override
	function _renderEachChildWidget($key, $widget, $callbackFunction = []) {
		return parent::_renderEachChildWidget($key, $widget, [
			'array' => function($key, $widget) {
				return $this->_renderChildType($key, (Object) $widget); //'<div>RENDER ARRAY '.$key.$widget['label'].'<div>'._NL;
			},
			// 'object' => function($key, $widget) {
			// 	debugMsg('RENDER OBJECT '.$key);
			// 	debugMsg($widget, '$widget');
			// },
			'text' => function($key, $text) {
				return $text._NL;
			}
		]);
	}

	private function _renderChildType($key, $widget = '{}') {
		if (empty($widget->inputName) && is_string($key)) $widget->inputName = $key;
		$text = SG\getFirst($widget->value, $widget->text);
		if (is_null($text) || $text == '') $text = '<span class="placeholder -no-print">'.$widget->placeholder.'</span>';
		else if ($widget->retType === 'nl2br') $text = trim(nl2br($text));
		else if ($widget->retType === 'html') $text = trim(sg_text2html($text));
		else if ($widget->retType === 'text') $text = trim(str_replace("\n",'<br />',$text));
		else if ($widget->retType === 'money' && $text != '') $text = number_format(sg_strip_money($text), 2);
		else if (preg_match('/^date/i', $widget->retType) && $text) {
			list($widget->retType, $retFormat) = explode(':', $widget->retType);
			if (!$retFormat) $retFormat = 'ว ดดด ปปปป';
			$text = sg_date($widget->value, $retFormat);
		}

		if (is_string($widget->selectOptions)) $selectOptions = explode(',', '==เลือก==,' . $widget->selectOptions);
		else if (is_array($widget->selectOptions) && count($widget->selectOptions) > 0) $selectOptions = ['==เลือก=='] + $widget->selectOptions;


		// $ret .= $this->_renderChildContainerStart(
		// 	$key,
		// 	[
		// 		'class' => 'inlineedit-field inline-edit-field -'.$widget->type.($widget->inputClass ? ' '.$widget->inputClass : ''),
		// 		'onClick' => '',
		// 		'data-action' => $widget->action && $widget->editMode ? $widget->action : NULL,
		// 		'data-type' => $widget->type,
		// 		'data-name' => $widget->inputName,
		// 		'data-group' => $widget->group,
		// 		'data-fld' => $widget->field,
		// 		'data-tr' => $widget->tranId,
		// 		'data-ret' => $widget->retType,
		// 		'data-button' => $widget->type === 'textarea' && $options['button'] !== false ? 'yes' : NULL,
		// 		'data-value' => htmlspecialchars(SG\getFirst($widget->value, $widget->text)),
		// 		'data-data' => $selectOptions ? htmlspecialchars(\json_encode($selectOptions)) : NULL,
		// 		'title' => $widget->title,
		// 		// $widget->attribute && is_array($widget->attribute) ? ' '.sg_implode_attr($widget->attribute)._NL : '')
		// 		'data-options' => $options ? json_encode($options) : NULL,

		// 		// 'class' => 'inline-edit-item'
		// 		// 	. ($widget->class ? ' '.$widget->class : '')
		// 		// 	. ($widget->type ? ' -type-'.$widget->type : ''),
		// 		// 'class' => ($widget->type ? ' -type-'.$widget->type : '').($widget->class ? ' '.$widget->class : '')
		// 	]
		// )._NL;
		// $ret .= 'type = '.$widget->type;

		switch ($widget->type) {
			case "comment": break;
			case 'textfield': $ret .= $this->_renderTypeTextField($widget); break;
			case 'radio':
			case 'checkbox': $ret .= $this->_renderTypeRadio($widget); break;
			// case 'select': $ret .= $this->_renderTypeSelect($text); break;
			default: $ret .= $this->_renderTypeText($text, $widget); break;
		}

		// $ret .= print_o($widget, '$widget');
		// $ret .= $this->_renderChildContainerEnd().'<!-- field -->'._NL;
		if (in_array('rawItem', $this->debug)) {
			$ret .= (new DebugMsg($widget, '$widget'))->build();
		}

		return $ret;
	}

	// TODO: delete
	function _render() {
		// debugMsg('START RENDER '.$this->label);
		// if (!$this->editMode) return $this->_renderNotField();
		// // debugMsg($this, '$this');
		// $ret = '';

		// if ($this->label) {
		// 	$ret .= '<label class="inline-edit-label'
		// 		. ($this->labelClass ? ' '.$this->labelClass : '')
		// 		. '"'
		// 		. ($this->labelStyle ? ' style="'.$this->labelStyle.'"' : '')
		// 		. '>'
		// 		. $this->label
		// 		. '</label>';
		// }

		// $text = $this->text;
		// if (is_null($text) || $text == '') $text = '<span class="placeholder -no-print">'.$this->placeholder.'</span>';
		// else if ($this->retType === 'nl2br') $text = trim(nl2br($text));
		// else if ($this->retType === 'html') $text = trim(sg_text2html($text));
		// else if ($this->retType === 'text') $text = trim(str_replace("\n",'<br />',$text));
		// else if ($this->retType === 'money' && $text != '') $text = number_format(sg_strip_money($text), 2);
		// else if (preg_match('/^date/i', $this->retType) && $text) {
		// 	list($this->retType, $retFormat) = explode(':', $this->retType);
		// 	if (!$retFormat) $retFormat = 'ว ดดด ปปปป';
		// 	$text = sg_date($this->value, $retFormat);
		// }

		// if (is_string($this->selectOptions)) $selectOptions = explode(',', '==เลือก==,' . $this->selectOptions);
		// else if (is_array($this->selectOptions) && count($this->selectOptions) > 0) $selectOptions = ['==เลือก=='] + $this->selectOptions;

		// switch ($this->type) {
		// 	case 'textfield': $ret .= $this->_renderTypeTextField($text); break;
		// 	case 'radio':
		// 	case 'checkbox': $ret .= $this->_renderTypeRadio(); break;
		// 	// case 'select': $ret .= $this->_renderTypeSelect($text); break;
		// 	default: $ret .= $this->_renderTypeText($text); break;
		// }
		// // $ret .= '</span>'._NL;

		// return $ret;
	}

	private function _renderLabel($widget) {
		if (empty($widget->label)) return;

		return '<label class="-label'
			. ($widget->labelClass ? ' '.$widget->labelClass : '')
			. '"'
			. ($widget->labelStyle ? ' style="'.$widget->labelStyle.'"' : '')
			. '>'
			. $widget->label
			. '</label>'._NL;
	}

	function _renderTypeTextField($widget) {
		return $this->_renderLabel($widget).'<span>'.$widget->text.'</span>';
	}

	function _renderTypeText($text, $widget) {
		$childEditMode = $this->editMode || $widget->editMode;

		// $options = $widget->options;
		$ret = '';

		// if ($this->editMode /* && $this->updateUrl */) $this->class = 'sg-inline-edit'.' '.$this->class;
		// $widget->class .= ' inline-edit-item -'.$widget->type;
		// $widget->text = trim($widget->text);
		// if (isset($this->debug)) $this->options['debug'] = $this->debug;
		// if ($widget->placeholder) $options['placeholder'] = $widget->placeholder;
		// if ($widget->onBlur) $options['onblur'] = $widget->onBlur;

		// $ret .= '<span'._NL
		// 	. ' class="inlineedit-field inline-edit-field -'.$widget->type.($widget->inputClass ? ' '.$widget->inputClass : '').'"'._NL
		// 	. ' onClick=""'._NL
		// 	.	($widget->action ? ' data-action="'.$widget->action.'"'._NL : '')
		// 	. ($widget->type ? ' data-type="'.$widget->type.'"'._NL : '')
		// 	. ($widget->inputName ? ' data-name="'.$widget->inputName.'"'._NL : '')
		// 	. ($widget->group ? ' data-group="'.$widget->group.'"'._NL : '')
		// 	. ($widget->field ? ' data-fld="'.$widget->field.'"'._NL : '')
		// 	. ' data-tr="'.$widget->tranId.'"'._NL
		// 	. ($widget->retType ? ' data-ret="'.$widget->retType.'"'._NL : '')
		// 	. ($widget->type === 'textarea' && $options['button'] !== false ? ' data-button="yes"' : '')
		// 	. ' data-value="'.htmlspecialchars(SG\getFirst($widget->value, $widget->text)).'"'._NL
		// 	. ($selectOptions ? ' data-data="'.htmlspecialchars(\json_encode($selectOptions)).'"' : '')
		// 	. ' title="'.$widget->title.'"'._NL
		// 	. ($widget->attribute && is_array($widget->attribute) ? ' '.sg_implode_attr($widget->attribute)._NL : '')
		// 	. ($options ? ' data-options=\''.json_encode($options).'\''._NL : '')
		// 	. '>'._NL;

		$ret .= $this->_renderLabel($widget);

		if ($childEditMode) {
			$ret .= '<span class="-for-input">'.$text.'</span>'._NL;
		} else {
			$ret .= '<span class="-for-view">'.$text.'</span>'._NL;
		}
		// $ret .= '</span><!-- field -->'._NL;
		$ret .= $widget->postText;
		return $ret;
	}

	// function _renderTypeSelect($text) {
	// 	$ret = $text;
	// 	return $ret;
	// }

	private function _renderRadioItem($widget) {
		$ret = '';
		foreach($widget->options as $key => $value) {
			$isCheck = NULL;
			if (is_array($widget->value)) {
				$isCheck = in_array($key, $widget->value);
			} else {
				$isCheck = $key == $widget->value;
			}
			$ret .= '<abbr class="checkbox -block">'
				. '<label>'
				. '<input class="-for-input" type="'.$widget->type.'"'
				. ' name="'.$widget->inputName.'"'
				. ' value="'.$key.'"'
				. ($isCheck ? ' checked="checked"' : '')
				. ' />'
				. '<span>'.$value.'</span>'
				. '</label>'
				. '</abbr>';
			// 				$ret.='<abbr class="checkbox -block"><label><input type="checkbox" data-type="checkbox" class="inline-edit-field '.($isEdit?'':'-disabled').'" name="parent[]" data-group="objective:info:actobj" data-fld="parent" data-tr="'.$activityInfo->trid.'" data-objid="'.$item->trid.'" value="'.$item->trid.'" '.(in_array($item->trid,$parentObjectiveId)?'checked="checked"':'').' data-url="'.url('project/develop/plan/'.$tpid).' "data-callback="projectDevelopMainactAddObjective" /> '.$item->title.'</label></abbr>';

		}
		return $ret;
	}

	function _renderTypeRadio($widget) {
		$childEditMode = $this->editMode || $widget->editMode;

		$ret = $this->_renderLabel($widget);

		if ($childEditMode) {
			$ret .= $this->_renderRadioItem($widget)._NL;
			// $ret .= '<span class="x-for-input">'.$this->_renderRadioItem($widget).'</span>'._NL;
		} else {
			$ret .= '<span class="-for-view">'.$this->_renderRadioItem($widget).'</span>'._NL;
		}

		// $ret .= (new DebugMsg($widget, '$widget'))->build();
		return $ret;

		list($choice, $label, $info) = explode(':', $this->text);
		$choice = trim($choice);
		$name = SG\getFirst($this->inputName, $this->field);
		if ($label == '' && strpos($this->text, ':') == false) $label = $choice;
		$label = trim($label);
		$ret .= '<label><input class="inline-edit-field '
			.'-'.$this->type
			.($this->inputClass ? ' '.$this->inputClass : '').'" '
			.($this->inputId ? 'id="'.$this->inputId.'"' : '')
			.'type="'.$this->type.'" '
			.'data-type="'.$this->type.'" '
			.'name="'.$this->inputName.'" '
			.'value="'.$choice.'"'
			. ($this->group ? ' data-group="'.$this->group.'"'._NL : '')
			. ($this->field ? ' data-fld="'.$this->field.'"'._NL : '')
			.(isset($this->value) && $this->value == $choice ? ' checked="checked"':'')
			.' onclick="" '
			// .$this->attribute
			.' style="width: 1.1em; min-width: 1.1em; vertical-align:middle;" '
			.'/> '
			.$label
			.'</label>'
			.$this->require
			.($this->info ? '<sup class="sg-info" title="'.$this->info.'">?</sup>' : '')
			.$this->postText;

		return $ret;
	}

	function _renderNotField() {
		debugMsg('START RENDER NOT FIELD');
		$ret = '';
		if (is_object($this) && method_exists($this, 'build')) {
		// debugMsg($this, '$this_renderNotField');
			// $ret .= $this->build();
		} else {
			$ret .= '<span class="inline-edit-view '
				.'-'.$this->type
				.($this->inputClass ? ' '.$this->inputClass : '').'" '
				.'>';
			if ($this->retType === 'html') {
				$ret .= trim(sg_text2html($this->text));
			} else if ($this->retType === 'text') {
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
			} else if (substr($this->retType, 0, 4) == 'date') {
				$format = substr($this->retType, 5);
				$ret .= $this->text ? sg_date($this->text, $format) : '';
			} else {
				$ret .= $this->text;
			}
			$ret .= $this->postText;
			$ret .= '</span>';
		}
		return $ret;
	}

	function _render_v1() {
		// // \SG\inlineEdit($fld = [], $text = NULL, $is_edit = NULL, $input_type = 'text', $data = [], $emptytext = '...')
		// if ($this->editMode) $this->class .= ' sg-inline-edit';

		// $fld = [];
		// if ($this->label) $fld['label'] = $this->label;
		// if (!is_null($this->group)) $fld['group'] = $this->group;
		// if (!is_null($this->field)) $fld['fld'] = $this->field;
		// if (!is_null($this->tranId)) $fld['tr'] = $this->tranId;
		// if (!is_null($this->inputName)) $fld['name'] = $this->inputName;
		// if (!is_null($this->value)) $fld['value'] = $this->value;
		// if (!is_null($this->minValue)) $fld['min-value'] = $this->minValue;
		// if (!is_null($this->maxValue)) $fld['max-value'] = $this->maxValue;
		// if (!is_null($this->require)) $fld['require'] = $this->require;
		// if (!is_null($this->retType)) $fld['ret'] = $this->retType;
		// if (!is_null($this->preText)) $fld['pretext'] = $this->preText;
		// if (!is_null($this->postText)) $fld['posttext'] = $this->postText;
		// if (!is_null($this->description)) $fld['desc'] = $this->desc;
		// if (!is_null($this->removeEmpty)) $fld['removeempty'] = $this->removeEmpty;
		// if (!is_null($this->updateUrl)) $fld['update-url'] = $this->updateUrl;

		// $fld['options'] = is_null($this->options) ? [] : $this->options;
		// $fld['container'] = is_null($this->container) ? (Object) [] : (Object) $this->container;

		// if ($this->inputClass) $fld['options']['class'] = $this->inputClass;
		// $fld['container']->class = ($this->class ? ' '.$this->class : '');

		// $ret = \SG\inlineEdit($fld, $this->text, $this->editMode, $this->type, $this->selectOptions, $this->emptyText);
		// // debugMsg($this, '$this');
		// // debugMsg($fld, '$fld');
		// return $ret;
	}

	// @override
	function toString1() {
		// if (is_null($this->text) || $this->text == '') {
		// 	$this->class .= ' -empty';
		// }
		// $ret = '<!-- Start of '.$this->widgetName.' -->'._NL;
		// $ret .= $this->_renderWidgetContainerStart(
		// 	function() {
		// 		return
		// 			($this->updateUrl ? ' data-update-url="'.$this->updateUrl.'"'._NL : NULL);
		// 	}
		// );
		// $ret .= $this->_render();
		// // if ($this->debug) $ret .= (new DebugMsg($this, '$this'))->build();
		// $ret .= $this->_renderWidgetContainerEnd()._NL;
		// $ret .= '<!-- End of '.$this->widgetName.' -->'._NL;
		// // $ret .= '<pre>'.htmlspecialchars($ret).'</pre>';
		// // $ret .= print_o($this, '$this');
		// return $ret;
	}

	// function _renderWidgetContainerStart($callbackFunction = NULL) {
	// }
	// @override
	// function _renderWidgetContainerStart($callbackFunction = NULL) {
	// 	if ($this->editMode ? $this->class .= ' '.$this->editModeClassName : '');
	// 	return parent::_renderChildContainerStart(function() {
	// 		// return ($this->action && $this->editMode ? ' data-action="'.$this->action.'"'._NL : '');
	// 	});
	// }

	// @override
	function toString2() {
		$childrenToRender = [];

		if ($this->editMode ? $this->class .= ' '.$this->editModeClassName : '');
		$ret .= $this->_renderWidgetContainerStart(function() {
			return ($this->action && $this->editMode ? ' data-action="'.$this->action.'"'._NL : '');
		});

		if (empty($this->children)) {
			// Render single child
			$child = (Array) get_object_vars($this);
			unset($child['action'], $child['updateUrl']);
			$childrenToRender[] = $child;
		} else {
			// Render multiple child from children
			foreach ($this->children as $key => $child) {
				// debugMsg($child, '$child');
				if (is_object($child) && get_class($child) === 'ChildrenWidget') {
					$childrenToRender[] = '<div class="'.$child->class.'">';
					foreach ($child->children as $subKey => $subChild) {
						if (is_string($subKey)) $subChild['inputName'] = $subKey;
						$childrenToRender[] = $subChild;
					}
					$childrenToRender[] = '</div>';
				} else {
					if (is_string($key)) $child['inputName'] = $key;
					$childrenToRender[] = $child;
				}
			}
		}

		// debugMsg($childrenToRender, '$childrenToRender');

		foreach ($childrenToRender as $key => $child) {
			// debugMsg($child, '$child');
			$ret .= $this->_renderEachChildWidget($key, $child, [
				'array' => function($key, $widget) {
					return $this->_renderChildType($key, (Object) $widget); //'<div>RENDER ARRAY '.$key.$widget['label'].'<div>'._NL;
				},
				'text' => function($key, $text) {
					return $text._NL;
				}
			]);
		}

		$ret .= $this->_renderWidgetContainerEnd();
		return $ret;
	}
} // End of class InlineEdit

?>