<?php
/**
* Admin toolbar
*
* @param Object $self
* @param String $title
* @param String $nav
* @param Object $info
* @param Object $options
* @return String
*/
function view_admin_toolbar($self,$title=NULL,$nav='default',$info=NULL,$options='{}') {
	$defaults='{menu:""}';
	$options=sg_json_decode($options);
	if (empty($nav)) $nav='default';

	cfg('social.googleplus',false);
	cfg('social.facebook',false);
	
	$ret='';


	$self->theme->title=isset($title)?$title:'Web Site Administrator Page';

	$ret.='<form id="search" class="search-box" method="get" action="'.url('admin/user/list').'" name="memberlist" role="search">';
	$ret.='<input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="20" value="'.$_GET['q'].'" data-query="'.url('admin/get/username').'" data-altfld="sid" data-callback="submit" placeholder="Username or Name or Email"><input type="submit" class="button" value="ค้นหาสมาชิก">';
	$ret.='</form>'._NL;

	$subnav=R::View('admin.'.$nav.'.nav',$info,$options);
	if ($subnav) {
		$ret.='<nav class="nav -submodule -admin -no-print"><!-- nav of admin.'.$nav.'.nav -->';
		$ret.=$subnav;
		$ret.='</nav><!-- submodule -->';
	}
	$self->theme->toolbar=$ret;
	return $ret;
}
?>