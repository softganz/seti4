<?php
/**
 * Docs    :: Module Docs Widget
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2026-04-24
 * Modify  :: 2026-04-24
 * Version :: 1
 *
 * @param Array $args
 * @return Widget
 *
 * @usage Class ModuleDocs extends ModuleDocsWidget
 */

import('package:external/parsedown/Parsedown.php'); // https://github.com/parsedown/parsedown

class ModuleDocsWidget extends WidgetBase {
	var $widgetName = 'Widget';
	var $version = 1;
	var $title = 'Module Documentation.';
	var $module = 'imed';
	var $sideBarPage = 'toc';
	var $startPage = 'overview';
	var $folder;
	var $docPath;

	function __construct($args = []) {
		parent::__construct($args);
		if ($this->widgetName === 'Widget') $this->widgetName = get_class($this);
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->title,
			]),
			'sideBar' => $this->load($this->sideBarPage),
			'body' => new Widget([
				'children' => [
					empty($this->docPath) ? $this->load($this->startPage) : $this->loadPage(),
				], // children
			]),
			'script' => $this->script(),
		]);
	}

	protected function loadPage() {
		return new Widget([
			'children' => [
				new Container([
					'class' => 'docs-article',
					'tagName' => 'article',
					'child' => new Container([
						'class' => 'docs-article-body',
						'child' => (function() {
							$file = implode('/', (Array) $this->docPath);
							return $this->load($file);
						})(), // child
					]), // Container
				]), // Container
			], // children
		]);
	}

	protected function load($file) {
		$file = $this->folder.'/page/'.$file.'.html';

		$ret = file_get_contents($file);

		if (empty($ret)) return new Container(['child' => 'Document not found.']);

		$ret = preg_replace_callback(
			'/\{([a-z0-9\-\/]+)\}/i',
			function($m) {
				if (strpos($m[1], '/') === 0) return Url::link($m[1]);
				return Url::link($this->module.'/docs/'.$m[1]);
			},
			$ret
		);
		
		$parser = new Parsedown();
		$ret = $parser->text($ret);

		return $ret;
	}

	protected function script() {
		return '<style type="text/css">
		body {font: 400 16px/24px Roboto,sans-serif;}
		h1, h2, h3, h4, h5, h6 {margin: 0; padding:8px 0;}
		h1 {font-size: 3.5rem;}
		h2 {font-size: 3.0rem;}
		h3 {font-size: 2.0em;}
		h4 {font-size: 2.0em;}
		h2.title {margin:0; padding:24px 8px;background:#039be5; color:#fff;font-size:1.4em;letter-spacing:0.1em;}
		pre>code {background: #eee; padding: 8px; border-radius: 4px; display: block;}
		</style>';
	}
}
?>