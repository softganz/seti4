<?php
/**
* Widget  :: Form Widget
* Created :: 2020-10-01
* Modify  :: 2025-07-15
* Version :: 34
*
* @param Array $args
* @return Widget
*
* @usage new Form([key => value,...])
*/

/*
	Form Attribute: id, class, method, variable, enctype, readonly, title, checkValid, action, leading, rel, done, children, width, height, style, description, footer, trailing, onSubmit, onFormSubmit, attribute
	Form Children: Array of
		- Text
		- new Widget([])
		- (Object) [tagName => String, children = Array]
*/

class Form extends Widget {
	var $widgetName = 'Form';
	var $tagName = 'form';
	var $method = 'POST';
	var $variable = NULL;
	var $readonly = false;
	var $config;
	var $leading;
	var $trailing;
	var $description;
	var $footer;
	var $onSubmit;
	var $onFormSubmit;
	var $children = [];
	var $formArray = [];

	function __construct($args = []) {
		$this->initConfig();
		if (is_object($args) || is_array($args)) {
			$args = is_array($args) ? (Object) $args : $args;
			parent::__construct($args);
		} else {
			// @deprecated, use argument as array
			$args = func_get_args();
			$this->variable = $args[0];
			$this->action = $args[1];
			$this->id = $args[2];
			$this->class = $args[3];
		}

		self::changeOptionsToChoice($this->children);
	}

	private static function changeOptionsToChoice(&$children) {
		foreach ($children as $key => $value) {
			if (is_array($value) && in_array($value['type'], ['radio', 'checkbox', 'select']) && $value['options'] && !array_key_exists('choice', $value)) {
				$children[$key]['choice'] = $value['options'];
				unset($children[$key]['options']);
			}
		}
	}

	function count() {return count($this->children);}

	function addText($text) {
		$this->children[uniqid()] = $text;
	}

	// @deprecated, use children
	function addField($key, $value) {
		$children = [$value];
		self::changeOptionsToChoice($children);
		$this->children[$key] = $children[0];
	}

	function field($key = NULL) {return $this->children;}

	//TODO:: Move form item to array $this->formArray
	function renderForm() {
		if (empty($this->children)) {
			foreach ($this as $fieldKey => $value) {
				if (is_null($value) || in_array($fieldKey, ['id','class','method','action','variable','readonly','config','widgetName','tagName','children','header','leading','enctype'])) continue;
				$this->children[$fieldKey] = $value;
				unset($this->{$fieldKey});
			}
		}

		if ($this->debug) debugMsg($this, '$this');

		$this->config = is_array($this->config) ? (Object) $this->config : $this->config;
		$this->readonly = SG\getFirst($this->config->readonly, $this->readonly);
		$this->variable = SG\getFirst($this->variable, $this->config->variable);
		$formEncrypt = $this->enctype = SG\getFirst($this->enctype, $this->config->enctype);
		$formMethod = $this->method = SG\getFirst($this->config->method, $this->method);
		$formAction = $this->action = SG\getFirst($this->action, $this->config->action);
		$formCheckValid = SG\getFirst($this->checkValid, $this->data['data-checkValid'], $this->data['data-checkvalid']);
		$formTitle = SG\getFirst($this->title, $this->config->title);

		if ($this->action) {
			$ret .= _NL.'<!-- sg-form -->'._NL;
			if (isset($this->leading)) $ret .= $this->leading;
			unset($this->config->data['rel']);
			$formStr = '<form'
				. ' id="'.$this->id.'"'
				. ' class="widget-form form '
				. ($this->class ? $this->class:'')
				. ($this->readonly ? ' -readonly' : '')
				. ($this->mainAxisAlignment ? ' -main-axis-'.strtolower($this->mainAxisAlignment) : '')
				. ($this->crossAxisAlignment ? ' -cross-axis-'.strtolower($this->crossAxisAlignment) : '')
				. '"' // class
				. ' method="'.$formMethod.'"'
				. ($formEncrypt ? ' enctype="multipart/form-data"' : '')
				. ' action="'.$formAction.'"'
				. (isset($formCheckValid) && $formCheckValid ? ' data-checkvalid="true"' : '')
				. (isset($this->attribute) ? ' '.(is_array($this->attribute) ? sg_implode_attr($this->attribute) : $this->attribute) : '')
				. (isset($this->config->attr) ? ' '.(is_array($this->config->attr) ? sg_implode_attr($this->config->attr) : $this->config->attr) : '')
				. (isset($this->config->data) ? ' '.(is_array($this->config->data) ? sg_implode_attr($this->config->data) : $this->config->data) : '')
				. ($this->onSubmit ? ' onSubmit = \'return '.$this->onSubmit.'(event)\'' : '')
				. ($this->onFormSubmit ? ' data-onformsubmit = \''.$this->onFormSubmit.'\'' : '')
				. ($this->style ? ' style="'.$this->style.'"' : '')
				. ' >';

			$ret .= $formStr._NL._NL;

			$this->formArray['form'] = $formStr;
		}

		if ($this->header->text) {
			$ret .= '<header class="header'
				. ($this->header->attr->class ? ' '.$this->header->attr->class : '').'">'
				. $this->header->text
				. '</header>';
		}

		// Render form title
		if ($formTitle) {
			if (SG\isWidget($formTitle)) {
				$ret .= $formTitle->build();
			} else {
				$ret .= '<h3 class="title">'.$formTitle.'</h3>'._NL;
			}
		}

		if ($this->description) $ret .= '<div class="description">'.$this->description.'</div>';

		$ret .= $this->_renderFormChild($this->children);

		if ($this->footer) $ret .= $this->footer;
		if ($this->action) $ret .= '</form>'._NL;
		if (isset($this->trailing)) $ret .= $this->trailing;

		return $ret;
	}

