<?php
/**
* Widget  :: Basic Widgets Collector
* Created :: 2020-10-01
* Modify  :: 2023-10-27
* Version :: 24
*
* @param Array $args
* @return Widget
*
* @usage new Widget([key => value,...])
*/

/**
* Class :: WidgetBase
* Widget class for base of all widget
*/
class WidgetBase {
	var $widgetName = 'Widget';
	var $version;
	function __construct($args = []) {
		foreach ($args as $argKey => $argValue) {
			$this->{$argKey} = $argValue;
		}
	}

	function extension() {
		// debugMsg('get_class = '.get_class($this));
		// debugMsg(get_class_methods($this), '$methid');
		// \EXTENSION\PPI\ProjectJoinList::test2();
	}
} // End of class WidgetBase

class Widget extends WidgetBase {
	var $widgetName = 'Widget';
	var $version;
	var $tagName = '';
	var $childTagName;
	var $id;
	var $class;
	var $itemClass;
	var $child;
	var $children = [];
	var $config = NULL; // Object
	var $attribute = [];
	var $attributeText;

	function __construct($args = []) {
		$this->initConfig();
		foreach ($args as $argKey => $argValue) {
			if ($argKey === 'class') $this->class .= ($this->class ? ' ' : '').$argValue;
			else if ($argKey === 'config' && is_array($argValue)) $argValue = (Object) $argValue;
			else if ($argKey === 'attribute' && is_array($argValue)) {
				$this->attribute = array_replace_recursive($this->attribute, $argValue);
			} else if ($argKey === 'children') {
				foreach ($argValue as $childName => $childValue) {
					if (is_null($childValue)) continue;
					$this->children[$childName] = $childValue;
				}
			} else if (preg_match('/^(data\-)(.*)/', $argKey, $out) || in_array($argKey, ['rel', 'before', 'done', 'boxWidth', 'boxHeight'])) {
				if ($out) $argKey = $out[2];
				$this->{$argKey} = $argValue;
				$this->data($argKey, $argValue);
				$this->attribute['data-'.$argKey] = $argValue;
			} else {
				$this->{$argKey} = $argValue;
			}
		}
		if ($this->widgetName == 'Widget') $this->widgetName = get_class($this);
		$this->initWidget();
	}

	// @override
	function initWidget() {}

	function initConfig() {
		$this->config = (Object) [
			'attr' => [],
			'data' => [],
			'header' => (Object) [],
		];
	}

	function addClass($class) {$this->config->class .= ' '.$class;}

	function addId($id) {$this->id = $id;}

	function addConfig($key,$value) {
		// if (!isset($this->config)) $this->initConfig();
		$this->config->{$key} = $value;
	}

	function addAttr($key,$value) {$this->config->attr[$key] = $value;}

	function addData($key,$value) {$this->config->data['data-'.$key] = $value;}

	function config($key, $value) {$this->config->{$key} = $value; return $key ? $this->config->{$key} : $this->config;}

	function attr($key, $value) {$this->config->attr[$key] = $value; return $key ? $this->config->attr[$key] : $this->config->attr;}

	function data($key = NULL, $value = NULL) {
		if (isset($key) && isset($value)) $this->config->data['data-'.$key] = $value;
		return $key ? $this->config->data['data-'.$key] : $this->config->data;
	}

	function header($str, $attr = '{}', $options = '{}') {
		$this->header = (Object) array('text' => $str, 'attr' => \SG\json_decode($attr), 'options' => \SG\json_decode($options));
	}

	function children($value = NULL) {
		$this->children[] = $value;
	}

	// Container of widget
	// @override
	function _renderWidgetContainerStart($callbackFunction = NULL) {
		return $this->tagName ?
			'<'.$this->tagName._NL
			. ' class="widget-'.strtolower($this->widgetName).($this->class ? ' '.$this->class : '')
			. ($this->mainAxisAlignment ? ' -main-axis-'.strtolower($this->mainAxisAlignment) : '')
			. ($this->crossAxisAlignment ? ' -cross-axis-'.strtolower($this->crossAxisAlignment) : '')
			. '"'._NL
			. ($this->id ? ' id="'.$this->id.'"'._NL : '')
			. ($this->href ? ' href="'.$this->href.'"'._NL : '')
			. ($this->config->data['data-rel'] ? ' data-rel="'.$this->config->data['data-rel'].'"'._NL : '')
			. ($this->config->data['data-done'] ? ' data-done="'.$this->config->data['data-done'].'"'._NL : '')
			. ($this->dataUrl ? ' data-url="'.$this->dataUrl.'"'._NL : '')
			. ($this->webview ? ' data-webview="'.$this->webview.'"'._NL : '')
			. ($this->data('options') ? 'data-options=\''.$this->data('options').'\''._NL : '')
			. ($this->data('class-name') ? 'data-class-name="'.$this->data('class-name').'"'._NL : '')
			. ($this->style ? ' style="'.$this->style.'"'._NL : '')
			. ($this->attribute && is_array($this->attribute) ? ' '.sg_implode_attr($this->attribute)._NL : '')
			. ($this->attributeText ? ' '.$this->attributeText._NL : '')
			. ($callbackFunction && is_callable($callbackFunction) ? $callbackFunction() : '')
			. '>'._NL
		: '';
	}

