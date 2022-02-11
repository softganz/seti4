<?php
function view_publicmon_toolbar($self, $title = '', $nav = 'default', $info = NULL, $options = '{}') {

	if (is_string($rs) AND substr($rs,0,1)=='{') $rs=sg_json_decode($rs);

	$ret = '';

	if (!is_null($title)) $self->theme->title = $title;

	$defaults='{menu:""}';
	$options=sg_json_decode($options);

	if ($nav=='stock') {
		$ret.='<form id="search" class="search-box" method="get" action="'.url('garage/stock').'" role="search"><input type="hidden" name="jid" id="jid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="รหัสสินค้า/อะไหล่" data-query="'.url('garage/api/repaircode').'" data-callback="'.url('garage/stock').'" data-altfld="jid"><button class="button" type="submit"><i class="icon -search"></i>ค้นหา</button></form>'._NL;
	} else {
		$ret .= '<form id="search" class="search-box" method="get" action="'.url('publicmon/search').'" role="search"><input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.htmlspecialchars(post('q')).'" placeholder="ค้น" data-query="'.url('publicmon/api/title').'" data-callback="'.url('publicmon/view/').'" data-altfld="sid"><button type="submit"><i class="icon -search"></i></button></form>'._NL;
	}

	$subnav = R::View('publicmon.'.$nav.'.nav',$info,$options);
	if ($subnav) {
		$ret .= '<nav class="nav -submodule -'.($nav=='default'?'publicmon':$nav).'"><!-- nav of publicmon.'.$nav.'.nav -->';
		$ret .= $subnav;
		$ret .= '</nav><!-- submodule -->';
	}


	$self->theme->toolbar = $ret;
	//debugMsg($ret);
}
?>