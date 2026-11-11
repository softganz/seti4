<?php
/**
 * Widget   :: Basic Widget Collector
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2020-10-01
 * Modified :: 2026-11-11
 * Version  :: 112
 *
 * @uses new Widget([key => value,...])
 */

/**
 * Widget Widget
 * Class :: WidgetBase class for base of all widget
 *
 * @param array $args
 * @uses extends WidgetBase
 */
#[AllowDynamicProperties]
class WidgetBase {
	public $widgetName = 'Widget';
	public $version;
	private static string $camelToDashRegex = '/([A-Z]+)/';

	function __construct($args = []) {
		foreach ((array) $args as $argKey => $argValue) {
			$this->{$argKey} = $argValue;
		}
		if (debug('widget')) {
			debugMsg((array) $this, '$WidgetBaseThis');
		}
	}

	protected static function camelToDash(?string $str): string {
		return preg_replace_callback(
			self::$camelToDashRegex,
			fn($m) => '-' . strtolower($m[1]),
			$str
		);
	}

	function extension() {
		// debugMsg('get_class = '.get_class($this));
		// debugMsg(get_class_methods($this), '$methid');
		// \EXTENSION\PPI\ProjectJoinList::test2();
	}

	protected function valid($value, $regx, $debug = false) {
		return \SG\valid($value, $regx, $debug);
	}
} // End of class WidgetBase

/**
 * Widget :: Children Widget
 * Widget for children group
 *
 * @param array $args
 * @uses new Children([key => value,...])
 */
class Children extends WidgetBase {
	public $widgetName = 'Children';
	public $version = '0.0.01';
	public $type;
	public ?array $children = [];
	function __construct(
		$args = [],
		?string $id = null,
		?string $class = null,
		?string $tagName = null,
		?array $children = []
	) {
		return parent::__construct(
			array_replace(
				[
					'id' => $id,
					'class' => $class,
					'tagName' => $tagName,
					'children' => $children
				],
				$args
			)
		);
	}
}

/**
 * Widget :: Widget
 *
 * @param array $args
 * @uses new Widget([key => value,...])
 */
class Widget extends WidgetBase {
	public $widgetName = 'Widget'; // string
	public $tagName = ''; // string
	public $childTagName; // string
	public $id; // string
	public $class; // string
	public $header; // string, widget
	public $itemClass; // string
	public $mainAxisAlignment; // string
	public $crossAxisAlignment; // string
	public ?string $href = ''; // string
	public $dataUrl; // string
	public $webview; // string
	public $style; // string
	public $onBuild; // function
	public $rel; // string
	public $done; // string
	public $action; // string
	public $debug; // string
	public mixed $child; // mixed
	public ?array $children = []; // array
	public ?array $attribute = []; // array
	public ?array $childContainer = []; // array

	// @deprecated
	public $config = null;

	function __construct($args = [], mixed $children = null) {
		$this->initConfig();

		// Set named argument
		if ($children) {
			$args['children'] = $children;
		}

		foreach ($args as $argKey => $argValue) {
			if ($argKey === 'class') {
				$this->class .= ($this->class ? ' ' : '') . $argValue;
			} elseif ($argKey === 'config' && is_array($argValue)) {
				$argValue = (object) $argValue;
			} elseif ($argKey === 'attribute' && is_array($argValue)) {
				$this->attribute = array_replace_recursive($this->attribute, $argValue);
			} elseif ($argKey === 'children') {
				foreach ((array) $argValue as $childName => $childValue) {
					if (is_null($childValue)) {
						continue;
					}
					$this->children[$childName] = $childValue;
				}
			} elseif ($argKey === 'child' && !is_null($argValue)) {
				$this->children[] = $argValue;
				$this->child = $argValue;
			} elseif (
				preg_match('/^(data\-)(.*)/', $argKey, $out) ||
				in_array($argKey, ['rel', 'before', 'done', 'boxWidth', 'boxHeight'])
			) {
				if ($out) {
					$argKey = $out[2];
				}
				$this->{$argKey} = $argValue;
				$this->data($argKey, $argValue);
				$this->attribute['data-' . $argKey] = $argValue;
			} else {
				$this->{$argKey} = $argValue;
			}
		}
		if ($this->widgetName == 'Widget') {
			$this->widgetName = get_class($this);
		}
		$this->initWidget();
	}

	// @override
	protected function initWidget() {}

	function initConfig() {
		$this->config = (object) [
			'attr' => [],
			'data' => [],
			'header' => (object) []
		];
	}

	// @deprecated
	function addClass($class) {
		$this->config->class .= ' ' . $class;
	}

	// @deprecated
	function addId($id) {
		$this->id = $id;
	}

	// @deprecated
	function addConfig($key, $value) {
		$this->config->{$key} = $value;

		// Add key to class property
		if (in_array($key, ['id'])) {
			$this->{$key} = $value;
		}
	}

	// @deprecated
	function addAttr($key, $value) {
		$this->config->attr[$key] = $value;
	}

	// @deprecated
	function addData($key, $value) {
		$this->config->data['data-' . $key] = $value;
	}

	// @deprecated
	function config($key, $value) {
		$this->config->{$key} = $value;
		return $key ? $this->config->{$key} : $this->config;
	}

	// @deprecated
	function attr($key, $value) {
		$this->config->attr[$key] = $value;
		return $key ? $this->config->attr[$key] : $this->config->attr;
	}

	// @deprecated
	function data($key = null, $value = null) {
		if (isset($key) && isset($value)) {
			$this->config->data['data-' . $key] = $value;
		}
		return $key
			? $this->config->data['data-' . $key] ?? ''
			: $this->config->data;
	}

	// @deprecated
	function header($str, $attr = '{}', $options = '{}') {
		$this->header = (object) [
			'text' => $str,
			'attr' => \SG\json_decode($attr),
			'options' => \SG\json_decode($options)
		];
	}

	function children($value = null) {
		if (isset($value)) {
			$this->children[] = $value;
		}

		if ($this->body ?? false) {
			$childrens = [$this->body];
		} elseif ($this->children ?? false) {
			$childrens = $this->children;
		} else {
			$childrens = [];
		}

		return $childrens;
	}

	// Container of widget
	// @override
	protected function renderWidgetContainerStart($callbackFunction = null) {
		return $this->tagName
			? ($this->widgetName != 'Widget'
					? '<!-- Start of ' . $this->widgetName . ' -->' . _NL
					: '')
					. '<'
					. $this->tagName
					. _NL
					. ($this->id ? ' id="' . $this->id . '"' . _NL : '')
					. ' class="widget-'
					. strtolower($this->widgetName ?? '')
					. (!empty($this->class) ? ' ' . $this->class : '')
					. (!empty($this->mainAxisAlignment)
						? ' -main-axis-' . strtolower($this->mainAxisAlignment)
						: '')
					. (!empty($this->crossAxisAlignment)
						? ' -cross-axis-' . strtolower($this->crossAxisAlignment)
						: '')
					. '"'
					. _NL
					. (!empty($this->href) ? ' href="' . $this->href . '"' . _NL : '')
					. (!empty($this->config->data['data-rel'])
						? ' data-rel="' . $this->config->data['data-rel'] . '"' . _NL
						: '')
					. (!empty($this->config->data['data-done'])
						? ' data-done="' . $this->config->data['data-done'] . '"' . _NL
						: '')
					. (!empty($this->dataUrl)
						? ' data-url="' . $this->dataUrl . '"' . _NL
						: '')
					. (!empty($this->webview)
						? ' data-webview="' . $this->webview . '"' . _NL
						: '')
					. (!empty($this->data('options'))
						? ' data-options=\'' . $this->data('options') . '\' ' . _NL
						: '')
					. (!empty($this->data('class-name'))
						? ' data-class-name="' . $this->data('class-name') . '"' . _NL
						: '')
					. (!empty($this->style) ? ' style="' . $this->style . '"' . _NL : '')
					. (!empty($this->attribute) && is_array($this->attribute)
						? ' ' . sg_implode_attr($this->attribute) . _NL
						: '')
					. (!empty($callbackFunction) && is_callable($callbackFunction)
						? $callbackFunction()
						: '')
					. '>'
					. _NL
			: '';
	}