	// @override
	function _renderWidgetContainerEnd() {
		return $this->tagName ? '</'.$this->tagName.'>'._NL : '';
	}

	// Container cover all children
	// @override
	function _renderChildrenContainerStart() {
		if (empty($this->childrenContainer)) return;
		return '<'.$this->childrenContainer['tagName']
			. ($this->childrenContainer['class'] ? ' class="'.$this->childrenContainer['class'].'"' : '')
			.' >'._NL;
	}

	// @override
	function _renderChildrenContainerEnd() {
		return $this->childrenContainer ? '</'.$this->childrenContainer['tagName'].'>'._NL : '';
	}

	// Container for each child of children
	// @override
	function _renderChildContainerStart($childrenKey, $args = [], $childrenValue = []) {
		$childTagName = \SG\getFirst($this->childTagName, $this->childContainer['tagName']);
		return $childTagName ? '<'.$childTagName
			. ' id="'.($args['id'] ? $args['id'] : '').'"'
			. ' class="'.($this->childContainer['class'] ? $this->childContainer['class']: '')
			. ($this->itemClass ? ' '.$this->itemClass : '')
			. (!is_numeric($childrenKey) ? ' -'.$childrenKey : '')
			. ($args['class'] ? ' '.trim($args['class']) : '').'"'
			. '>'
			: '';
	}

	// @override
	function _renderChildContainerEnd() {
		$childTagName = \SG\getFirst($this->childTagName, $this->childContainer['tagName']);
		return $this->childContainer ? '</'.$childTagName.'>' : '';
	}

	// @override
	function _renderChildren($childrens = [], $args = []) {
		if (empty($childrens)) $childrens = [];
		if ($this->body) $childrens[] = $this->body;
		if ($this->child) $childrens[] = $this->child;
		if ($this->children) $childrens = $childrens + $this->children;

		$ret .= $this->_renderChildrenContainerStart();

		foreach ($childrens as $childrenKey => $childrenValue) {
			$extraArgs = [];
			if (is_string($childrenValue) && $childrenValue === '<sep>') $extraArgs['class'] = $args['class'].' -sep';
			$ret .= $this->_renderChildContainerStart($childrenKey, $args + $extraArgs, $childrenValue);
			$ret .= $this->_renderEachChildWidget($childrenKey, $childrenValue);
			$ret .= $this->_renderChildContainerEnd()._NL;
		}

		$ret .= $this->_renderChildrenContainerEnd();
		return $ret;
	}

	// @override
	function _renderEachChildWidget($key, $widget) {
		$result = '';
		if (is_object($widget) && method_exists($widget, 'build')) {
			// Build Widget
			$buildResult = $widget->build();
			if (is_object($buildResult) && method_exists($buildResult, 'build')) {
				$result .= $buildResult->build();
			} else {
				$result .= $buildResult;
			}
		} else if (is_object($widget)) {
			// Build General Object
			$result .= \SG\json_encode($widget);
		} else if (is_array($widget)) {
			// Build Array
			$result .= \SG\json_encode($widget);
		} else if (is_string($widget) && $widget === '<sep>') {
			// Build Seperator
			$result = '<hr class="separator" size="0" />';
		} else {
			// Build Text
			$result .= $widget;
		}
		return $result;
	}

	// @override
	function toString() {
		$ret = $this->widgetName != 'Widget' ? '<!-- Start of '.$this->widgetName.' -->'._NL : '';
		$ret .= $this->_renderWidgetContainerStart();
		if ($this->header) {
			if (is_object($this->header) && method_exists($this->header, 'build')) {
				$ret .= $this->header->build();
			} if (is_string($this->header)) {
				$ret .= $this->header;
			}
		}
		if (isset($this->children) || isset($this->child) || isset($this->body)) {
			$ret .= $this->_renderChildren();
		}
		$ret .= $this->_renderWidgetContainerEnd()._NL;
		$ret .= $this->widgetName != 'Widget' ? '<!-- End of '.$this->widgetName.' -->'._NL : '';
		return $ret;
	}

	// @override
	function build() {return $this->toString();}

	// @deprecated
	function show() {return $this->build();}
} // End of class Widget


/**
* Basic Widget
*/

class Container extends Widget {
	var $widgetName = 'Container';
	var $tagName = 'div';
	var $fillButton = false;

	function __construct($args = []) {
		// debugMsg($args, '$args');
		parent::__construct($args);
	}

