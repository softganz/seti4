<?php
/**
* Widget  :: Page Render Widget
* Created :: 2023-01-01
* Modify  :: 2025-10-31
* Version :: 7
*
* @param Object $pageClass
* @param String $requestResult
* @return String
*
* @usage new PageRenderWidget($pageClass, $requestResult)
*/

class renderPageWidget extends Widget {
	var $pageClass;
	var $requestResult;

	function __construct($requestResult, $pageClass) {
		$this->pageClass = $pageClass;
		$this->requestResult = $requestResult;
	}

	function build() {
		if (is_object($this->requestResult) && method_exists($this->requestResult, 'build'))  {
			return $this->buildObject();
		}

		if (is_object($this->requestResult) || is_array($this->requestResult)) {
			return (new DebugMsg($this->requestResult, '$result'))->build();
		}

		return $this->renderOldPage();
	}

	private function buildObject() {
		$ret = $this->renderAppBar()
			. $this->renderToolbar()
			. $this->renderRibbon()
			. $this->renderFloatingActionButton()
			. $this->renderSideBar()
			. '<div id="main" class="page -main">'
			. $this->requestResult->build()
			. '</div>';

			return $ret;
	}

	private function renderAppBar() {
		if (!isset($this->pageClass->appBarText)) return;
		
		page_class('-module-has-toolbar');
		
		return $this->pageClass->appBarText;
	}

	private function renderSideBar() {
		// Get Scaffold SideBar from property sideBar
		$sideBar = SG\getFirst(
			$this->requestResult->sideBar,
			$this->pageClass->sideBar,
			$this->pageClass->theme->sidebar
		);

		if (!isset($sideBar)) return;

		$sideBarWidget = false;

		if (is_object($sideBar) && method_exists($sideBar, 'build')) {
			do {
				$sideBarWidget = get_class($sideBar) === 'SideBar';
				$sideBar = $sideBar->build();
			} while (is_object($sideBar) && method_exists($sideBar, 'build'));
		} else if (is_string($sideBar)) {
			// do nothing
		} else {
			$sideBar = NULL;
		}

		if (!empty($sideBar)) {
			page_class('-module-has-sidebar');
			if ($sideBarWidget) {
				$ret .= $sideBar;
			} else {
				$ret .= '<div id="sidebar" class="page -sidebar">'._NL;
				$ret .= $sideBar._NL;
				$ret .= '</div><!--sidebar-->'._NL;
			}
		}
		return $ret;
	}

	private function renderFloatingActionButton() {
		$ret = '';
		
		if (is_object($this->pageClass->floatingActionButton) && method_exists($this->pageClass->floatingActionButton, 'build')) {
			$ret = $this->pageClass->floatingActionButton->build();
		}

		return $ret;
	}

	private function renderToolbar() {
		if (!(!R::option('notoolbar') && $this->pageClass->theme->option->title && isset($this->pageClass->theme->title))) {
			return;
		}

		if (is_object($this->pageClass->theme->toolbar)) {
			page_class('-module-has-toolbar');
			return $this->pageClass->theme->toolbar->toString();
		}
		
		if (is_string($this->pageClass->theme->toolbar)) {
			page_class('-module-has-toolbar');
			return (new AppBar([
				'id' => $this->pageClass->module.'-toolbar',
				'class' => 'sg-toolbar toolbar -main -'.$this->pageClass->module
					. (isset($this->pageClass->theme->submodule) ? ' -'.$this->pageClass->theme->submodule : '')
					. ($this->pageClass->theme->appBarClass ? ' '.$this->pageClass->theme->appBarClass : ''),
				'title' => $this->pageClass->theme->title,
				'subTitle' => isset($this->pageClass->theme->subtitle) ? $this->pageClass->theme->subtitle : NULL,
				'leading' => $this->pageClass->theme->leading,
				'trailing' => $this->pageClass->theme->trailing,
				'navigator' => $this->pageClass->theme->moduleNav ? $this->pageClass->theme->moduleNav : $this->pageClass->theme->toolbar,
			]))->build();

			// $ret .= '<!-- Module Toolbar Start -->'._NL
			// 	. '<div '
			// 	. 'id="'.$this->pageClass->module.'-toolbar" '
			// 	. 'class="widget-appbar sg-toolbar toolbar -main -'.$this->pageClass->module
			// 	. (isset($this->pageClass->theme->submodule) ? ' -'.$this->pageClass->theme->submodule : '')
			// 	. ($this->pageClass->theme->appBarClass ? ' '.$this->pageClass->theme->appBarClass : '')
			// 	. '"'
			// 	. '>'._NL;
			// if ($this->pageClass->theme->moduleNav) {
			// 	$ret .= '<nav class="nav -module -'.$this->pageClass->module.'">'.$this->pageClass->theme->moduleNav.'</nav>'._NL;
			// }
			// $ret .= '<h2 class="-title">'.$this->pageClass->theme->title.'</h2>'._NL;
			// if (isset($this->pageClass->theme->subtitle)) $ret .= '<h5>'.$this->pageClass->theme->subtitle.'</h5>'._NL;
			// $ret .= $this->pageClass->theme->toolbar._NL;
			// $ret .= '</div><!--Module Toolbar End -->'._NL;

			// return $ret;
		}

		return (new AppBar([
			'title' => $this->pageClass->theme->title,
			'subTitle' => $this->pageClass->theme->subtitle
		]))->build();
	}

