<?php
/**
* Module Method
* Created 2020-04-03
* Modify  2020-04-03
*
* @param Object $self
* @param Int $forumId
* @return String
*/

$debug = true;

function forum_home($self, $forumId = NULL) {
	$defaultItems = 50;

	$getOrder = post('ord');
	$getSort = post('sort');
	$getItems = \SG\getFirst(post('items'), $defaultItems);
	$types = BasicModel::get_topic_type($self->content_type);

	$self->theme->header->text = tr($types->name);
	$self->theme->header->description = $types->description;
	$self->theme->class = 'content-paper';
	$self->theme->class .= ' paper-content-'.$types->type;

	$orderFieldList = array(
		'default' => '`tpid`',
		'view' => '`view`',
		'reply' => '`reply`',
		'last' => '`last_reply`',
	);
	$sortList = array('a' => 'ASC', 'd' => 'DESC');

	$topicCondition = new stdClass();
	$topicCondition->type = 'forum';
	$topicCondition->tag = $forumId;

	$topicOptions = new stdClass();
	$topicOptions->items = $getItems;
	$topicOptions->debug = false;
	$topicOptions->page = post('page');
	$topicOptions->order = \SG\getFirst($orderFieldList[$getOrder], $orderFieldList['default']);
	$topicOptions->sort = \SG\getFirst($sortList[$getSort], $sortList['d'] );
	$topicOptions->pagePara = array();
	$topicOptions->pagePara['ord'] = $getOrder;
	$topicOptions->pagePara['sort'] = $getSort;
	if ($getItems != $defaultItems) $topicOptions->pagePara['items'] = $getItems;

	$displayCondition = new stdClass();
	if ($getSort) $displayCondition->sort = $getSort;
	if ($getItems != $defaultItems) $displayCondition->items = $getItems;

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

	$topics = R::Model('paper.get.topics',$topicCondition, $topicOptions);

	$ret .= R::View('paper.list.style.table', $self, $topics, $displayCondition);

	$ret .= $topics->page->show._NL;

	event_tricker('paper.listing.complete',$self,$topics,$topicCondition);

	return $ret;
}
?>