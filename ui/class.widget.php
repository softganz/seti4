<?php
/********************************************
* Class :: Widget version 1.0
* Basic Widgets Collection
*
* Created 2020-10-01
* Modify  2021-08-23
*
********************************************/

/********************************************
* Class :: Widget
* Widget class for base of all widget
********************************************/
class WidgetBase {
	var $widgetName = 'Widget';
	var $version;
	function __construct($args = []) {
		foreach ($args as $argKey => $argValue) {
			$this->{$argKey} = $argValue;
		}
	}
}

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
			if ($argKey === 'config' && is_array($argValue)) $argValue = (Object) $argValue;
			if ($argKey === 'attribute' && is_array($argValue)) {
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
		$this->header = (Object) array('text' => $str, 'attr' => SG\json_decode($attr), 'options' => SG\json_decode($options));
	}

	function children($value = NULL) {
		$this->children[] = $value;
	}

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
			$result .= SG\json_encode($widget);
		} else if (is_array($widget)) {
			// Build Array
			$result .= SG\json_encode($widget);
		} else if (is_string($widget) && $widget === '<sep>') {
			// Build Seperator
			$result = '<hr class="separator" size="0" />';
		} else {
			// Build Text
			$result .= $widget;
		}
		return $result;
	}

	// Container of widget
	// @override
	function _renderWidgetContainerStart() {
		return $this->tagName ?
			'<'.$this->tagName.' '
			. ($this->id ? ' id="'.$this->id.'" ' : '')
			. 'class="widget-'.strtolower($this->widgetName).($this->class ? ' '.$this->class : '')
			. ($this->mainAxisAlignment ? ' -main-axis-'.strtolower($this->mainAxisAlignment) : '')
			. ($this->crossAxisAlignment ? ' -cross-axis-'.strtolower($this->crossAxisAlignment) : '')
			. '" '
			. ($this->href ? ' href="'.$this->href.'"' : '')
			. ($this->config->data['data-rel'] ? ' data-rel="'.$this->config->data['data-rel'].'"' : '')
			. ($this->config->data['data-done'] ? ' data-done="'.$this->config->data['data-done'].'"' : '')
			. ($this->dataUrl ? ' data-url="'.$this->dataUrl.'"' : '')
			. ($this->webview ? ' data-webview="'.$this->webview.'"' : '')
			. ($this->data('options') ? 'data-options=\''.$this->data('options').'\'' : '')
			. ($this->data('class-name') ? 'data-class-name="'.$this->data('class-name').'"' : '')
			. ($this->style ? ' style="'.$this->style.'" ' : '')
			. ($this->attribute && is_array($this->attribute) ? ' '.sg_implode_attr($this->attribute) : '')
			. ($this->attributeText ? ' '.$this->attributeText : '')
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
		$childTagName = SG\getFirst($this->childTagName, $this->childContainer['tagName']);
		return $childTagName ? '<'.$childTagName
		. ' class="'.($this->childContainer['class'] ? $this->childContainer['class']: '')
		. ($this->itemClass ? ' '.$this->itemClass : '')
		. (!is_numeric($childrenKey) ? ' -'.$childrenKey : '')
		. ($args['class'] ? ' '.trim($args['class']) : '').'"'
		. '>'
		: '';
	}

	// @override
	function _renderChildContainerEnd() {
		$childTagName = SG\getFirst($this->childTagName, $this->childContainer['tagName']);
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


// Basic Widget

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
	var $leading;
	var $title;
	var $subtitle;
	var $trailing;
	var $crossAxisAlignment = 'start';

	function __construct($args = []) {
		parent::__construct($args);
	}

	// @override
	function toString() {
		return $this->_renderWidgetContainerStart()
			. ($this->leading ? '<div class="-leading">'.$this->_renderEachChildWidget(NULL, $this->leading).'</div>'._NL : '')
			. '<div class="-title">'
			. ($this->title ? '<span class="-title-text">'.$this->_renderEachChildWidget(NULL, $this->title).'</span>' : '')
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
			if (function_exists('print_o')) {
				$this->msg = print_o($this->msg, $this->varName);
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

class Message extends Widget {
	var $widgetName = 'Message';
	var $tagName = 'div';
	var $responseCode;
	var $type;
	var $text;

	// @override
	function toString() {
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
			'class' => trim(
				'widget-'.strtolower($this->widgetName).(empty($this->type) ? '' : ' btn')
				. ($this->type ? ' -'.$this->type : '')
				. ($this->class ? ' '.$this->class : '')
			),
			'title' => SG\getFirst($this->title),
			'data-rel' => SG\getFirst($this->rel),
			'data-before' => SG\getFirst($this->before),
			'data-done' => SG\getFirst($this->done),
			'target' => SG\getFirst($this->target),
			'onClick' => $this->onClick ? $this->onClick : NULL,
		], (Array) $this->attribute);

		if (is_null($attribute['href'])) {
			unset($attribute['href']);
		} else {
			$attribute['href'] = preg_replace('/\{\{projectId\}\}/', $this->variable->projectId, $attribute['href']);
		}

		$button = '<a '.sg_implode_attr($attribute).'>'
			. ($this->icon && $this->iconPosition == 'left' ? $this->_renderChildren([$this->icon]) : '')
			. ($this->text ? '<span>'.$this->text.'</span>' : '')
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
		if (preg_match('/$</', $this->icon)) return $this->icon;

		$attribute = array_replace_recursive(
			$this->attribute,
			[
				'class' => trim('widget-'.strtolower($this->widgetName).' icon -material '.SG\getFirst($this->class))
			]
		);
		return '<i '.sg_implode_attr($attribute).'>'
			. $this->icon
			. '</i>';
	}
}

class ExpandButton extends Widget {
	function toString() {
		return '<a class="sg-expand btn -link -no-print" href="javascript:void(0)"><i class="icon -material">expand_less</i></a>';
	}
}

class InlineEdit extends Widget {
	var $widgetName = 'InlineEdit';
	// var $tagName = 'span';
	var $version = '0.01';
	var $text;
	var $type = 'text';
	var $editMode;
	var $emptyText = '...';
	var $selectOptions = [];

	function __construct($args = []) {
		parent::__construct($args);
	}

	function _render() {
		// SG\inlineEdit($fld = [], $text = NULL, $is_edit = NULL, $input_type = 'text', $data = [], $emptytext = '...')
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
		if (!is_null($this->ret)) $fld['ret'] = $this->ret;
		if (!is_null($this->preText)) $fld['pretext'] = $this->preText;
		if (!is_null($this->postText)) $fld['posttext'] = $this->postText;
		if (!is_null($this->description)) $fld['desc'] = $this->desc;
		if (!is_null($this->removeEmpty)) $fld['removeempty'] = $this->removeEmpty;
		$fld['options'] = is_null($this->options) ? [] : $this->options;
		$fld['container'] = is_null($this->container) ? (Object) [] : $this->container;

		if ($this->inputClass) $fld['options']['class'] = $this->inputClass;
		$fld['container']->class = 'widget-inlineedit'.($this->class ? ' '.$this->class : '');

		$ret = SG\inlineEdit($fld, $this->text, $this->editMode, $this->type, $this->selectOptions, $this->emptyText);
		// debugMsg($fld, '$fld');
		return $ret;
	}

	// @override
	function toString() {
		$ret = '<!-- Start of '.$this->widgetName.' -->'._NL;
		$ret .= $this->_renderWidgetContainerStart();
		$ret .= $this->_render();
		if ($this->debug) $ret .= (new DebugMsg($this, '$this'))->build();
		$ret .= $this->_renderWidgetContainerEnd()._NL;
		$ret .= '<!-- End of '.$this->widgetName.' -->'._NL;
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
			$options = is_string($child->options) ? SG\json_decode($child->options): (Object) $child->options;

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
			$container = SG\json_decode($this->container);
			$containerTag = $this->config->nav ? 'nav' : $container->tag;
			unset($container->tag);
			$containerAttr = sg_implode_attr($container);

			$ret = '<'.$containerTag.' '.$containerAttr.'>'.$ret.'</'.$containerTag.'>';
		}

		return $ret;
	}
} // End of class List