	// @override
	function toString() {
		$ret = '<!-- Start of '.$this->widgetName.' -->'._NL;
		$ret .= $this->_renderWidgetContainerStart();
		if (isset($this->children) || isset($this->child) || isset($this->body)) {
			$ret .= $this->_renderChildren();
		}
		$ret .= $this->_renderWidgetContainerEnd();
		$ret .= '<!-- End of '.$this->widgetName.' -->'._NL;
		return $ret;
	}
} // End of class Container

class Center extends Widget {
	var $widgetName = 'Center';
	var $tagName = 'div';
	var $class = '-sg-text-center';

	function __construct($args = []) {
		parent::__construct($args);
	}
} // End of class Center

class Column extends Widget {
	var $widgetName = 'Column';
	var $tagName = 'div';
	var $childContainer = ['tagName' => 'div', 'class' => '-item'];

	function __construct($args = []) {
		parent::__construct($args);
	}
} // End of class Column

class Row extends Widget {
	var $widgetName = 'Row';
	var $version = '0.0.10';
	var $tagName = 'div';
	var $childContainer = ['tagName' => 'div', 'class' => '-item'];
} // End of class Row

class FloatingActionButton extends Widget {
	var $widgetName = 'FloatingActionButton';
	var $tagName = 'div';
	var $childContainer = ['tagName' => 'div', 'class' => '-item'];
	// var $fillButton = false;

	// function toString() {
	// 	$ret = '<div class="widget-'.strtolower($this->widgetName).' -right-bottom'.($this->fillButton ? ' -fill-button' : '').'">';
	// 	$ret .= $this->_renderChildren();
	// 	$ret .= '</div>';
	// 	return $ret;
	// }
} // End of class FloatingActionButton

class ListTile extends Widget {
	var $widgetName = 'ListTile';
	var $tagName = 'div';
	var $titleTag = 'span';
	var $leading;
	var $title;
	var $subtitle;
	var $trailing;

	function __construct($args = []) {
		parent::__construct($args);
	}

	// @override
	function toString() {
		return $this->_renderWidgetContainerStart()
			. ($this->leading ? '<div class="-leading">'.$this->_renderEachChildWidget(NULL, $this->leading).'</div>'._NL : '')
			. '<div class="-title">'
			. ($this->title ? '<'.$this->titleTag.' class="-title-text">'.$this->_renderEachChildWidget(NULL, $this->title).'</'.$this->titleTag.'>' : '')
			. ($this->subtitle ? '<span class="-subtitle-text">'.$this->_renderEachChildWidget(NULL, $this->subtitle).'</span>' : '')
			. '</div>'._NL
			. ($this->trailing ? '<div class="-trailing">'.$this->_renderEachChildWidget(NULL, $this->trailing).'</div>'._NL : '')
			. $this->_renderWidgetContainerEnd();
	}
} // End of class ListTile

class Card extends Widget {
	var $widgetName = 'Card';
	var $tagName = 'div';

	function __construct($args = []) {
		parent::__construct($args);
	}
} // End of class Card

class Nav extends Widget {
	var $widgetName = 'Nav';
	var $tagName = 'nav';
	var $class = 'nav';
	var $childrenContainer = ['tagName' => 'ul'];
	var $childContainer = ['tagName' => 'li', 'class' => '-item'];

	function __construct($args = []) {
		parent::__construct($args);
		if ($args['type']) $this->class .= ' -'.$args['type'];
	}
} // End of class Nav

class ScrollView extends Widget {
	var $widgetName = 'ScrollView';
	var $tagName = 'div';
	var $scrollDirection = 'horizontal';

	function __construct($args = []) {
		parent::__construct($args);
	}
} // End of class ScrollView

class DebugMsg extends Widget {
	var $msg;
	var $varName;

	function __construct($msg = NULL, $varName = NULL) {
		$this->msg = $msg;
		$this->varName = $varName;
	}

	function build() {
		if (is_object($this->msg) || is_array($this->msg)) {
			if (function_exists('\SG\print_o')) {
				$this->msg = \SG\print_o($this->msg, $this->varName);
			} else {
				$this->msg = print_r($this->msg,1);
			}
		}
		if (isset($this->msg) && user_access('access debugging program')) {
			if (preg_match('/^(SELECT|UPDATE|INSERT|DELETE)/i', $this->msg)) {
				$this->msg = '<pre>'.$this->msg.'</pre>';
			}
			return "\r\n".'<div class="debug-msg">'.$this->msg.'</div>'."\r\n";
		}
	}
} // End of class DebugMsg

class Message extends WidgetBase {
	var $responseCode;
	var $text;
	function __construct($args = []) {
		parent::__construct($args);
		unset($this->widgetName, $this->version);
	}

	// @override
	function build() {
		if ($this->responseCode) http_response_code($this->responseCode);
		return $this->text;
	}
} // End of class Message

