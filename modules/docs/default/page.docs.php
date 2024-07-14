<?php
function docs($self) {
	$self->theme->title='Documentation';

	$arg=func_get_args();
	array_shift($arg);

	$ret.='<nav class="nav -page">';
	$ui=new Ui(NULL,'ui-nav');
	$ui->add('<a href="'.url('docs').'">Overview</a>');
	$ui->add('<a href="'.url('docs/guides').'">Giudes</a>');
	$ui->add('<a href="'.url('docs/reference').'">Reference</a>');
	$ui->add('<a class="sg-action" href="'.url('docs/page/docs/samples').'">Samples</a>');
	$ui->add('<a href="'.url('docs/libraies').'">Libraries</a>');
	$ret.=$ui->build();
	$ret.='</nav>';

	if (empty($arg)) {
		$ret.=__docs_load('overview');
	} else {
		$ret.='<nav class="nav docs-nav">';
		$ret.=__docs_load('docs.list');
		$ret.='</nav>';


		$ret.='<article class="docs-article">';
		$ret.='<div class="docs-article-body">';
		if ($arg[0]=='page') {
			array_shift($arg);
			$page=implode('.',$arg);
			$ret.=R::Page($page);
		} else {
			if (count($arg)>=3) {
				$file=implode('/',array_slice($arg,0,2)).'/'.implode('.',array_slice($arg,2));
			} else if (count($arg)>=2) {
				$file=implode('/',array_slice($arg,0,1)).'/'.implode('.',array_slice($arg,1));
			} else {
				$file=$arg[0];
			}
			$ret.=__docs_load($file);
		}

		//$ret.=print_o($arg,'arg');
		$ret.='</div>';
		$ret.='</article>';
	}

	$ret.='<style type="text/css">
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
	return $ret;
}

function __docs_load($file) {
	$file=dirname(__FILE__).'/../page/'.$file.'.html';
	$ret=file_get_contents($file);
	//$ret.=$file;
	//preg_replace('/href=\"\{class\/form\}\"/',$ret,'aaaa');
	$ret = preg_replace_callback('/\{([a-z0-9_\-\/\.]+)\}/i', '__url_replace' ,$ret);
	$ret = preg_replace_callback("#\<code(.*?)\>(.*?)\</code\>#si", '__sg_encode_code_callback', $ret); // [code]...[/code]

	//$ret = preg_replace('/<code>(\n)<\/code>/i', '<br />' ,$ret);

	return $ret;
}

function __url_replace($m) {/*print_o($m,'$m',1);*/return ' '.url('docs/'.$m[1]);}

?>