	function _renderFormChild($childrens) {
		foreach ($childrens as $fieldKey => $formElement) {
			if (is_object($formElement) && method_exists($formElement, 'build')) {
				// Form element is widget
				$ret .= $formElement->build();
			} else if (is_object($formElement)) {
				// Form element is object
				self::changeOptionsToChoice($formElement->children);
				if ($formElement->tagName) {
					$ret .= '<'.$formElement->tagName.' id="'.$formElement->id.'" class="form-container -type-'.$formElement->tagName.($formElement->class ? ' '.$formElement->class : '').'">'
						. ($formElement->label ? '<label>'.$formElement->label.'</label>' : '');
				}
				$ret .= $this->_renderFormChild($formElement->children);
				if ($formElement->tagName) {
					$ret .= '</'.$formElement->tagName.'>';
				}
			} else if (is_array($formElement) AND is_array(reset($formElement))) {
				// @deprecated
				// Form element is array and children is array, Render each children as form element
				foreach ($formElement as $groupKey => $groupItem) {
					if (is_object($groupItem) && method_exists($groupItem, 'build')) {
						// Item is widget
						$ret .= $groupItem->build();
					} else {
						// Item is array or string
						list($tag_id, $renderChildrenResult) = $this->_renderChild($groupKey, $groupItem);
						$this->formArray[$tag_id] = $renderChildrenResult;
						$ret .= $renderChildrenResult;
					}
				}
			} else if (is_array($formElement) && array_key_exists('children', $formElement)) {
				// @deprecated
				// Form element is array and has key children
				foreach ($formElement['children'] as $groupKey => $groupItem) {
					if (is_object($groupItem) && method_exists($groupItem, 'build')) {
						// Item is widget
						$ret .= $groupItem->build();
					} else {
						// Item is array or string
						list($tag_id, $renderChildrenResult) = $this->_renderChild($groupKey, $groupItem);
						$this->formArray[$tag_id] = $renderChildrenResult;
						$ret .= $renderChildrenResult;
					}
				}
			} else {
				list($tag_id, $renderChildrenResult) = $this->_renderChild($fieldKey, $formElement);
				$this->formArray[$tag_id] = $renderChildrenResult;
				$ret .= $renderChildrenResult;
			}
		}
		return $ret;
	}