class ErrorMessage extends Message {
	var $widgetName = 'ErrorMessage';
	// function build() {
	// 	return (Object) ['responseCode' => $this->responseCode, 'text' => $this->text];
	// }
} // End of class ErrorMessage

// Element wiget
class Button extends Widget {
	var $widgetName = 'Button';
	var $version = '0.01';
	var $tagName = 'a';
	var $href;
	var $type; // default, primary, link, floating, secondary,success, info, warning, danger, link, cancel
	var $text;
	var $icon;
	var $iconPosition = 'left'; // left,right,top,bottom
	var $variable;
	var $description;

	function __construct($args = [], $variable = NULL) {
		parent::__construct($args);
		$this->variable = $variable;
	}

	function toString() {
		// Check right by access
		if ($this->access) {
			if (!defined($this->access)) return NULL;
			else if (!($this->variable->RIGHT & constant($this->access))) return NULL;
		}

		$attribute = array_replace_recursive([
			'href' => $this->href,
			'id' => $this->id,
			'class' => trim(
				'widget-'.strtolower($this->widgetName)
				. (empty($this->type) ? '' : ' btn')
				. ($this->type ? ' -'.$this->type : '')
				. ($this->class ? ' '.$this->class : '')
			),
			'title' => \SG\getFirst($this->title),
			'data-rel' => \SG\getFirst($this->rel),
			'data-before' => \SG\getFirst($this->before),
			'data-done' => \SG\getFirst($this->done),
			'target' => \SG\getFirst($this->target),
			// 'onClick' => $this->onClick ? $this->onClick : NULL,
			'style' => $this->style,
		], (Array) $this->attribute);

		if (is_null($attribute['href'])) {
			unset($attribute['href']);
		} else {
			$attribute['href'] = preg_replace('/\{\{projectId\}\}/', $this->variable->projectId, $attribute['href']);
		}

		$button = '<a '
			. sg_implode_attr($attribute)
			. ($this->onClick ? ' onClick=\''.$this->onClick.'\'' : '')
			. '>'
			. ($this->icon && $this->iconPosition == 'left' ? $this->_renderChildren([$this->icon]) : '')
			. ($this->text ? '<span class="-label">' . $this->text . ($this->description ? '<em class="-desc">'.$this->description.'</em>' : '') . '</span>' : '')
			. ($this->icon && $this->iconPosition == 'right' ? $this->_renderChildren([$this->icon]) : '')
			. '</a>';
		return $button;
	}
} // End of class Button

// Usage: new Icon(iconName, property=[])
class Icon extends Widget {
	var $widgetName = 'Icon';
	var $version = '0.02';
	var $icon;
	var $type = 'material';

	function __construct($icon, $args = []) {
		$this->icon = $icon;
		parent::__construct($args);
	}

	function toString() {
		if (is_string($this->icon) && preg_match('/$</', $this->icon)) return $this->icon;

		if (is_object($this->icon)) return $this->icon;

		$attribute = array_replace_recursive(
			$this->attribute,
			[
				'class' => trim('widget-'.strtolower($this->widgetName).' icon -material '.\SG\getFirst($this->class))
			]
		);
		return '<i '.sg_implode_attr($attribute).'>'
			. $this->icon
			. '</i>';
	}
} // End of class Icon

class ExpandButton extends Widget {
	var $icon = 'chevron_right';
	function toString() {
		return '<a class="sg-expand btn -link -no-print" href="javascript:void(0)"><i class="icon -material">'.$this->icon.'</i></a>';
	}
} // End of class ExpandButton

class InlineEdit extends Widget {
	var $widgetName = 'InlineEdit';
	var $tagName = 'span';
	var $version = '0.02';
	var $class;
	var $editMode = false;
	var $type = 'text';
	var $text;
	var $value;
	var $label;
	var $group;
	var $field;
	var $tranId;
	var $updateUrl;
	var $retType;
	var $inputClass = NULL;
	var $inputName;
	var $title = 'คลิกเพื่อแก้ไข';
	var $placeholder = '...';
	var $selectOptions = [];
	var $options = [];

	function __construct($args = []) {
		parent::__construct($args);
		if ($this->editMode && $this->updateUrl) $this->class = 'sg-inline-edit'.' '.$this->class;
		$this->class .= ' inline-edit-item -'.$this->type;
		$this->text = trim($this->text);
		if (!isset($this->value)) $this->value = $this->text;
	}

