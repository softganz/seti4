<?php
/**
 * Contents :: List Of Content
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2008-07-19
 * Modify   :: 2026-08-25
 * Version  :: 3
 *
 * @param String $contentTypes
 * @return Widget
 *
 * @uses contents/{type}
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

		event_tricker('paper.listing.start',$self,$topics,$para);

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