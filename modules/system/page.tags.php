<?php
/**
 * Tags     :: List Of Tags
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2008-07-19
 * Modified :: 2026-08-24
 * Version  :: 4
 *
 * @param String $tagIdList
 * @return Widget
 *
 * @uses tags/{tagIdList}
 */

use Paper\Model\PaperModel;
use Paper\Widget\PaperListWidget;
use Softganz\DB;

class Tags extends Page {
	var $tagIdList;
	var $listStyle;
	var $page;
	var $items;
	var $order;

	private const LISTSTYLE = 'div';
	private const PAGE = 1;
	private const ITEMS = 10;
	private const ORDERBY = 'nodeId';

	function __construct($tagIdList = NULL) {
		parent::__construct([
			'tagIdList' => $tagIdList,
			'listStyle' => \SG\getFirst(post('listStyle'), self::LISTSTYLE),
			'page' => \SG\getFirst(post('page'), self::PAGE),
			'items' => \SG\getFirst(post('items'), self::ITEMS),
			'order' => \SG\getFirst(post('order'), self::ORDERBY),
		]);
		// debugMsg($this, '$this');
	}

	function build() {
		head('<meta name="robots" content="noindex,nofollow">');

		if (empty($this->tagIdList)) return $this->listTags();

		return $this->listTagTopics();
	}

	// Show tags cloud
	function listTags() {
		$ret = '';

		$tagDbs = DB::select([
			'SELECT
				t.`tid`, t.`name`, t.`process`
			, (SELECT COUNT(`tid`) AS `max` FROM %tag_topic% GROUP BY `tid` ORDER BY `max` DESC LIMIT 1) AS `max`
			, (SELECT COUNT(*) FROM %tag_topic% tp WHERE tp.`tid` = t.`tid`) AS `topics`
			FROM %tag% t
			WHERE `vid` IS NOT NULL
			ORDER BY t.`name` ASC'
		]);

		foreach ($tagDbs->items as $tag) {
			if ($tag->process == -1) continue;
			$level = $tag->max ? round($tag->topics * 4 / $tag->max) + 1 : 1;
			$ret .= '<a href="'.url('tags/'.$tag->tid).'" class="btn -tagadelic -level'.$level.'">'.$tag->name.'</a> '._NL;
		}

		return new Scaffold([
			'appBar' => new AppBar(['title' => 'Tags']),
			'body' => $ret,
		]);
	}

	function listTagTopics() {
		$types = BasicModel::get_topic_type($this->tagIdList);

		// event_tricker('paper.listing.init',$self,$topics,$para);

		$topics = PaperModel::items([
			'tags' => $this->tagIdList,
			'type' => '*',
			'options' => [
				'debug' => false,
				'field' => 'detail,photo',
				'page' => $this->page,
				'items' => $this->items,
				'order' => $this->order,
			],
		]);

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

		$pagenv = PaperModel::pageNavigator($pageCondition);

		// event_tricker('paper.listing.complete',$self,$topics,$para);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Topic List',
			]), // AppBar
			'body' => new Container([
				'class' => 'tag-topics -style-'.$this->listStyle,
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