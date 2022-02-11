<?php
/********************************************
* Class :: Form version 2.0
* Form class for create Form
*
* Created 2020-10-01
* Modify  2021-08-23
*
* @usage new Form([])
********************************************/

class Form extends Widget {
	var $widgetName = 'Form';
	var $tagName = 'form';
	var $method = 'POST';
	var $variable;
	var $readonly = false;
	var $config;
	var $leading;
	var $trailing;
	var $description;
	var $footer;
	var $children = [];

	function __construct($args = []) {
		$this->initConfig();
		if (is_object($args) || is_array($args)) {
			$args = is_array($args) ? (Object) $args : $args;
			parent::__construct($args);
		} else {
			$args = func_get_args();
			$this->variable = $args[0];
			$this->action = $args[1];
			$this->id = $args[2];
			$this->class = $args[3];
		}
	}

	function count() {return count($this->children);}

	function addText($text) {
		$this->children[uniqid()] = $text;
	}

	function addField($key, $value) {
		$this->children[$key] = $value;
	}

	function field($key = NULL) {return $this->children;}

	//TODO:: Move form item to array $formArray
	function renderForm($returnType = 'text') {
		if (empty($this->children)) {
			foreach ($this as $fieldKey => $value) {
				if (is_null($value) || in_array($fieldKey,['id','class','method','action','variable','readonly','config','widgetName','tagName','children','header','leading','enctype'])) continue;
				$this->children[$fieldKey] = $value;
				unset($this->{$fieldKey});
			}
		}

		if ($this->debug) debugMsg($this, '$this');

		$formArray = [];

		$this->config = is_array($this->config) ? (Object) $this->config : $this->config;
		$this->readonly = SG\getFirst($this->config->readonly, $this->readonly);
		$formVariable = $this->variable = SG\getFirst($this->variable, $this->config->variable);
		$formEncrypt = $this->enctype = SG\getFirst($this->enctype, $this->config->enctype);
		$formMethod = $this->method = SG\getFirst($this->config->method, $this->method);
		$formAction = $this->action = SG\getFirst($this->action, $this->config->action);
		$formCheckValid = SG\getFirst($this->checkValid, $this->data['data-checkValid'], $this->data['data-checkvalid']);
		$formTitle = SG\getFirst($this->title, $this->config->title);

		if ($this->action) {
			$ret .= _NL.'<!-- sg-form -->'._NL;
			if (isset($this->leading)) $ret .= $this->leading;
			$formStr = '<form id="'.$this->id.'" '
				. 'class="form '.($this->class ? $this->class.' ':'').($this->readonly ? '-readonly' : '').'" '
				. 'method="'.$formMethod.'" '
				. ($formEncrypt ? 'enctype="multipart/form-data" ' : '')
				. 'action="'.$formAction.'" '
				. (isset($formCheckValid) && $formCheckValid ? 'data-checkvalid="true" ' : '')
				. (isset($this->attribute) ? ' '.(is_array($this->attribute) ? sg_implode_attr($this->attribute) : $this->attribute) : '')
				. (isset($this->config->attr) ? ' '.(is_array($this->config->attr) ? sg_implode_attr($this->config->attr) : $this->config->attr) : '')
				. (isset($this->config->data) ? ' '.(is_array($this->config->data) ? sg_implode_attr($this->config->data) : $this->config->data) : '')
				. ($this->style ? ' style="'.$this->style.'"' : '')
				. ' >';

			$ret .= $formStr._NL._NL;

			$formArray['form'] = $formStr;
		}

		if ($this->header->text) {
			$ret .= '<header class="header'
				. ($this->header->attr->class ? ' '.$this->header->attr->class : '').'">'
				. $this->header->text
				. '</header>';
		}

		if ($formTitle) $ret .= '<h3 class="title">'.$formTitle.'</h3>'._NL;
		if ($this->description) $ret .= '<div class="description">'.$this->description.'</div>';

		foreach ($this->children as $fieldKey => $item) {
			if (is_object($item) && method_exists($item, 'build')) {
				$ret .= $item->build();
			} else if (is_array($item) && array_key_exists('children', $item)) {
				foreach ($item['children'] as $groupKey => $groupItem) {
					// $ret .= $this->_renderChild($formVariable, $groupKey, $groupItem);
					list($tag_id, $renderChildrenResult) = $this->_renderChild($formVariable, $groupKey, $groupItem);
					$formArray[$tag_id] = $renderChildrenResult;
					$ret .= $renderChildrenResult;
				}
			} else {
				list($tag_id, $renderChildrenResult) = $this->_renderChild($formVariable, $fieldKey, $item);
				$formArray[$tag_id] = $renderChildrenResult;
				$ret .= $renderChildrenResult;
			}
		}

		if ($this->footer) $ret .= $this->footer;
		if ($this->action) $ret .= '</form>'._NL;
		if (isset($this->trailing)) $ret .= $this->trailing;

		return $returnType == 'text' ? $ret : $formArray;
	}