	// @override
	protected function renderWidgetContainerEnd() {
		return $this->tagName
			? _NL
					. '</'
					. $this->tagName
					. '><!-- End of '
					. $this->widgetName
					. ' -->'
					. _NL
			: '';
	}

	// Container cover all children
	// @override
	protected function renderChildrenContainerStart() {
		if (empty($this->childrenContainer)) {
			return;
		}
		return '<'
			. $this->childrenContainer['tagName']
			. (!empty($this->childrenContainer['class'])
				? ' class="' . $this->childrenContainer['class'] . '"'
				: '')
			. ' >'
			. _NL;
	}

	// @override
	protected function renderChildrenContainerEnd() {
		return !empty($this->childrenContainer)
			? '</' . $this->childrenContainer['tagName'] . '>' . _NL
			: '';
	}

	// Container for each child of children
	// @override
	protected function renderChildContainerStart(
		$childKey,
		$attributes = [],
		$child = []
	) {
		foreach ($attributes as $key => $value) {
			if (is_null($value)) {
				unset($attributes[$key]);
			}
		}

		$container = (array) ($this->container ?? []);

		$childTagName = \SG\getFirst(
			$this->childTagName ?? null,
			$this->childContainer['tagName'] ?? null
		);
		$attributes['class'] =
			(!empty($this->childContainer['class'])
				? $this->childContainer['class']
				: '')
			. (!empty($this->itemClass) ? ' ' . $this->itemClass : '')
			. (!empty($attributes['class']) ? ' ' . $attributes['class'] : '')
			. (!empty($container['class']) ? ' ' . $container['class'] : '')
			. (!is_numeric($childKey) ? ' -child-' . $childKey : '')
			. (!is_numeric($childKey) ? ' -' . $childKey : ''); // @deprecated class ' -' . $childKey
		$attributes['class'] = trim($attributes['class']);

		$childAttribute = isset($container['children'][$childKey])
			? (array) $container['children'][$childKey]
			: [];

		unset($container['class'], $container['children']);

		$attributes = array_replace_recursive(
			[
				'id' => null,
				'class' => null,
				'style' => null
				// And other attributes
			],
			$container,
			$childAttribute,
			$attributes
		);

		return $childTagName
			? '<'
					. $childTagName
					. ' '
					. sg_implode_attr($attributes)
					. '>'
					. ($this->debug === 'container'
						? 'Child tag name = '
							. $childTagName
							. '<br>Child key = '
							. $childKey
							. '<br>'
							. print_o($container, '$container')
							. print_o($attributes, '$attributes')
						: '')
			: '';
	}

	// @override
	protected function renderChildContainerEnd($child = [], $childKey = null) {
		$childTagName = \SG\getFirst(
			$this->childTagName ?? null,
			$this->childContainer['tagName'] ?? null
		);
		return $childTagName ? '</' . $childTagName . '>' . _NL : '';
	}

	// Can override
	// Render single widget
	protected function renderEachChildWidget(
		$widget,
		$key = null,
		$callbackFunction = [],
		$options = []
	) {
		$options = (array) array_replace(
			[
				'prefix' => null,
				'subfix' => null
			],
			(array) $options
		);

		$result = isset($options['prefix']) ? $options['prefix'] : '';

		if (is_object($widget) && method_exists($widget, 'build')) {
			// Build Widget
			if (
				isset($callbackFunction['object']) &&
				is_callable($callbackFunction['object'])
			) {
				$result .= $callbackFunction['object']($key, $widget);
			} else {
				$buildResult = $widget->build();
				if (is_object($buildResult) && method_exists($buildResult, 'build')) {
					$result .= $buildResult->build();
				} else {
					$result .= $buildResult;
				}
			}
		} elseif (is_object($widget)) {
			// Build General Object
			$result .=
				isset($callbackFunction['object']) &&
				is_callable($callbackFunction['object'])
					? $callbackFunction['object']($key, $widget)
					: \SG\json_encode($widget);
		} elseif (is_array($widget)) {
			// Build array
			$result .=
				!empty($callbackFunction['array']) &&
				is_callable($callbackFunction['array'])
					? $callbackFunction['array']($key, $widget)
					: \SG\json_encode($widget);
		} elseif (is_string($widget) && $widget === '<sep>') {
			// Build Seperator
			$result .=
				isset($callbackFunction['seperator']) &&
				is_callable($callbackFunction['seperator'])
					? $callbackFunction['seperator']($key, $widget)
					: '<hr class="separator" size="0" />';
		} else {
			// Build Text
			$result .=
				isset($callbackFunction['text']) &&
				is_callable($callbackFunction['text'])
					? $callbackFunction['text']($key, $widget)
					: $widget;
		}

		$result .= isset($options['subfix']) ? $options['subfix'] : '';

		return $result;
	}

	// @override
	// Render all item of childrens
	protected function renderChildren($childrens = [], $args = []) {
		$childrens = (array) $childrens;
		$args = array_replace(
			[
				'class' => null,
				'prefix' => null,
				'subfix' => null
			],
			(array) $args
		);
		$prefix = $args['prefix'];
		$subfix = $args['subfix'];
		unset($args['prefix'], $args['subfix']);

		$ret = $prefix;
		$ret .= $this->renderChildrenContainerStart();

		foreach ($childrens as $childKey => $child) {
			$extraArgs = [];
			if (is_string($child) && $child === '<sep>') {
				// Children is separator
				$extraArgs['class'] = $args['class'] . ' -sep';
			} elseif (is_string($child) && $child === '<spacer>') {
				// Children is spacer
				$extraArgs['class'] =
					$args['class'] . ($args['class'] ? ' ' : '') . '-spacer';
				$child = '';
			} elseif (is_object($child) && get_class($child) === 'Children') {
				// children is class of Children
				if (!empty($child->tagName)) {
					$ret .=
						'<'
						. $child->tagName
						. ' id="'
						. ($child->id ?? '')
						. '" class="-children-widget '
						. ($child->class ?? '')
						. '">';
				}
				foreach ($child->children as $subKey => $subChild) {
					if (is_string($subKey)) {
						$subChild['inputName'] = $subKey;
					}
					$ret .= $this->renderChildContainerStart($subKey, [], $subChild);
					$ret .= $this->renderEachChildWidget($subChild, $subKey);
					$ret .= $this->renderChildContainerEnd($subChild, $subKey) . _NL;
				}
				if (!empty($child->tagName)) {
					$ret .= '</' . $child->tagName . '>';
				}
				continue;
			} elseif (is_string($childKey)) {
				// echo '<br> childKey='.$childKey.' | '.gettype($child).'<br>';
				// echo print_o($child, '$child');
				// $child->inputName = $childKey; // Error when child is string
			}

			$ret .= $this->renderChildContainerStart(
				$childKey,
				$args + $extraArgs,
				$child
			);
			$ret .= $this->renderEachChildWidget($child, $childKey);
			$ret .= $this->renderChildContainerEnd($child, $childKey);
		}

		$ret .= $this->renderChildrenContainerEnd();
		$ret .= $subfix;

		return $ret;
	}

	// @override
	function toString() {
		$ret = $this->renderWidgetContainerStart();
		if ($this->header) {
			if (is_object($this->header) && method_exists($this->header, 'build')) {
				$ret .= $this->header->build();
			}
			if (is_string($this->header)) {
				$ret .= $this->header;
			}
		}
		if ($this->children()) {
			$ret .= $this->renderChildren($this->children());
		}
		$ret .= $this->renderWidgetContainerEnd();
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
	function show() {
		return $this->build();
	}
} // End of class Widget

/**
 * Basic Widget Group
 */

/**
 * Widget :: DOM Widget
 *
 * @param array $args
 * @uses new DOM(['tag' => 'img', 'class' => 'class-name', 'onClick' => 'script', 'child/children'])
 */
class DOM extends Widget {
	public $widgetName;
	public $version = '0.0.01';
	public $tagName;
	public $class;
	public $settings = [];

	function __construct($args = []) {
		$this->tagName = array_shift($args);
		$this->widgetName = 'dom-' . $this->tagName;
		$this->settings = (array) ($args['settings'] ?? []);
		if (isset($args['children'])) {
			$this->children = $args['children'];
		} elseif ($args['child']) {
			$this->children[] = $args['child'];
		}
		$this->class = $args['class'] ? $args['class'] : '';

		unset(
			$args['tag'],
			$args['settings'],
			$args['child'],
			$args['children'],
			$args['class']
		);

		parent::__construct(['attribute' => $args]);

		if (!empty($this->settings['debug'])) {
			debugMsg($this, '$this');
		}
	}

