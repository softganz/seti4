<?php
/**
 * Widget  :: Basic Widget Collector
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2020-10-01
 * Modify  :: 2026-02-11
 * Version :: 67
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

// Widget for children group
class Children extends WidgetBase {
	var $widgetName = 'Children';
	var $version = '0.00.01';
	var $type;
	var $children = [];
}

class Widget extends WidgetBase {
	var $widgetName = 'Widget'; // String
	var $version; // String
	var $tagName = ''; // String
	var $childTagName; // String
	var $id; // String
	var $class; // String
	var $header; // String, Widget
	var $itemClass; // String
	var $mainAxisAlignment; // String
	var $crossAxisAlignment; // String
	var $href; // String
	var $dataUrl; // String
	var $webview; // String
	var $style; // String
	var $onBuild; // function
	var $rel; // String
	var $done; // String
	var $action; // String
	var $debug; // String
	var $child; // Any
	var $children = []; // Array
	var $attribute = []; // Array
	var $childContainer = []; // Array

	// @deprecated
	var $attributeText;
	var $config = NULL; // Object

	function __construct($args = []) {
		$this->initConfig();
		foreach ($args as $argKey => $argValue) {
			if ($argKey === 'class') {
				$this->class .= ($this->class ? ' ' : '').$argValue;
			} else if ($argKey === 'config' && is_array($argValue)) {
				$argValue = (Object) $argValue;
			} else if ($argKey === 'attribute' && is_array($argValue)) {
				$this->attribute = array_replace_recursive($this->attribute, $argValue);
			} else if ($argKey === 'children') {
				foreach ($argValue as $childName => $childValue) {
					if (is_null($childValue)) continue;
					$this->children[$childName] = $childValue;
				}
			} else if ($argKey === 'child' && !is_null($argValue)) {
				$this->children[] = $argValue;
				$this->child = $argValue;
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
	protected function initWidget() {}

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
		$this->config->{$key} = $value;

		// Add key to class property
		if (in_array($key, ['id'])) $this->{$key} = $value;
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
		if (isset($value)) $this->children[] = $value;

		if ($this->body) $childrens = [$this->body];
		// else if ($this->child) $childrens = [$this->child];
		else if ($this->children) $childrens = $this->children;
		else $childrens = [];
		// debugMsg($childrens, 'CHILDRENS');
		return $childrens;
	}

	// Container of widget
	// @override
	function _renderWidgetContainerStart($callbackFunction = NULL) {
		return $this->tagName ?
			($this->widgetName != 'Widget' ? '<!-- Start of '.$this->widgetName.' -->'._NL : '')
			. '<'.$this->tagName._NL
			. ($this->id ? ' id="'.$this->id.'"'._NL : '')
			// Start if class
			. ' class="widget-'.strtolower($this->widgetName).($this->class ? ' '.$this->class : '')
			. ($this->mainAxisAlignment ? ' -main-axis-'.strtolower($this->mainAxisAlignment) : '')
			. ($this->crossAxisAlignment ? ' -cross-axis-'.strtolower($this->crossAxisAlignment) : '')
			. '"'._NL
			// End of class
			. ($this->href ? ' href="'.$this->href.'"'._NL : '')
			. ($this->config->data['data-rel'] ? ' data-rel="'.$this->config->data['data-rel'].'"'._NL : '')
			. ($this->config->data['data-done'] ? ' data-done="'.$this->config->data['data-done'].'"'._NL : '')
			. ($this->dataUrl ? ' data-url="'.$this->dataUrl.'"'._NL : '')
			. ($this->webview ? ' data-webview="'.$this->webview.'"'._NL : '')
			. ($this->data('options') ? ' data-options=\''.$this->data('options').'\' '._NL : '')
			. ($this->data('class-name') ? ' data-class-name="'.$this->data('class-name').'"'._NL : '')
			. ($this->style ? ' style="'.$this->style.'"'._NL : '')
			. ($this->attribute && is_array($this->attribute) ? ' '.sg_implode_attr($this->attribute)._NL : '')
			. ($this->attributeText ? ' '.$this->attributeText._NL : '')
			. ($callbackFunction && is_callable($callbackFunction) ? $callbackFunction() : '')
			. '>'._NL
		: '';
	}

	// @override
	function _renderWidgetContainerEnd() {
		return $this->tagName ? _NL.'</'.$this->tagName.'><!-- End of '.$this->widgetName.' -->'._NL : '';
	}

	// Container cover all children
	// @override
	function _renderChildrenContainerStart() {
		// debugMsg($this->childrenContainer,'$this->childrenContainer');
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
	function _renderChildContainerStart($childKey, $attributes = [], $child = []) {
		foreach ($attributes as $key => $value) if (is_null($value)) unset($attributes[$key]);

		$container = (Array) $this->container;

		$childTagName = \SG\getFirst($this->childTagName, $this->childContainer['tagName']);
		$attributes['class'] = ($this->childContainer['class'] ? $this->childContainer['class'] : '')
			. ($this->itemClass ? ' '.$this->itemClass : '')
			. (!is_numeric($childKey) ? ' -'.$childKey : '')
			. ($attributes['class'] ? ' '.$attributes['class'] : '')
			. ($container['class'] ? ' '.$container['class'] : '');

		$childAttribute = isset($container['children'][$childKey]) ? (Array) $container['children'][$childKey] : [];

		unset($container['class'], $container['children']);

		$attributes = array_replace_recursive(
			[
				'id' => NULL,
				'class' => NULL,
				'style' => NULL,
				// And other attributes
			],
			$container,
			$childAttribute,
			$attributes
		);

		return $childTagName ? '<'.$childTagName.' '.sg_implode_attr($attributes).'>'
			.($this->debug === 'container' ? 'Child tag name = '.$childTagName.'<br>Child key = '.$childKey.'<br>'.print_o($container, '$container').print_o($attributes, '$attributes') : '') : '';
	}

	// @override
	function _renderChildContainerEnd($childKey = NULL, $child = []) {
		$childTagName = \SG\getFirst($this->childTagName, $this->childContainer['tagName']);
		return $childTagName ? '</'.$childTagName.'>'._NL : '';
	}

	// @override
	function _renderEachChildWidget($key, $widget, $callbackFunction = []) {
		$result = '';
		if (is_object($widget) && method_exists($widget, 'build')) {
			// Build Widget
			if ($callbackFunction['object'] && is_callable($callbackFunction['object'])) {
				$result .= $callbackFunction['object']($key, $widget);
			} else {
				$buildResult = $widget->build();
				if (is_object($buildResult) && method_exists($buildResult, 'build')) {
					$result .= $buildResult->build();
				} else {
					$result .= $buildResult;
				}
			}
		} else if (is_object($widget)) {
			// Build General Object
			$result .= $callbackFunction['object'] && is_callable($callbackFunction['object']) ? $callbackFunction['object']($key, $widget) : SG\json_encode($widget);
			$result .= \SG\json_encode($widget);
		} else if (is_array($widget)) {
			// Build Array
			$result .= $callbackFunction['array'] && is_callable($callbackFunction['array']) ? $callbackFunction['array']($key, $widget) : SG\json_encode($widget);
		} else if (is_string($widget) && $widget === '<sep>') {
			// Build Seperator
			$result .= $callbackFunction['seperator'] && is_callable($callbackFunction['seperator']) ? $callbackFunction['seperator']($key, $widget) : '<hr class="separator" size="0" />';
		} else {
			// Build Text
			$result .= $callbackFunction['text'] && is_callable($callbackFunction['text']) ? $callbackFunction['text']($key, $widget) : $widget;
		}
		// $result .= _NL;
		return $result;
	}

	// @override
	function _renderChildren($childrens = [], $args = []) {
		$childrens = (Array) $childrens;

		$ret .= $this->_renderChildrenContainerStart();

		foreach ($childrens as $childKey => $child) {
			$extraArgs = [];
			if (is_string($child) && $child === '<sep>') {
				// Children is separator
				$extraArgs['class'] = $args['class'].' -sep';
			} else if (is_string($child) && $child === '<spacer>') {
				// Children is spacer
				$extraArgs['class'] = $args['class'].($args['class'] ? ' ' : '').'-spacer';
				$child = '';
			} else if (is_object($child) && get_class($child) === 'Children') {
				// children is class of Children
				if ($child->tagName) $ret .= '<'.$child->tagName.' id="'.$child->id.'" class="-children-widget '.$child->class.'">';
				foreach ($child->children as $subKey => $subChild) {
					if (is_string($subKey)) $subChild['inputName'] = $subKey;
					$ret .= $this->_renderChildContainerStart($subKey, [], $subChild);
					$ret .= $this->_renderEachChildWidget($subKey, $subChild);
					$ret .= $this->_renderChildContainerEnd($subKey, $subChild)._NL;
				}
				if ($child->tagName) $ret .= '</'.$child->tagName.'>';
				continue;
			} else {
				if (is_string($key)) $child['inputName'] = $key;
			}

			$ret .= $this->_renderChildContainerStart($childKey, $args + $extraArgs, $child);
			$ret .= $this->_renderEachChildWidget($childKey, $child);
			$ret .= $this->_renderChildContainerEnd($childKey, $child);
		}

		$ret .= $this->_renderChildrenContainerEnd();
		return $ret;
	}

	// @override
	function toString() {
		$ret = $this->_renderWidgetContainerStart();
		if ($this->header) {
			if (is_object($this->header) && method_exists($this->header, 'build')) {
				$ret .= $this->header->build();
			} if (is_string($this->header)) {
				$ret .= $this->header;
			}
		}
		if ($this->children()) {
			$ret .= $this->_renderChildren($this->children());
		}
		$ret .= $this->_renderWidgetContainerEnd();
		return $ret;
	}

	// @override
	function build() {
		if ($this->onBuild && is_callable($this->onBuild)) {
			$onBuildFunction = $this->onBuild;
			$onBuildFunction($this);
		}
		return $this->toString();
	}

	// @deprecated
	function show() {return $this->build();}
} // End of class Widget


/**
* Basic Widget
*/

