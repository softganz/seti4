<?php
/**
* Paper Home Page
* Created 2019-01-01
* Modify  2019-06-02
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function paper_home($self) {
	$getPage = post('page');

	$isFirstPage = $getPage <= 1;
	$ret = '';

	// Process Paper Home
	$self->para = $para = para(func_get_args());

	$ret .= '<form class="search-box" method="get" action="'.url('paper/list').'">'
		. '<input type="text" class="form-text -fill" name="q" value="'.htmlspecialchars(post('q')).'" placeholder="ป้อนหัวข้อที่ต้องการค้นหา" />'
		. '<button class="btn -link" type="submit" name="" value=""><i class="icon -material">search</i></button>'
		. '</form>';

	// show topic that mark as home sticky
	if ($isFirstPage) {
		$sticky_para = (Object) [
			'sticky' => _HOME_STICKY,
			'field' => 'detail,photo',
			'order' => 'weight',
			'sort' => 'asc',
			'limit' => 10,
			'option' => option('no_page_bottom,no_menu,no_header,no_package_footer,no_toolbar,no_div'),
		];
		$sticky = PaperModel::get_topic_by_condition($sticky_para);
	}

	if ($sticky->_num_rows) {
		$ret .= '<div id="home-sticky" class="sticky">'._NL;
		switch ($para->{'list-style'}) {
			case 'table' : $ret .= view::list_style_table($sticky, $para);break;
			case 'ul' : $ret .= view::list_style_ul($sticky, $para);break;
			case 'dl' : $ret .= view::list_style_dl($sticky, $para);break;
			default : $ret .= view::list_style_div($sticky, $para);break;
		}
		$ret .= '</div><!--home-sticky-->'._NL;
	}

	// show topic that mark as promote
	$promote_para = (Object) [
		'condition' => 'promote=1',
		'field' => 'detail,photo',
		'order' => 'tpid',
		'sort' => 'desc',
		'items' => cfg('paper.promote.items'),
		'option' => option('no_page_bottom,no_menu,no_header,no_package_footer,no_toolbar,no_div'),
	];

	$options = (Object) [
		'debug' => false,
		'field' => 'detail,photo',
		'page' => $getPage,
	];

	//$promote = PaperModel::get_topic_by_condition($promote_para);

	$promote = R::Model('paper.get.topics',$promote_para, $options);

	if ($isFirstPage) {
		foreach ($promote->items as $key => $item) {
			if ($item->sticky==_HOME_STICKY) unset($promote->items[$key]);
		}
	}



	$ret .= $promote->page->show._NL;

	$ret .= '<div id="home-promote" class="promote">'._NL;

	switch ($para->{'list-style'}) {
		case 'table' : $ret .= view::list_style_table($promote, $para);break;
		case 'ul' : $ret .= view::list_style_ul($promote, $para);break;
		case 'dl' : $ret .= view::list_style_dl($promote, $para);break;
		default : $ret .= view::list_style_div($promote, $para);break;
	}
	$ret .= '</div><!--home-promote-->'._NL;

	$ret .= $promote->page->show._NL;


	if (isset($GLOBALS['ad']->paper_list)) {
		$ret.='<div id="ad-paper_list" class="ads">'.$GLOBALS['ad']->paper_list.'</div>';
	}

	$self->theme->option = option('no_header,no_package_footer,no_toolbar,no_div');
	return $ret;
}
?>