	private function renderRibbon() {
		if ($this->pageClass->theme->option->ribbon && isset($this->pageClass->theme->navigator)) {
			$ret .= '<div id="ribbon" class="ribbon navigator'.(isset($this->pageClass->theme->ribbon->class)?' '.$this->pageClass->theme->ribbon->class:'').'">'.$this->pageClass->theme->navigator.'</div>'._NL;
			if ($this->pageClass->theme->option->toolbar) $ret .= '<div id="ribbon-toolbar"></div>'._NL;
		}

		return $ret;
	}

	private function renderOldPage() {
		if ($GLOBALS['gadget']) {
			return $this->pageClass->theme->requestResult.$this->requestResult;
		} else if (cfg('Content-Type') == 'text/xml') {
			return $this->pageClass->theme->requestResult.$this->requestResult;
		} else if (!_AJAX && is_array($this->requestResult) && isset($this->requestResult['location'])) {
			call_user_func_array('location',$this->requestResult['location']);
		} else if (_AJAX) {
			// debugMsg('RENDER AJAX REQUEST');
			if (is_array($this->pageClass->theme->requestResult) || is_object($this->pageClass->theme->requestResult)) {
				$ret=json_encode($this->pageClass->theme->requestResult);
			} else if (is_array($this->requestResult) || is_object($this->requestResult)) {
				//echo 'Return json_encode '.print_o($this->requestResult,'$this->requestResult');
				$ret = json_encode($this->requestResult);
			} else {
				$ret = $this->pageClass->theme->requestResult.$this->requestResult;
			}
			if (is_object($ret) && method_exists($ret, 'build')) {
				return $ret->build();
			} else {
				return $ret;
			}
		} else if (_HTML && is_array($this->requestResult)) {
			$ret = print_o($this->requestResult);
			return $ret;
		} else if (_HTML) {
			return $this->requestResult;
		}

		if (isset($this->pageClass->menu)) $GLOBALS['module_menu'] = $this->pageClass->menu;
		$id = isset($this->pageClass->theme->id) ? $this->pageClass->theme->id : 'content-'.$this->pageClass->module;
		$class = 'page -main'.(isset($this->pageClass->theme->class)?' '.$this->pageClass->theme->class:'');
		$option = $this->pageClass->theme->option;

		if (isset($this->pageClass->theme->title) && $option->title) title(strip_tags($this->pageClass->theme->title));
		//if ($option->container) $ret .= _NL.'<div id="'.$id.'" class="'.$class.'">'._NL;
		//$ret.='<div class="overlay">'._NL;

		if ($option->header) {
			if (isset($this->pageClass->theme->pretext)) $ret.=$this->pageClass->theme->pretext;
			if (isset($this->pageClass->theme->header->text)) $ret .= '<h2 class="header">'.$this->pageClass->theme->header->text.'</h2>'._NL;
			if (isset($this->pageClass->theme->header->posttext)) $ret .= $this->pageClass->theme->header->posttext._NL;
			if (isset($GLOBALS['ad']->header)) $ret.='<div id="ad-header" class="ads">'.$GLOBALS['ad']->header.'</div>';
			if (isset($this->pageClass->theme->header->description)) $ret .= '<div class="header-description">'.$this->pageClass->theme->header->description.'</div>'._NL;
		}

		// debugMsg(R::option('notoolbar') ? 'No Toolbar is true' : 'No Toolbar is false');
		// debugMsg($option, '$option');
		// debugMsg($this->pageClass, '$this->pageClass');

		// if (is_object($this->pageClass->appBar) && method_exists($this->pageClass->appBar, 'build')) {
		// 	if ($this->pageClass->appBar->removeOnApp && is_object(R()->appAgent)) {
		// 		// don't show appBar
		// 	} else {
		// 		$this->pageClass->appBarText = $this->pageClass->appBar->build();
		// 	}
		// } else {
		// 	$this->pageClass->theme->title = $ret->appBar->title;
		// }

		// if (is_object($this->pageClass->appBar) && method_exists($this->pageClass->appBar, 'build')) {
		// 	if ($this->pageClass->appBar->removeOnApp && is_object(R()->appAgent)) {
		// 		// don't show appBar
		// 	} else {
		// 		$this->pageClass->appBarText = $this->pageClass->appBar->build();
		// 	}
		// 	// if ($ret->appBar->sideBar) $exeClass->theme->sidebar = $ret->appBar->sideBar;
		// } else if (is_object($this->pageClass->appBar->title)) {
		// 	$this->pageClass->theme->toolbar = $this->pageClass->appBar->title;
		// 	$this->pageClass->theme->title = $this->pageClass->appBar->title;
		// } else {
		// 	$this->pageClass->theme->title = $this->pageClass->appBar->title;
		// }

		$ret .= $this->renderToolbar();

		// if ($this->pageClass->appBarText) {
		// 	$ret .= $this->pageClass->appBarText;
		// 	page_class('-module-has-toolbar');
		// } else if (!R::option('notoolbar') && $option->title && isset($this->pageClass->theme->title)) {
		// 	// debugMsg('CREATE TOOLBAR');
		// 	if (is_object($this->pageClass->theme->toolbar)) {
		// 		// debugMsg('CREATE TOOLBAR OBJECT');
		// 		// debugMsg($this->pageClass->theme->toolbar->toString(),'$AAAAA');
		// 		$ret .= $this->pageClass->theme->toolbar->toString();
		// 		page_class('-module-has-toolbar');
		// 	} else if (is_string($this->pageClass->theme->toolbar)) {
		// 		if (i()->username == 'softganz') {
		// 			//$ret .= $this->pageClass->theme->toolbar;
		// 			//debugMsg('$this->pageClass->theme->toolbar = '.$this->pageClass->theme->toolbar);
		// 		}
		// 		$ret .= '<!-- Module Toolbar Start -->'._NL
		// 			. '<div '
		// 			. 'id="'.$this->pageClass->module.'-toolbar" '
		// 			. 'class="widget-appbar sg-toolbar toolbar -main -'.$this->pageClass->module
		// 			. (isset($this->pageClass->theme->submodule) ? ' -'.$this->pageClass->theme->submodule : '')
		// 			. ($this->pageClass->theme->appBarClass ? ' '.$this->pageClass->theme->appBarClass : '')
		// 			. '"'
		// 			. '>'._NL;
		// 		if ($this->pageClass->theme->moduleNav) {
		// 			$ret .= '<nav class="nav -module -'.$this->pageClass->module.'">'.$this->pageClass->theme->moduleNav.'</nav>'._NL;
		// 		}
		// 		$ret .= '<h2 class="-title">'.$this->pageClass->theme->title.'</h2>'._NL;
		// 		if (isset($this->pageClass->theme->subtitle)) $ret .= '<h5>'.$this->pageClass->theme->subtitle.'</h5>'._NL;
		// 		$ret .= $this->pageClass->theme->toolbar._NL;
		// 		$ret .= '</div><!--Module Toolbar End -->'._NL;
		// 		page_class('-module-has-toolbar');
		// 	} else {
		// 		$ret .= '<h2 class="title">'.$this->pageClass->theme->title.'</h2>'._NL;
		// 		if (isset($this->pageClass->theme->subtitle)) $ret .= '<h5 class="subtitle">'.$this->pageClass->theme->subtitle.'</h5>'._NL;
		// 	}
		// }

		$ret .= $this->renderRibbon();

		// if ($option->ribbon && isset($this->pageClass->theme->navigator)) {
		// 	$ret .= '<div id="ribbon" class="ribbon navigator'.(isset($this->pageClass->theme->ribbon->class)?' '.$this->pageClass->theme->ribbon->class:'').'">'.$this->pageClass->theme->navigator.'</div>'._NL;
		// 	if ($option->toolbar) $ret .= '<div id="ribbon-toolbar"></div>'._NL;
		// }

		// // Get Scaffold SideBar from property sideBar
		// $sideBar = SG\getFirst($this->pageClass->sideBar, $this->pageClass->theme->sidebar);
		// $sideBarWidget = false;

		// if (is_object($sideBar) && method_exists($sideBar, 'build')) {
		// 	do {
		// 		$sideBarWidget = get_class($sideBar) === 'SideBar';
		// 		$sideBar = $sideBar->build();
		// 	} while (is_object($sideBar) && method_exists($sideBar, 'build'));
		// } else if (is_string($sideBar)) {
		// 	// do nothing
		// } else {
		// 	$sideBar = NULL;
		// }

		// if (!empty($sideBar)) {
		// 	page_class('-module-has-sidebar');
		// 	if ($sideBarWidget) {
		// 		$ret .= $sideBar;
		// 	} else {
		// 		$ret .= '<div id="sidebar" class="page -sidebar">'._NL;
		// 		$ret .= $sideBar._NL;
		// 		$ret .= '</div><!--sidebar-->'._NL;
		// 	}
		// }

		$container_id=is_string($option->container)?$option->container:'main';
		//$this->pageClass->theme->container->{'data-refresh'}='aaaa';
		$container_attr=sg_implode_attr($this->pageClass->theme->container);

		$showContainer = $option->container && !R::option('fullpage');
		if ($showContainer) $ret .= _NL.'<div id="'.$container_id.'" class="'.$class.'" '.$container_attr.'>';
		if (!empty($this->pageClass->theme->navbar)) {
			$ret.='<div class="navbar -main">'._NL;
			$ret.=$this->pageClass->theme->navbar._NL;
			$ret.='</div><!--navbar-->'._NL;
		}
		if (isset($this->pageClass->theme->requestResult)) $ret .= _NL.$this->pageClass->theme->requestResult._NL;
		if (is_array($this->requestResult) && isset($this->requestResult['html'])) $ret .= _NL.$this->requestResult['html']._NL;
		else if (isset($this->requestResult)) $ret .= _NL.$this->requestResult._NL;
		if ($showContainer) $ret .= '</div><!--'.$container_id.'-->'._NL;

		if (is_object($this->pageClass->floatingActionButton) && method_exists($this->pageClass->floatingActionButton, 'build')) {
			$ret .= $this->pageClass->floatingActionButton->build();
			// debugMsg($this->pageClass->floatingActionButton, '$floatingActionButton');
		}

		$release_date=cfg($this->pageClass->module.'.release');
		if ($option->package) $ret .= '<div class="package-footer -no-print">'.$this->pageClass->module.' version '.$this->pageClass->version.($release_date?' release '.$release_date:'').'. <a class="sg-action" href="'.url($this->pageClass->module.'/help').'" data-rel="box" data-width="640">'.tr('Help').'</a></div>'._NL._NL;
		//if ($option->container) $ret .= '</div><!--'.$id.'-->'._NL._NL;
		//if (i()->username == 'softganz') debugMsg(htmlview($ret));

		return $ret;
	}
}
?>