	// @override
	function toString() {
		$unpairedTags = ['img', 'br'];
		$ret = $this->renderWidgetContainerStart();
		if ($this->children()) {
			$ret .= $this->renderChildren($this->children());
		}
		if (!in_array($this->tagName, $unpairedTags)) {
			$ret .= $this->renderWidgetContainerEnd();
		}
		return $ret;
	}
}

/**
 * Widget :: HtmlTemplate Widget
 *
 * @param array $args
 * @uses new HtmlTemplate([key => value,...])
 */
class HtmlTemplate extends Widget {
	public $widgetName = 'Template';
	public $version = '0.0.01';
	public $tagName = 'template';
}

/**
 * Widget :: Header Widget
 *
 * @param array $args
 * @uses new Header([key => value,...])
 */
class Header extends Widget {
	public $widgetName = 'Header';
	public $version = '0.0.01';
	public $tagName = 'header';
	public $titleTag = 'span';
	public $leading = '';
	public $title = '';
	public $subTitle = '';
	public $trailing = '';

	function __construct(
		$args = [],
		?string $id = null,
		?string $class = null,
		mixed $title = null,
		mixed $subTitle = null,
		mixed $leading = null,
		mixed $trailing = null,
		?string $crossAxisAlignment = null,
		bool $debug = false
	) {
		parent::__construct(
			array_replace(
				[
					'id' => $id,
					'class' => $class,
					'title' => $title,
					'subTitle' => $subTitle,
					'leading' => $leading,
					'trailing' => $trailing,
					'crossAxisAlignment' => $crossAxisAlignment,
					'debug' => $debug
				],
				$args
			)
		);
	}

	// @override
	function toString() {
		return $this->renderWidgetContainerStart()
			. ($this->leading
				? '<div class="-leading">'
					. $this->renderEachChildWidget($this->leading)
					. '</div>'
					. _NL
				: '')
			. '<div class="-title">'
			. ($this->title
				? '<'
					. $this->titleTag
					. ' class="-title-text">'
					. $this->renderEachChildWidget($this->title)
					. '</'
					. $this->titleTag
					. '>'
				: '')
			. ($this->subTitle
				? '<span class="-subtitle-text">'
					. $this->renderEachChildWidget($this->subTitle)
					. '</span>'
				: '')
			. '</div>'
			. _NL
			. ($this->trailing
				? '<div class="-trailing">'
					. $this->renderEachChildWidget($this->trailing)
					. '</div>'
					. _NL
				: '')
			. $this->renderChildren($this->children())
			. $this->renderWidgetContainerEnd();
	}
} // End of class Header

/**
 * Widget :: Container Widget
 *
 * @param array $args
 * @uses new Container([key => value,...])
 */
class Container extends Widget {
	public $widgetName = 'Container';
	public $version = '0.0.01';
	public $tagName = 'div';
	public $fillButton = false;

	function __construct(
		$args = [],
		?string $widgetName = null,
		?string $id = null,
		?string $class = null,
		?string $style = null,
		?string $tagName = 'div',
		mixed $child = null,
		?array $children = []
	) {
		parent::__construct(
			array_replace(
				[
					'widgetName' => $widgetName,
					'id' => $id,
					'class' => $class,
					'style' => $style,
					'tagName' => $tagName,
					'child' => $child,
					'children' => $children
				],
				$args
			)
		);
	}

	// @override
	function toString() {
		$ret = $this->renderWidgetContainerStart();
		if ($this->children()) {
			$ret .= $this->renderChildren($this->children());
		}
		$ret .= $this->renderWidgetContainerEnd();
		return $ret;
	}
} // End of class Container

/**
 * Widget :: Section Widget
 *
 * @param array $args
 * @uses new Container([key => value,...])
 */
class Section extends Widget {
	public $widgetName = 'Section';
	public $version = '0.0.01';
	public $tagName = 'section';
	public $fillButton = false;

	function __construct(
		$args = [],
		?string $id = null,
		?string $class = null,
		?string $style = null,
		mixed $child = null,
		?array $children = []
	) {
		parent::__construct(
			array_replace(
				[
					'id' => $id,
					'class' => $class,
					'style' => $style,
					'child' => $child,
					'children' => $children
				],
				$args
			)
		);
	}

	// @override
	function toString() {
		$ret = $this->renderWidgetContainerStart();
		if ($this->children()) {
			$ret .= $this->renderChildren($this->children());
		}
		$ret .= $this->renderWidgetContainerEnd();
		return $ret;
	}
} // End of class Section

/**
 * Widget :: Center Widget
 *
 * @param array $args
 * @uses new Center([key => value,...])
 */
class Center extends Widget {
	public $widgetName = 'Center';
	public $version = '0.0.01';
	public $tagName = 'div';
	public $class = '-sg-text-center';

	function __construct($args = []) {
		parent::__construct($args);
	}
} // End of class Center

/**
 * Widget :: List Order Widget
 *
 * @param array $args
 * @uses new ListOrder([key => value,...])
 */
class ListOrder extends Widget {
	public $widgetName = 'ListOrder';
	public $version = '0.0.01';
	public $tagName = 'ul';
	public ?array $childContainer = ['tagName' => 'li', 'class' => '-item'];

	function __construct($args = []) {
		parent::__construct($args);
		if (!empty($this->type)) {
			$this->tagName = $this->type;
		}
	}
} // End of class ListOrder

/**
 * Widget :: Column Widget
 *
 * @param array $args
 * @uses new Column([key => value,...])
 */
class Column extends Widget {
	public $widgetName = 'Column';
	public $version = '0.0.01';
	public $tagName = 'div';
	public ?array $childContainer = ['tagName' => 'div', 'class' => '-item'];

	function __construct(
		$args = [],
		?string $id = null,
		?string $class = null,
		?string $style = null,
		?array $children = []
	) {
		parent::__construct(
			array_replace(
				[
					'id' => $id,
					'class' => $class,
					'style' => $style,
					'children' => $children
				],
				$args
			)
		);
	}
} // End of class Column

/**
 * Widget :: Row Widget
 *
 * @param array $args
 * @uses new Row([key => value,...])
 */
class Row extends Widget {
	public $widgetName = 'Row';
	public $version = '0.0.10';
	public $tagName = 'div';
	public ?array $childContainer = ['tagName' => 'div', 'class' => '-item'];
	function __construct(
		$args = [],
		?string $id = null,
		?string $class = null,
		?string $style = null,
		?string $mainAxisAlignment = null,
		?string $crossAxisAlignment = null,
		mixed $child = null,
		?array $children = []
	) {
		parent::__construct(
			array_replace(
				[
					'id' => $id,
					'class' => $class,
					'style' => $style,
					'mainAxisAlignment' => $mainAxisAlignment,
					'crossAxisAlignment' => $crossAxisAlignment,
					'child' => $child,
					'children' => $children
				],
				$args
			)
		);
	}
} // End of class Row

/**
 * Widget :: Floating Action Button Widget
 *
 * @param array $args
 * @uses new FloatingActionButton([key => value,...])
 */
class FloatingActionButton extends Widget {
	public $widgetName = 'FloatingActionButton';
	public $version = '0.0.01';
	public $tagName = 'div';
	public ?array $childContainer = ['tagName' => 'div', 'class' => '-item'];
} // End of class FloatingActionButton

/**
 * Widget :: List Tile Widget
 *
 * @param array $args
 * @uses new ListTile([key => value,...])
 */
class ListTile extends Widget {
	public $widgetName = 'ListTile';
	public $version = '0.0.01';
	public $tagName = 'div';
	public $titleTag = 'span';
	public $leading;
	public $title;
	public $subTitle;
	public $trailing;

	function __construct($args = []) {
		parent::__construct($args);
	}