	function _render() {
		if (!$this->editMode) return $this->_renderText();

		$ret = '';

		if ($this->label) {
			$ret .= '<label class="inline-edit-label'
				. ($this->labelClass ? ' '.$this->labelClass : '')
				. '"'
				. ($this->labelStyle ? ' style="'.$this->labelStyle.'"' : '')
				. '>'
				. $this->label
				. '</label>';
		}

		$text = $this->text;
		if (is_null($text) || $text == '') $text = '<span class="placeholder -no-print">'.$this->placeholder.'</span>';
		else if ($this->retType === 'nl2br') $text = trim(nl2br($text));
		else if ($this->retType === 'html') $text = trim(sg_text2html($text));
		else if ($this->retType === 'text') $text = trim(str_replace("\n",'<br />',$text));
		else if ($this->retType === 'money' && $text != '') $text = number_format(sg_strip_money($text), 2);
		else if (preg_match('/^date/i', $this->retType) && $text) {
			list($this->retType, $retFormat) = explode(':', $this->retType);
			if (!$retFormat) $retFormat = 'ว ดดด ปปปป';
			$text = sg_date($this->value, $retFormat);
		}

		if (is_string($this->selectOptions)) $selectOptions = explode(',', '==เลือก==,' . $this->selectOptions);
		else if (is_array($this->selectOptions) && count($this->selectOptions) > 0) $selectOptions = ['==เลือก=='] + $this->selectOptions;

		switch ($this->type) {
			case 'textfield': $ret .= $this->_renderTypeTextField($text); break;
			case 'radio':
			case 'checkbox': $ret .= $this->_renderTypeRadio(); break;
			// case 'select': $ret .= $this->_renderTypeSelect($text); break;
			default: $ret .= $this->_renderTypeText($text); break;
		}
		// $ret .= '</span>'._NL;

		return $ret;
	}

	function _renderTypeTextField($text) {
		return '<span class="inline-edit-view">'.$text.'</span>';
	}

	function _renderTypeText($text) {
		$options = array_merge_recursive($this->options, ['placeholder' => $this->placeholder]);

		$ret .= '<span'._NL
			. ' class="inline-edit-field -'.$this->type.($this->inputClass ? ' '.$this->inputClass : '').'"'._NL
			. ' onClick=""'._NL
			. ($this->type ? ' data-type="'.$this->type.'"'._NL : '')
			. ($this->group ? ' data-group="'.$this->group.'"'._NL : '')
			. ($this->field ? ' data-fld="'.$this->field.'"'._NL : '')
			. ' data-tr="'.$this->tranId.'"'
			. ($this->retType ? ' data-ret="'.$this->retType.'"'._NL : '')
			. ($this->type === 'textarea' && $options['button'] !== false ? ' data-button="yes"' : '')
			. ' data-value="'.SG\getFirst($this->value, $this->text).'"'._NL
			. ($selectOptions ? ' data-data="'.htmlspecialchars(\json_encode($selectOptions)).'"' : '')
			. ' title="'.$this->title.'"'._NL
			. ' data-options=\''.SG\json_encode($options).'\''._NL
			. '>'._NL;

		$ret .= '<span>'._NL;
		$ret .= $text;
		$ret .= $this->postText;
		$ret .= '</span>'._NL;
		return $ret;
	}

	// function _renderTypeSelect($text) {
	// 	$ret = $text;
	// 	return $ret;
	// }