	function _renderChild($fieldKey, $formElement) {
		if (is_string($formElement)) {
			if ($formElement === '<spacer>') $formElement = '<div class="form-item -spacer"></div>';
			return [NULL, $formElement._NL._NL];
		}

		$formElement = (Object) $formElement;

		$name = '';
		$tag_id = '';
		$containerClass = '';

		if ($formElement->config) {
			$formElement->config = SG\json_decode($formElement->config);
		}

		if ($formElement->id) {
			$tag_id = $formElement->id;
		} else {
			$tag_id = $formElement->name ? $formElement->name : ($this->variable ? $this->variable.'-':'').$fieldKey;
			$tag_id = 'edit-'.preg_replace(array('/([\W]+$)+/','/([\W])+/'),array('','-'),$tag_id);
		}

		$tag_id = strtolower($tag_id);

		if ($formElement->name !== false) {
			$name = $formElement->name ? $formElement->name : ($this->variable ? $this->variable.'['.$fieldKey.']' : $fieldKey);
		}

		if (isset($formElement->container) && is_object($formElement->container)) {
			$formElement->container = (Array) $formElement->container;
		} else if (isset($formElement->container) && is_string($formElement->container) && substr($formElement->container,0,1) == '{') {
			$formElement->container = (Array) SG\json_decode($formElement->container);
		}

		$isFormGroup = preg_match('/-group/', $formElement->container['class']);

		$containerClass = $formElement->containerclass;
		if ($formElement->container) {
			if ($formElement->container['class']) {
				$containerClass .= trim(' '.$formElement->container['class']);
			}
		}

		unset($formElement->container['class']);

		$ret .= '<div id="form-item-'.$tag_id.'" '
				. 'class="form-'.(in_array($formElement->type,array('','')) ? $formElement->type : 'item -'.$tag_id).($containerClass ? ' '.$containerClass : '')
				. ($formElement->type == 'hidden' ? ' -hidden' : '')
				. '"'
				. ' '.sg_implode_attr($formElement->container)
				. '>'._NL;
		if ($formElement->label) {
			$ret .= '	<label for="'.$tag_id.'" class="'.($formElement->config->label == 'hide' ? '-hidden' : '').'">'
				. $formElement->label.(in_array($formElement->type, ['select', 'radio', 'checkbox']) && !preg_match('/\:$/', $formElement->label) ? ':' : '')
				. ($formElement->require ? ' <span class="form-required" title="This field is required.">*</span>':'')
				. '</label>'
				. _NL;
		}

		if ($isFormGroup) $ret .= '<span class="form-group">'._NL;

		$ret .= $this->_renderAttribute(SG\getFirst($formElement->preText, $formElement->pretext));

		// Item attribute from key attribute, if not define use key attr
		// Implode attribute to string
		$formElement->attribute = SG\getFirst($formElement->attribute, $formElement->attr, []);
		if ($formElement->attribute && (is_array($formElement->attribute) || is_object($formElement->attribute))) {
			$formElement->attribute = sg_implode_attr($formElement->attribute);
		}

		switch ($formElement->type) {
			case 'textfield' : $ret .= $this->_renderTextField($tag_id, $name, $formElement); break;
			case 'hidden': $ret .= $this->_renderHidden($tag_id, $name, $formElement); break;
			case 'text' :
			case 'password' : $ret .= $this->_renderTextPassword($tag_id, $name, $formElement); break;
			case 'textarea' : $ret .= $this->_renderTextArea($tag_id, $name, $formElement); break;
			case 'radio' :
			case 'checkbox' : $ret .= $this->_renderRadioCheckbox($tag_id, $name, $formElement); break;
			case 'select' : $ret .= $this->_renderSelect($tag_id, $name, $formElement); break;
			case 'file' : $ret .= $this->_renderFile($tag_id, $name, $formElement); break;
			case 'button' : $ret .= $this->_renderButton($tag_id, $name, $formElement); break;
			case 'submit' : $ret .= $this->_renderSubmit($tag_id, $name, $formElement); break;
			case 'date' : $ret .= $this->_renderDate($tag_id, $name, $formElement); break;
			case 'time' : $ret .= $this->_renderTime($tag_id, $name, $formElement); break;
			case 'hour' : $ret .= $this->_renderHour($tag_id, $name, $formElement); break;
			case 'colorpicker' : $ret .= $this->_renderColorPicker($tag_id, $name, $formElement); break;
		}

		$ret .= $this->_renderAttribute(SG\getFirst($formElement->postText, $formElement->posttext));

		if ($isFormGroup) $ret .= '</span><!-- form-group -->'._NL;
		if ($formElement->description) $ret .= _NL.'<div class="description">'.$formElement->description.'</div>';
		$ret .= _NL.'</div>';

		$ret .= _NL._NL;
		return [$tag_id, $ret];
	}

	private function _renderAttribute($attribute) {
		if (is_object($attribute) && method_exists($attribute, 'build')) {
			// Item is widget
			$ret = $attribute->build();
		} else {
			// Item is array or string
			$ret = $attribute;
		}
		return $ret;
	}

	private function onElementEvent($event, $onChangeValue) {
		if (empty($onChangeValue)) return '';
		switch ($onChangeValue) {
			case 'submit':
				$result = ' '.$event.' = \'$(this).closest(form).submit()\'';
				break;

			default:
				$result = ' '.$event.' = \''.$onChangeValue.'\'';
				break;
		}
		return $result;
	}

	// Render Field

	function _renderTextField($tag_id, $name, $formElement) {
		return '<div id="'.$tag_id.'">'.$formElement->value.'</div>';
	}