	// @override
	function toString() {
		return $this->renderWidgetContainerStart()
			. ($this->leading
				? '<div class="-leading">'
					. $this->renderEachChildWidget($this->leading)
					. '</div>'
					. _NL
				: '')
			. '<div class="-title">'
			. ($this->title
				? '<'
					. $this->titleTag
					. ' class="-title-text">'
					. $this->renderEachChildWidget($this->title)
					. '</'
					. $this->titleTag
					. '>'
				: '')
			. ($this->subTitle ?? ($this->subtitle ?? null)
				? '<span class="-subtitle-text">'
					. $this->renderEachChildWidget($this->subTitle ?? $this->subtitle)
					. '</span>'
				: '')
			. '</div>'
			. _NL
			. ($this->trailing
				? '<div class="-trailing">'
					. $this->renderEachChildWidget($this->trailing)
					. '</div>'
					. _NL
				: '')
			. $this->renderChildren($this->children())
			. $this->renderWidgetContainerEnd();
	}
} // End of class ListTile

/**
 * Widget :: Card Widget
 *
 * @param array $args
 * @uses new Card([key => value,...])
 */
class Card extends Widget {
	public $widgetName = 'Card';
	public $version = '0.0.01';
	public $tagName = 'div';
	public $titleTag = 'div';

	function __construct(
		$args = [],
		?string $id = null,
		?string $class = null,
		?string $href = null,
		// ?array $header = null,
		mixed $child = null,
		?array $children = []
	) {
		parent::__construct(
			array_replace(
				[
					'id' => $id,
					'class' => $class,
					'href' => $href,
					// 'header' => $header,
					'child' => $child,
					'children' => $children
				],
				$args
			)
		);
	}

	// @override
	function toString() {
		return $this->renderWidgetContainerStart()
			. ($this->header
				? '<header class="-header">'
					. (isset($this->header['leading'])
						? '<div class="-leading">'
							. $this->renderEachChildWidget($this->header['leading'])
							. '</div>'
							. _NL
						: '')
					. '<div class="-title">'
					. (isset($this->header['title'])
						? '<'
							. \SG\getFirst(
								$this->header['titleTag'] ?? null,
								$this->titleTag ?? null
							)
							. ' class="-title-text">'
							. $this->renderEachChildWidget($this->header['title'])
							. '</'
							. \SG\getFirst(
								$this->header['titleTag'] ?? null,
								$this->titleTag ?? null
							)
							. '>'
						: '')
					. (isset($this->header['subtitle'])
						? '<span class="-subtitle-text">'
							. $this->renderEachChildWidget($this->header['subtitle'])
							. '</span>'
						: '')
					. '</div>'
					. _NL
					. (isset($this->header['trailing'])
						? '<div class="-trailing">'
							. $this->renderEachChildWidget($this->header['trailing'])
							. '</div>'
							. _NL
						: '')
					. '</header><!-- header -->'
				: '') // header
			. $this->renderChildren($this->children())
			. $this->renderWidgetContainerEnd();
	}
} // End of class Card

/**
 * Widget :: Nav Widget
 *
 * @param array $args
 * @uses new Nav([key => value,...])
 */
class Nav extends Widget {
	public $widgetName = 'Nav';
	public $version = '0.0.01';
	public $tagName = 'nav';
	public $class = 'nav';
	public ?string $type = null;
	public ?string $direction = null;
	public $multipleLevel = false;

	function __construct(
		$args = [],
		?string $id = null,
		?string $class = null,
		?string $style = null,
		?string $type = null,
		?string $direction = null,
		?string $mainAxisAlignment = null,
		?string $crossAxisAlignment = null,
		?array $children = []
	) {
		parent::__construct(
			array_replace(
				[
					'id' => $id,
					'class' => $class,
					'style' => $style,
					'type' => $type,
					'direction' => $direction,
					'mainAxisAlignment' => $mainAxisAlignment,
					'crossAxisAlignment' => $crossAxisAlignment,
					'children' => $children
				],
				$args
			)
		);
		if ($this->type) {
			$this->class .= ' -type-' . $this->type;
		}
		if ($this->direction ?? null) {
			$this->class .= ' -' . $this->direction;
		}
	}

	// function __construct($args = []) {
	// 	parent::__construct($args);

	// 	if ($args['type'] ?? null) {
	// 		$this->class .= ' -type-' . $args['type'];
	// 	}
	// 	if ($args['direction'] ?? null) {
	// 		$this->class .= ' -' . $args['direction'];
	// 	}
	// }

	#[\Override]
	protected function renderEachChildWidget(
		$widget,
		$key = null,
		$callbackFunction = [],
		$options = []
	) {
		return parent::renderEachChildWidget($widget, $key, [
			'array' => function ($key, $widget) {
				$result = '<ul class="nav-list">' . _NL;
				foreach ($widget as $eachKey => $eachWidget) {
					$result .=
						'<li class="-item">'
						. trim($this->renderEachChildWidget($eachWidget, $eachKey))
						. '</li>'
						. _NL;
				}
				$result .= '</ul>' . _NL;
				return $result;
			},
			'object' => function ($key, $widget) {
				return trim($widget->build());
			},
			'text' => function ($key, $text) {
				return $text;
			}
		]);
		return $result;
	}

	// @override
	protected function renderChildren($childrens = [], $args = []) {
		foreach ($childrens as $key => $value) {
			if (is_array($value)) {
				$this->multipleLevel = true;
				break;
			}
		}
		if (!$this->multipleLevel) {
			$this->childrenContainer = [
				'tagName' => 'ul',
				'class' =>
					'-nav-list'
					. (!empty($this->childClass) ? ' ' . $this->childClass : '')
			];
			$this->childContainer = ['tagName' => 'li', 'class' => '-item'];
		}
		return parent::renderChildren($childrens, $args);
	}
} // End of class Nav

/**
 * Widget :: Side Bar Widget
 *
 * @param array $args
 * @uses new SideBar([key => value,...])
 */
class SideBar extends Widget {
	public $widgetName = 'SideBar';
	public $version = '0.0.01';
	public $tagName = 'aside';
	public ?string $type; // null, wide
	public mixed $gripHeader = 'Menu';

	function __construct(
		$args = [],
		?string $id = null,
		?string $class = null,
		?string $type = null,
		?string $gripHeader = null,
		?array $children = []
	) {
		parent::__construct(
			array_replace(
				[
					'id' => $id,
					'class' => $class,
					'type' => $type,
					'gripHeader' => $gripHeader,
					'children' => $children
				],
				$args
			)
		);

		if (!empty($this->type)) {
			$this->class =
				'-type-' . $this->type . ($this->class ? ' ' . $this->class : '');
		}
	}

	#[\Override]
	protected function renderWidgetContainerStart($callbackFunction = null) {
		$header = is_object($this->gripHeader)
			? $this->gripHeader
			: new \Header([
				'title' => 'Menu',
				'leading' => new Icon('menu')
			]);
		$header->id = 'sidebar-toggle';
		$header->class = '-sidebar-toggle';
		$header->trailing = '<input type="checkbox" id="sidebar-toggle-checkbox" class="-toggle-checkbox">
				<label for="sidebar-toggle-checkbox" class="-grip" aria-label="ขยายหรือย่อเมนู">
				<i class="icon -material">menu_open</i>
				</label>';
		// <span class="icon"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
		// 	<path d="M10 6 8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"></path>
		// </svg></span>
		return parent::renderWidgetContainerStart() // . '<span id="sidebar-toggle" class="-sidebar-toggle">
			. // . '<span class="-grip-header">'
			$this->renderChildren([$header]); // . '</span>'
		// . '</span>';
	}
} // End of class Nav

/**
 * Widget :: Scroll View Widget
 *
 * @param array $args
 * @uses new ScrollView([key => value,...])
 */
class ScrollView extends Widget {
	public $widgetName = 'ScrollView';
	public $version = '0.0.01';
	public $tagName = 'div';
	public $scrollDirection = 'horizontal';

	function __construct($args = []) {
		parent::__construct($args);
	}
} // End of class ScrollView

/**
 * Widget :: Debug Message Widget
 *
 * @param array $args
 * @uses new DebugMsg([key => value,...])
 */
class DebugMsg extends Widget {
	public $version = '0.0.01';
	public $msg;
	public $varName;
	public $callFrom;

