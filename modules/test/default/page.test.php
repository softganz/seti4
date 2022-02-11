<?php
function test($self) {
	R::View('test.toolbar',$self,'Test Page Title Toolbar',NULL);

	$ret.='<p><h2><font color="red">This is a test page result</font></h2></p>';
	$ret.='<p>Translate "Save"={tr:Save}</p>';
	$ret.='<p>Url={url:paper/10}</p>';

	$ret.='<p>';
	$ret.='<a class="btn" href="{url:test/array}">Test Array</a> ';
	$ret.='<a class="btn" href="{url:test/array?html}">Test Array Return HTML</a> ';

	$ret.='<a class="btn" href="{url:test/object}">Test Object</a> ';
	$ret.='<a class="btn" href="{url:test/object?html}">Test Object Return HTML</a> ';

	$ret.='</p>';

	//$ret.='<p>Process widget</p>';
	$ret.='<div class="widget stat" ></div>';
	//$ret.='<div class="widget " data-url="garage/job/view" data-para-id="343" data-header="This is a header"></div>';
	//$ret.='<div class="widget " data-url="garage/job/view" data-para-id="342"></div>';

	//$ret.=R::Page('garage.do.view',NULL,343);
	$ret.='<p>'.print_o($self,'$self').'</p>';
	return $ret;
}
?>