<?php
function view_org_toolbar($self, $title = NULL, $nav = 'default', $info = NULL, $options = '{}') {

	$defaults = '{menu:"", modulenav: true, searchform: true}';
	$options = sg_json_decode($options,$defaults);
	if (is_string($info) AND substr($info,0,1)=='{')
		$info = sg_json_decode($info);
	if (empty($nav)) $nav = 'default';

	$orgId = $info->orgid;

	$toolbarText = '';

	$self->theme->title = $title.($info->name ? ' @'.$info->name : '');

	if ($options->searchform) {
		$toolbarText .= '<form method="get" action="'.url('org/member').'" id="search" class="search-box" data-query="'.url('org/api/person').'" role="search"><input type="text" name="qn" id="search-box" class="form-text" size="20" value="'.post('qn').'" placeholder="ป้อน ชื่อ นามสกุล หรือ เบอร์โทร"><button class="btn -link"><i class="icon -search"></i></button></form>';
	}

	if ($nav == 'none') {
		$toolbarText = '<span class="-hidden"></span>';
	} else {
		$subnav = R::View('org.nav.'.$nav, $info, $options);
		if ($subnav) {
			$toolbarText .= '<nav class="nav -submodule -'.($nav=='default'?'org':$nav).'"><!-- nav of org.'.$nav.'.nav -->';
			$toolbarText .= $subnav;
			$toolbarText .= '</nav><!-- submodule -->';
		}
	}

	$self->theme->toolbar = $toolbarText;

	head('js.org.js','<script type="text/javascript" src="org/js.org.js"></script>');
	head('jspi','<script type="text/javascript" src="https://www.google.com/jsapi"></script>');
}
?>