	function __construct($msg = null, $varName = null, $callFrom = null) {
		parent::__construct([
			'msg' => $msg,
			'varName' =>
				is_object($msg) && !isset($varName) ? get_class($msg) : $varName,
			'callFrom' => isset($callFrom) ? $callFrom : debug_backtrace()
		]);
	}

	function build() {
		if (!user_access('access debugging program')) {
			return;
		}

		$isObject = is_object($this->msg) || is_array($this->msg);

		$callString =
			'<details><summary>Call from : '
			. $this->callFrom[0]['file']
			. ' @line '
			. $this->callFrom[0]['line']
			. '</summary><div class="widget-scrollview"><pre style="white-space: pre-wrap">';
		foreach ($this->callFrom as $key => $value) {
			$callString .=
				($value['file'] ?? '') . ' @line ' . ($value['line'] ?? '') . '<br>';
		}
		$callString .= '</pre></div></details>';

		if ($isObject) {
			$this->msg =
				'<div>' . self::printObject($this->msg, $this->varName) . '</div>';
		} elseif (
			isset($this->msg) &&
			preg_match('/^(SELECT|UPDATE|INSERT|DELETE)/i', trim($this->msg))
		) {
			$this->msg = '<pre>' . $this->msg . '</pre>';
		} elseif (!isset($this->msg)) {
			$this->msg = 'NULL';
		}

		return _NL
			. '<div class="debug-msg">'
			. ($this->varName && !$isObject ? $this->varName . ' = ' : '')
			. '<span class="widget-button sg-expand" data-rel="next"><i class="icon -material">expand_more</i></span>'
			. $this->msg
			. ($this->callFrom
				? '<div class="-call-from">' . $callString . '</div>'
				: '')
			. '</div>'
			. _NL;
	}

	static function printObject($arr = [], $name = '', $options = []) {
		if ($name && is_object($arr)) {
			$prefix = '->';
			$suffix = '';
		} elseif ($name && is_array($arr)) {
			$prefix = '[';
			$suffix = ']';
		} else {
			$prefix = $suffix = '';
		}

		$result =
			'<ul class="-array-value '
			. (isset($options['class']) ? $options['class'] : '')
			. '">'
			. _NL;
		if (is_object($arr) || (is_array($arr) and count($arr) > 0)) {
			foreach ($arr as $key => $value) {
				$vtype = GetType($value);
				$hasChild = in_array($vtype, ['array', 'object']);
				$result .=
					'<li>'
					. '<span class="'
					. ($hasChild ? 'widget-button sg-expand' : '')
					. '" '
					. 'data-rel="next">'
					. $name
					. $prefix
					. $key
					. $suffix
					. ($hasChild ? '<i class="icon -material">expand_more</i>' : '')
					. '<span class="-var-type">['
					. (is_object($value) ? get_class($value) . ' ' : '')
					. $vtype
					. ']</span>'
					. (preg_match('/array|object/i', $vtype) ? '' : '<span> : </span>')
					. '</span>';
				switch ($vtype) {
					case 'boolean':
						$result .= $value ? 'true' : 'false';
						break;
					case 'array':
						$result .= self::printObject(
							$value,
							$name . $prefix . $key . $suffix
						);
						break;
					case 'object':
						$result .= self::printObject(
							$value,
							$name . $prefix . $key . $suffix
						);
						break;
					default:
						$result .= '<span class="-value">' . $value . '</span>';
						break;
				}
				$result .= '</li>' . _NL;
			}
		} else {
			$result .= '<li>(empty)</li>' . _NL;
		}
		$result .= '</ul>' . _NL;

		return $result;
	}
} // End of class DebugMsg

/**
 * Widget :: Message Widget
 *
 * @param array $args
 * @uses new Message([key => value,...])
 */
class Message extends WidgetBase {
	public $widgetName = 'Message';
	public $version = '0.0.01';
	public $responseCode;
	public $error = false;
	public $text;
	function __construct($args = []) {
		parent::__construct($args);

		if (isset($args['errorMessage'])) {
			$this->error = true;
		}
		$this->returnObject = is_array($args) || is_object($args) ? $args : null;
	}

	// @override
	function build() {
		$message = $this->error ? $this->errorMessage : $this->text;

		if ($this->responseCode) {
			http_response_code($this->responseCode);
		}

		$result = $this->returnObject ? $this->returnObject : $message;

		if (_AJAX) {
			return $result;
		}

		if ($this->responseCode === _HTTP_ERROR_FORBIDDEN) {
			$templateFile = 'access_denied';
			$message = file_get_contents(
				_CORE_FOLDER . '/core/assets/template/' . $templateFile . '.html'
			);
		}

		return '<div class="widget-message'
			. ($this->error ? ' -error' : '')
			. ($this->responseCode ? ' -code-' . $this->responseCode : '')
			. '">'
			. $message
			. '</div>';
	}
} // End of class Message

/**
 * Widget :: Error Message Widget
 *
 * @param array $args
 * @uses new ErrorMessage([key => value,...])
 */
class ErrorMessage extends Message {
	public $widgetName = 'ErrorMessage';
	public $version = '0.0.01';
} // End of class ErrorMessage

/**
 * Widget :: Button widget
 *
 * @param array $args
 * @usage new Button([key => value,...])
 */
class Button extends Widget {
	public $widgetName = 'Button';
	public $version = '0.0.02';
	public $tagName = 'a';
	public ?string $href;
	public $type = 'default'; // default, primary, link, floating, secondary,success, info, warning, danger, link, cancel
	public $text;
	public $title;
	public string $iconPosition = 'left'; // left,right,top,bottom
	public $description;
	public $access;
	public $target;
	public $boxType; // Set box class name
	public $boxWidth; // Set box width value
	public $boxHeight; // Set box height value
	public $icon;
	public $variable;
	public $before;
	public $onClick;

	function __construct(
		$args = [],
		$variable = null,
		?string $confirmTitle = null,
		?string $confirmText = null,
		?string $type = null,
		?string $class = null,
		?string $href = null,
		?string $rel = null,
		?string $done = null,
		?object $icon = null,
		?string $text = null,
		?string $title = null,
		string $iconPosition = 'left',
		string|int|null $boxWidth = null,
		string|int|null $boxHeigth = null,
		mixed $access = null,
		?string $webviewTitle = null,
		?string $boxType = null
	) {
		parent::__construct(
			array_replace(
				[
					'type' => $type,
					'class' => $class,
					'href' => $href,
					'rel' => $rel,
					'done' => $done,
					'icon' => $icon,
					'text' => $text,
					'title' => $title,
					'iconPosition' => $iconPosition,
					'access' => $access,
					'variable' => $variable,
					'boxWidth' => $boxWidth,
					'boxHeigth' => $boxHeigth,
					'boxType' => $boxType,
					'data-webview' => $webviewTitle
				],
				(array) $args
			)
		);
		if ($confirmTitle) {
			$this->attribute['data-title'] = $confirmTitle;
		}
		if ($confirmText) {
			$this->attribute['data-confirm'] = $confirmText;
		}
		if (is_array($this->attribute)) {
			unset(
				$this->attribute['data-boxWidth'],
				$this->attribute['data-boxHeight']
			);
		}
		unset(
			$this->config,
			$this->dataUrl,
			$this->action,
			$this->childContainer,
			$this->header,
			$this->itemClass,
			$this->childTagName
		);
		if ($this->debug) {
			debugMsg($this, 'Icon');
		}
	}

