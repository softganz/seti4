<?php
/**
 * Widget  :: Dropbox widget for create Dropbox
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2020-10-01
 * Modify  :: 2026-05-13
 * Version :: 6
 *
 * @param Array $args
 * @return Widget
 *
 * @usage new Dropbox([
 * 	'id' => String,
 * 	'class' => String,
 *  'position' => 'left' | 'right' | 'center',
 * 	'children' => [],
 * ])
 */

class Dropbox extends Widget {
	var $type = 'click';
	var $class = '';
	var $text = '';
	var $icon = NULL;
	var $iconText = 'more_vert';
	var $title = 'มีเมนูย่อย';
	var $url = NULL;
	var $link = NULL;
	var $position = 'left';
	var $hideOnNoChild = false;
	var $print = false;
	var $debug = false;
	var $footer = '';
	var $childrenContainer = ['tagName' => 'ul'];
	var $childContainer = ['tagName' => 'li', 'class' => '-item'];

	function __construct($args = []) {
		parent::__construct($args);
	}

	function add($value = NULL) {
		if (isset($value)) $this->children[] = $value;
	}

	function build() {
		// If no child, do not show
		if ($this->hideOnNoChild) {
			$hasChild = false;
			foreach ($this->children as $child) {
				if (isset($child)) {
					$hasChild = true;
					break;
				}
			}

			if (!$hasChild) return;
		}

		if ($this->debug) debugMsg($this, '$this');

		if ($this->position === 'left') $this->class .= ' -leftside';
		else if ($this->position === 'center') $this->class .= ' -centerside';
		else $this->class .= ' -rightside';
		if (!$this->print) $this->class .= ' -no-print';

		$dropLink = SG\getFirst(
			$this->link,
			'<a href="javascript:void(0)" title="' . $this->title . '">'
				. ($this->text != '' ? '<span>' . $this->_renderEachChildWidget($this->text) . '</span>' : '')
				. ($this->icon ? $this->_renderEachChildWidget($this->icon) : '<i class="icon -' . ($this->icon ? $this->icon : 'material') . '">' . $this->iconText . '</i>')
				. '</a>'
		);

		$ret = _NL . '<!-- Start of widget-dropbox -->' . _NL
			. '<span class="widget-dropbox sg-dropbox ' . $this->type . ' ' . $this->class . '" data-type="' . $this->type . '"' . ($this->url ? ' data-url="' . $this->url . '"' : '') . '>' . _NL
			. $dropLink . _NL
			. '<div class="-wrapper -hidden">' . _NL
			. '<div class="-arrow"></div>' . _NL
			. '<div class="-content">' . _NL
			. $this->_renderChildren($this->children())
			. '<div class="-footer">' . ($this->footer ? $this->_renderEachChildWidget($this->footer) : '') . '</div>' . _NL
			. '</div>' . _NL
			. '</div>' . _NL
			. '</span><!-- End of widget-dropbox -->' . _NL;

		return $ret;
	}
}
?>