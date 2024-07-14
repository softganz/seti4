<?php
/**
* Docs    :: Docs Page Controller
* Created :: 2024-07-14
* Modify  :: 2024-07-14
* Version :: 2
*
* @param String $args
* @return Widget
*
* @usage docs
*/

class Docs extends PageController {
	var $args;

	function __construct() {
		$this->args = func_get_args();
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Documentation',
				'navigator' => [
					'<a href="'.url('docs').'">Overview</a>',
					'<a href="'.url('docs/guides').'">Giudes</a>',
					'<a href="'.url('docs/reference').'">Reference</a>',
					'<a href="'.url('docs/page/docs/samples').'">Samples</a>',
					'<a href="'.url('docs/libraies').'">Libraries</a>',
				]
			]),
			'body' => new Widget([
				'children' => [
					empty($this->args) ? $this->load('overview') : $this->loadPage(),
					$this->script(),
				], // children
			])
		]);
	}

	private function loadPage() {
		return new Widget([
			'children' => [
				new Container([
					'tagName' => 'nav',
					'class' => 'nav docs-nav',
					'child' => $this->load('docs.list'),
				]), // Container

				new Container([
					'class' => 'docs-article',
					'tagName' => 'article',
					'child' => new Container([
						'class' => 'docs-article-body',
						'child' => (function() {
							if ($this->args[0] == 'page') {
								array_shift($this->args);
								$page = implode('.',$this->args);
								$ret .= R::Page($page);
							} else {
								if (count($this->args) >= 3) {
									$file = implode('/',array_slice($this->args,0,2)).'/'.implode('.',array_slice($this->args,2));
								} else if (count($this->args) >= 2) {
									$file = implode('/',array_slice($this->args,0,1)).'/'.implode('.',array_slice($this->args,1));
								} else {
									$file = $this->args[0];
								}
								$ret .= $this->load($file);
							}
							return $ret;
						})(), // child
					]), // Container
				]), // Container
			], // children
		]);
	}

	private function load($file) {
		$file = dirname(__FILE__).'/../page/'.$file.'.html';
		$ret = file_get_contents($file);
		//$ret.=$file;
		//preg_replace('/href=\"\{class\/form\}\"/',$ret,'aaaa');
		$ret = preg_replace_callback('/\{([a-z0-9_\-\/\.]+)\}/i', 'Docs::url_replace' ,$ret);
		$ret = preg_replace_callback("#\<code(.*?)\>(.*?)\</code\>#si", '__sg_encode_code_callback', $ret); // [code]...[/code]

		//$ret = preg_replace('/<code>(\n)<\/code>/i', '<br />' ,$ret);

		return $ret;
	}

	public static function url_replace($m) {
		return ' '.url('docs/'.$m[1]);
	}

	private function script() {
		return '<style type="text/css">
		body {font: 400 16px/24px Roboto,sans-serif;}
		h1, h2, h3, h4, h5, h6 {margin: 0; padding:8px 0;font-family: Roboto,sans-serif; font-weight: normal; color: #ccc;}
		h2 {font-size:1.4em;}
		h3 {font-size:1.2em;}
		h4 {font-size:1.1em;}
		h2.title {margin:0; padding:24px 8px;background:#039be5; color:#fff;font-size:1.4em;letter-spacing:0.1em;}
		.nav.-page {margin:0 0 16px 0; background:#039BE5;}
		.nav.-page a {color:#BBE4F8; text-transform:uppercase;letter-spacing:0.1em;}
		.nav.-page a:hover {color:#fff;}

		#footer-wrapper {clear:both;}
		</style>';
	}
}
?>