	function _renderTypeRadio() {
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

	function _renderText() {
		$ret .= '<span class="inline-edit-view '
			.'-'.$this->type
			.($this->inputClass ? ' '.$this->inputClass : '').'" '
			.'>';
		if ($this->retType === 'html') {
			$ret .= trim(sg_text2html($text));
		} else if ($fld['ret'] == 'text') {
			$ret .= trim(str_replace("\n", '<br />', $text));
		} else if ($input_type == "money") {
			$ret .= number_format(sg_strip_money($text), 2);
		} else if (in_array($input_type, array('radio', 'checkbox'))) {
			list($choice, $label, $info) = explode(':', $text);
			$choice = trim($choice);
			$name = getFirst($fld['name'],$fld['fld']);
			if ($label == '' && strpos($text, ':') == false) $label = $choice;
			$label = trim($label);
			$ret .= '<input type="'.$input_type.'" '
				.($fld['value'] == $choice ? 'checked="checked" readonly="readonly" disabled="disabled"' : 'disabled="disabled"')
				.' style="margin:0;margin-top: -1px; display:inline-block;min-width: 1em; vertical-align: middle;" /> '
				.$label;
		} else if (substr($fld['ret'], 0, 4) == 'date') {
			$format = substr($fld['ret'], 5);
			$ret .= $text ? sg_date($text, $format) : '';
		} else {
			$ret .= $this->text;
		}
		$ret .= $this->postText;
		$ret .= '</span>';
		return $ret;
	}

	function _render_v1() {
		// \SG\inlineEdit($fld = [], $text = NULL, $is_edit = NULL, $input_type = 'text', $data = [], $emptytext = '...')
		if ($this->editMode) $this->class .= ' sg-inline-edit';

		$fld = [];
		if ($this->label) $fld['label'] = $this->label;
		if (!is_null($this->group)) $fld['group'] = $this->group;
		if (!is_null($this->field)) $fld['fld'] = $this->field;
		if (!is_null($this->tranId)) $fld['tr'] = $this->tranId;
		if (!is_null($this->inputName)) $fld['name'] = $this->inputName;
		if (!is_null($this->value)) $fld['value'] = $this->value;
		if (!is_null($this->minValue)) $fld['min-value'] = $this->minValue;
		if (!is_null($this->maxValue)) $fld['max-value'] = $this->maxValue;
		if (!is_null($this->require)) $fld['require'] = $this->require;
		if (!is_null($this->retType)) $fld['ret'] = $this->retType;
		if (!is_null($this->preText)) $fld['pretext'] = $this->preText;
		if (!is_null($this->postText)) $fld['posttext'] = $this->postText;
		if (!is_null($this->description)) $fld['desc'] = $this->desc;
		if (!is_null($this->removeEmpty)) $fld['removeempty'] = $this->removeEmpty;
		if (!is_null($this->updateUrl)) $fld['update-url'] = $this->updateUrl;

		$fld['options'] = is_null($this->options) ? [] : $this->options;
		$fld['container'] = is_null($this->container) ? (Object) [] : (Object) $this->container;

		if ($this->inputClass) $fld['options']['class'] = $this->inputClass;
		$fld['container']->class = ($this->class ? ' '.$this->class : '');

		$ret = \SG\inlineEdit($fld, $this->text, $this->editMode, $this->type, $this->selectOptions, $this->emptyText);
		// debugMsg($this, '$this');
		// debugMsg($fld, '$fld');
		return $ret;
	}

	// @override
	function toString() {
		if (is_null($this->text) || $this->text == '') {
			$this->class .= ' -empty';
		}
		$ret = '<!-- Start of '.$this->widgetName.' -->'._NL;
		$ret .= $this->_renderWidgetContainerStart(
			function() {
				return
					($this->updateUrl ? ' data-update-url="'.$this->updateUrl.'"'._NL : NULL);
			}
		);
		$ret .= $this->_render();
		if ($this->debug) $ret .= (new DebugMsg($this, '$this'))->build();
		$ret .= $this->_renderWidgetContainerEnd()._NL;
		$ret .= '<!-- End of '.$this->widgetName.' -->'._NL;
		// $ret .= '<pre>'.htmlspecialchars($ret).'</pre>';
		// $ret .= print_o($this, '$this');
		return $ret;
	}
} // End of class Container

class StepMenu extends Widget {
	var $widgetName = 'StepMenu';
	var $tagName = 'nav';
	var $class = '';
	var $childrenContainer = ['tagName' => 'ul'];
	var $childContainer = ['tagName' => 'li'];
	var $currentStep = NULL;
	var $activeStep = [];

	function __construct($args = []) {
		parent::__construct($args);
	}

	function _renderChildContainerStart($stepIndex, $args = [], $childrenValue = []) {
		$stepIndex++;
		return '<'.$this->childContainer['tagName'].' '
			. 'class="ui-item -step-'.$stepIndex.($this->childContainer['class'] ? $this->childContainer['class'] : '').($stepIndex == $this->currentStep ? ' -current-step' : '').(isset($this->activeStep[$stepIndex]) && $this->activeStep[$stepIndex] ? ' -active' : '').'" '
			. '>';
	}

	// @override
	function _renderChildren($childrens = [], $args = []) {
		return parent::_renderChildren();
	}
} // End of class StepMenu

class ListItem extends Widget {
	var $widgetName = 'ListItem';
	var $forceBuild = false;
	var $seperator = ' · ';
	var $tagName = 'ul';
	var $uiItemClass = 'ui-item -item';
	var $wrapperType = array('ul' => 'li','span' => 'span','div' => 'div', 'div a'=>'a', 'ol'=>'li');
	var $type = 'action';

	function _renderChildren($childrens = [], $args = []) {
		$ret = '';
		foreach ($this->children as $key => $value) {
			if (is_array($value)) {
				$child = (Object) $value;
			} else if (is_object($value)) {
				$child = $value;
			} else {
				$child = (Object) ['text' => $value, 'options' => NULL];
			}

			// Convert options to object
			$options = is_string($child->options) ? \SG\json_decode($child->options): (Object) $child->options;

			$uiItemClass = $this->uiItemClass.($options->class ? ' '.$options->class : '');
			if (in_array($child->text, array('-','<sep>'))) {
				$uiItemClass .= ' -sep';
				$child->text = '<hr size="1" />';
			}
			$options->class = $uiItemClass;

			$uiItemTag = $this->wrapperType[$this->tagName];
			$ret .= $uiItemTag ? '<'.$uiItemTag.' '.sg_implode_attr($options).'>' : '';
			$ret .= $child->text;
			$ret .= $uiItemTag ? '</'.$uiItemTag.'>' : '';
			$ret .= _NL;
		}
		return $ret;
	}

