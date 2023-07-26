<?php
/**
* Tags    :: List Of Tags
* Created :: 2008-07-19
* Modify  :: 2023-07-26
* Version :: 2
*
* @param String $tagIdList
* @return Widget
*
* @usage tags/{tagIdList}
*/

use Paper\Model\PaperModel;
use Paper\Widget\PaperListWidget;

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
		$stmt = 'SELECT
				t.`tid`, t.`name`, t.`process`
			, (SELECT COUNT(`tid`) AS `max` FROM %tag_topic% GROUP BY `tid` ORDER BY `max` DESC LIMIT 1) AS `max`
			, (SELECT COUNT(*) FROM %tag_topic% tp WHERE tp.`tid` = t.`tid`) AS `topics`
			FROM %tag% t
			WHERE `vid` IS NOT NULL
			ORDER BY t.`name` ASC';

		$tagDbs = mydb::select($stmt);

		foreach ($tagDbs->items as $tag) {
			if ($tag->process == -1) continue;
			$level = round($tag->topics/$tag->max*4)+1;
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

/**
 * tags class for topic listing by tags
 *
 * @package tags
 * @version 1.30.0
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2008-07-19
 * @modify 2013-10-06
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

function tags($self, $tagIdList = NULL) {
	$self->para = $para = para(func_get_args(),'items=10','field='.cfg('paper.listing.field'),'list-style=div',1);
	page_class('-tags');
	cfg('page_id','tags'.($tagIdList?'-'.$tagIdList:''));
	user_menu('home','home',url());
	user_menu('tags','tags',url('tags'));
	head('<meta name="robots" content="noindex,nofollow">');
	head('googlead','<script></script>');

	$tag_name=array();

	if (empty($tagIdList)) {
		// Show tags cloud
		$self->theme->title = 'Tags';

		$stmt = 'SELECT
				t.`tid`, t.`name`, t.`process`
			, (SELECT COUNT(`tid`) AS `max` FROM %tag_topic% GROUP BY `tid` ORDER BY `max` DESC LIMIT 1) AS `max`
			, (SELECT COUNT(*) FROM %tag_topic% tp WHERE tp.`tid` = t.`tid`) AS `topics`
			FROM %tag% t
			WHERE `vid` IS NOT NULL
			ORDER BY t.`name` ASC';

		$tagDbs = mydb::select($stmt);

		foreach ($tagDbs->items as $tag) {
			if ($tag->process == -1) continue;
			$level = round($tag->topics/$tag->max*4)+1;
			$ret .= '<a href="'.url('tags/'.$tag->tid).'" class="btn -tagadelic -level'.$level.'">'.$tag->name.'</a> '._NL;
		}
	} else if (is_numeric($tagIdList) || preg_match('/,/', $tagIdList)) {
		// Show tag topics list
		$para->tag = $tagIdList;
		if (post('prov')) $para->changwat = post('prov');
		$para->items = cfg('paper.promote.items');
		event_tricker('paper.listing.init',$self,$topics,$para);

		$tagDbs = mydb::select('SELECT DISTINCT * FROM %tag% WHERE tid in (:tid) ORDER BY tid ASC', ':tid', 'SET-STRING:'.$tagIdList);

		//$ret .= $tagIdList.mydb()->_query;
		//$ret .= print_o($para,'$para');

		foreach ($tagDbs->items as $tag) {
			$tag_name[$tag->tid]=$tag->name;
			if (!$tag_description) $tag_description=$tag->description;
		}
		if ($tagDbs->_num_rows==1) {
			$para->{'list-style'}= \SG\getFirst($tagDbs->items[0]->liststyle,$para->{'list-style'});
			$para->{'list-class'}=$tagDbs->items[0]->listclass;
			// Show all child tags
			if ($tagDbs->items[0]->process==1) {
				$tagDbs=mydb::select('SELECT DISTINCT tg.* FROM %tag_hierarchy% h LEFT JOIN %tag% tg USING(`tid`) WHERE h.`parent` in ('.$tagIdList.') ORDER BY tg.`tid` ASC');
				$para->tag='';
				foreach ($tagDbs->items as $item) $para->tag.=$item->tid.',';
				$para->tag=trim($para->tag,',');
			}
		}

		$self->theme->title=$self->theme->header->text = SG\getFirst(implode(' , ',$tag_name),'Tags');

		// show page description
		$description = SG\getFirst($tag_description,$topic->type_description);
		if ($description) {
			ob_start();
			eval ('?>'.$description);
			$self->theme->header->description=ob_get_clean();
		}



		if ($tag_name) user_menu('tid',implode(',',$tag_name),url('tags/'.$tagIdList));

		$ret .= '<div class="tag-topics'.($para->{'list-class'} ? ' '.$para->{'list-class'}.' ':'').'">'._NL;
		if (empty($tag_name)) {
			$ret .= message('error','Tag was not define');
		} else {
			$conditions = [
				'tags' => $tagIdList,
				'options' => [
					'debug' => false,
					'field' => 'detail,photo',
					'page' => post('page'),
				],
			];
			$topics = PaperModel::items($conditions);

			// Get Sticky topic on first page of tags
			if (empty($conditions->options->page) || $conditions->options->page == 1) {
				$stickyPara = clone($para);
				$stickyPara->sticky = _CATEGORY_STICKY;
				$stickyPara->options = [
					'field' => 'detail,photo',
					'limit' => cfg('sticky.category.items'),
					'debug' => false,
				];
				$stickys = PaperModel::items($stickyPara);
				// debugMsg($stickys,'$stickys');

				foreach ($topics->items as $key=>$topic) if ($topic->sticky==_CATEGORY_STICKY) unset($topics->items[$key]);
				$topics->items=array_merge($stickys->items,$topics->items);
				$topics->_num_rows=count($topics->items);
				$topics->_empty=$topics->_num_rows<=0;
			}

			$pageCondition = [
				'items' => $para->items,
				'page' => post('page'),
				'total' => $topics->total,
				'url' => q(),
				'cleanUrl' => true,
				'pagePara' => [
					'page' => post('page'),
				]
			];
			$pagenv = PaperModel::pageNavigator($pageCondition);
			$ret .= $pagenv->show._NL;
		}
		if (isset($GLOBALS['ad']->tags_list)) $ret.='<div id="ad-tags_list" class="ads">'.$GLOBALS['ad']->tags_list.'</div>';

		if (debug('method')) $ret.=print_o($para,'$para').print_o($tagDbs,'$tagDbs').print_o($topics,'$topics');
		$ret .= '</div><!-- tag-topics -->'._NL;
	} else {
		$ret .= '<header class="header"><h3>#'.$tagIdList.'</h3></header>';
	}

	BasicModel::member_menu();
	$self->theme->navigator=user_menu();
	return $ret;
}
?>