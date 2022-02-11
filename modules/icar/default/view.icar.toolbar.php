<?php
/**
* Project Toolbar
*
* @param Object $self
* @param String $title
* @param String $nav
* @param Object $carInfo
* @param Object $options
* @return String
*/

function view_icar_toolbar($self, $title = NULL, $nav = 'default', $carInfo = NULL, $options = '{}') {
	$defaults = '{menu:"", modulenav: false}';
	$options = sg_json_decode($options,$defaults);

	if (is_string($carInfo) AND substr($carInfo, 0, 1) == '{') $carInfo = sg_json_decode($carInfo);
	if (empty($nav)) $nav = 'default';

	$ret = '';


	// Module navigator
	if ($options->modulenav) {
		$ui = new Ui();
		if ($ui->count()) $self->theme->moduleNav = $ui->build('ul','navgroup -main');
	}

	if ($title) {
	} else if ($carInfo->carname) {
		$title = $carInfo->carname.' , '.$carInfo->plate;
	} else {
		$title = 'iCarSmile : Car Cost Control';
	}
	$self->theme->title = $title ; 

	if ($nav == 'default' || $nav == 'icar') {
		$ret .= '<form id="search" class="search-box" method="get" action="'.url('icar/my').'" name="memberlist" role="search"><input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="ทะเบียน,เลขเครื่อง,เลขตัวถัง,ยี่ห้อ" data-query="'.url('icar/search').'" data-callback="'.url('icar/').'" data-altfld="sid"><button type="submit"><i class="icon -search"></i></button></form>';
		//'<form class="search-box" method="get" action="'.url('icar/my').'" role="search"><!-- <input type="hidden" name="carid" id="carid" />--><input type="text" name="q" id="icar-search" size="10" value="'.htmlspecialchars($_REQUEST['q']).'" placeholder="ทะเบียน,เลขเครื่อง,เลขตัวถัง,ยี่ห้อ" title="ป้อนทะเบียนรถ"><button class="button"><i class="icon -search"></i><!-- <span class="-hidden">ค้นหารถ</span> --></button></form>'._NL;
	}

	$subnav = R::View('icar.'.$nav.'.nav',$carInfo,$options);
	if ($subnav) {
		$ret .= '<nav class="nav -submodule -'.($nav=='default'?'project':$nav).'"><!-- nav of project.'.$nav.'.nav -->';
		$ret .= $subnav;
		$ret .= '</nav><!-- submodule -->';
	}

	//$ret .= print_o($carInfo,'$carInfo');

	$self->theme->toolbar = $ret;
	$self->theme->submodule = $nav;

	return $ret;
}
?>