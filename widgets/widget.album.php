<?php
/**
* Widget  :: Album
* Created :: 2022-10-09
* Modify  :: 2022-10-09
* Version :: 1
*
* @param Array $args
* @return Widget
*
* @usage new Album([
* 	'img' => String,
* 	'link' => Widget,
* 	'title' => Widget,
* 	'navigator' => Widget,
* 	'children' => [],
* ])
*/

class Album extends Widget {
	var $widgetName = 'Album';
	var $forceBuild = false;
	var $tagName = 'ul';
	var $childContainer = ['tagName' => 'li', 'class' => '-item'];
	var $img;
	var $title;
	var $navigator;

	// Container for each child of children
	// @override
	function _renderChildContainerStart($childrenKey, $args = [], $childrenValue = []) {
		if (is_object($childrenValue)) {
			$args['class'] = $childrenValue->class;
			$args['id'] = $childrenValue->id;
		} else {
			$args['class'] = $childrenValue['class'];
			$args['id'] = $childrenValue['id'];
		}
		return parent::_renderChildContainerStart($childrenKey, $args, $childrenValue);
	}

	function _renderChildContainerStart1($childrenKey, $args = [], $childrenValue = []) {
		debugMsg($childrenValue,'$childrenValue');
		$childTagName = SG\getFirst($this->childTagName, $this->childContainer['tagName']);
		return $childTagName ? '<'.$childTagName
		. ' class="'.($this->childContainer['class'] ? $this->childContainer['class']: '')
		. ($this->itemClass ? ' '.$this->itemClass : '')
		. (!is_numeric($childrenKey) ? ' -'.$childrenKey : '')
		. ($args['class'] ? ' '.trim($args['class']) : '').'"'
		. '>'
		: '';
	}

	function _renderChildContainerStartX($stepIndex, $args = [], $childrenValue = []) {
		$stepIndex++;
		return '<'.$this->childContainer['tagName'].' '
			. 'class="ui-item -step-'.$stepIndex.($this->childContainer['class'] ? $this->childContainer['class'] : '').($stepIndex == $this->currentStep ? ' -current-step' : '').(isset($this->activeStep[$stepIndex]) && $this->activeStep[$stepIndex] ? ' -active' : '').'" '
			. '>';
	}

	function _renderEachChildWidget($key, $widget) {
		// debugMsg($widget, '$widget');
		if (is_object($widget)) return widget::_renderEachChildWidget($key, $widget);

		$imageTag = '<img class="photoitem" src="'.$widget['img'].'" />';
		$ret = '';
		if ($widget['link']) {
			$widget['link']->text = $imageTag;
			$ret .= Widget::_renderEachChildWidget(NULL, $widget['link']);
		} else {
			$ret .= $imageTag;
		}
		if ($widget['title']) $ret .= Widget::_renderEachChildWidget(NULL, $widget['title']);
		if ($widget['detail']) $ret .= Widget::_renderEachChildWidget(NULL, $widget['detail']);
		if ($widget['navigator']) {
			if (is_array($widget['navigator'])) {
				$ret .= (new Nav($widget['navigator']))->build();
			} else {
				$ret .= Widget::_renderEachChildWidget(NULL, $widget['navigator']);
			}
		}
		return $ret;
	}

	// function _renderChildren($childrens = [], $args = []) {
	// 	return parent::_renderChildren();
	// }

	function build() {
		if ($this->upload) {
			$this->children = array_replace_recursive(['upload' => &$this->upload], $this->children);
		}
		return parent::build();
	}
	// function _renderChildren($childrens = [], $args = []) {
	// 	$ret = '';
	// 	foreach ($this->children as $key => $value) {
	// 		if (is_array($value)) {
	// 			$child = (Object) $value;
	// 		} else if (is_object($value)) {
	// 			$child = $value;
	// 		} else {
	// 			$child = (Object) ['text' => $value, 'options' => NULL];
	// 		}

	// 		// Convert options to object
	// 		$options = is_string($child->options) ? SG\json_decode($child->options): (Object) $child->options;

	// 		$uiItemClass = $this->uiItemClass.($options->class ? ' '.$options->class : '');
	// 		if (in_array($child->text, array('-','<sep>'))) {
	// 			$uiItemClass .= ' -sep';
	// 			$child->text = '<hr size="1" />';
	// 		}
	// 		$options->class = $uiItemClass;

	// 		$uiItemTag = $this->wrapperType[$this->tagName];
	// 		$ret .= $uiItemTag ? '<'.$uiItemTag.' '.sg_implode_attr($options).'>' : '';
	// 		$ret .= $child->text;
	// 		$ret .= $uiItemTag ? '</'.$uiItemTag.'>' : '';
	// 		$ret .= _NL;
	// 	}
	// 	return $ret;
	// }
} // End of class Ui
?>