	function _renderChild($formVariable, $fieldKey, $item) {
		if (is_object($item) && method_exists($item, 'build')) {
			return $item->build();
		} else if (is_string($item)) {
			return [NULL, $item._NL._NL];
		} else if (is_array($item)) {
			$item = (Object) $item;
		}

		$name = '';
		$tag_id = '';
		$containerClass = '';

		// Fixed bug :: Old version direct set property with form->field->key = value
		// if (property_exists($this, $fieldKey)) {
		// 	foreach ($this->{$fieldKey} as $fkey => $fvalue) {
		// 		$item->{$fkey} = $fvalue;
		// 	}
		// 	unset($this->$fieldKey);
		// }

		if ($item->config) {
			$item->config = SG\json_decode($item->config);
		}

		if ($item->id) {
			$tag_id = $item->id;
		} else {
			$tag_id = $item->name ? $item->name : ($formVariable ? $formVariable.'-':'').$fieldKey;
			$tag_id = 'edit-'.preg_replace(array('/([\W]+$)+/','/([\W])+/'),array('','-'),$tag_id);
		}

		$tag_id = strtolower($tag_id);

		if ($item->name !== false) {
			$name = $item->name ? $item->name : ($formVariable ? $formVariable.'['.$fieldKey.']' : $fieldKey);
		}

		if (isset($item->container) && is_object($item->container)) {
			$item->container = (Array) $item->container;
		} else if (isset($item->container) && is_string($item->container) && substr($item->container,0,1) == '{') {
			$item->container = (Array) SG\json_decode($item->container);
		}

		$isFormGroup = preg_match('/-group/', $item->container['class']);

		// if ($item->container['type']) {
		// 	switch ($item->container['type']) {
		// 		case 'fieldset' :
		// 			$ret .= '<fieldset class="'.($item->container['collapsible']?'collapsible':'').'">'._NL;
		// 			if ($item->container['legend']) $ret .= '<legend>'.$item->container['legend'].'</legend>'._NL;
		// 			break;
		// 	}
		// 	if ($item->container['collapsible']) $ret.='<div id="'.$tag_id.'" style="display: none; height: auto;" class="fieldset-wrapper">'._NL;
		// }

		$containerClass = $item->containerclass;
		if ($item->container) {
			if ($item->container['class']) {
				$containerClass .= trim(' '.$item->container['class']);
			}
		}

		$ret .= '<div id="form-item-'.$tag_id.'" '
				. 'class="form-'.(in_array($item->type,array('','')) ? $item->type : 'item -'.$tag_id).($containerClass ? ' '.$containerClass : '')
				. ($item->type == 'hidden' ? ' -hidden' : '')
				. '"'
				. ' '.sg_implode_attr($item->container)
				. '>'._NL;
		if ($item->label) {
			$ret .= '	<label for="'.$tag_id.'" class="'.($item->config->label == 'hide' ? '-hidden' : '').'">'.$item->label.($item->require?' <span class="form-required" title="This field is required.">*</span>':'').'</label>'._NL;
		}

		if ($isFormGroup) $ret .= '<span class="form-group">'._NL;
		if ($item->pretext)
			$ret .= $item->pretext;

		if ($item->attr && (is_array($item->attr) || is_object($item->attr))) {
			$item->attr = sg_implode_attr($item->attr);
		}

		switch ($item->type) {
			case 'textfield' : $ret .= $this->_renderTextField($tag_id, $name, $item); break;
			case 'hidden': $ret .= $this->_renderHidden($tag_id, $name, $item); break;
			case 'text' :
			case 'password' : $ret .= $this->_renderTextPassword($tag_id, $name, $item); break;
			case 'textarea' : $ret .= $this->_renderTextArea($tag_id, $name, $item); break;
			case 'radio' :
			case 'checkbox' : $ret .= $this->_renderRadioCheckbox($tag_id, $name, $item); break;
			case 'select' : $ret .= $this->_renderSelect($tag_id, $name, $item); break;
			case 'file' : $ret .= $this->_renderFile($tag_id, $name, $item); break;
			case 'button' : $ret .= $this->_renderButton($tag_id, $name, $item); break;
			case 'submit' : $ret .= $this->_renderSubmit($tag_id, $name, $item); break;
			case 'date' : $ret .= $this->_renderDate($tag_id, $name, $item); break;
			case 'time' : $ret .= $this->_renderTime($tag_id, $name, $item); break;
			case 'hour' : $ret .= $this->_renderHour($tag_id, $name, $item); break;
			case 'colorpicker' : $ret .= $this->_renderColorPicker($tag_id, $name, $item); break;
		}

		if ($item->posttext) $ret .= $item->posttext._NL;
		if ($isFormGroup) $ret .= '</span><!-- form-group -->'._NL;
		if ($item->description) $ret .= _NL.'<div class="description">'.$item->description.'</div>';
		$ret .= _NL.'</div>';

		// if ($item->container['type']) {
		// 	if ($item->container['collapsible']) $ret.=_NL.'</div>';
		// 	$ret .= _NL.'</'.$item->container['type'].'>';
		// }
		$ret .= _NL._NL;
		return [$tag_id, $ret];
	}

