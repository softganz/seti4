<?php
/********************************************
* Class :: Dropbox
* Dropbox class for create Dropbox
*
* Created 2020-10-01
* Modify  2020-10-01
*
* Property
* config {nav: "nav -icons"}
*
* @usage new Dropbox()
********************************************/

class Dropbox extends Widget {
	var $type = 'click';
	var $class = '';
	var $text = '';
	var $icon = null;
	var $iconText = 'more_vert';
	var $title = 'มีเมนูย่อย';
	var $url = NULL;
	var $position = 'left';
	var $print = false;
	var $childrenContainer = ['tagName' => 'ul'];
	var $childContainer = ['tagName' => 'li', 'class' => '-item'];

	function __construct($args = []) {
		parent::__construct($args);
	}

	function option($key, $value = NULL) {
		$this->options->{$key} = $value;
	}

	function add($str, $option = '{}') {
		$this->children[] = $str;//, $option);
	}

	function show($text = NULL, $options = '{}') {
		return $this->build($text, $options);
	}

	function build() {
		if ($this->debug) debugMsg($this, '$this');

		if (empty($this->children)) return '';

		$text = $this->_renderChildren();

		// $options = \SG\json_decode($options,isset($this) ? $this->options : NULL, Dropbox::$defaultOption);
		if ($this->position == 'left') $this->class .= ' leftside';
		else if ($this->position == 'center') $this->class .= ' -center';
		else $this->class .= ' rightside';
		if (!$this->print) $this->class .= ' -no-print';

		$defaultLink = '<a href="javascript:void(0)" title="'.$this->title.'">'
			. ($this->text != '' ? '<span>'.$this->_renderEachChildWidget(NULL, $this->text).'</span>' : '')
			. ($this->icon ? $this->_renderEachChildWidget(NULL, $this->icon) : '<i class="icon -'.($this->icon ? $this->icon : 'material').'">'.($this->iconText).'</i>')
			. '</a>';

		$dropLink = \SG\getFirst($this->link, $defaultLink);

		$ret = _NL.'<!-- Start of widget-dropbox -->'._NL
			. '<span class="widget-dropbox sg-dropbox '.$this->type.' '.$this->class.'" data-type="'.$this->type.'"'.($this->url ? ' data-url="'.$this->url.'"' : '').'>'._NL
			. $dropLink._NL
			. '<div class="sg-dropbox--wrapper -wrapper -hidden">'._NL
			. '<div class="sg-dropbox--arrow -arrow"></div>'._NL
			. '<div class="sg-dropbox--content -content">'._NL.$text.'</div>'._NL
			. '</div>'._NL
			. '</span><!-- End of widget-dropbox -->'._NL;

		return $ret;
	}
} // End of class Dropbox
?>