// new DOM(['tag' => 'img', 'class' => 'class-name', 'onClick' => 'script', 'child/children'])
class DOM extends Widget {
	var $widgetName;
	var $version = '0.00.01';
	var $tagName;
	var $class;
	var $settings = [];

	function __construct($args = []) {
		$this->tagName = array_shift($args);
		$this->widgetName = 'dom-'.$this->tagName;
		$this->settings = (Array) $args['settings'];
		if ($args['children']) $this->children = $args['children'];
		else if ($args['child']) $this->children[] = $args['child'];
		$this->class = ($args['class'] ? $args['class'] : '');

		unset($args['tag'], $args['settings'], $args['child'], $args['children'], $args['class']);

		parent::__construct(['attribute' => $args]);

		if ($this->settings['debug']) debugMsg($this, '$this');
	}

	// @override
	function toString() {
		$unpairedTags = ['img', 'br'];
		$ret = $this->_renderWidgetContainerStart();
		if ($this->children()) {
			$ret .= $this->_renderChildren($this->children());
		}
		if (!in_array($this->tagName, $unpairedTags)) $ret .= $this->_renderWidgetContainerEnd();
		return $ret;
	}
}

class HtmlTemplate extends Widget {
	var $widgetName = 'Template';
	var $tagName = 'template';
}

