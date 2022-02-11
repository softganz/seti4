<?php
function view_saveup_toolbar($self,$title=NULL,$nav=NULL,$info=NULL,$options='{}') {
	$defaults='{}';
	$options=sg_json_decode($options);
	if (is_string($info) AND substr($info,0,1)=='{') $info=sg_json_decode($info);

	head('js.saveup.js','<script type="text/javascript" src="saveup/js.saveup.js"></script>');

	$ret='';

	$self->theme->moduleNav=R::View('saveup.nav.module',$info,$options);

	//$self->theme->moduleNav=$ui->build();

	$self->theme->title=isset($title)?$title:'Proejct Management';

	if (1 || empty($nav)) {
		$ret.='<form id="saveup-searchmember" class="search-box" method="get" action="'.url('saveup/member/list').'" name="memberlist" role="search"><input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="ป้อนชื่อหรือรหัสสมาชิก" data-query="'.url('saveup/api/member').'" data-callback="'.url('saveup/member/view/').'" data-altfld="sid"><button type="submit" class="btn" name="search"><i class="icon -search"></i><span class="-hidden">ค้นหา</span></button></form>'._NL;
	}

	$subnav=R::View('saveup'.(empty($nav)?'':'.'.$nav).'.nav',$info,$options);
	if ($subnav) {
		$ret.='<nav class="nav -submodule -saveup">';
		$ret.=$subnav;
		$ret.='</nav><!-- submodule -->';
	}
	$self->theme->toolbar=$ret;
	return $ret;
}
?>