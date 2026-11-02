<?php
/**
 * Widget   :: Dropbox widget for create Dropbox
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2020-10-01
 * Modified :: 2026-11-02
 * Version  :: 8
 *
 * @param Array $args
 *
 * @uses new Dropbox([
 * 	'id' => String,
 * 	'class' => String,
 *  'position' => 'left' | 'right' | 'center',
 * 	'children' => [],
 * ])
 */

class Dropbox extends Widget {
	public $type = 'click';
	public $class = '';
	public $text = '';
	public $icon = NULL;
	public $iconText = 'more_vert';
	public $title = 'มีเมนูย่อย';
	public $url = NULL;
	public $link = NULL;
	public $position = 'left';
	public bool $hideOnNoChild;
	public $print = false;
	public $debug = false;
	public $footer = '';
	public $childrenContainer = ['tagName' => 'ul'];
	public array $childContainer = ['tagName' => 'li', 'class' => '-item'];

	function __construct(
		$args = [],
		?string $id = null,
		?string $class = null,
		?string $style = null,
		mixed $child = null,
		mixed $children = null,
		string $position = 'left',
		bool $hideOnNoChild = false,
		bool $debug = false
	) {
		parent::__construct(
			array_replace(
				[
					'id' => $id,
					'class' => $class,
					'style' => $style,
					'child' => $child,
					'children' => $children,
					'position' => $position,
					'hideOnNoChild' => $hideOnNoChild,
					'debug' => $debug
				],
				$args
			)
		);
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
				. ($this->text != '' ? '<span>' . $this->renderEachChildWidget($this->text) . '</span>' : '')
				. ($this->icon ? $this->renderEachChildWidget($this->icon) : '<i class="icon -' . ($this->icon ? $this->icon : 'material') . '">' . $this->iconText . '</i>')
				. '</a>'
		);

		$ret = _NL . '<!-- Start of widget-dropbox -->' . _NL
			. '<span class="widget-dropbox sg-dropbox ' . $this->type . ' ' . $this->class . '" data-type="' . $this->type . '"' . ($this->url ? ' data-url="' . $this->url . '"' : '') . '>' . _NL
			. $dropLink . _NL
			. '<div class="-wrapper -hidden">' . _NL
			. '<div class="-arrow"></div>' . _NL
			. '<div class="-content">' . _NL
			. $this->renderChildren($this->children())
			. '<div class="-footer">' . ($this->footer ? $this->renderEachChildWidget($this->footer) : '') . '</div>' . _NL
			. '</div>' . _NL
			. '</div>' . _NL
			. '</span><!-- End of widget-dropbox -->' . _NL;

		return $ret;
	}
}
?>