	// Render Field

	function _renderTextField($tag_id, $name, $item) {
		return '<div id="'.$tag_id.'">'.$item->value.'</div>';
	}

	function _renderHidden($tag_id, $name, $item) {
		return '<input type="hidden" name="'.$name.'" id="'.$tag_id.'" class="'.($item->require?'-require':'').'" value="'.htmlspecialchars($item->value).'" />'._NL._NL;
	}

	function _renderTextPassword($tag_id, $name, $item) {
		$ret = '<input'
			. ($this->readonly || $item->readonly ?' readonly="readonly"' : '')
			. ($item->autocomplete ? ' autocomplete="'.$item->autocomplete.'"' : '')
			. ($item->maxlength ? ' maxlength="'.$item->maxlength.'"' : '')
			. ($item->size ? ' size="'.$item->size.'"' : '')
			. ($name ? ' name="'.$name.'"' : ' ')
			. ' id="'.$tag_id.'"'
			. ' class="form-'.$item->type
				. ($item->class ? ' '.$item->class : '')
				. ($item->require ? ' -require' : '')
				. ($item->readonly ? ' -readonly' : '')
				. '"'
			. ' type="'.$item->type.'"'
			. ($item->attr ? ' '.$item->attr : '')
			. ($item->style ? ' style="'.$item->style.'"' : '')
			. ($item->{"autocomplete-url"} ? ' autocomplete-url="'.$item->{"autocomplete-url"}.'"' : '')
			. ' value="'.htmlspecialchars($item->value).'"'
			. (isset($item->placeholder) ? ' placeholder="'.$item->placeholder.'"' : '')
			. ' />';
		return $ret;
	}