	function build() {
		if (empty($this->children) && $this->forceBuild === false) return;

		// $uiType = ['action' => 'ui-action', 'card' => 'ui-card', 'menu' => 'ui-menu', 'album' => 'ui-album', 'nav' => 'ui-nav'];

		$ret = '';

		$attrText = '';
		$join = $this->tagName;
		$attrs = [];

		if ($this->config->id) $attrs['id'] = $this->config->id;
		else if ($this->id) $attrs['id'] = $this->id;

		$attrs['class'] = 'widget-'.strtolower($this->widgetName);
		if ($this->type) $attrs['class'] .= ' '.$uiType[$this->type];
		if ($this->class) $attrs['class'] .= ' '.$this->class;
		if ($this->columnPerRow) $attrs['class'] .= ' -sg-flex -co-'.$this->columnPerRow;

		foreach ($this->config->data as $key => $value) $attrs[$key] = $value;

		foreach ($this->config->attr as $key => $value) $attrs[$key] = $value;

		foreach ($attrs as $key => $value) {
			$attrText .= $key.'="'.$value.'" ';
		}

		$attrText = trim($attrText);

		list($joinTag) = explode(' ', $join);
		$ret .= '<'.$joinTag.' '.$attrText.'>'._NL;

		if ($this->header->text) {
			$headerClass = $this->header->attr->class;
			unset($this->header->attr->class);
			$ret .= '<header class="header'.($headerClass ? ' '.$headerClass : '').'" '.sg_implode_attr($this->header->attr).'>'
				. ($this->header->options->preText ? $this->header->options->preText : '')
				. $this->header->text
				. ($this->header->options->postText ? $this->header->options->postText : '')
				. '</header>';
			if ($headerClass) $this->header->attr->class = $headerClass;
		}
		$ret .= $this->_renderChildren();
		$ret .= '</'.$joinTag.'>'._NL;

		if ($this->config->nav) {
			$this->container = $this->config->nav;
		} else if ($this->config->container) {
			$this->container = $this->config->container;
		}

		if ($this->container) {
			$container = \SG\json_decode($this->container);
			$containerTag = $this->config->nav ? 'nav' : $container->tag;
			unset($container->tag);
			$containerAttr = sg_implode_attr($container);

			$ret = '<'.$containerTag.' '.$containerAttr.'>'.$ret.'</'.$containerTag.'>';
		}

		return $ret;
	}
} // End of class ListItem

class TabBar extends Widget {
	var $widgetName = 'TabBar';
	var $tagName = 'div';
	var $class = 'sg-tabs';

	function _renderChildren($childrens = [], $args = []) {
		$tabItems = '<ul class="tabs">';
		$tabContent = '';
		foreach ($this->children as $key => $child) {
			if (is_array($child)) $child = (Object) $child;

			$tabItems .= '<li'
				// . ($child->id ? ' id="'.$child->id.'"' : '')
				. ' class="'.$child->class.($child->active ? ' -active' : '').'"'
				. '>';
			$tabItems .= $this->_renderEachChildWidget(NULL, $child->action);
			$tabItems .= '</li>';
			// debugMsg($child, '$child');

			$tabContent .= '<div'
				. ' id="'.$child->id.'"'
				. ' class="'.($child->active ? '' : '-hidden').'">'
				. $this->_renderEachChildWidget($key, $child->content)
				. '</div>';
		}
		$tabItems .= '</ul>';
		return $tabItems.$tabContent;
	}
} // End of class TabWidget

class ProfilePhoto extends Widget {
	var $widgetName = 'ProfilePhoto';
	var $version = '0.01';
	var $username;
	var $size; // small,big
	// parent property : $class,$attribute;

	function __construct($username = NULL, $args = []) {
		$this->username = $username;
		parent::__construct($args);
	}

	function toString() {
		$attribute = array_replace_recursive(
			$this->attribute,
			[
				'class' => trim('widget-'.strtolower($this->widgetName).' '.\SG\getFirst($this->class)).($this->size ? ' -size-'.$this->size : ''),
				'src' => UserModel::profilePhoto($this->username),
			]
		);
		return '<img '.sg_implode_attr($attribute).' />';
	}
} // End of class ProfilePhoto




// Complex Widget

class Scaffold extends Widget {
	var $widgetName = 'Scaffold';
	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
		// debugMsg('SCAFFOLD WAS BUILD');
		// if ($this->appBar) {
		// 	$this->theme->toolbar = $this->appBar->toString();
		// 	$this->theme->title = $this->appBar->title;
		// 	// $this->self->theme->toolbar = $this->appBar->title;
		// 	// page_class('-module-has-toolbar');

		// 	//$this->appBar->title->toString();
		// 	//debugMsg('AppBar was set');
		// 	//debugMsg($this, '$Scaffold');
		// }
		return $this->toString();
	}
} // End of class Scaffold

class AppBar extends Widget {
	var $widgetName = 'AppBar';
	var $tagName = 'div';
	var $title;
	var $subtitle;
	var $leading;
	var $trailing;
	var $navigator;
	var $boxHeader = false;
	var $showInBox = false;
	var $removeOnApp = false;