class ProfilePhoto extends Widget {
	var $widgetName = 'ProfilePhoto';
	var $version = '0.01';
	var $username;

	function __construct($username, $args = []) {
		$this->username = $username;
		parent::__construct($args);
	}

	function toString() {
		$attribute = array_replace_recursive(
			$this->attribute,
			[
				'class' => trim('widget-'.strtolower($this->widgetName).' '.SG\getFirst($this->class)).($this->size ? ' -size-'.$this->size : ''),
				'src' => UserModel::profilePhoto($this->username),
			]
		);
		return '<img '.sg_implode_attr($attribute).' />';
	}
}




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
			. '<h2 class="-title">'
			. ($this->title ? $this->_renderEachChildWidget(NULL, $this->title) : '')
			. '</h2>'._NL
			. ($this->trailing ? '<div class="-trailing -no-print">'.$this->_renderEachChildWidget(NULL, $this->trailing).'</div>'._NL : '')
			. ($this->navigator && ($navigatorResult = $this->_renderNavigator()) ? '<nav class="-nav -no-print">'.$navigatorResult.'</nav>' : '')
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
	var $debug = false;

	function __construct($args = []) {
		$this->widgetName = get_class($this);
		// Get module name form first word by split uppercase of widgetName
		$this->module = strToLower(preg_split('/(?=[A-Z])/', $this->widgetName, -1, PREG_SPLIT_NO_EMPTY)[0]);
		$this->version = cfg($this->module.'.version');
		parent::__construct($args);
		if ($this->debug) {
			debugMsg('PAGE CONTROLLER Id = '.$this->qtRef.' , Action = '.$this->action.' , Arg['.$this->argIndex.'] = '.$this->_args[$this->argIndex]);
			debugMsg($this->_args, '$args');
			debugMsg($this, '$this');
		}
	}

	// Test function
	function foo() {return 'foo';}
} // End of class PageBase

class Page extends PageBase {
	var $widgetName = 'Page';

	function __construct($args = []) {
		parent::__construct($args);
		$this->theme = (Object) ['option' => cfg('topic.property')->option];
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar(['title' => 'Web Page']),
			'body' => new Widget(['child' => 'This page is underconstruction.']),
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
			return new ErrorMessage([
				'responseCode' => _HTTP_ERROR_BAD_REQUEST,
				'text' => 'Action not found!!!'
			]);
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