class Header extends Widget {
	var $widgetName = 'Header';
	var $tagName = 'header';
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
			. $this->_renderChildren($this->children())
			. $this->_renderWidgetContainerEnd();
	}
} // End of class Header

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
		$ret = $this->_renderWidgetContainerStart();
		if ($this->children()) {
			$ret .= $this->_renderChildren($this->children());
		}
		$ret .= $this->_renderWidgetContainerEnd();
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

class ListOrder extends Widget {
	var $widgetName = 'ListOrder';
	var $tagName = 'ul';
	var $childContainer = ['tagName' => 'li', 'class' => '-item'];

	function __construct($args = []) {
		parent::__construct($args);
		if ($this->type) $this->tagName = $this->type;
	}
} // End of class ListOrder

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
	// 	$ret .= $this->_renderChildren($this->chiildren());
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
			. $this->_renderChildren($this->children())
			. $this->_renderWidgetContainerEnd();
	}
} // End of class ListTile

class Card extends Widget {
	var $widgetName = 'Card';
	var $tagName = 'div';
	var $titleTag = 'div';

	function __construct($args = []) {
		parent::__construct($args);
	}

	// @override
	function toString() {
		return $this->_renderWidgetContainerStart()
			. (
				$this->header ?
					'<div class="-header">'
					. ($this->header['leading'] ? '<div class="-leading">'.$this->_renderEachChildWidget(NULL, $this->header['leading']).'</div>'._NL : '')
					. '<div class="-title">'
					. ($this->header['title'] ? '<'.SG\getFirst($this->header['titleTag'], $this->titleTag).' class="-title-text">'.$this->_renderEachChildWidget(NULL, $this->header['title']).'</'.SG\getFirst($this->header['titleTag'], $this->titleTag).'>' : '')
					. ($this->header['subtitle'] ? '<span class="-subtitle-text">'.$this->_renderEachChildWidget(NULL, $this->header['subtitle']).'</span>' : '')
					. '</div>'._NL
					. ($this->header['trailing'] ? '<div class="-trailing">'.$this->_renderEachChildWidget(NULL, $this->header['trailing']).'</div>'._NL : '')
					. '</div><!-- header -->'
			 : '' // header
			)
			. $this->_renderChildren($this->children())
			. $this->_renderWidgetContainerEnd();
	}
} // End of class Card

