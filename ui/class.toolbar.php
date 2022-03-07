<?php
/********************************************
* Class :: Toolbar
* Toolbar class for create toolbar
*
* Created 2020-10-01
* Modify  2020-10-01
*
* Property
* config {nav: "nav -icons"}
*
* @usage new Toolbar()
********************************************/

class Toolbar extends Widget {
	var $widgetName = 'Toolbar';
	var $self = NULL;
	var $title = '';
	var $navGroup = Array();
	var $nav = '';
	var $info = NULL;

	function __construct($self, $title = NULL, $nav = NULL, $info = NULL, $class = NULL, $options = '{}') {
		if (is_array($self)) {
			parent::__construct($self);
			$this->self = $self->context;
			$this->config->class = 'sg-toolbar '.($self['class'] ? $self['class'] : '-main');
			if ($self['navigator']) {
				foreach ($self['navigator'] as $key => $value) $this->addNav($key, $value);
			}
		} else {
			$this->self = $self;
			$this->title = $title;
			$this->config->class = 'sg-toolbar '.($class ? $class : '-main');
			$this->info = (is_string($info) AND substr($info,0,1) == '{') ? SG\json_decode($info) : $info;

			if (is_string($nav)) {
				$this->nav = $nav;
				$this->navGroup = R::View($self->module.'.'.$nav.'.nav', $info, $options);
				$this->config->class .= ' -'.$self->module.'-'.str_replace('.', '-', $nav);
			}
			$this->options = SG\json_decode($options);

			$this->self->theme->title = $title;
			$self->theme->toolbar = $this;
		}
		// return $this;
	}

	function addNav($key, $nav) {
		$this->navGroup[$key] = $nav;
	}

	function toString() {
		$ret .= '<!-- Module Toolbar Start -->'._NL
			. '<div '
			. 'id="'.$this->self->module.'-toolbar" '
			. 'class="'.$this->config->class
			. (isset($this->self->theme->submodule) ? ' -'.$this->self->theme->submodule : '')
			. '"' // end of class
			. '>'._NL;
		if ($this->title) $ret .= '<h2>'.$this->title.'</h2>'._NL;
		if ($this->subTitle) $ret .= '<h5>'.$this->subTitle.'</h5>'._NL;

		if ($this->navGroup) {
			$navStr = '';
			$isMoreNav = false;
			foreach ($this->navGroup as $navKey => $navUi) {
				if (strtoupper($navKey) == 'PRETEXT' && is_string($navUi)) {
					$navStr = $navUi.$navUi;
				} else if (is_string($navUi)) {
					// String Ui
					$navStr .= $navUi;
				} else if (is_object($navUi)) {
					// Widget
					if (strtoupper($navKey) == 'MORE' && $navUi->count()) {
						$moreNav = new Ui(NULL, 'ui-nav -more');
						$moreNav->add(sg_dropbox($navUi->build()));
						//$navStr .= $moreNav->build()._NL;
						$navStr .= '<span class="ui-nav -more">'.sg_dropbox($navUi->build()).'</span>';
						$isMoreNav = true;
					} else {
						$navStr .= $navUi->build()._NL;
					}
				}
			}
			$ret .= '<nav class="nav -submodule'.($this->nav ? ' -'.str_replace('.', '-', $this->nav) : '').($isMoreNav ? ' -is-more-nav' : '').'"><!-- nav of '.$this->nav.'.nav -->'._NL
				. $navStr
				. '</nav><!-- nav -->'._NL;
		}
		$ret .= '</div><!-- Module Toolbar End :: sg-toolbar -->'._NL._NL;
		return $ret;
	}

	function build() {
		$this->self->theme->title = $this->title;
		$this->self->theme->toolbar = $this;
		page_class('-module-has-toolbar');
		//debugMsg($this, '$thisToolbar');
		return $this->toString();
	}
} // End of class Toolbar
?>