	function _renderTextArea($tag_id, $name, $item) {
		$ret .= '	<div class="resizable-textarea">'
			. '<textarea'
			. ($this->readonly || $item->readonly ? ' readonly="readonly"' : '')
			. ' cols="'.($item->cols ? $item->cols : '60').'"'
			. ' rows="'.($item->rows ? $item->rows : 10).'"'
			. ' name="'.$name.'"'
			. ' id="'.$tag_id.'"'
			. ' class="form-textarea resizable processed'
				. ($item->require ? ' -require' : '')
				. ($item->readonly ? ' -readonly' : '')
				. ($item->class ? ' '.$item->class : '')
				. '"'
			. ($item->attr ? ' '.$item->attr : '')
			. (isset($item->placeholder) ? ' placeholder="'.$item->placeholder.'"' : '')
			. '>'
			. $item->value
			. '</textarea>'
			. '<div style="margin-right: -4px;" class="grippie"></div>'
			. '</div>';
		return $ret;
	}

	function _renderRadioCheckbox($tag_id, $name, $item) {
		$ret = '';
		// if (!is_array($item->value)) $item->value=(array)$item->value;
		if (!isset($item->display)) $item->display='-block';
		$itemIndex = 0;
		//debugMsg($item,'$item');

		foreach ($item->options as $option_key => $option_value) {
			if (is_null($option_value)) continue;
			$itemIndex++;
			if (is_array($option_value) || is_object($option_value)) {
				//debugMsg('$option_key = '.$option_key);
				//debugMsg($option_value, '$option_value');
				$ret.='<span class="options-group"><span class="options-group-label">'.$option_key.'</span>'._NL;
				//$ret .= $this->_renderRadio($option_key, $tag_id, $item, $option_value['key'], $option_value['label']);

				foreach ($option_value as $option_key=>$option_value) {
					//$ret .= '	<option value="'.$option_key.'"'.(in_array($option_key,$item->value)?' selected="selected"':'').'>&nbsp;&nbsp;'.$option_value.'</option>'._NL;
					$ret .= '<label class="option -'.$item->display.'" >';
					$ret .= '<input'
						. ($this->readonly || $item->readonly ? ' readonly="readonly"':'')
						. ' name="'.$name.($item->multiple ? '['.$option_key.']' : '').'"'
						. ' value="'.$option_key.'"';
					if (is_array($item->value)) {
						$option_value_key=array_keys($item->value);
						$ret .= in_array($option_key,array_intersect(array_keys($item->options),$item->value)) ? ' checked="checked"':'';

						//debugMsg('Array option_key='.$option_key.'<br />');
						//debugMsg($item->options, '$item->options');
						//debugMsg('option_value_key='.print_o($item->value,'$item->value').'<br />');
					} else {
						//echo 'Else option_key='.$option_key.'<br />';
						$ret .= $option_key == $item->value ? ' checked="checked"':'';
					}
					$ret .= ' class="form-'.$item->type.($item->class ? ' '.$item->class : '').($item->require ? ' -require':'').'"'
						. ' type="'.$item->type.'"'
						. ($item->attr?' '.$item->attr:'')
						. ' /> ';
					$ret .= $option_value;

					$ret .= '</label>'._NL;
				}
				$ret.='</span>'._NL;
			} else {
				//$ret .= $this->_renderRadio($fieldKey, $tag_id, $item, $option_key, $option_value);
				if ($item->separate) {
					$name = $item->name ? $item->name.$option_key : ($this->variable ? $this->variable.'['.$fieldKey.$option_key.']' : $fieldKey);
				}

				if ($item->config->capsule) {
					$ret .= '<'.$item->config->capsule->tag.' class="'.$item->config->capsule->class.'">';
				}
				$ret .= '		<label class="option'.($item->display ? ' '.$item->display : '').'">';
				if (substr($option_value, 0, 6) == '&nbsp;') {
					// Show label only
				} else {
					if (preg_match('/^\s/', $option_value)) {
						$ret .= '&nbsp;&nbsp;&nbsp;&nbsp;';
					}
					$ret .= '<input id="'.$tag_id.'-'.$itemIndex.'"'
						. ($this->readonly || $item->readonly ? ' readonly="readonly" disabled="disabled"' : '')
						. ' name="'.$name.($item->multiple ? '['.$option_key.']' : '').'"'
						. ' value="'.$option_key.'"';
					if (is_array($item->value)) {
						$option_value_key = array_keys($item->value);
						$ret .= in_array($option_key, array_intersect(array_keys($item->options), $item->value)) ? ' checked="checked"':'';
					} else if (isset($item->value) && $option_key == $item->value) {
						$ret .= ' checked="checked"';
					}
					$ret .= ' class="form-'.$item->type.($item->class ? ' '.$item->class : '').($item->require?' -require':'').'"'
						. ' type="'.$item->type.'"'
						. ($item->attr ? ' '.$item->attr:'')
						. ' /> ';
				}
				$ret .= $option_value;
				$ret .= '</label>'._NL;
				if ($item->config->capsule) {
					$ret .= '</'.$item->config->capsule->tag.'>';
				}
			}
		}
		return $ret;
	}