	function _renderHidden($tag_id, $name, $formElement) {
		return '<input type="hidden" name="'.$name
			. '" id="'.$tag_id.'"'
			. ' class="'.($formElement->require?'-require':'').'"'
			. ' value="'.htmlspecialchars($formElement->value).'"'
			. ' placeholder="'.$formElement->placeholder.'"'
			. ($formElement->attribute ? ' '.$formElement->attribute : '')
			. ' >'._NL._NL;
	}

	function _renderTextPassword($tag_id, $name, $formElement) {
		$ret = '<input'
			. ($this->readonly || $formElement->readonly ?' readonly="readonly"' : '')
			. ($formElement->autocomplete ? ' autocomplete="'.$formElement->autocomplete.'"' : '')
			. ($formElement->maxlength ? ' maxlength="'.$formElement->maxlength.'"' : '')
			. ($formElement->size ? ' size="'.$formElement->size.'"' : '')
			. ($name ? ' name="'.$name.'"' : ' ')
			. ' id="'.$tag_id.'"'
			. ' class="form-'.$formElement->type
				. ($formElement->class ? ' '.$formElement->class : '')
				. ($formElement->require ? ' -require' : '')
				. ($formElement->readonly ? ' -readonly' : '')
				. '"'
			. ' type="'.$formElement->type.'"'
			. ($formElement->attribute ? ' '.$formElement->attribute : '')
			. ($formElement->style ? ' style="'.$formElement->style.'"' : '')
			. ($formElement->{"autocomplete-url"} ? ' autocomplete-url="'.$formElement->{"autocomplete-url"}.'"' : '')
			. ' value="'.htmlspecialchars($formElement->value).'"'
			. (isset($formElement->placeholder) ? ' placeholder="'.$formElement->placeholder.'"' : '')
			. '>';
		return $ret;
	}

	function _renderTextArea($tag_id, $name, $formElement) {
		$ret .= '	<div class="resizable-textarea">'
			. '<textarea'
			. ($this->readonly || $formElement->readonly ? ' readonly="readonly"' : '')
			. ' cols="'.($formElement->cols ? $formElement->cols : '60').'"'
			. ' rows="'.($formElement->rows ? $formElement->rows : 10).'"'
			. ' name="'.$name.'"'
			. ' id="'.$tag_id.'"'
			. ' class="form-textarea resizable processed'
				. ($formElement->require ? ' -require' : '')
				. ($formElement->readonly ? ' -readonly' : '')
				. ($formElement->class ? ' '.$formElement->class : '')
				. '"'
			. ($formElement->attribute ? ' '.$formElement->attribute : '')
			. (isset($formElement->placeholder) ? ' placeholder="'.$formElement->placeholder.'"' : '')
			. '>'
			. $formElement->value
			. '</textarea>'
			. '<div style="margin-right: -4px;" class="grippie"></div>'
			. '</div>';
		return $ret;
	}

