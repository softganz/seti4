<?php
/**
 * Widget  :: Album
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2022-10-09
 * Modify  :: 2026-04-26
 * Version :: 3
 *
 * @param Array $args
 * @return Widget
 *
 * @usage new Album([
 * 	'id' => String,
 * 	'class' => String,
 * 	'img' => String,
 * 	'link' => Widget,
 * 	'title' => Widget,
 * 	'itemClass' => String,
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

	#[\Override]
	protected function _renderEachChildWidget($widget, $key = NULL, $callbackFunction = [], $options = []) {
		if (is_object($widget)) return parent::_renderEachChildWidget($widget, $key);

		$imageTag = '<img class="photoitem -photo" src="' . $widget['img'] . '" />';
		$ret = '';
		if ($widget['link']) {
			$widget['link']->text = $imageTag;
			$ret .= parent::_renderEachChildWidget($widget['link']);
		} else {
			$ret .= $imageTag;
		}
		if ($widget['title']) $ret .= parent::_renderEachChildWidget($widget['title']);
		if ($widget['detail']) $ret .= parent::_renderEachChildWidget($widget['detail']);
		if ($widget['navigator']) {
			if (is_array($widget['navigator'])) {
				$ret .= (new Nav($widget['navigator']))->build();
			} else {
				$ret .= parent::_renderEachChildWidget($widget['navigator']);
			}
		}
		return $ret;
	}

	function build() {
		if ($this->upload) {
			$this->children = array_replace_recursive(['upload' => &$this->upload], $this->children);
		}
		return parent::build();
	}
} // End of class Album
?>