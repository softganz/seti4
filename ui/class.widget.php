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
class Widget {
	var $widgetName = 'Widget';
	var $version;
	var $tagName = '';
	var $childTagName;
	var $id;
	var $class;
	var $config = NULL; // Object
	var $attribute = [];

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
			// debugMsg('_render Call build "'.$widget->widgetName.'" (Class '.get_class($widget).') id "'.$widget->id.'"');
			// debugMsg($widget, '$widget');
			$buildResult = $widget->build();
			if (is_object($buildResult) && method_exists($buildResult, 'build')) {
				$result .= $buildResult->build();
			} else {
				$result .= $buildResult;
			}
		} else if (is_object($widget)) {
			// debugMsg('_render without build "'.$widget->widgetName.'" id "'.$widget->id.'"');
			$result .= SG\json_encode($widget);
		} else if (is_array($widget)) {
			// debugMsg('_render array');
			$result .= SG\json_encode($widget);
		} else if (is_string($widget) && $widget === '<sep>') {
			$result = '<hr class="separator" size="0" />';
		} else {
			// debugMsg('_render value');
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
	function _renderChildContainerStart($childrenKey, $args = []) {
		$childTagName = SG\getFirst($this->childTagName, $this->childContainer['tagName']);
		return $this->childContainer ? '<'.$childTagName
		. ' class="'.($this->childContainer['class'] ? $this->childContainer['class']: '')
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
			$ret .= $this->_renderChildContainerStart($childrenKey, $args + $extraArgs);
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

	// function _renderChildContainerStart($childrenKey, $args = []) {
	// 	return '<div class="-item">'._NL;
	// }

	// function _renderChildContainerEnd($args = []) {
	// 	return '</div>';
	// }
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
	var $crossAxisAlignment = 'center';

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
	var $code;
	var $type;
	var $text;

	// @override
	function toString() {
		if ($this->code) http_response_code($this->code);
		$text = $this->text;

		return $text;
	}
} // End of class Message

class ErrorMessage extends Message {
	var $widgetName = 'ErrorMessage';
} // End of class ErrorMessage

// Element wiget
class Button extends Widget {
	var $widgetName = 'Button';
	var $version = '0.01';
	var $tagName = 'a';
	var $text;
	var $icon;

	function __construct($args = []) {
		parent::__construct($args);
		// debugMsg($args, '$args');
		// debugMsg($this, '$this');
	}

	function toString() {
		$attribute = [
			'href' => $this->url,
			'class' => trim('widget-'.strtolower($this->widgetName).' btn '.SG\getFirst($this->class)),
			'title' => SG\getFirst($this->title),
		] + (Array) $this->attribute;
		$button = '<a '.sg_implode_attr($attribute).'>'
			. ($this->icon ? $this->_renderChildren([$this->icon]) : '')
			   // '<i class="icon -material">'.$this->icon.'</i>' : '')
			. ($this->text ? '<span>'.$this->text.'</span>' : '')
			. '</a>';
		return $button;
	}
} // End of class Button

class Icon extends Widget {
	var $widgetName = 'Icon';
	var $version = '0.01';
	var $icon;
	var $type = 'material';

	function __construct($icon, $args = []) {
		$this->icon = $icon;
		parent::__construct($args);
	}
	function toString() {
		$attr = [
			'class' => trim('widget-'.strtolower($this->widgetName).' icon -material '.SG\getFirst($this->class)),
		] + (Array) $this->attribute;

		if (preg_match('/$</', $this->icon)) {
			return $this->icon;
		} else {
			$icon = '<i '.sg_implode_attr($attr).'>'
				. $this->icon
				. '</i>';
			return $icon;
		}
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

class Page extends Widget {
	var $module = NULL;
	var $widgetName = 'Page';

	function __construct($args = []) {
		$this->version = cfg($this->module.'.version');
		$this->theme = (Object) ['option' => cfg('topic.property')->option];
		$this->widgetName = get_class($this);
		// debugMsg('Page Class = '.get_class($this));
		parent::__construct($args);
	}
} // End of class Page

class StepMenuWidget extends Widget {
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

	function _renderChildContainerStart($stepIndex, $args = []) {
		return '<'.$this->childContainer['tagName'].' '
			. 'class="ui-item -step-'.$stepIndex.($this->childContainer['class'] ? $this->childContainer['class'] : '').($stepIndex == $this->currentStep ? ' -current-step' : '').(in_array($stepIndex,$this->activeStep) ? ' -active' : '').'" '
			. '>';
	}

	// @override
	function _renderChildren($childrens = [], $args = []) {
		return parent::_renderChildren();
	}
} // End of class StepMenuWidget
?>