class Nav extends Widget {
	var $widgetName = 'Nav';
	var $tagName = 'nav';
	var $class = 'nav';
	var $multipleLevel = false;
	// var $childrenContainer = ['tagName' => 'ul'];
	// var $childContainer = ['tagName' => 'li', 'class' => '-item'];

	function __construct($args = []) {
		parent::__construct($args);

		if ($args['type']) $this->class .= ' -type-'.$args['type'];
		if ($args['direction']) $this->class .= ' -'.$args['direction'];
	}

	// @override
	function _renderEachChildWidget($key, $widget, $callbackFunction = []) {
		return parent::_renderEachChildWidget($key, $widget, [
			'array' => function($key, $widget) {
				$result = '<ul>'._NL;
				foreach ($widget as $eachKey => $eachWidget) {
					$result .= '<li class="-item">'.trim($this->_renderEachChildWidget($eachKey, $eachWidget)).'</li>'._NL;
				}
				$result .= '</ul>'._NL;
				return $result;
			},
			'object' => function($key, $widget) {
				return trim($widget->build());
			},
			'text' => function($key, $text) {
				return $text;
			}
		]);
		return $result;
	}

	// @override
	function _renderChildren($childrens = [], $args = []) {
		// debugMsg($childrens, '$childrens');
		foreach ($childrens as $key => $value) {
			if (is_array($value)) {
				$this->multipleLevel = true;
				break;
			}
		}
		if (!$this->multipleLevel) {
			$this->childrenContainer = ['tagName' => 'ul'];
			$this->childContainer = ['tagName' => 'li', 'class' => '-item'];
		}
		return parent::_renderChildren($childrens, $args);
	}
} // End of class Nav

class SideBar extends Widget {
	var $widgetName = 'SideBar';
	var $tagName = 'aside';