	/*
	* Options format:
	* 	[key1 => value1, key2 => value2, groupObject, groupArray, ...]
	* 		groupArray: [['key' => 'xxx', value'=>'xxx'], ...]
	*			groupObject: [[key1 => value1], [key2 => value2], ...]
	* Option value format: LABEL:, SEPARATOR, &nbsp;, space, tab
	*/
	function _renderRadioCheckbox($tag_id, $name, $formElement) {
		static $itemIndex = 0;
		$ret = '';
		if (!isset($formElement->display)) $formElement->display = '-block';

		// choice begin with RANGE:
		if (is_string($formElement->choice) && preg_match('/^RANGE\:(.*)/', $formElement->choice, $out)) {
			$formElement->choice = [];
			if (preg_match('/\.\./', $out[1])) {
				// TODO: range from start to end
			} else {
				foreach (explode(',', $out[1]) as $value) $formElement->choice[trim($value)] = trim($value);
			}
		}

		foreach ($formElement->choice as $optionKey => $optionValue) {
			if (is_null($optionValue)) continue;

			if (is_array($optionValue) || is_object($optionValue)) {
				$optionValue = (Array) $optionValue;

				if (isset($optionValue['key'])) {
					// Option format is [['key' => 'xxx', value'=>'xxx'],...]
					$itemFormElement = clone $formElement;
					$itemFormElement->choice = [$optionValue['key'] => $optionValue['value']];
					$ret .= $this->_renderRadioCheckbox(
						$tag_id,
						$name,
						$itemFormElement
					);
				// } else if ($optionValue['name']) {
				// 	// @deprecated
				// 	// Option format is [['name' => 'xxx','label'=>'xxx','value'=>'xxx'],...]
				// 	$itemIndex++;
				// 	$ret .= '<abbr class="'.$formElement->type.'"><label class="option -'.$formElement->display.'" >';
				// 	$ret .= '<input'
				// 		. ($this->readonly || $formElement->readonly ? ' readonly="readonly" disabled="disabled"':'')
				// 		. ' name="'.$optionValue['name'].'"'
				// 		. ' value="'.$optionValue['value'].'"';
				// 	$ret .= is_null($formElement->value[$optionValue['name']]) ? '' : ' checked="checked"';
				// 	$ret .= ' class="form-'.$formElement->type.($formElement->class ? ' '.$formElement->class : '').($formElement->require ? ' -require':'').'"'
				// 		. ' type="'.$formElement->type.'"'
				// 		. ($formElement->attribute ? ' '.$formElement->attribute : '')
				// 		. '> ';
				// 	$ret .= $optionValue['label'];
				// 	$ret .= '</label></abbr>'._NL;
				} else {
					// Option format is ['value'=>'label',...]
					$ret .= '<span class="options-group"><span class="options-group-label">'.$optionKey.':</span>'._NL;
					$itemFormElement = clone $formElement;
					$itemFormElement->choice = $optionValue;
					$ret .= $this->_renderRadioCheckbox(
						$tag_id,
						$name,
						$itemFormElement
					);
					$ret .= '</span>'._NL;
				}
			} else {
				// option value is string
				if ($formElement->separate) {
					$name = $formElement->name ? $formElement->name.$optionKey : ($this->variable ? $this->variable.'['.$fieldKey.$optionKey.']' : $fieldKey);
				}

				if ($formElement->config->capsule) {
					$ret .= '<'.$formElement->config->capsule->tag.' class="'.$formElement->config->capsule->class.'">';
				}
				if (preg_match('/^(LABEL\:)(.*)/', $optionValue, $out)) {
					$ret .= '<div>'.$out[2].'</div>';
				} else if (preg_match('/^SEPARATOR$/', $optionValue)) {
					$ret .= '<hr class="-sep" />';
				} else if (preg_match('/^(\&nbsp\;)(.*)/', $optionValue, $out)) {
					// Show label only
					$ret .= '<div>'.$out[2].'</div>';
				} else {
					$itemIndex++;
					$ret .= '<abbr class="'.$formElement->type.'"><label class="option'.($formElement->display ? ' '.$formElement->display : '').'">';
					if (preg_match('/^\s/', $optionValue)) {
						$ret .= '&nbsp;&nbsp;&nbsp;&nbsp;';
					}
					$ret .= '<input id="'.$tag_id.'-'.$optionKey.'"'
						. ($this->readonly || $formElement->readonly ? ' readonly="readonly" disabled="disabled"' : '')
						. ' name="'.($formElement->namePrefix ? $formElement->namePrefix.$optionKey : '').$name.($formElement->multiple ? '['.$optionKey.']' : '').'"'
						. ' value="'.$optionKey.'"';
					if (is_array($formElement->value)) {
						$optionValue_key = array_keys($formElement->value);
						$ret .= in_array($optionKey, array_intersect(array_keys((Array) $formElement->choice), (Array) $formElement->value)) ? ' checked="checked"':'';
					} else if (isset($formElement->value) && $optionKey == $formElement->value) {
						$ret .= ' checked="checked"';
					}
					$ret .= ' class="form-'.$formElement->type.($formElement->class ? ' '.$formElement->class : '').($formElement->require?' -require':'').'"'
						. ' type="'.$formElement->type.'"'
						. ($formElement->attribute ? ' '.$formElement->attribute:'')
						. $this->onElementEvent('onChange', $formElement->onChange)
						. '> ';
					$ret .= '<span>'.$optionValue.'</span>';
					$ret .= '</label></abbr>'._NL;
				}
				// $ret .= 'STRING:'.$optionValue;
				if ($formElement->config->capsule) {
					$ret .= '</'.$formElement->config->capsule->tag.'>';
				}
			}
		}
		return $ret;
	}

