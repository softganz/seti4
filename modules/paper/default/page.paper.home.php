<?php
/**
* Paper   :: Home Page
* Created :: 2019-01-01
* Modify  :: 2024-07-25
* Version :: 2
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
			'option' => option('no_page_bottom,no_menu,no_header,no_package_footer,no_toolbar,no_div'),
			'options' => [
				'field' => 'detail,photo',
				'order' => 'weight',
				'sort' => 'asc',
				'limit' => 10,
			]
		];
		$sticky = PaperModel::items($sticky_para);
	}

	if ($sticky->_num_rows) {
		$ret .= '<div id="home-sticky" class="sticky">'._NL;
		switch ($para->{'list-style'}) {
			case 'table' : $ret .= R::View('paper.list.style.table', $self, $sticky, $para);break;
			case 'ul' : $ret .= R::View('paper.list.style.ul', $self, $sticky, $para);break;
			case 'div' : $ret .= R::View('paper.list.style.div', $self, $sticky, $para);break;
			default : $ret .= R::View('paper.list.style.dl', $self, $sticky, $para);break;
		}
		$ret .= '</div><!--home-sticky-->'._NL;
	}

	// show topic that mark as promote
	$promote_para = (Object) [
		'condition' => 'promote=1',
		'option' => option('no_page_bottom,no_menu,no_header,no_package_footer,no_toolbar,no_div'),
		'options' => (Object) [
			'debug' => false,
			'field' => 'body,photo',
			'order' => 'tpid',
			'sort' => 'desc',
			'items' => cfg('paper.promote.items'),
			'page' => $getPage,
		]
	];

	$promote = PaperModel::items($promote_para);

	if ($isFirstPage) {
		foreach ($promote->items as $key => $item) {
			if ($item->sticky==_HOME_STICKY) unset($promote->items[$key]);
		}
	}



	$ret .= $promote->page->show._NL;

	$ret .= '<div id="home-promote" class="promote">'._NL;

	switch ($para->{'list-style'}) {
			case 'table' : $ret .= R::View('paper.list.style.table', $self, $promote, $para);break;
			case 'ul' : $ret .= R::View('paper.list.style.ul', $self, $promote, $para);break;
			case 'div' : $ret .= R::View('paper.list.style.div', $self, $promote, $para);break;
			default : $ret .= R::View('paper.list.style.dl', $self, $promote, $para);break;
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