<?php
/**
 * Widget  :: Album
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2022-10-09
 * Modify  :: 2026-07-29
 * Version :: 5
 *
 * @param Array $args
 *
 * @uses new Album([
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
	protected function renderChildContainerStart($key, $args = [], $child = []) {
		if (is_object($child)) {
			$args['class'] = $child->class;
			$args['id'] = $child->id;
		} else {
			$args['class'] = $child['class'];
			$args['id'] = $child['id'];
		}
		return parent::renderChildContainerStart($key, $args, $child);
	}

	#[\Override]
	protected function renderEachChildWidget($widget, $key = NULL, $callbackFunction = [], $options = []) {
		if (is_object($widget)) return parent::renderEachChildWidget($widget, $key);

		$imageTag = '<img class="photoitem -photo" src="' . $widget['img'] . '" />';
		$ret = '';
		if ($widget['link']) {
			$widget['link']->text = $imageTag;
			$ret .= parent::renderEachChildWidget($widget['link']);
		} else {
			$ret .= $imageTag;
		}
		if ($widget['title']) $ret .= parent::renderEachChildWidget($widget['title']);
		if ($widget['detail']) $ret .= parent::renderEachChildWidget($widget['detail']);
		if ($widget['navigator']) {
			if (is_array($widget['navigator'])) {
				$ret .= (new Nav($widget['navigator']))->build();
			} else {
				$ret .= parent::renderEachChildWidget($widget['navigator']);
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