	function _renderSelect($tag_id, $name, $formElement) {
		if (!is_array($formElement->value)) $formElement->value = (Array) $formElement->value;
		$ret = '	<select '
			. ($this->readonly || $formElement->readonly ? 'readonly="readonly" ' : '').' '
			. ($formElement->multiple ? 'multiple="multiple" ' : '').($formElement->size?'size="'.$formElement->size.'" ':'')
			. ' name="'.$name.'" id="'.$tag_id.'" '
			. 'class="form-'.$formElement->type.($formElement->class ? ' '.$formElement->class : '').($formElement->require ? ' -require' : '').($this->readonly || $formElement->readonly ? ' -disabled' : '').'"'
			. $this->onElementEvent('onChange', $formElement->onChange)
			. ($formElement->style ? 'style="'.$formElement->style.'"' : '')
			. ($formElement->attribute ? ' '.$formElement->attribute : '')
			. '>'._NL;

		if (is_string($formElement->choice) && preg_match('/^\</', $formElement->choice)) {
			// Option is HTML Tag
			$ret .= $formElement->choice;
			unset($formElement->choice);
		} else if (is_string($formElement->choice)) {
			// Option is string and contain ,
			$selectOptions = [];
			foreach (explode(',', $formElement->choice) as $eachOption) {
				if (preg_match('/(.*)\=\>(.*)/', $eachOption, $out)) {
					// Option format key=value
					$selectOptions[strtoupper(trim($out[1])) === 'NULL' ? '' : trim($out[1])] = trim($out[2]);
				} else if (preg_match('/^([0-9\-]+)\.\.([0-9\-]+)/', $eachOption, $out)) {
					// Option format 1..10
					for ($i = $out[1]; $i<=$out[2]; $i++) {
						$selectOptions[$i] = $i;
					}
				} else {
					$selectOptions[$eachOption] = $eachOption;
				}
			}
			$formElement->choice = $selectOptions;
		}
		if ($formElement->choice) $ret .= $this->_renderSelectOption($formElement->choice, $formElement->value);
		$ret .= '	</select>';
		return $ret;
	}

	private function _renderSelectOption($choice, $inputValue) {
		$ret = '';
		foreach ($choice as $optionKey => $optionValue) {
			if (is_object($optionValue)) $optionValue = (Array) $optionValue;

			if (is_array($optionValue) && array_key_exists('label', $optionValue)) {
				// Option is array has key label : [1=>"label", attr=>["data-key"=>"data-key-value",...]]
				$ret .= '	<option'
					. ' value="'.$optionKey.'"'
					. (in_array($optionKey, $inputValue) ? ' selected="selected"' : '')
					. ($optionValue['attr'] ? ' '.sg_implode_attr($optionValue['attr']) : '')
					. '>'
					. $optionValue['label']
					. '</option>'._NL;
			} else if (is_array($optionValue)) {
				// Option is array, then make option group
				$ret .= '	<optgroup label="'.$optionKey.'">'._NL;
				$ret .= $this->_renderSelectOption($optionValue, $inputValue);
				$ret .= '	</optgroup>'._NL;
			} else if (preg_match('/^LABEL\:/', $optionValue)) {
				// do nothing
			} else if (preg_match('/^SEPARATOR$/', $optionValue)) {
				// Option is seperatpr
				$ret .= '<option class="-sep" disabled="disabled" style="height: 1px; display: block;">---</option>';
			} else {
				// Option is string
				$ret .= '	<option value="'.$optionKey.'"'.(in_array($optionKey, $inputValue) ? ' selected="selected"' : '').'>'.$optionValue.'</option>'._NL;
			}
		}
		return $ret;
	}

	function _renderFile($tag_id, $name, $formElement) {
		if ($formElement->count) {
			for ($i = 1; $i <= $formElement->count; $i++) {
				$ret .= '<input '.($formElement->size?'size="'.$formElement->size.'" ':'').' name="'.$name.'['.$i.']" id="'.$tag_id.'-'.$i.'" class="form-'.$formElement->type.($formElement->require?' -require':'').'" type="'.$formElement->type.'">';
			}
		} else {
			$ret .= '<input '
				. ($formElement->size ? 'size="'.$formElement->size.'" ' : '')
				. 'name="'.$name.'" '
				. 'id="'.$tag_id.'" '
				. 'class="form-'.$formElement->type.($formElement->class ? ' '.$formElement->class : '').($formElement->require?' -require':'').'" '
				. 'type="'.$formElement->type.'" '
				. ($formElement->multiple ? 'multiple="multiple"' : '')
				. ($formElement->accept ? 'accept="'.$formElement->accept.'" ' : '')
				. ($formElement->attribute ? ' '.$formElement->attribute : '')
				. '>';
		}
		return $ret;
	}