	function __construct($args = []) {
		parent::__construct($args);

		if ($args['type']) $this->class = '-type-'.$args['type'].($this->class ? ' '.$this->class : '');
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
	var $callFrom;

	function __construct($msg = NULL, $varName = NULL, $callFrom = NULL) {
		parent::__construct([
			'msg' => $msg,
			'varName' => is_object($msg) && !isset($varName) ? get_class($msg) : $varName,
			'callFrom' => isset($callFrom) ? $callFrom : debug_backtrace(),
		]);
	}

	function build() {
		if (!user_access('access debugging program')) return;

		$callString = '<details><summary>Call from : '.$this->callFrom[0]['file'].' @line '.$this->callFrom[0]['line'].'</summary><div class="widget-scrollview"><pre style="white-space: pre-wrap">';
		foreach ($this->callFrom as $key => $value) {
			$callString .= $value['file'].' @line '.$value['line'].'<br>';
		}
		$callString .= '</pre></div></details>';

		if (is_object($this->msg) || is_array($this->msg)) {
			$this->msg = '<div>'.self::printObject($this->msg, $this->varName).'</div>';
		} else if (isset($this->msg) && preg_match('/^(SELECT|UPDATE|INSERT|DELETE)/i', trim($this->msg))) {
			$this->msg = '<pre>'.$this->msg.'</pre>';
		} else if (!isset($this->msg)) {
			$this->msg = 'NULL';
		}

		return "\r\n".'<div class="debug-msg">'
			. '<span class="widget-button sg-expand" data-rel="next"><i class="icon -material">expand_more</i></span>'
			. $this->msg
			. ($this->callFrom ? '<div class="-call-from">'.$callString.'</div>' : '')
			. '</div>'
			. "\r\n";
	}

	static function printObject($arr = [], $name = '', $options = []) {
		if ($name && is_object($arr)) {$prefix = '->'; $suffix = '';}
		else if ($name && is_array($arr)) {$prefix = '['; $suffix = ']';}
		else $prefix = $suffix = '';

		$result = '<ul class="-array-value '.(isset($options['class']) ? $options['class'] : '').'">'._NL;
		if ( is_object($arr) || (is_array($arr) and count($arr) > 0) ) {
			foreach ( $arr as $key=>$value ) {
				$vtype = GetType($value);
				$hasChild = in_array($vtype, ['array', 'object']);
				$result .= '<li><span>'
					. '<span class="'.($hasChild ? 'widget-button sg-expand' : '').'" data-rel="next">'
					. $name.$prefix.$key.$suffix
					. ($hasChild ? '<i class="icon -material">expand_more</i>' : '')
					. '</font> '
					. '<span class="-var-type">['.(is_object($value) ? get_class($value).' ' : '').$vtype.']</span> : </span>';
				switch ($vtype) {
					case 'boolean' : $result .= $value ? 'true' : 'false'; break;
					case 'array' : $result .= self::printObject($value,$name.$prefix.$key.$suffix); break;
					case 'object' : $result .= self::printObject($value,$name.$prefix.$key.$suffix); break;
					default : $result .= '<span class="-value">'.$value.'</font>'; break;
				}
				$result .= '</li>'._NL;
			}
		} else {
			$result .= '<li>(empty)</li>'._NL;
		}
		$result .= '</ul>'._NL;

		return $result;
	}
} // End of class DebugMsg

class Message extends WidgetBase {
	var $widgetName = 'Message';
	var $responseCode;
	var $error = false;
	var $text;
	function __construct($args = []) {
		parent::__construct($args);

		if (isset($args['errorMessage'])) $this->error = true;
		$this->returnObject = is_array($args) || is_object($args) ? $args : NULL;
	}

	// @override
	function build() {
		$message = $this->error ? $this->errorMessage : $this->text;

		if ($this->responseCode) http_response_code($this->responseCode);

		$result = $this->returnObject ? $this->returnObject : $message;

		if (_AJAX) return $result;

		return '<div class="widget-message'.($this->error ? ' -error' : '').($this->responseCode ? ' -code-'.$this->responseCode : '').'">'
			. $tmessage
			. ($this->error ? $this->errorMessage : '')
			. '</div>';
	}
} // End of class Message

class ErrorMessage extends Message {
	var $widgetName = 'ErrorMessage';
} // End of class ErrorMessage

/**
 * Button widget
 * @param Array $args
 * @return Object
 * @usage new Button([key => value,...])
 */
class Button extends Widget {
	var $widgetName = 'Button';
	var $version = '0.01';
	var $tagName = 'a';
	var $href;
	var $type = 'default'; // default, primary, link, floating, secondary,success, info, warning, danger, link, cancel
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
				. ($this->type === 'default' ? '' : ' btn')
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
			. ($this->text ? '<span class="-label">' . $this->_renderChildren([$this->text]) . '</span>' : '')
			. ($this->description ? '<span class="-desc">'. $this->_renderChildren([$this->description]) : '')
			. ($this->icon && $this->iconPosition == 'right' ? $this->_renderChildren([$this->icon]) : '')
			. '</a>';
		return $button;
	}
} // End of class Button