	function toString() {
		// Check right by access
		if ($this->access) {
			if (!defined($this->access)) {
				return null;
			} elseif (!($this->variable->RIGHT & constant($this->access))) {
				return null;
			}
		}

		$attribute = array_replace_recursive(
			[
				'href' => $this->href,
				'id' => $this->id,
				'class' => trim(
					'widget-'
						. strtolower($this->widgetName)
						. ($this->type === 'default' ? '' : ' btn')
						. ($this->type ? ' -' . $this->type : '')
						. ($this->iconPosition == 'right' ? ' -icon-right' : '')
						. ($this->class ? ' ' . $this->class : '')
				),
				'title' => $this->title,
				'data-rel' => $this->rel,
				'data-before' => $this->before,
				'data-done' => $this->done,
				'target' => $this->target,
				'style' => $this->style,
				'data-class-name' => $this->boxType ? '-' . $this->boxType : null,
				'data-width' => $this->boxWidth ? $this->boxWidth : null,
				'data-height' => $this->boxHeight ? $this->boxHeight : null
			],
			(array) $this->attribute
		);

		if (is_null($attribute['href'])) {
			unset($attribute['href']);
		} else {
			$attribute['href'] = preg_replace(
				'/\{\{projectId\}\}/',
				$this->variable->projectId ?? '',
				$attribute['href'] ?? ''
			);
		}

		$button =
			'<a '
			. sg_implode_attr($attribute)
			. ($this->onClick ? ' onClick=\'' . $this->onClick . '\'' : '')
			. '>'
			. ($this->icon && $this->iconPosition == 'left'
				? $this->renderChildren([$this->icon])
				: '')
			. ($this->text
				? '<span class="-label">'
					. $this->renderChildren([$this->text])
					. '</span>'
				: '')
			. ($this->description
				? '<span class="-description">'
					. $this->renderChildren([$this->description])
					. '</span>'
				: '')
			. ($this->icon && $this->iconPosition == 'right'
				? $this->renderChildren([$this->icon])
				: '')
			. ($this->children ? $this->renderChildren($this->children) : '')
			. '</a>';
		return $button;
	}
} // End of class Button

/**
 * Widget :: Back Button widget
 *
 * @param array $args
 * @usage new BackButton([key => value,...])
 */
class BackButton extends Widget {
	public $widgetName = 'BackButton';
	public $version = '0.0.01';
	public $tagName = 'a';
	public ?string $href = 'javascript:history.back()';
	public $type; // default, primary, link, floating, secondary,success, info, warning, danger, link, cancel
	public $text;
	public $icon = '<i class="icon -material">arrow_back</i>';
	public $iconPosition = 'left'; // left,right,top,bottom
	public $description;
	public $onClick;

	function __construct($args = [], $variable = null) {
		parent::__construct($args);
	}

	function toString() {
		$attribute = array_replace_recursive(
			[
				'href' => $this->href,
				'id' => $this->id,
				'class' => trim(
					'widget-'
						. strtolower($this->widgetName)
						. (empty($this->type) ? '' : ' btn')
						. ($this->type ? ' -' . $this->type : '')
						. ($this->class ? ' ' . $this->class : '')
				),
				'title' => $this->title,
				'data-rel' => $this->rel,
				'data-before' => $this->before,
				'data-done' => $this->done,
				'target' => $this->target,
				'style' => $this->style
			],
			(array) $this->attribute
		);

		if (is_null($attribute['href'])) {
			unset($attribute['href']);
		}

		$button =
			'<'
			. $this->tagName
			. ' '
			. sg_implode_attr($attribute)
			. ($this->onClick ? ' onClick=\'' . $this->onClick . '\'' : '')
			. '>'
			. ($this->icon && $this->iconPosition == 'left'
				? $this->renderChildren([$this->icon])
				: '')
			. ($this->text
				? '<span class="-label">'
					. $this->renderChildren([$this->text])
					. '</span>'
				: '')
			. ($this->description
				? '<span class="-description">'
					. $this->renderChildren([$this->description])
					. '</span>'
				: '')
			. ($this->icon && $this->iconPosition == 'right'
				? $this->renderChildren([$this->icon])
				: '')
			. '</'
			. $this->tagName
			. '>';
		return $button;
	}
} // End of class BackButton

/**
 * Widget :: Icon Widget
 *
 * @param array $args
 * @uses new Icon(iconName, property=[])
 * @uses new Icon('iconName1, iconName2', property=[])
 */
class Icon extends Widget {
	public $widgetName = 'Icon';
	public $version = '0.0.03';
	public string $icon;
	public ?string $secondary = null; // For secondary icon
	public string $type;

	function __construct(
		string $icon,
		$args = [],
		string $type = 'material',
		?string $class = null,
		?string $secondary = null,
		?bool $debug = false
	) {
		$this->icon = $icon;

		parent::__construct(
			array_replace(
				[
					'type' => $type,
					'class' => $class,
					'secondary' => $secondary,
					'debug' => $debug
				],
				(array) $args
			)
		);
		unset(
			$this->config,
			$this->childContainer,
			$this->child,
			$this->children,
			$this->header,
			$this->childTagName,
			$this->tagName,
			$this->itemClass,
			$this->href,
			$this->dataUrl,
			$this->webview,
			$this->rel,
			$this->done,
			$this->action
		);
		if ($this->debug) {
			debugMsg($this, 'Icon');
		}
	}

	function toString() {
		if (is_string($this->icon) && preg_match('/$</', $this->icon)) {
			return $this->icon;
		}

		if (is_object($this->icon)) {
			return $this->icon;
		}
		if (!is_string($this->icon)) {
			return;
		}

		$attribute = array_replace_recursive($this->attribute, [
			'class' => trim(
				'widget-'
					. strtolower($this->widgetName)
					. ' icon -material '
					. $this->class
			)
		]);

		// 2 icons
		if (preg_match('/\,/', $this->icon)) {
			[$icon1, $icon2] = explode(',', $this->icon);

			return '<span class="icon-group">'
				. '<i '
				. sg_implode_attr($attribute)
				. '>'
				. $icon1
				. '</i>'
				. '<i '
				. sg_implode_attr($attribute)
				. '>'
				. $icon2
				. '</i>'
				. '</span>';
		}

		if ($this->secondary) {
			return '<span class="icon-group">'
				. '<i '
				. sg_implode_attr($attribute)
				. '>'
				. $this->icon
				. '</i>'
				. $this->secondary
				. '</span>';
		}

		return '<i ' . sg_implode_attr($attribute) . '>' . $this->icon . '</i>';
	}
} // End of class Icon

/**
 * Widget :: Expand Button Widget
 *
 * @param array $args
 * @uses new ExpandButton([key => value,...])
 */
class ExpandButton extends Widget {
	public $version = '0.0.01';
	public $icon = 'chevron_right';
	function toString() {
		return '<a'
			. ' class="widget-button -link sg-expand btn -link -no-print'
			. ($this->class ? ' ' . $this->class : '')
			. '"'
			. ' href="javascript:void(0)"'
			. sg_implode_attr($this->attribute)
			. '>'
			. '<i class="icon -material">'
			. $this->renderChildren([$this->icon])
			. '</i>'
			. '</a>';
	}
} // End of class ExpandButton

/**
 * Widget :: Step Menu Widget
 *
 * @param array $args
 * @uses new StepMenu([key => value,...])
 */
class StepMenu extends Widget {
	public $widgetName = 'StepMenu';
	public $version = '0.0.01';
	public $tagName = 'nav';
	public $class = '';
	public $childrenContainer = ['tagName' => 'ul'];
	public ?array $childContainer = ['tagName' => 'li'];
	public $currentStep = null;
	public $activeStep = [];

	function __construct($args = []) {
		parent::__construct($args);
	}

	protected function renderChildContainerStart(
		$stepIndex,
		$args = [],
		$child = []
	) {
		$stepIndex++;
		return '<'
			. $this->childContainer['tagName']
			. ' '
			. 'class="ui-item -step-'
			. $stepIndex
			. (!empty($this->childContainer['class'])
				? $this->childContainer['class']
				: '')
			. ($stepIndex == $this->currentStep ? ' -current-step' : '')
			. (isset($this->activeStep[$stepIndex]) && $this->activeStep[$stepIndex]
				? ' -active'
				: '')
			. '" '
			. '>';
	}
} // End of class StepMenu

/**
 * Widget :: List Item Widget
 *
 * @param array $args
 * @uses new ListItem([key => value,...])
 */
class ListItem extends Widget {
	public $widgetName = 'ListItem';
	public $version = '0.0.01';
	public $forceBuild = false;
	public $seperator = ' · ';
	public $tagName = 'ul';
	public $uiItemClass = 'ui-item -item';
	public $wrapperType = [
		'ul' => 'li',
		'span' => 'span',
		'div' => 'div',
		'div a' => 'a',
		'ol' => 'li'
	];
	public $type = 'action';