	function _renderButton($tag_id, $name, $formElement) {
		if (empty($formElement->items) && !empty($formElement->value)) {
			// Single button
			$ret .= '<button type="submit" '
				. (empty($formElement->name) ? '' : 'name="'.$name.'"')
				. ' class="btn '.($formElement->class ? $formElement->class : '-primary').'"'
				. ' value="'.htmlspecialchars(strip_tags($formElement->value)).'"'
				. ($this->readonly || $formElement->readonly ? ' disabled="disabled" ' : '')
				. ($formElement->attribute ? ' '.$formElement->attribute : '')
				. '>'
				. SG\getFirst($formElement->text, $formElement->value)
				. '</button>';
		} else if (is_array($formElement->items) && !empty($formElement->items['value'])) {
			$ret .= '<button'
				. (isset($formElement->items['type']) ? ' type="'.$formElement->items['type'].'"' : '')
				. ' name="'.(isset($formElement->items['name']) ? $formElement->items['name'] : $name).'"'
				. ' class="btn'.($formElement->items['class'] ? ' '.$formElement->items['class'] : '').'"'
				. ' value="'.htmlspecialchars(strip_tags($formElement->items['value'])).'"'
				. ($this->readonly || $formElement->readonly ? ' disabled="disabled"' : '')
				. $formElement->attribute
				. ' >'
				. $formElement->items['value']
				. '</button>';
		} else {
			// Multiple button
			foreach ($formElement->items as $key => $button) {
				if (is_null($button)) {
					continue;
				} else if ($button['type'] == 'text') {
					$ret .= $button['value'];
				} else {
					$ret .= '<button'
						. (isset($button['type']) ? ' type="'.$button['type'].'"' : '')
						. ' name="'.SG\getFirst($button['name'],is_string($key) ? $key : $name).'"'
						. ' class="btn'.($button['class'] ? ' '.$button['class'] : '').'"'
						. ' value="'.SG\getFirst($button['btnvalue'],htmlspecialchars(strip_tags($button['value']))).'"'
						. ($this->readonly || $formElement->readonly ? ' disabled="disabled"' : '')
						. ($button['attribute'] && (is_array($button['attribute']) || is_object($button['attribute'])) ? ' '.sg_implode_attr($button['attribute']) : $button['attribute'])
						.' >'
						. $button['value']
						. '</button>';
				}
			}
		}
		return $ret;
	}

	function _renderSubmit($tag_id, $name, $formElement) {
		$ret = '';
		foreach ($formElement->items as $key=>$value) {
			if (substr($key,0,4)=='text') $ret .= $value;
			else $ret .= '	<input name="'.$key.'" '.($this->isReadOnly || $formElement->readonly?'readonly="readonly" ':'').'class="btn'.($key=='save'?' -primary':'').' -'.$key.($formElement->class?' '.$formElement->class:'').'"  type="'.$formElement->type.'" value="'.$value.'">';
		}
		return $ret;
	}

	function _renderDate($tag_id, $name, $formElement) {
		$months['BC'] = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
		$months['DC'] = ['Jan','Feb','Apr','March','May','June','July','Aug','Sep','Oct','Nov','Dec'];

		list($year_from,$year_no,$year_sort) = explode(',',$formElement->year->range);
		if (empty($year_from)) $year_from = date('Y');
		else if (in_array(substr($year_from,0,1),array('-','+'))) $year_from = date('Y')+$year_from;
		if (empty($year_no)) $year_no = 5;

		$ret = '<select id="'.$tag_id.'-date" class="form-select'.($formElement->class ? ' '.$formElement->class : '').'" name="'.$name.'[date]" '.($this->readonly || $formElement->readonly?'readonly="readonly" disabled="disabled" ':'').'>'._NL;
		$ret .= '<option value="">'.($formElement->year->type=='BC'?'วันที่':'Date').'</option>'._NL;
		for ($i = 1; $i <= 31; $i++) {
			$ret .= '<option value="'.sprintf('%02d',$i).'"'.($formElement->value->date==$i?' selected':'').'>'.sprintf('%02d',$i).'</option>'._NL;
		}
		$ret .= '</select>'._NL;
		$ret .= '<select id="'.$tag_id.'-month" class="form-select'.($formElement->class ? ' '.$formElement->class : '').'" name="'.$name.'[month]" '.($this->readonly || $formElement->readonly?'readonly="readonly" disabled="disabled" ':'').'>'._NL;
		$ret .= '<option value="">'.($formElement->year->type=='BC'?'เดือน':'Month').'</option>'._NL;
		for ($i = 1; $i <= 12; $i++) {
			$ret .= '<option value="'.sprintf('%02d',$i).'"'.($formElement->value->month==$i?' selected':'').'>'.sprintf('%02d',$i).'-'.($formElement->year->type=='BC'?$months['BC'][$i-1]:$months['DC'][$i-1]).'</option>'._NL;
		}
		$ret .= '</select>'._NL;
		$ret .= '<select id="'.$tag_id.'-year" class="form-select'.($formElement->class ? ' '.$formElement->class : '').'" name="'.$name.'[year]" '.($this->readonly || $formElement->readonly?'readonly="readonly" disabled="disabled" ':'').'>'._NL;
		$ret .= '<option value="">'.($formElement->year->type=='BC'?'ปี พ.ศ.':'Year').'</option>'._NL;
		if ($year_sort == 'DESC') {
			for ($i = $year_from; $i > $year_from-$year_no; $i--) {
				$ret.='<option value="'.$i.'"'.($formElement->value->year==$i?' selected':'').'>'.($formElement->year->type=='BC'?$i+543:$i).'</option>'._NL;
			}
		} else {
			for ($i = $year_from; $i < $year_from+$year_no; $i++) {
				$ret .= '<option value="'.$i.'"'.($formElement->value->year==$i?' selected':'').'>'.($formElement->year->type=='BC'?$i+543:$i).'</option>'._NL;
			}
		}
		$ret .= '</select>'._NL;
		return $ret;
	}