	// Method _renderRadioNew not used/ not test
	function _renderRadioNew($fieldKey, $tag_id, $item, $option_key, $option_value) {
		$ret = '';
		if ($item->separate) {
			$name = $item->name ? $item->name.$option_key : ($this->variable ? $this->variable.'['.$fieldKey.$option_key.']' : $fieldKey);
		}

		if ($item->config->capsule) {
			$ret .= '<'.$item->config->capsule->tag.' class="'.$item->config->capsule->class.'">';
		}
		$ret .= '		<label class="option'.($item->display ? ' '.$item->display : '').'">';
		if (substr($option_value, 0, 6) == '&nbsp;') {
			// Show label only
		} else {
			$ret .= '<input id="'.$tag_id.'-'.$itemIndex.'"'
				. ($this->readonly || $item->readonly ? ' readonly="readonly" disabled="disabled"' : '')
				. ' name="'.$name.($item->multiple ? '['.$option_key.']' : '').'"'
				. ' value="'.$option_key.'"';
			if (is_array($item->value)) {
				$option_value_key = array_keys($item->value);
				$ret .= in_array($option_key, array_intersect(array_keys($item->options), $item->value)) ? ' checked="checked"':'';
			} else if (isset($item->value) && $option_key == $item->value) {
				$ret .= ' checked="checked"';
			}
			$ret .= ' class="form-'.$item->type.($item->class ? ' '.$item->class : '').($item->require?' -require':'').'"'
				. ' type="'.$item->type.'"'
				. ($item->attr ? ' '.$item->attr:'')
				. ' /> ';
		}
		$ret .= $option_value;
		$ret .= '</label>'._NL;
		if ($item->config->capsule) {
			$ret .= '</'.$item->config->capsule->tag.'>';
		}
		return $ret;
	}

