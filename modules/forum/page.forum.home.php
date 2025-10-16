<?php
/**
* Forum   :: Home
* Created :: 2020-04-03
* Modify  :: 2023-07-26
* Version :: 2
*
* @param Object $self
* @param Int $forumId
* @return String
*/

use Paper\Model\PaperModel;
use Paper\Widget\PaperListWidget;

function forum_home($self, $forumId = NULL) {
	$defaultItems = 50;

	$page = post('page');
	$listStyle = 'table';
	$getOrder = post('order');
	$getSort = post('sort');
	$getItems = \SG\getFirst(post('items'), $defaultItems);
	$types = BasicModel::get_topic_type($self->content_type);

	$self->theme->header->text = tr($types->name);
	$self->theme->header->description = $types->description;
	$self->theme->class = 'content-paper';
	$self->theme->class .= ' paper-content-'.$types->type;

	$orderFieldList = [
		'default' => '`tpid`',
		'view' => '`view`',
		'reply' => '`reply`',
		'last' => '`last_reply`',
	];
	$sortList = ['a' => 'ASC', 'd' => 'DESC'];

	$topicCondition = [
		'type' => 'forum',
		'tags' => $forumId,
		'options' => [
			'items' => $getItems,
			'debug' => false,
			'page' => $page,
			'order' => $getOrder,
			'pagePara' => [
				'ord' => $getOrder,
				'sort' => $getSort,
				'items' => $getItems != $defaultItems ? $getItems : NULL,
			],
		],
	];

	$displayCondition = (Object) [
		'sort' => $getSort ? $getSort : NULL,
		'items' => $getItems != $defaultItems ? $getItems : NULL,
	];

	if (!user_access('access forums')) return message('error','access denied');

	user_menu('home',tr('home'),url());
	user_menu('forum',tr('forum'),url('forum'));

	event_tricker('paper.listing.init',$self,$topics,$topicCondition);

	BasicModel::member_menu();

	if (user_access('create forum content')) user_menu('new',tr('Create new topic'),url('paper/post/forum'));

	$self->theme->navigator = user_menu();

	//content('type','forum');

	if (user_access('create forum content')) {
		$ret .= '<nav class="nav -page -sg-text-right"><a class="btn -primary" href="'.url('paper/post/forum').'"><i class="icon -material">add_circle</i><span>{tr:Post new Forum topic}</span></a></nav>';
	}


	//$ret .= print_o($topicOptions, '$topicOptions');
	//$ret .= print_o($topicCondition, '$topicCondition');


	$vocab = mydb::select('SELECT * FROM %vocabulary_types% WHERE type="forum" LIMIT 1');

	if ($vocab->_num_rows) {
		$tree = BasicModel::get_taxonomy_tree($vocab->vid);
		$topic_count = mydb::select('SELECT tid,COUNT(*) AS topics FROM %tag_topic% WHERE vid = :vid GROUP BY tid', ':vid',$vocab->vid);
		foreach ($topic_count->items as $topic) $topics[$topic->tid] = $topic->topics;
	}

	event_tricker('paper.listing.start',$self,$topics,$topicCondition);

	if ($tree) {
		$tables = new Table();
		$tables->addId('forum-list');
		$tables->thead = array(
			'name' => 'Forum',
			'topics -amt' => 'Topics',
			'posts -amt' => 'Posts',
			'lastpost -date' => 'Last post',
		);

		foreach ($tree as $term) {
			$tables->rows[] = array(
				str_repeat('--', $term->depth).'<a href="'.url('forum/'.$term->tid).'">'.$term->name.'</a>',
				$topics[$term->tid],
				'',
				'',
				'config' => array('class' => count($term->parents) == 1 ? 'containers' : null)
			);
		}
		$ret .= $tables->build();
	}

	$ret .= '<h3>Last post topics</h3>';

	$topics = PaperModel::items($topicCondition);

	$ret .= (new PaperListWidget([
		'id' => 'home-promote',
		'class' => 'promote',
		'listStyle' => $listStyle,
		'url' => q(),
		'order' => $getOrder,
		'headerSortParameter' => ['listStyle' => $listStyle, 'page' => $page, 'items' => $getItems],
		'children' => $topics->items,
	]))->build();


	$ret .= $topics->page->show._NL;

	event_tricker('paper.listing.complete',$self,$topics,$topicCondition);

	return $ret;
}
?>