	function _renderTime($tag_id, $name, $formElement) {
		$times = [];
		$start_time = SG\getFirst($formElement->start, 0);
		$end_time = SG\getFirst($formElement->end ,24);
		$step_time = SG\getFirst($formElement->step, 15);
		for ($hr = $start_time; $hr < $end_time; $hr++) {
			for ($min = 0; $min < 60; $min += $step_time) {
				$times[] = sprintf('%02d',$hr).':'.sprintf('%02d',$min);
			}
		}
		$ret = '<select id="'.$tag_id.'" class="form-select'.($formElement->class?' '.$formElement->class:'').'" name="'.$name.'">'._NL;
		foreach ($times as $time) {
			$ret .= '<option value="'.$time.'"'.($time == $formElement->value?' selected="selected"':'').'>'.$time.'</option>';
		}
		$ret .= '</select>';
		return $ret;
	}

	function _renderHour($tag_id, $name, $formElement) {
		$start_time = SG\getFirst($formElement->start, 0);
		$end_time = SG\getFirst($formElement->end, 24);
		$step_time = SG\getFirst($formElement->step, 15);
		$ret = '<select id="'.$tag_id.'" class="form-select" name="'.$name.'[hour]">'._NL;
		for ($hr = $start_time; $hr < $end_time; $hr++) {
			$ret .= '<option value="'.sprintf('%02d',$hr).'"'.($hr == $formElement->value->hour?' selected="selected"':'').'>'.sprintf('%02d',$hr).'</option>';
		}
		$ret .= '</select> : ';
		$ret .= '<select id="'.$tag_id.'" class="form-select" name="'.$name.'[min]">'._NL;
		for ($min = 0; $min < 60; $min++) {
			$ret .= '<option value="'.sprintf('%02d',$min).'"'.($min == $formElement->value->min?' selected="selected"':'').'>'.sprintf('%02d',$min).'</option>';
		}
		$ret .= '</select>';
		return $ret;
	}

	function _renderColorPicker($tag_id, $name, $formElement) {
		$ret = '';
		if (empty($formElement->color)) $formElement->color='#ffffff, #cccccc, #c0c0c0, #999999, #666666, #333333, #000000, #ffcccc, #ff6666, #ff0000, #cc0000, #990000, #660000, #330000, #ffcc99, #ff9966, #ff9900, #ff6600, #cc6600, #993300, #663300, #ffff99, #ffff66, #ffcc66, #ffcc33, #cc9933, #996633, #663333, #ffffcc, #ffff33, #ffff00, #ffcc00, #999900, #666600, #333300, #99ff99, #66ff99, #33ff33, #33cc00, #009900, #006600, #003300, #99ffff, #33ffff, #66cccc, #00cccc, #339999, #336666, #003333, #ccffff, #66ffff, #33ccff, #3366ff, #3333ff, #000099, #000066, #ccccff, #9999ff, #6666cc, #6633ff, #6600cc, #333399, #330099, #ffccff, #ff99ff, #cc66cc, #cc33cc, #993399, #663366, #330033';
		foreach (explode(',',$formElement->color) as $color) {
			$color = trim($color);
			$ret .= '<span style="background:'.$color.'; display:inline-block; padding:4px; border-radius: 4px; width: 18px; height: 18px;"><input type="radio" name="'.$name.'" value="'.$color.'"'.($color == $formElement->value?' checked="checked"':'').' style="outline: none; box-shadow: none; margin: 0 0 0 2px; padding: 0; height: 16px; width: 16px;"></span>'._NL;
		}
		return $ret;
	}

	function build() {
		return $this->renderForm();
	}

	function get($id = NULL) {
		$this->renderForm();
		return $id ? $this->formArray[$id] : $this->formArray;
	}
} // End of class Form
?>