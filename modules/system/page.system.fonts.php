<?php
function system_fonts($self = NULL) {
	head('<link href="https://fonts.googleapis.com/css?family=Athiti|Chonburi|Itim|Kanit|Maitree|Mitr|Pattaya|Pridi|Prompt|Sriracha|Taviraj|Trirong" rel="stylesheet">');
	$ret.='<p class="font Mitr">สวัสดี Mitr</p>';
	$ret.='<p class="font Pattaya">สวัสดี Pattaya</p>';
	$ret.='<p class="font Taviraj">สวัสดี Taviraj</p>';
	$ret.='<p class="font Kanit">สวัสดี Kanit</p>';
	$ret.='<p class="font Maitree">สวัสดี Maitree</p>';
	$ret.='<p class="font Sriracha">สวัสดี Sriracha</p>';
	$ret.='<p class="font Prompt">สวัสดี Prompt</p>';
	$ret.='<p class="font Itim">สวัสดี Itim</p>';
	$ret.='<p class="font Chonburi">สวัสดี Chonburi</p>';
	$ret.='<p class="font Pridi">สวัสดี Pridi</p>';
	$ret.='<p class="font Athiti">สวัสดี Athiti</p>';
	$ret.='<p class="font Trirong">สวัสดี Trirong</p>';

	$ret.='<a href="https://fonts.google.com/?query=trirong&selection.family=Athiti|Chonburi|Itim|Kanit|Maitree|Mitr|Pattaya|Pridi|Prompt|Sriracha|Taviraj|Trirong" target="_blank">Google Fonts</a>';
	$ret.='<style type="text/css">
	p.font {font-size: 3em; line-height: 1em;}
	.Mitr {font-family: "Mitr", sans-serif;}
	.Pattaya {font-family: "Pattaya", sans-serif;}
	.Taviraj {font-family: "Taviraj", serif;}
	.Kanit {font-family: "Kanit", sans-serif;}
	.Maitree {font-family: "Maitree", serif;}
	.Sriracha {font-family: "Sriracha", cursive;}
	.Prompt {font-family: "Prompt", sans-serif;}
	.Itim {font-family: "Itim", cursive;}
	.Chonburi {font-family: "Chonburi", cursive;}
	.Pridi {font-family: "Pridi", serif;}
	.Athiti {font-family: "Athiti", sans-serif;}
	.Trirong {font-family: "Trirong", serif;}
	</style>';
	return $ret;
}
?>