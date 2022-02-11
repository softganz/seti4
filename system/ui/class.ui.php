<?php
/********************************************
* Class :: Ui
* Ui class for create ui
*
* Created 2020-08-01
* Modify  2020-08-01
*
* Property
* config {nav: "nav -icons"}
*
* @usage module/{$Id}/method
********************************************/

class Ui extends Widget {
	var $widgetName = 'Ui';
	var $forceBuild = false;
	var $seperator = ' · ';
	var $tagName = 'ul';
	var $uiItemClass = 'ui-item -item';
	var $wrapperType = array('ul' => 'li','span' => 'span','div' => 'div', 'div a'=>'a', 'ol'=>'li');
	var $type = 'action';

	function __construct($join = NULL, $class = NULL) {
		if (is_object($join) || is_array($join)) {
			$parameter = is_array($join) ? (Object) $join : $join;
			if (isset($parameter->id)) $this->id = $parameter->id;
			// if (isset($parameter->class)) $this->class = $parameter->class;
			if (isset($parameter->tagName)) $this->tagName = $parameter->tagName;
			if (isset($parameter->class)) $this->class = (substr($parameter->class,0,1) == '-' ? $this->class.' ' : '').$parameter->class;
			if (isset($parameter->config)) {
				foreach ($parameter->config as $key => $value) {
					if (preg_match('/([\w]+)\-([\w\-]+)/', $key, $out)) {
						if ($out[1] == 'data') $this->config->data[$key] = $value;
					} else {
						$this->config->{$key} = $value;
					}
				}
			}
			parent::__construct($parameter);
		} else {
			$this->initConfig();
			if ($join) $this->tagName = $join;
			if ($class) $this->class = (substr($class,0,1) == '-' ? $this->class.' ' : '').$class;
		}
		if ($this->debug) debugMsg($this,'Ui()');
	}

	function Add($link, $options = '{}') {
		if (is_array($link)) {
			foreach ($link as $key => $value) {
				$options = $value[1];
				$this->children[$key] = (Object) [
					'text' => $value[0],
					'options' => is_string($options) ? SG\json_decode($options): (object) $options
				];
			}
		} else if ($link) {
			$this->children[] = (Object) [
				'text' => $link,
				'options' => is_string($options) ? SG\json_decode($options): (Object) $options
			];
		}
		return $this;
	}

	function Clear() {$this->children = []; return $this;}

	function Count() {return count((Array) $this->children);}

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

		$uiType = ['action' => 'ui-action', 'card' => 'ui-card', 'menu' => 'ui-menu', 'album' => 'ui-album', 'nav' => 'ui-nav'];

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

	function show($forceBuild = NULL) {
		if (isset($forceBuild)) $this->forceBuild = $forceBuild;

		return $this->build($forceBuild);
	}
} // End of class Ui
?>