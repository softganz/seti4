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

use Paper\Model\PaperModel;
use Paper\Widget\PaperListWidget;
use Softganz\DB;

class Contents extends Page {
	var $contentTypes;
	var $listStyle;
	var $page;
	var $items;
	var $order;
	var $right;

	private const LISTSTYLE = 'div';
	private const PAGE = 1;
	private const ITEMS = 10;
	private const ORDERBY = 'nodeId';

	function __construct($contentTypes = NULL) {
		$singleType = !preg_match('/,/', $contentTypes) && user_access('create '.$contentTypes.' paper');

		parent::__construct([
			'contentTypes' => $contentTypes,
			'listStyle' => SG\getFirst(post('listStyle'), self::LISTSTYLE),
			'page' => SG\getFirst(post('page'), self::PAGE),
			'items' => SG\getFirst(post('items'), self::ITEMS),
			'order' => SG\getFirst(post('order'), self::ORDERBY),
			'singleType' => $singleType,
			'right' => (Object) [
				'create' => $singleType ? true : false,
			],
		]);
		// debugMsg($this, '$this');
	}

	function build() {
		head('<meta name="robots" content="noindex,nofollow">');

		// $types = BasicModel::get_topic_type($this->contentTypes);

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
			'items' => $this->items,
			'page' => $this->page,
			'total' => $topics->total,
			'url' => q(),
			'cleanUrl' => true,
			'pagePara' => [
				'page' => $this->page,
				'items' => $this->items == self::ITEMS ? NULL : $this->items,
				'order' => $this->order == self::ORDERBY ? NULL : $this->items,
				'listStyle' => $this->listStyle == self::LISTSTYLE ? NULL : $this->listStyle,
			]
		];
		// debugMsg($pageCondition, '$pageConfition');

		$pagenv = PaperModel::pageNavigator($pageCondition);

		event_tricker('paper.listing.complete',$self,$topics,$para);

		if ($this->singleType) {
			$title = DB::select([
				'SELECT `name` FROM %topic_types% WHERE `type` = :type LIMIT 1',
				'var' => [':type' => $this->contentTypes]
			])->name;
		} else {
			$title = 'Contents';
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $title,
				'trailing' => new Row([
					'children' => [
						$this->right->create ? new Button([
							'type' => 'primary',
							'href' => url('paper/post/'.$this->contentTypes),
							'text' => 'สร้าง '.$title,
							'icon' => new Icon('add'),
						]) : NULL, // Button
					], // children
				]), // Row
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
					$pagenv->show,
				], // children
			]), // Widget
		]);
	}
}
?>