/**
 * BackButton widget
 * @param Array $args
 * @return Object
 * @usage new BackButton([key => value,...])
 */
class BackButton extends Widget {
	var $widgetName = 'BackButton';
	var $version = '0.01';
	var $tagName = 'a';
	var $href = 'javascript:history.back()';
	var $type; // default, primary, link, floating, secondary,success, info, warning, danger, link, cancel
	var $text;
	var $icon = '<i class="icon -material">arrow_back</i>';
	var $iconPosition = 'left'; // left,right,top,bottom
	var $description;
	var $onClick;

	function __construct($args = [], $variable = NULL) {
		parent::__construct($args);
	}

	function toString() {
		$attribute = array_replace_recursive(
			[
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
				'style' => $this->style,
			],
			(Array) $this->attribute
		);

		if (is_null($attribute['href'])) {
			unset($attribute['href']);
		}

		$button = '<'.$this->tagName.' '
			. sg_implode_attr($attribute)
			. ($this->onClick ? ' onClick=\''.$this->onClick.'\'' : '')
			. '>'
			. ($this->icon && $this->iconPosition == 'left' ? $this->_renderChildren([$this->icon]) : '')
			. ($this->text ? '<span class="-label">' . $this->_renderChildren([$this->text]) . '</span>' : '')
			. ($this->description ? '<span class="-desc">'. $this->_renderChildren([$this->description]) : '')
			. ($this->icon && $this->iconPosition == 'right' ? $this->_renderChildren([$this->icon]) : '')
			. '</'.$this->tagName.'>';
		return $button;
	}
} // End of class BackButton

// Usage: new Icon(iconName, property=[])
// Usage: new Icon('iconName1,iconName2', property=[])
class Icon extends Widget {
	var $widgetName = 'Icon';
	var $version = '0.03';
	var $icon;
	var $secondary; // For secondary icon
	var $type = 'material';

	function __construct($icon, $args = []) {
		$this->icon = $icon;
		parent::__construct($args);
		unset($this->config, $this->childContainer, $this->child, $this->children, $this->attributeText);
	}

	function toString() {
		if (is_string($this->icon) && preg_match('/$</', $this->icon)) return $this->icon;

		if (is_object($this->icon)) return $this->icon;
		if (!is_string($this->icon)) return;

		$attribute = array_replace_recursive(
			$this->attribute,
			[
				'class' => trim('widget-'.strtolower($this->widgetName).' icon -material '.\SG\getFirst($this->class))
			]
		);

		// 2 icons
		if (preg_match('/\,/', $this->icon)) {
			list($icon1, $icon2) = explode(',', $this->icon);

			return '<span class="icon-group">'
				. '<i '.sg_implode_attr($attribute).'>'
				. $icon1
				. '</i>'
				. '<i '.sg_implode_attr($attribute).'>'
				. $icon2
				. '</i>'
				. '</span>';
		}

		if ($this->secondary) {
			return '<span class="icon-group">'
				. '<i '.sg_implode_attr($attribute).'>'
				. $this->icon
				. '</i>'
				. $this->secondary
				. '</span>';
		}

		return '<i '.sg_implode_attr($attribute).'>'
			. $this->icon
			. '</i>';
	}
} // End of class Icon