	function _renderSelect($tag_id, $name, $item) {
		if (!is_array($item->value)) $item->value = (Array) $item->value;
		$selectStr = '	<select '
			. ($this->readonly || $item->readonly ? 'readonly="readonly" ' : '').' '
			. ($item->multiple ? 'multiple="multiple" ' : '').($item->size?'size="'.$item->size.'" ':'')
			. ' name="'.$name.'" id="'.$tag_id.'" '
			. 'class="form-'.$item->type.($item->class ? ' '.$item->class : '').($item->require ? ' -require' : '').($this->readonly || $item->readonly ? ' -disabled' : '').'"'
			. ($item->onChange ? ' onChange=\''.$item->onChange.'\'' : '')
			. ($item->style ? 'style="'.$item->style.'"' : '')
			. ($item->attr ? ' '.$item->attr : '')
			. ($item->attribute ? ' '.$item->attribute : '')
			. '>'._NL;

		if (is_string($item->options) && preg_match('/^\</', $item->options)) {
			// Option is tag
			$selectStr .= $item->options;
			unset($item->options);
		} else if (is_string($item->options)) {
			// Option is string and contain ,
			$selectOptions = array();
			foreach (explode(',', $item->options) as $eachOption) {
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
			$item->options = $selectOptions;
		}

		foreach ($item->options as $option_key => $option_value) {
			if (is_object($option_value)) $option_value = (Array) $option_value;
			if (is_array($option_value) && array_key_exists('label', $option_value)) {
				// Option is array has key label : [1=>"label", attr=>["data-key"=>"data-key-value",...]]
				$selectStr .= '	<option '
					. 'value="'.$option_key.'"'
					. (in_array($option_key,$item->value)?' selected="selected"':'')
					. ($option_value['attr'] ? ' '.sg_implode_attr($option_value['attr']) : '')
					. '>'
					. $option_value['label']
					. '</option>'._NL;
			} else if (is_array($option_value)) {
				// Option is array, then make option group
				$selectStr .= '	<optgroup label="'.$option_key.'">'._NL;
				foreach ($option_value as $option_key=>$option_value) {
					$selectStr .= '	<option value="'.$option_key.'"'.(in_array($option_key,$item->value)?' selected="selected"':'').'>&nbsp;&nbsp;'.$option_value.'</option>'._NL;
				}
				$selectStr .= '	</optgroup>'._NL;
			} else if (substr($option_key,0,3) === 'sep') {
				// Option is seperatpr
				$selectStr .= '<option disabled="disabled">'.$option_value.'</option>';
			} else {
				// Option is string
				$selectStr .= '	<option value="'.$option_key.'"'.(in_array($option_key,$item->value)?' selected="selected"':'').'>'.$option_value.'</option>'._NL;
			}
		}
		$selectStr .= '	</select>';
		$ret .= $selectStr;
		return $ret;
	}

	function _renderFile($tag_id, $name, $item) {
		if ($item->count) {
			for ($i = 1; $i <= $item->count; $i++) {
				$ret .= '	<input '.($item->size?'size="'.$item->size.'" ':'').' name="'.$name.'['.$i.']" id="'.$tag_id.'-'.$i.'" class="form-'.$item->type.($item->require?' -require':'').'" type="'.$item->type.'" />';
			}
		} else {
			$ret .= '	<input '.($item->size?'size="'.$item->size.'" ':'').' name="'.$name.'" id="'.$tag_id.'" class="form-'.$item->type.($item->class ? ' '.$item->class : '').($item->require?' -require':'').'" type="'.$item->type.'"'.($item->multiple?'multiple="multiple"':'').' />';
		}
		return $ret;
	}

	function _renderButton($tag_id, $name, $item) {
		if (empty($item->items) && !empty($item->value)) {
			$ret .= '	<button type="submit" '.(empty($item->name) ? '' : 'name="'.$name.'"').' class="btn '.SG\getFirst($item->class, '-primary').'" value="'.htmlspecialchars(strip_tags($item->value)).'" '.($this->readonly || $item->readonly ? 'disabled="disabled" ' : '').'>'.SG\getFirst($item->text, $item->value).'</button> ';
		} else if (is_array($item->items) && !empty($item->items['value'])) {
			$ret .= '	<button'.(isset($item->items['type'])?' type="'.$item->items['type'].'"':'').' name="'.(isset($item->items['name'])?$item->items['name']:$name).'" class="btn'.($item->items['class']?' '.$item->items['class']:'').'" value="'.htmlspecialchars(strip_tags($item->items['value'])).'" '.($this->readonly || $item->readonly?'disabled="disabled" ':'').'>'.$item->items['value'].'</button> ';
		} else {
			foreach ($item->items as $key => $button) {
				if (is_null($button)) {
					continue;
				} else if ($button['type'] == 'text') {
					$ret .= $button['value'];
				} else {
					$ret .= '	<button'
						. (isset($button['type'])?' type="'.$button['type'].'"':'')
						. ' name="'.SG\getFirst($button['name'],is_string($key) ? $key : $name).'" '
						. 'class="btn'.($button['class']?' '.$button['class']:'').'" '
						. 'value="'.SG\getFirst($button['btnvalue'],htmlspecialchars(strip_tags($button['value']))).'" '
						. ($this->readonly || $item->readonly?'disabled="disabled" ':'').'>'
						. $button['value']
						. '</button> ';
				}
			}
		}
		return $ret;
	}

	function _renderSubmit($tag_id, $name, $item) {
		$ret = '';
		foreach ($item->items as $key=>$value) {
			if (substr($key,0,4)=='text') $ret .= $value;
			else $ret .= '	<input name="'.$key.'" '.($this->isReadOnly || $item->readonly?'readonly="readonly" ':'').'class="btn'.($key=='save'?' -primary':'').' -'.$key.($item->class?' '.$item->class:'').'"  type="'.$item->type.'" value="'.$value.'" />';
		}
		return $ret;
	}

	function _renderDate($tag_id, $name, $item) {
		$months['BC'] = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
		$months['DC'] = ['Jan','Feb','Apr','March','May','June','July','Aug','Sep','Oct','Nov','Dec'];

		list($year_from,$year_no,$year_sort) = explode(',',$item->year->range);
		if (empty($year_from)) $year_from = date('Y');
		else if (in_array(substr($year_from,0,1),array('-','+'))) $year_from = date('Y')+$year_from;
		if (empty($year_no)) $year_no = 5;

		$ret = '<select id="'.$tag_id.'-date" class="form-select" name="'.$name.'[date]" '.($this->readonly || $item->readonly?'readonly="readonly" disabled="disabled" ':'').'>'._NL;
		$ret .= '<option value="">'.($item->year->type=='BC'?'วันที่':'Date').'</option>'._NL;
		for ($i = 1; $i <= 31; $i++) {
			$ret .= '<option value="'.sprintf('%02d',$i).'"'.($item->value->date==$i?' selected':'').'>'.sprintf('%02d',$i).'</option>'._NL;
		}
		$ret .= '</select>'._NL;
		$ret .= '<select id="'.$tag_id.'-month" class="form-select" name="'.$name.'[month]" '.($this->readonly || $item->readonly?'readonly="readonly" disabled="disabled" ':'').'>'._NL;
		$ret .= '<option value="">'.($item->year->type=='BC'?'เดือน':'Month').'</option>'._NL;
		for ($i = 1; $i <= 12; $i++) {
			$ret .= '<option value="'.sprintf('%02d',$i).'"'.($item->value->month==$i?' selected':'').'>'.sprintf('%02d',$i).'-'.($item->year->type=='BC'?$months['BC'][$i-1]:$months['DC'][$i-1]).'</option>'._NL;
		}
		$ret .= '</select>'._NL;
		$ret .= '<select id="'.$tag_id.'-year" class="form-select" name="'.$name.'[year]" '.($this->readonly || $item->readonly?'readonly="readonly" disabled="disabled" ':'').'>'._NL;
		$ret .= '<option value="">'.($item->year->type=='BC'?'ปี พ.ศ.':'Year').'</option>'._NL;
		if ($year_sort == 'DESC') {
			for ($i = $year_from; $i > $year_from-$year_no; $i--) {
				$ret.='<option value="'.$i.'"'.($item->value->year==$i?' selected':'').'>'.($item->year->type=='BC'?$i+543:$i).'</option>'._NL;
			}
		} else {
			for ($i = $year_from; $i < $year_from+$year_no; $i++) {
				$ret .= '<option value="'.$i.'"'.($item->value->year==$i?' selected':'').'>'.($item->year->type=='BC'?$i+543:$i).'</option>'._NL;
			}
		}
		$ret .= '</select>'._NL;
		return $ret;
	}

	function _renderTime($tag_id, $name, $item) {
		$times = [];
		$start_time = SG\getFirst($item->start, 0);
		$end_time = SG\getFirst($item->end ,24);
		$step_time = SG\getFirst($item->step, 15);
		for ($hr = $start_time; $hr < $end_time; $hr++) {
			for ($min = 0; $min < 60; $min += $step_time) {
				$times[] = sprintf('%02d',$hr).':'.sprintf('%02d',$min);
			}
		}
		$ret = '<select id="'.$tag_id.'" class="form-select'.($item->class?' '.$item->class:'').'" name="'.$name.'">'._NL;
		foreach ($times as $time) {
			$ret .= '<option value="'.$time.'"'.($time == $item->value?' selected="selected"':'').'>'.$time.'</option>';
		}
		$ret .= '</select>';
		return $ret;
	}

	function _renderHour($tag_id, $name, $item) {
		$start_time = SG\getFirst($item->start, 0);
		$end_time = SG\getFirst($item->end, 24);
		$step_time = SG\getFirst($item->step, 15);
		$ret = '<select id="'.$tag_id.'" class="form-select" name="'.$name.'[hour]">'._NL;
		for ($hr = $start_time; $hr < $end_time; $hr++) {
			$ret .= '<option value="'.sprintf('%02d',$hr).'"'.($hr == $item->value->hour?' selected="selected"':'').'>'.sprintf('%02d',$hr).'</option>';
		}
		$ret .= '</select> : ';
		$ret .= '<select id="'.$tag_id.'" class="form-select" name="'.$name.'[min]">'._NL;
		for ($min = 0; $min < 60; $min++) {
			$ret .= '<option value="'.sprintf('%02d',$min).'"'.($min == $item->value->min?' selected="selected"':'').'>'.sprintf('%02d',$min).'</option>';
		}
		$ret .= '</select>';
		return $ret;
	}

	function _renderColorPicker($tag_id, $name, $item) {
		$ret = '';
		if (empty($item->color)) $item->color='#ffffff, #cccccc, #c0c0c0, #999999, #666666, #333333, #000000, #ffcccc, #ff6666, #ff0000, #cc0000, #990000, #660000, #330000, #ffcc99, #ff9966, #ff9900, #ff6600, #cc6600, #993300, #663300, #ffff99, #ffff66, #ffcc66, #ffcc33, #cc9933, #996633, #663333, #ffffcc, #ffff33, #ffff00, #ffcc00, #999900, #666600, #333300, #99ff99, #66ff99, #33ff33, #33cc00, #009900, #006600, #003300, #99ffff, #33ffff, #66cccc, #00cccc, #339999, #336666, #003333, #ccffff, #66ffff, #33ccff, #3366ff, #3333ff, #000099, #000066, #ccccff, #9999ff, #6666cc, #6633ff, #6600cc, #333399, #330099, #ffccff, #ff99ff, #cc66cc, #cc33cc, #993399, #663366, #330033';
		foreach (explode(',',$item->color) as $color) {
			$color = trim($color);
			$ret .= '<span style="background:'.$color.'; display:inline-block; padding:4px; border-radius: 4px; width: 18px; height: 18px;"><input type="radio" name="'.$name.'" value="'.$color.'"'.($color == $item->value?' checked="checked"':'').' style="outline: none; box-shadow: none; margin: 0 0 0 2px; padding: 0; height: 16px; width: 16px;" /></span>'._NL;
		}
		return $ret;
	}

	function build() {
		return $this->renderForm();
	}

	function get($id = NULL) {
		$result = $this->renderForm('array');
		return $id ? $result[$id] : $result;
	}
} // End of class Form
?>