	protected function renderChildren($childrens = [], $args = []) {
		$ret = '';
		foreach ($childrens as $key => $value) {
			if (is_array($value)) {
				$child = (object) $value;
			} elseif (is_object($value)) {
				$child = $value;
			} else {
				$child = (object) ['text' => $value, 'options' => null];
			}

			// Convert options to object
			$options = is_string($child->options)
				? \SG\json_decode($child->options)
				: (object) $child->options;

			$uiItemClass =
				$this->uiItemClass . ($options->class ? ' ' . $options->class : '');
			if (in_array($child->text, ['-', '<sep>'])) {
				$uiItemClass .= ' -sep';
				$child->text = '<hr size="1" />';
			}
			$options->class = $uiItemClass;

			$uiItemTag = $this->wrapperType[$this->tagName];
			$ret .= $uiItemTag
				? '<' . $uiItemTag . ' ' . sg_implode_attr($options) . '>'
				: '';
			$ret .= $child->text;
			$ret .= $uiItemTag ? '</' . $uiItemTag . '>' : '';
			$ret .= _NL;
		}
		return $ret;
	}

	function build() {
		if (empty($this->children()) && $this->forceBuild === false) {
			return;
		}

		$ret = '';
		$attrText = '';
		$join = $this->tagName;
		$attrs = [];

		if ($this->config->id) {
			$attrs['id'] = $this->config->id;
		} elseif ($this->id) {
			$attrs['id'] = $this->id;
		}

		$attrs['class'] = 'widget-' . strtolower($this->widgetName);
		if ($this->type) {
			$attrs['class'] .= ' ' . $this->type;
		}
		if ($this->class) {
			$attrs['class'] .= ' ' . $this->class;
		}
		if ($this->columnPerRow) {
			$attrs['class'] .= ' -sg-flex -co-' . $this->columnPerRow;
		}

		foreach ($this->config->data as $key => $value) {
			$attrs[$key] = $value;
		}

		foreach ($this->config->attr as $key => $value) {
			$attrs[$key] = $value;
		}

		foreach ($attrs as $key => $value) {
			$attrText .= $key . '="' . $value . '" ';
		}

		$attrText = trim($attrText);

		[$joinTag] = explode(' ', $join);
		$ret .= '<' . $joinTag . ' ' . $attrText . '>' . _NL;

		if ($this->header->text) {
			$headerClass = $this->header->attr->class;
			unset($this->header->attr->class);
			$ret .=
				'<header class="header'
				. ($headerClass ? ' ' . $headerClass : '')
				. '" '
				. sg_implode_attr($this->header->attr)
				. '>'
				. ($this->header->options->preText
					? $this->header->options->preText
					: '')
				. $this->header->text
				. ($this->header->options->postText
					? $this->header->options->postText
					: '')
				. '</header>';
			if ($headerClass) {
				$this->header->attr->class = $headerClass;
			}
		}
		$ret .= $this->renderChildren($this->children());
		$ret .= '</' . $joinTag . '>' . _NL;

		if ($this->config->nav) {
			$this->container = $this->config->nav;
		} elseif ($this->config->container) {
			$this->container = $this->config->container;
		}

		if ($this->container) {
			$container = \SG\json_decode($this->container);
			$containerTag = $this->config->nav ? 'nav' : $container->tag;
			unset($container->tag);
			$containerAttr = sg_implode_attr($container);

			$ret =
				'<'
				. $containerTag
				. ' '
				. $containerAttr
				. '>'
				. $ret
				. '</'
				. $containerTag
				. '>';
		}

		return $ret;
	}
} // End of class ListItem

/**
 * Widget :: Tab Bar Widget
 *
 * @param array $args
 * @uses new TabBar([key => value,...])
 */
class TabBar extends Widget {
	public $widgetName = 'TabBar';
	public $version = '0.0.01';
	public $tagName = 'div';
	public $class = 'widget-tabbar sg-tabs';

	protected function renderChildren($childrens = [], $args = []) {
		$tabItems = '<ul class="tabs">';
		$tabContent = '';
		foreach ($childrens as $key => $child) {
			if (is_array($child)) {
				$child = (object) $child;
			}

			$tabItems .=
				'<li'
				. ' class="'
				. $child->class
				. ($child->active ? ' -active' : '')
				. '"'
				. '>';
			$tabItems .= $this->renderEachChildWidget($child->action);
			$tabItems .= '</li>';

			$tabContent .=
				'<div'
				. ' id="'
				. $child->id
				. '"'
				. ' class="'
				. ($child->active ? '' : '-hidden')
				. '">'
				. $this->renderEachChildWidget($child->content, $key)
				. '</div>';
		}
		$tabItems .= '</ul>';
		return $tabItems . $tabContent;
	}
} // End of class TabWidget

/**
 * Widget :: Profile Photo Widget
 *
 * @param array $args
 * @uses new ProfilePhoto([key => value,...])
 */
class ProfilePhoto extends Widget {
	public $widgetName = 'ProfilePhoto';
	public $version = '0.0.01';
	public $username;
	public $size; // small,big
	// parent property : $class,$attribute;

	function __construct($username = null, $args = []) {
		$this->username = $username;
		parent::__construct($args);
	}

	function toString() {
		$attribute = array_replace_recursive($this->attribute, [
			'class' =>
				trim('widget-' . strtolower($this->widgetName) . ' ' . $this->class)
				. ($this->size ? ' -size-' . $this->size : ''),
			'src' => UserModel::profilePhoto($this->username),
			'alt' => htmlspecialchars($this->title ?? ''),
			'title' => htmlspecialchars($this->title ?? '')
		]);
		return '<img ' . sg_implode_attr($attribute) . ' />';
	}
} // End of class ProfilePhoto

/**
 * Widget :: Notify Widget
 *
 * @param array $args
 * @uses new Notify([key => value,...])
 */
class Notify extends Widget {
	public $widgetName = 'Notify';
	public $version = '0.0.01';
	public $tagName = 'div';
	public $titleTag = 'div';
	public $class = 'notify';

	function __construct(
		$args = [],
		?string $id = null,
		?string $class = null,
		?string $style = null,
		mixed $child = null,
		mixed $children = []
	) {
		parent::__construct(
			array_replace(
				[
					'id' => $id,
					'class' => $class,
					'style' => $style,
					'child' => $child,
					'children' => $children
				],
				$args
			)
		);
	}

	// @override
	function toString() {
		return $this->renderWidgetContainerStart()
			. $this->renderChildren($this->children())
			. $this->renderWidgetContainerEnd();
	}
} // End of class Card

/**
 * Widget :: Stack Widget
 *
 * @param array $args
 * @uses new XXStackX([key => value,...])
 */
class Stack extends Widget {
	public $version = '0.0.01';
}

/**
 * Widget :: Grid View Widget
 *
 * @param array $args
 * @uses new XXGridViewX([key => value,...])
 */
class GridView extends Widget {
	public $version = '0.0.01';
}

/**
 * Widget :: Drawer Widget
 *
 * @param array $args
 * @uses new Drawer([key => value,...])
 */
class Drawer extends Widget {
	public $version = '0.0.01';
}

// Complex Widget Group

/**
 * Widget :: Scaffold Widget
 *
 * @param array $args
 * @uses new Scaffold([key => value,...])
 */
class Scaffold extends Widget {
	public $widgetName = 'Scaffold';
	public $version = '0.0.01';
	public mixed $appBar = null;
	public mixed $sideBar = null;
	public mixed $body = null;
	public mixed $floatingActionButton = null;
	public mixed $script = null;

	function __construct(
		$args = [],
		mixed $appBar = null,
		mixed $sideBar = null,
		mixed $body = null,
		mixed $floatingActionButton = null,
		?array $var = null,
		mixed $script = null
	) {
		parent::__construct(
			array_replace(
				[
					'appBar' => $appBar,
					'sideBar' => $sideBar,
					'body' => $body,
					'floatingActionButton' => $floatingActionButton,
					'var' => $var,
					'script' => $script
				],
				$args
			)
		);
	}
} // End of class Scaffold

/**
 * Widget :: App Bar Widget
 *
 * @param array $args
 * @uses new AppBar([key => value,...])
 */
class AppBar extends Widget {
	public $widgetName = 'AppBar';
	public $version = '0.0.01';
	public $tagName = 'div';
	public mixed $title = null;
	public mixed $subTitle = null;
	public mixed $leading = null;
	public mixed $trailing = null;
	public mixed $navigator = null;
	public ?object $dropbox = null;
	public bool $boxHeader = false;
	public bool $showInBox = false;
	public bool $removeOnApp = false;
	public bool $navigatorMultipleLevel = false;