class ExpandButton extends Widget {
	var $icon = 'chevron_right';
	function toString() {
		return '<a'
			. ' class="sg-expand btn -link -no-print'.($this->class ? ' '.$this->class : '').'"'
			. ' href="javascript:void(0)"'
			. sg_implode_attr($this->attribute)
			. '>'
			. '<i class="icon -material">'.$this->icon.'</i>'
			. '</a>';
	}
} // End of class ExpandButton

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

	function _renderChildContainerStart($stepIndex, $args = [], $child = []) {
		$stepIndex++;
		return '<'.$this->childContainer['tagName'].' '
			. 'class="ui-item -step-'.$stepIndex.($this->childContainer['class'] ? $this->childContainer['class'] : '').($stepIndex == $this->currentStep ? ' -current-step' : '').(isset($this->activeStep[$stepIndex]) && $this->activeStep[$stepIndex] ? ' -active' : '').'" '
			. '>';
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
		foreach ($childrens as $key => $value) {
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
		if (empty($this->children()) && $this->forceBuild === false) return;

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
		$ret .= $this->_renderChildren($this->children());
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
	var $class = 'widget-tabbar sg-tabs';

	function _renderChildren($childrens = [], $args = []) {
		$tabItems = '<ul class="tabs">';
		$tabContent = '';
		foreach ($childrens as $key => $child) {
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
				'alt' => htmlspecialchars($this->title),
				'title' => htmlspecialchars($this->title),
			]
		);
		return '<img '.sg_implode_attr($attribute).' />';
	}
} // End of class ProfilePhoto

class Stack extends Widget {
}

class GridView extends Widget {}

class Drawer extends Widget {}

// Complex Widget

class Scaffold extends Widget {
	var $widgetName = 'Scaffold';
	var $appBar = NULL;
	var $sideBar = NULL;
	var $body = NULL;
	var $floatingActionButton = NULL;
	var $script = NULL;

	function __construct($args = []) {
		parent::__construct($args);
	}

	// @override
	// function _renderChildren($childrens = [], $args = []) {
	// 	// debugMsg($childrens, '$childrens');
	// 	return parent::_renderChildren($this->children(), $args);
	// }

