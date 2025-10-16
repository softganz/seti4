<?php
/**
* Paper   :: Topics List Page
* Created :: 2019-01-01
* Modify  :: 2024-07-08
* Version :: 4
*
* @return Widget
*
* @usage paper/list
*/

use Paper\Model\PaperModel;
use Paper\Widget\PaperListWidget;

class PaperList extends Page {
	var $userId;
	var $tags;
	var $searchText;
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
			'searchText' => post('q'),
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
			'user' => $this->userId,
			'tags' => $this->tags,
			'searchText' => $this->searchText,
			'options' => [
				'debug' => false,
				'field' => 'detail,photo',
				'page' => $this->page,
				'items' => $this->items,
				'order' => $this->order,
			],
		]);
		// debugMsg($topics->debug, '$debug');


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

		return new Scaffold([
			'appBar' => NULL, // AppBar
			'body' => new Container([
				'id' => 'content-paper',
				'class' => 'content-paper -style-'.$this->listStyle,
				'children' => [
					'<form class="search-box" method="get" action="'.url('paper/list').'" style="flex: 1">'
					. '<input type="text" class="form-text -fill" name="q" value="'.htmlspecialchars(post('q')).'" placeholder="ป้อนหัวข้อที่ต้องการค้นหา" />'
					// . '<button class="btn -link" type="submit" name="" value=""><i class="icon -material">search</i></button>'
					. '</form>',
					$pagenv->show,
					new PaperListWidget([
						'listStyle' => $this->listStyle,
						'url' => q(),
						'order' => $this->order,
						'headerSortParameter' => ['listStyle' => $this->listStyle, 'page' => $this->page, 'items' => $this->items],
						'children' => $topics->items,
					]),
					$pagenv->show,
				], // children
			]), // Container
		]);
	}
}




// Unused code

/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

function paper_list($self) {
	$self->para = $para = sg_json_decode(post(),para(func_get_args(),'field='.cfg('paper.listing.field'),'list-style=div','option=na',1));

	$getPage = post('page');

	event_tricker('paper.listing.init',$self,$topics,$para);

	$conditions = [
		'options' => [
			'debug' => false,
			'field' => 'detail,photo',
			'page' => $getPage,
		],
	];

	$ret .= '<header class="header -box -hidden"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>Paper Listing</h3></header>';

	$ret .= '<form class="search-box" method="get" action="'.url('paper/list').'">'
		. '<input type="text" class="form-text -fill" name="q" value="'.htmlspecialchars(post('q')).'" placeholder="ป้อนหัวข้อที่ต้องการค้นหา" />'
		. '<button class="btn -link" type="submit" name="" value=""><i class="icon -material">search</i></button>'
		. '</form>';

	$topics = PaperModel::items($conditions);

	if (isset($topics->forum)) $GLOBALS['paper']->forum=$topics->forum;

	$self->theme->class='content-paper';

	if ($para->tag) $self->theme->class.=' paper-tag-'.$para->tag;
	if ($para->forum||$topics->forum->fid) $self->theme->class.=' paper-forum-'.\SG\getFirst($para->forum,$topics->forum->fid);
	if ($para->category||$topics->forum->cid) $self->theme->class.=' paper-category-'.\SG\getFirst($para->category,$topics->forum->cid);
	if (!$para->option->no_header) {
		$self->theme->header->text = SG\getFirst($topics->forum->category,$topics->forum->forum,'Topic list');
		if ($topics->forum->description) $self->theme->header->description=$topics->forum->description;
	}
	if (!$para->option->no_menu) {
		user_menu('home',tr('home'),url());
		if (user_access('administer contents,administer papers')) user_menu('paper','paper',url('paper'));
		if ($topics->forum->fid && cfg('member.menu.paper.forum')) user_menu('forum',$topics->forum->forum,url('paper/forum/'.$topics->forum->fid));
		if ($topics->forum->cid) user_menu('category',$topics->forum->category,url('paper/category/'.$topics->forum->cid));
		BasicModel::member_menu();
		if ($topics->forum->cid && ($topics->forum->public==1 ||
			($topics->forum->public==2 && i()->ok) ||
			user_access('administer contents,administer papers,create '.$topics->forum->fid.' paper'))) {
			user_menu('new',tr('Create new topic'),url('paper/post/category/'.$topics->forum->cid));
		} else if ($topics->forum->fid && user_access('administer contents,administer papers,create '.$topics->forum->fid.' paper')) {
			user_menu('new',tr('Create new topic'),url('paper/post/forum/'.$topics->forum->fid));
		}
		if ($para->ip && user_access('access administrator pages')) user_menu('banip','Ban this IP for 1 day',url('admin/ban/request', ['ip' => $para->ip]));

		$self->theme->navigator=user_menu();
	}


	event_tricker('paper.listing.start',$self,$topics,$para);

	if ($para->ip) {
		$self->theme->title='List topic by ip '.$para->ip;
	}

	if (!$para->option->no_page_top) $ret .= $topics->page->show._NL;


	if (!$para->option->no_page_bottom) $ret .= $topics->page->show._NL;


	if (is_object($para->option)) $self->theme->option=(object)array_merge((array)$self->theme->option,(array)$para->option);
	//		$ret=print_o($para,'$para').print_o($self->theme,'$theme').$ret;



	event_tricker('paper.listing.complete',$self,$topics,$para);


	if (debug('method')) $ret.=print_o($para,'$para').print_o($topics,'$topics');

	return $ret;
}
?>