	function __construct(
		$args = [],
		mixed $title = null,
		mixed $subTitle = null,
		mixed $leading = null,
		mixed $trailing = null,
		mixed $navigator = null,
		?object $dropbox = null,
		bool $boxHeader = false,
		bool $showInBox = false,
		bool $removeOnApp = false,
		bool $navigatorMultipleLevel = false
	) {
		parent::__construct(
			array_replace(
				[
					'title' => $title,
					'subTitle' => $subTitle,
					'leading' => $leading,
					'trailing' => $trailing,
					'navigator' => $navigator,
					'dropbox' => $dropbox,
					'boxHeader' => $boxHeader,
					'showInBox' => $showInBox,
					'removeOnApp' => $removeOnApp,
					'navigatorMultipleLevel' => $navigatorMultipleLevel
				],
				$args
			)
		);
	}

	/**
	 * Navigator format:
	 * #1: <a></a> : String
	 * #2: [1,2,3,widget,dropbbox] : array
	 * #3: [[1,2],[3,4],widget,dropbbox] : array of array
	 * #4: [widget,dropbbox] : array of widget
	 */
	protected function renderNavigator($navigators = null) {
		// $navigators = $navigators ? $navigators : NULL;
		$navigatorText = '';

		if (is_array($navigators)) {
			$navigatorText = '';
			foreach ($navigators as $key => $value) {
				if (is_array($value)) {
					$widget = new Nav(['children' => $value]);
					$navigatorText .= $this->renderEachChildWidget($widget, $key);
				} elseif (is_object($value) && method_exists($value, 'build')) {
					$navigatorText .= $value->build();
				} else {
					$navigatorText .= $value . _NL;
				}
			}
		} elseif (is_object($navigators)) {
			$navigatorText .= $this->renderEachChildWidget($navigators);
		} else {
			$navigatorText .= $navigators;
		}

		return $navigatorText ? $navigatorText : null;
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

		if ($this->showInBox && !preg_match('/header \-box/', $this->class)) {
			$this->class .= ' header -box';
		}
		if ($this->showInBox && $this->boxHeaderBack) {
			$this->leading = $this->boxHeaderBack;
		}

		if ($this->dropbox) {
			$this->class .= ' -has-dropbox';
		}

		// On single level navigator, add tag ul
		$navigatorResult = $this->renderNavigator($this->navigator);
		if (!$this->navigatorMultipleLevel && $navigatorResult) {
			$navigatorResult = '<ul>' . _NL . $navigatorResult . _NL . '</ul>';
		}

		return $this->renderWidgetContainerStart()
			. '<div class="-leading">'
			. $this->renderEachChildWidget($this->leading)
			. '</div>'
			. _NL
			. '<div class="-title"><h2 class="-text">'
			. ($this->title ? $this->renderEachChildWidget($this->title) : '')
			. '</h2>'
			. ($this->subTitle
				? '<div class="-sub">'
					. $this->renderEachChildWidget($this->subTitle)
					. '</div>'
				: '')
			. '</div>'
			. _NL
			. '<div class="-trailing -no-print">'
			. $this->renderEachChildWidget($this->trailing)
			. '</div>'
			. _NL
			. ($this->navigator && $navigatorResult
				? '<nav class="-nav -no-print">'
					. _NL
					. $navigatorResult
					. _NL
					. '</nav>'
					. _NL
				: '')
			. ($this->dropbox
				? '<div class="-dropbox">'
					. $this->dropbox->build()
					. '</div><!-- end of -dropboox -->'
					. _NL
				: '')
			. ($this->children()
				? '<div class="-children">'
					. $this->renderChildren($this->children())
					. '</div>'
				: '')
			. $this->renderWidgetContainerEnd();
	}
} // End of class AppBar

/**
 * Web Page Widget Group
 */

/**
 * Widget :: Page Base Widget
 *
 * For URL page interface
 */
class PageBase extends WidgetBase {
	public $widgetName = 'PageBase';
	public $module = null;
	protected $args = [];

	function __construct($args = []) {
		$this->widgetName = get_class($this);
		// Get module name form first word by split uppercase of widgetName
		$this->module = strToLower(
			preg_split('/(?=[A-Z])/', $this->widgetName, -1, PREG_SPLIT_NO_EMPTY)[0]
		);
		$this->version = cfg($this->module . '.version');
		parent::__construct($args);

		if (debug('widget')) {
			debugMsg(
				'PAGE BASE Action = '
					. $this->action
					. ' , Arg['
					. $this->argIndex
					. '] = '
					. $args[$this->argIndex]
			);
			debugMsg($args, '$PageBaseArgs');
			debugMsg($this, '$PageBaseThis');
		}
	}

	// Test function return array in PageApi or text in other
	function foo() {
		return get_parent_class($this) === 'PageApi'
			? success('Foo' . (post('msg') ? ' with ' . post('msg') : ''))
			: 'Foo' . (post('msg') ? ' with ' . post('msg') : '');
	}

	// Test function return text
	function fooText() {
		return 'Foo' . (post('msg') ? ' with ' . post('msg') : '');
	}
} // End of class PageBase

/**
 * Widget :: Web Page Widget
 *
 * @param array $args
 * @uses new Page([key => value,...])
 */
class Page extends PageBase {
	public $widgetName = 'Page';
	public $version = '0.0.01';

	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
		return new Scaffold([
			'appBar' => method_exists($this, 'appBar')
				? $this->appBar()
				: new AppBar(['title' => 'Web Page']),
			'body' => method_exists($this, 'body')
				? $this->body()
				: new Widget(['child' => 'This page is underconstruction.'])
		]);
	}
} // End of class Page

/**
 * Widget :: Web Api Widget
 *
 * @param array $args
 * @uses new PageApi([key => value,...])
 * @uses api/name
 * @uses api/name.method
 * @uses api/name..method
 */
class PageApi extends PageBase {
	public $widgetName = 'PageApi';
	public $version = '0.0.01';
	private $runInternalMethod = true;
	protected $actionDefault = null;
	protected $action;
	private $actionMethod;
	protected $args = [];

	function __construct($args = []) {
		$this->args = $args;
		parent::__construct($args);

		$this->action = empty($this->action) ? $this->actionDefault : $this->action;

		if (preg_match('/^api\./', $this->action ?? '')) {
			$this->runInternalMethod = false;
		}

		// Replace .a with .A
		$this->actionMethod = preg_replace_callback(
			'/\.(\w)/',
			function ($matches) {
				return strtoupper($matches[1]);
			},
			$this->action ?? ''
		);
	}

	function build() {
		if ($this->runInternalMethod) {
			return $this->runMethod();
		} else {
			return $this->runExternalMethod();
		}
	}

	// Test function return array in PageApi or text in other
	function foo() {
		return apiSuccess(
			'Foo' . (Request::all('msg') ? ' with ' . Request::all('msg') : '')
		);
	}

	private function runMethod() {
		if (!$this->canRunMethod()) {
			return apiError(_HTTP_ERROR_NOT_FOUND, 'Action not found!!!');
		}
		if (!method_exists($this, 'rightToBuild')) {
			return $this->{$this->actionMethod}();
		}

		$rightToBuild = $this->rightToBuild();
		if ($rightToBuild === true) {
			return $this->{$this->actionMethod}();
		} else {
			return apiError(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		}
	}

	private function runExternalMethod() {
		return R::pageWidget($this->action, (array) $this->args);
	}

	private function canRunMethod() {
		return method_exists($this, $this->actionMethod) &&
			($reflection = new ReflectionMethod($this, $this->actionMethod)) &&
			$reflection->isPublic();
	}
} // End of class PageApi

/**
 * Widget :: Web Page Controller Widget
 *
 * @param array $args
 * @uses new PageController([key => value,...])
 */
class PageController extends PageBase {
	public $widgetName = 'PageController';
	public $version = '0.0.01';
	public $action;
	public $argIndex = 2;
	public $args = [];
	public $info;

	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
		return R::pageWidget(
			$this->action,
			[-1 => $this->info] + array_slice($this->args, $this->argIndex)
		);
	}
} // End of class PageController
?>