	function build() {
		// debugMsg('SCAFFOLD WAS BUILD');
		// debugMsg($this->body, '$body');
		// debugMsg($this, '$Scaffold');
		// if ($this->appBar) {
		// 	$this->theme->toolbar = $this->appBar->toString();
		// 	$this->theme->title = $this->appBar->title;
		// 	// $this->self->theme->toolbar = $this->appBar->title;
		// 	// page_class('-module-has-toolbar');

		// 	//$this->appBar->title->toString();
		// 	//debugMsg('AppBar was set');
		// 	//debugMsg($this, '$Scaffold');
		// }
		return parent::build();
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
	var $dropbox;
	var $boxHeader = false;
	var $showInBox = false;
	var $removeOnApp = false;
	var $navigatorMultipleLevel = false;

	function __construct($args = []) {
		parent::__construct($args);
	}

	/**
	 * Navigator format:
	 * #1: <a></a> : String
	 * #2: [1,2,3,widget,dropbbox] : Array
	 * #3: [[1,2],[3,4],widget,dropbbox] : Array of Array
	 * #4: [widget,dropbbox] : Array of widget
	 */
	function _renderNavigator($navigators = NULL) {
		// $navigators = $navigators ? $navigators : NULL;
		$navigatorText = '';

		if (is_array($navigators)) {
			$navigatorText = '';
			foreach ($navigators as $key => $value) {
				if (is_array($value)) {
					$w = new Nav(['children' => $value]);
					$navigatorText .= $this->_renderEachChildWidget(['class' => $key], $w);
				// 	$navigatorText .= _NL.'<ul>'._NL;
				// 	$navigatorText .= $this->_renderNavigator($value);
				// 	$navigatorText .= _NL.'</ul>';
				} else if (is_object($value) && method_exists($value, 'build')) {
					$navigatorText .= $value->build();
				} else {
					$navigatorText .= $value._NL;
				}
			}
			// $navigatorText .= _NL;
			// return trim($navigatorText);
		} else if (is_object($navigators)) {
			// debugMsg($navigators,'$navigators');
			$navigatorText .= $this->_renderEachChildWidget(NULL, $navigators);
		} else {
			$navigatorText .= $navigators;
		}

		// $navigatorText = $navigatorText;
		return $navigatorText ? $navigatorText : NULL;
	}

	// @override
	function toString() {
		if (is_array($this->navigator)) {
			// Check navigator has array, set navigatorMultipleLevel to true
			foreach ($this->navigator as $key => $value) {
				if (is_array($value)) {
					$this->navigatorMultipleLevel = true;
					break;
				}
			}

			foreach ($this->navigator as $key => $value) {
				if (is_object($value) && $value->widgetName === 'Dropbox') {
					$this->dropbox = $value;
					unset($this->navigator[$key]);
					break;
				}
			}
		}

		if ($this->showInBox && !preg_match('/header \-box/', $this->class)) $this->class .= ' header -box';
		if ($this->showInBox && $this->boxHeaderBack) $this->leading = $this->boxHeaderBack;

		if ($this->dropbox) $this->class .= ' -has-dropbox';

		// On single level navigator, add tag ul
		$navigatorResult = $this->_renderNavigator($this->navigator);
		if (!$this->navigatorMultipleLevel && $navigatorResult) $navigatorResult = '<ul>'._NL.$navigatorResult._NL.'</ul>';

		return $this->_renderWidgetContainerStart()
			. ($this->leading ? '<div class="-leading">'.$this->_renderEachChildWidget(NULL, $this->leading).'</div>'._NL : '')
			. '<div class="-title"><h2 class="-text">'
			. ($this->title ? $this->_renderEachChildWidget(NULL, $this->title) : '')
			. '</h2>'
			. ($this->subTitle ? '<div class="-sub">'.$this->_renderEachChildWidget(NULL, $this->subTitle).'</div>' : '')
			. '</div>'._NL
			. ($this->trailing ? '<div class="-trailing -no-print">'.$this->_renderEachChildWidget(NULL, $this->trailing).'</div>'._NL : '')
			. ($this->navigator && $navigatorResult ? '<nav class="-nav -no-print">'._NL.$navigatorResult._NL.'</nav>'._NL : '')
			. ($this->dropbox ? '<div class="-dropbox">'.$this->dropbox->build().'</div><!-- end of -dropboox -->'._NL : '')
			. ($this->children() ? '<div class="-children">'.$this->_renderChildren($this->children()).'</div>' : '')
			. $this->_renderWidgetContainerEnd();
	}
} // End of class AppBar




/**
* Page Widget Group
*
* For URL page interface
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

	protected function valid($value, $regx, $debug = false) {
		if ($debug) debugMsg('Debug of function SG\valid of <b>'.$value.'</b> with regx <b>'.$regx.'</b>');
		$valid = preg_match($regx, $value, $out);
		if ($debug) debugMsg($out, '$out');
		return $valid ? $value : NULL;
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
	private $runInternalMethod = true;
	protected $actionDefault;
	protected $action;
	private $actionMethod;
	protected $args = [];

	function __construct($args = []) {
		parent::__construct($args);

		if (empty($this->action)) $this->action = $this->actionDefault;

		if (preg_match('/^api\./', $this->action)) $this->runInternalMethod = false;

		// Replace .a with .A
		$this->actionMethod = preg_replace_callback(
			'/\.(\w)/',
			function($matches) {return strtoupper($matches[1]);},
			$this->action
		);
	}

	function build() {
		if ($this->runInternalMethod) return $this->runMethod();
		else return $this->runExternalMethod();
	}

	private function runMethod() {
		if (!$this->canRunMethod()) return apiError(_HTTP_ERROR_NOT_FOUND, 'Action not found!!!');
		if (!method_exists($this, 'rightToBuild')) return $this->{$this->actionMethod}();

		$rightToBuild = $this->rightToBuild();
		if ($rightToBuild === true) {
			return $this->{$this->actionMethod}();
		} else {
			return apiError(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		}
	}

	private function runExternalMethod() {
		return R::PageWidget(
			$this->action,
			(Array) $this->args
		);
	}

	private function canRunMethod() {
		return method_exists($this, $this->actionMethod)
			&& ($reflection = new ReflectionMethod($this, $this->actionMethod))
			&& $reflection->isPublic();
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