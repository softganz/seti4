<?php
/**
* Contents:: List Of Content
* Created :: 2008-07-19
* Modify  :: 2023-07-25
* Version :: 2
*
* @param String $contentTypes
* @return Widget
*
* @usage contents/{type}
*/

import('model:paper.php');
import('widget:paper.list.php');

use Paper\Model\PaperModel;
use Paper\Widget\PaperListWidget;

class Contents extends Page {
	var $contentTypes;
	var $listStyle;
	var $page = 1;
	var $items = 10;
	var $order = 'nodeId';

	private const ITEMS = 10;
	private const ORDERBY = 'nodeId';

	function __construct($contentTypes = NULL) {
		parent::__construct([
			'contentTypes' => $contentTypes,
			'listStyle' => \SG\getFirst(post('listStyle'), $this->listStyle),
			'page' => \SG\getFirst(post('page'), $this->page),
			'items' => \SG\getFirst(post('items'), self::ITEMS),
			'order' => \SG\getFirst(post('order'), $this->order),
		]);
		// debugMsg($this, '$this');
	}

	function build() {
		$types = BasicModel::get_topic_type($this->contentTypes);

		event_tricker('paper.listing.init',$self,$topics,$para);

		$conditions = [
			'type' => $this->contentTypes,
			'options' => [
				'debug' => false,
				'field' => 'detail,photo',
				'page' => $this->page,
				'items' => $this->items,
				'order' => $this->order,
			],
		];

		$topics = PaperModel::items($conditions);

		/*
		$self->theme->class='content-paper';
		$self->theme->class.=' paper-content-'.\SG\getFirst($this->types);
		$self->theme->header->text = SG\getFirst($types->name);
		if ($types->description) {
			ob_start();
			eval ('?>'.$types->description);
			$self->theme->header->description=ob_get_clean();
		}

		user_menu('home','Home',url());
		user_menu('type',$types->name,url('contents/'.$types->type));
		BasicModel::member_menu();
		if ($topics->forum->cid && ($topics->forum->public==1 ||
			($topics->forum->public==2 && i()->ok) ||
			user_access('administer contents,administer papers,create '.$topics->forum->fid.' paper'))) {
			user_menu('new','Create new topic',url('paper/post/category/'.$topics->forum->cid));
		} else if ($topics->forum->fid && user_access('administer contents,administer papers,create '.$topics->forum->fid.' paper')) {
			user_menu('new','Create new topic',url('paper/post/forum/'.$topics->forum->fid));
		}

		// $self->theme->navigator=user_menu();
		*/

		head('<meta name="robots" content="noindex,nofollow">');

		event_tricker('paper.listing.start',$self,$topics,$para);

		// if ($para->category && (empty($para->page) || $para->page==1)) {
		// 	$sticky_para = (Object) [
		// 		'sticky' => _CATEGORY_STICKY,
		// 		'type' => $para->category,
		// 		'limit' => cfg('sticky.category.items'),
		// 	];
		// 	$stickys = PaperModel::items($sticky_para);
		// 	foreach ($topics->items as $key=>$topic) {
		// 		if ($topic->sticky==_CATEGORY_STICKY) unset($topics->items[$key]);
		// 	}
		// 	$topics->items=array_merge($stickys->items,$topics->items);
		// 	$topics->_num_rows=count($topics->items);
		// 	$topics->_empty=$topics->_num_rows<=0;
		// }

		$pageCondition = [
			'items' => $para->items,
			'page' => $this->page,
			'total' => $topics->total,
			'url' => q(),
			'cleanUrl' => true,
			'pagePara' => [
				'page' => $this->page,
				'items' => $this->items == self::ITEMS ? NULL : $this->items,
				'order' => $this->order == self::ORDERBY ? NULL : $this->items,
				'listStyle' => $this->listStyle,
			]
		] ;
		// debugMsg($pageCondition, '$pageConfition');

		$pagenv = PaperModel::pageNavigator($pageCondition);

		event_tricker('paper.listing.complete',$self,$topics,$para);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Contents',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					$pagenv->show,
					new PaperListWidget([
						'listStyle' => $this->listStyle,
						'url' => q(),
						'order' => $this->order,
						'headerSortParameter' => ['listStyle' => $this->listStyle, 'page' => $this->page, 'items' => $this->items],
						'children' => $topics->items,
					]),
					!$para->option->no_page && $topics->page->show ? $pagenv->show : NULL,
				], // children
			]), // Widget
		]);
	}
}
?>