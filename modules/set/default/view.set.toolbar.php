<?php
function view_set_toolbar($self=NULL,$title=NULL,$nav='default',$psn=NULL,$options='{}') {
	$defaults='{menu:""}';
	$options=sg_json_decode($options);
	//if (is_string($psn) AND substr($psn,0,1)=='{') $psn=sg_json_decode($psn);
	if (empty($nav)) $nav='default';


	$ret='';

	$self->theme->title=isset($title)?$title:'@SET';

	$subnav=R::View('set.'.$nav.'.nav',$psn,$options);
	if ($subnav) {
		$ret.='<nav class="nav -submodule -set"><!-- nav of set.'.$nav.'.nav -->';
		$ret.=$subnav;
		$ret.='</nav><!-- submodule -->';
	}
	$self->theme->toolbar=$ret;
	return $ret;
}
?>