<?php
/**
* Paper   :: Home Page
* Created :: 2019-01-01
* Modify  :: 2023-07-26
* Version :: 3
*
* @return Widget
*
* @usage paper
*/

use Paper\Model\PaperModel;
use Paper\Widget\PaperListWidget;

class PaperHome extends Page {
	var $tags;
	var $listStyle;
	var $page;
	var $items;
	var $order;

	private const LISTSTYLE = 'div';
	private const PAGE = 1;
	private const ITEMS = 10;
	private const ORDERBY = 'nodeId';

	function __construct() {
		parent::__construct([
			'tags' => post('tags'),
			'listStyle' => \SG\getFirst(post('listStyle'), self::LISTSTYLE),
			'page' => \SG\getFirst(post('page'), self::PAGE),
			'items' => \SG\getFirst(post('items'), self::ITEMS),
			'order' => \SG\getFirst(post('order'), self::ORDERBY),
		]);
		// debugMsg($this, '$this');
	}

	function build() {
		head('<meta name="robots" content="noindex,nofollow">');

		// event_tricker('paper.listing.init',$self,$topics,$para);

		$topics = PaperModel::items([
			'tags' => $this->tags,
			'options' => [
				'debug' => false,
				'field' => 'detail,photo',
				'page' => $this->page,
				'items' => $this->items,
				'order' => $this->order,
			],
		]);
		// debugMsg($topics, '$topics');


		event_tricker('paper.listing.start',$self,$topics,$para);

		$pageCondition = [
			'items' => $this->items,
			'page' => $this->page,
			'total' => $topics->total,
			'url' => q(),
			'cleanUrl' => true,
			'pagePara' => [
				'tags' => $this->tags,
				'page' => $this->page,
				'items' => $this->items == self::ITEMS ? NULL : $this->items,
				'order' => $this->order == self::ORDERBY ? NULL : $this->items,
				'listStyle' => $this->listStyle == self::LISTSTYLE ? NULL : $this->listStyle,
			]
		];

		$pagenv = PaperModel::pageNavigator($pageCondition);

		// event_tricker('paper.listing.complete',$self,$topics,$para);

		// // show topic that mark as home sticky
		// if ($isFirstPage) {
		// 	$sticky_para = (Object) [
		// 		'sticky' => _HOME_STICKY,
		// 		'option' => option('no_page_bottom,no_menu,no_header,no_package_footer,no_toolbar,no_div'),
		// 		'options' => [
		// 			'field' => 'detail,photo',
		// 			'order' => 'weight',
		// 			'sort' => 'asc',
		// 			'limit' => 10,
		// 		]
		// 	];
		// 	$sticky = PaperModel::items($sticky_para);
		// }

		// if ($isFirstPage) {
		// 	foreach ($promote->items as $key => $item) {
		// 		if ($item->sticky==_HOME_STICKY) unset($promote->items[$key]);
		// 	}
		// }

		return new Scaffold([
			'appBar' => NULL, // AppBar
			'body' => new Container([
				'class' => 'tag-topics -style-'.$this->listStyle,
				'children' => [
					'<form class="search-box" method="get" action="'.url('paper/list').'" style="flex: 1">'
					. '<input type="text" class="form-text -fill" name="q" value="'.htmlspecialchars(post('q')).'" placeholder="ป้อนหัวข้อที่ต้องการค้นหา" />'
					// . '<button class="btn -link" type="submit" name="" value=""><i class="icon -material">search</i></button>'
					. '</form>',
					$pagenv->show,
					new PaperListWidget([
						'id' => 'home-promote',
						'class' => 'promote',
						'listStyle' => $this->listStyle,
						'url' => q(),
						'order' => $this->order,
						'headerSortParameter' => ['listStyle' => $this->listStyle, 'page' => $this->page, 'items' => $this->items],
						'children' => $topics->items,
					]),
					$pagenv->show,
					// isset($GLOBALS['ad']->tags_list) ? new Container([
					// 	'id' => 'ad-tags_list',
					// 	'class' => 'ads',
					// 	'child' => $GLOBALS['ad']->tags_list,
					// ]) : NULL, // Container
				], // children
			]), // Container
		]);
	}
}
?>