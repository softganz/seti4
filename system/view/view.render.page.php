<?php
/**
 * Theme module display
 *
 * @param Object $self
 * @param String $body
 * @return String
 */

$debug = false;

class ViewRenderPage extends Widget {
	var $self;
	var $body;

	function __construct($self, $body = NULL) {
		$this->self = $self;
		$this->body = $body;
	}

	function build() {
		$self = $this->self;
		$body = $this->body;

		$ret = '';

		//if (i()->username == 'softganz') debugMsg('_AJAX='.(_AJAX ? 'yes':'no'));
		if ($GLOBALS['gadget']) {
			return $self->theme->body.$body;
		} else if (cfg('Content-Type') == 'text/xml') {
			//if (_HOST=='nadrec.softganz.com' || (_HOST=='softganz.com' && i()->username='softganz')) echo '<h1>__theme return 2</h1>';
			return $self->theme->body.$body;
		} else if (!_AJAX && is_array($body) && isset($body['location'])) {
			//if (_HOST=='nadrec.softganz.com' || (_HOST=='softganz.com' && i()->username='softganz')) echo '<h1>__theme return 3</h1>';
			call_user_func_array('location',$body['location']);
		} else if (_AJAX) {
			// debugMsg('RENDER AJAX REQUEST');
			//if (_HOST=='nadrec.softganz.com' || (_HOST=='softganz.com' && i()->username='softganz')) echo '<h1>__theme return 4</h1>';
			if (is_array($self->theme->body) || is_object($self->theme->body)) {
				$ret=json_encode($self->theme->body);
			} else if (is_array($body) || is_object($body)) {
				//echo 'Return json_encode '.print_o($body,'$body');
				$ret = json_encode($body);
			} else {
				$ret = $self->theme->body.$body;
			}
			if (is_object($ret) && method_exists($ret, 'build')) {
				return $ret->build();
			} else {
				// debugMsg('RENDER AJAX BUILD');
				return $ret;
			}
		} else if (_HTML && is_array($body)) {
			//if (_HOST=='nadrec.softganz.com' || (_HOST=='softganz.com' && i()->username='softganz')) echo '<h1>__theme return 5</h1>';
			$ret = print_o($body);
			return $ret;
		} else if (_HTML) {
			//if (_HOST=='nadrec.softganz.com' || (_HOST=='softganz.com' && i()->username='softganz')) echo '<h1>__theme return 6</h1>';
			return $body;
		}
		//if (_HOST=='nadrec.softganz.com' || (_HOST=='softganz.com' && i()->username='softganz')) echo '<h1>Show body</h1>';


		// Get Scaffold SideBar from property sideBar
		$sideBar = '';
		if (is_object($self->sideBar) && method_exists($self->sideBar, 'build')) {
			$sideBar = $self->sideBar->build();
		} else if (is_string($self->sideBar)) {
			$sideBar = $self->sideBar;
		} else if ($self->theme->sidebar) {
			$sideBar = $self->theme->sidebar;
		}

		// debugMsg($self,'$self');

		if (isset($self->menu)) $GLOBALS['module_menu'] = $self->menu;
		$id = isset($self->theme->id) ? $self->theme->id : 'content-'.$self->module;
		$class = 'page -main'.(isset($self->theme->class)?' '.$self->theme->class:'');
		$option = $self->theme->option;

		if (isset($self->theme->title) && $option->title) title(strip_tags($self->theme->title));
		//if ($option->container) $ret .= _NL.'<div id="'.$id.'" class="'.$class.'">'._NL;
		//$ret.='<div class="overlay">'._NL;

		if ($option->header) {
			if (isset($self->theme->pretext)) $ret.=$self->theme->pretext;
			if (isset($self->theme->header->text)) $ret .= '<h2 class="header">'.$self->theme->header->text.'</h2>'._NL;
			if (isset($self->theme->header->posttext)) $ret .= $self->theme->header->posttext._NL;
			if (isset($GLOBALS['ad']->header)) $ret.='<div id="ad-header" class="ads">'.$GLOBALS['ad']->header.'</div>';
			if (isset($self->theme->header->description)) $ret .= '<div class="header-description">'.$self->theme->header->description.'</div>'._NL;
		}

		// debugMsg(R::option('notoolbar') ? 'No Toolbar is true' : 'No Toolbar is false');
		// debugMsg($option, '$option');
		// debugMsg($self, '$self');

		if (is_object($self->appBar) && method_exists($self->appBar, 'build')) {
			// debugMsg($self->appBar, '$appBar');
		}

		// if (is_object($self->appBar) && method_exists($self->appBar, 'build')) {
		// 	if ($self->appBar->removeOnApp && is_object(R()->appAgent)) {
		// 		// don't show appBar
		// 	} else {
		// 		$self->appBarText = $self->appBar->build();
		// 	}
		// } else {
		// 	$self->theme->title = $ret->appBar->title;
		// }

		// if (is_object($self->appBar) && method_exists($self->appBar, 'build')) {
		// 	if ($self->appBar->removeOnApp && is_object(R()->appAgent)) {
		// 		// don't show appBar
		// 	} else {
		// 		$self->appBarText = $self->appBar->build();
		// 	}
		// 	// if ($ret->appBar->sideBar) $exeClass->theme->sidebar = $ret->appBar->sideBar;
		// } else if (is_object($self->appBar->title)) {
		// 	$self->theme->toolbar = $self->appBar->title;
		// 	$self->theme->title = $self->appBar->title;
		// } else {
		// 	$self->theme->title = $self->appBar->title;
		// }

		if ($self->appBarText) {
			$ret .= $self->appBarText;
			page_class('-module-has-toolbar');
		} else if (!R::option('notoolbar') && $option->title && isset($self->theme->title)) {
			// debugMsg('CREATE TOOLBAR');
			if (is_object($self->theme->toolbar)) {
				// debugMsg('CREATE TOOLBAR OBJECT');
				// debugMsg($self->theme->toolbar->toString(),'$AAAAA');
				$ret .= $self->theme->toolbar->toString();
				page_class('-module-has-toolbar');
			} else if (is_string($self->theme->toolbar)) {
				if (i()->username == 'softganz') {
					//$ret .= $self->theme->toolbar;
					//debugMsg('$self->theme->toolbar = '.$self->theme->toolbar);
				}
				$ret .= '<!-- Module Toolbar Start -->'._NL
					. '<div '
					. 'id="'.$self->module.'-toolbar" '
					. 'class="widget-appbar sg-toolbar toolbar -main -'.$self->module
						. (isset($self->theme->submodule) ? ' -'.$self->theme->submodule : '').'"'
					. '>'._NL;
				if ($self->theme->moduleNav) {
					$ret .= '<nav class="nav -module -'.$self->module.'">'.$self->theme->moduleNav.'</nav>'._NL;
				}
				$ret .= '<h2 class="-title">'.$self->theme->title.'</h2>'._NL;
				if (isset($self->theme->subtitle)) $ret .= '<h5>'.$self->theme->subtitle.'</h5>'._NL;
				$ret .= $self->theme->toolbar._NL;
				$ret .= '</div><!--Module Toolbar End -->'._NL;
				page_class('-module-has-toolbar');
			} else {
				$ret .= '<h2 class="title">'.$self->theme->title.'</h2>'._NL;
				if (isset($self->theme->subtitle)) $ret .= '<h5 class="subtitle">'.$self->theme->subtitle.'</h5>'._NL;
			}
		}

		if ($option->ribbon && isset($self->theme->navigator)) {
			$ret .= '<div id="ribbon" class="ribbon navigator'.(isset($self->theme->ribbon->class)?' '.$self->theme->ribbon->class:'').'">'.$self->theme->navigator.'</div>'._NL;
			if ($option->toolbar) $ret .= '<div id="ribbon-toolbar"></div>'._NL;
		}

		if (!empty($sideBar)) {
			page_class('-module-has-sidebar');
			$ret .= '<div id="sidebar" class="page -sidebar">'._NL;
			$ret .= $sideBar._NL;
			$ret .= '</div><!--sidebar-->'._NL;
		}

		$container_id=is_string($option->container)?$option->container:'main';
		//$self->theme->container->{'data-refresh'}='aaaa';
		$container_attr=sg_implode_attr($self->theme->container);

		$showContainer = $option->container && !R::option('fullpage');
		if ($showContainer) $ret .= _NL.'<div id="'.$container_id.'" class="'.$class.'" '.$container_attr.'>';
		if (!empty($self->theme->navbar)) {
			$ret.='<div class="navbar -main">'._NL;
			$ret.=$self->theme->navbar._NL;
			$ret.='</div><!--navbar-->'._NL;
		}
		if (isset($self->theme->body)) $ret .= _NL.$self->theme->body._NL;
		if (is_array($body) && isset($body['html'])) $ret .= _NL.$body['html']._NL;
		else if (isset($body)) $ret .= _NL.$body._NL;
		if ($showContainer) $ret .= '</div><!--'.$container_id.'-->'._NL;

		if (is_object($self->floatingActionButton) && method_exists($self->floatingActionButton, 'build')) {
			$ret .= $self->floatingActionButton->build();
			// debugMsg($self->floatingActionButton, '$floatingActionButton');
		}

		$release_date=cfg($self->module.'.release');
		if ($option->package) $ret .= '<div class="package-footer -no-print">'.$self->module.' version '.$self->version.($release_date?' release '.$release_date:'').'. <a class="sg-action" href="'.url($self->module.'/help').'" data-rel="box" data-width="640">'.tr('Help').'</a></div>'._NL._NL;
		//if ($option->container) $ret .= '</div><!--'.$id.'-->'._NL._NL;
		//if (i()->username == 'softganz') debugMsg(htmlview($ret));

		return $ret;
	}
}
?>