	function __construct($args = []) {
		parent::__construct($args);
	}

	function _renderNavigator() {
		if (!is_array($this->navigator)) return $this->_renderEachChildWidget(NULL, $this->navigator);

		$ret = '';
		foreach ($this->navigator as $key => $value) {
			$ret .= $this->_renderEachChildWidget($key, $value);
		}
		return $ret;
	}

	function _renderNavigator_new() {
		$children = [];
		if (!is_array($this->navigator)) $children[] = $this->navigator;
		else foreach ($this->navigator as $key => $value) $children[] = $value;

		return new ScrollView([
			'tagName' => 'nav',
			'class' => '-nav',
			'children' => $children,
		]);
	}

	// @override
	function toString() {
		if ($this->showInBox && !preg_match('/header \-box/', $this->class)) $this->class .= ' header -box';
		if ($this->showInBox && $this->boxHeaderBack) $this->leading = $this->boxHeaderBack;

		return $this->_renderWidgetContainerStart()
			. ($this->leading ? '<div class="-leading">'.$this->_renderEachChildWidget(NULL, $this->leading).'</div>'._NL : '')
			. '<div class="-title"><h2 class="-text">'
			. ($this->title ? $this->_renderEachChildWidget(NULL, $this->title) : '')
			. '</h2>'
			. ($this->subTitle ? '<div class="-sub">'.$this->_renderEachChildWidget(NULL, $this->subTitle).'</div>' : '')
			. '</div>'._NL
			. ($this->trailing ? '<div class="-trailing -no-print">'.$this->_renderEachChildWidget(NULL, $this->trailing).'</div>'._NL : '')
			. ($this->navigator && ($navigatorResult = $this->_renderNavigator()) ? '<nav class="-nav -no-print">'.$navigatorResult.'</nav>' : '')
			. ($this->children || $this->child ? '<div class="-children">'.$this->_renderChildren().'</div>' : '')
			. $this->_renderWidgetContainerEnd();
	}
} // End of class AppBar




/**
 * Page Widget Group
 *
 * For page interface
 */
class PageBase extends WidgetBase {
	var $widgetName = 'PageBase';
	var $module = NULL;

	function __construct($args = []) {
		$this->widgetName = get_class($this);
		// Get module name form first word by split uppercase of widgetName
		$this->module = strToLower(preg_split('/(?=[A-Z])/', $this->widgetName, -1, PREG_SPLIT_NO_EMPTY)[0]);
		$this->version = cfg($this->module.'.version');
		parent::__construct($args);
		if (debug('page')) {
			debugMsg('PAGE CONTROLLER Id = '.$this->qtRef.' , Action = '.$this->action.' , Arg['.$this->argIndex.'] = '.$this->_args[$this->argIndex]);
			debugMsg($this->_args, '$args');
			debugMsg($this, '$this');
		}
	}

	// Test function return Array in PageApi or text in other
	function foo() {return get_parent_class($this) === 'PageApi' ? success('Foo'.(post('msg') ? ' with '.post('msg') : '')) : 'Foo'.(post('msg') ? ' with '.post('msg') : '');}

	// Test function return text
	function fooText() {return 'Foo'.(post('msg') ? ' with '.post('msg') : '');}
} // End of class PageBase

class Page extends PageBase {
	var $widgetName = 'Page';

	function __construct($args = []) {
		parent::__construct($args);
		$this->theme = (Object) ['option' => cfg('topic.property')->option];
	}

	function build() {
		return new Scaffold([
			'appBar' => method_exists($this, 'appBar') ? $this->appBar() : new AppBar(['title' => 'Web Page']),
			'body' => method_exists($this, 'body') ? $this->body() : new Widget(['child' => 'This page is underconstruction.']),
		]);
	}
} // End of class Page

class PageApi extends PageBase {
	var $widgetName = 'PageApi';
	var $action;
	var $actionMethod;

	function __construct($args = []) {
		parent::__construct($args);
		$this->actionMethod = (preg_replace_callback('/\.(\w)/', function($matches) {return strtoupper($matches[1]);}, $this->action));
	}

	function build() {
		if (method_exists($this, $this->actionMethod) && ($reflection = new ReflectionMethod($this, $this->actionMethod)) && $reflection->isPublic()) {
			return $this->{$this->actionMethod}();
		} else {
			return error(_HTTP_ERROR_BAD_REQUEST, 'Action not found!!!');
		}
	}
} // End of class PageApi

class PageController extends PageBase {
	var $widgetName = 'PageController';
	var $action;
	var $argIndex = 2;
	var $args = [];
	var $info;

	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
		return R::PageWidget(
			$this->action,
			[-1 => $this->info] + array_slice($this->args, $this->argIndex)
		);
	}
} // End of class PageController
?>