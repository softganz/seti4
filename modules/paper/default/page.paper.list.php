<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function paper_list($self) {
	$self->para = $para = sg_json_decode(post(),para(func_get_args(),'field='.cfg('paper.listing.field'),'list-style=div','option=na',1));

	$getPage = post('page');

	event_tricker('paper.listing.init',$self,$topics,$para);

	$options = [
		'debug' => false,
		'field' => 'detail,photo',
		'page' => $getPage,
	];

	//$promote = PaperModel::get_topic_by_condition($promote_para);

	//debugMsg($para, '$para');

	$ret .= '<header class="header -box -hidden"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>Paper Listing</h3></header>';

	$ret .= '<form class="search-box" method="get" action="'.url('paper/list').'">'
		. '<input type="text" class="form-text -fill" name="q" value="'.htmlspecialchars(post('q')).'" placeholder="ป้อนหัวข้อที่ต้องการค้นหา" />'
		. '<button class="btn -link" type="submit" name="" value=""><i class="icon -material">search</i></button>'
		. '</form>';

	$topics = R::Model('paper.get.topics',$para, $options);

	//$topics = PaperModel::get_topic_by_condition($para);

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
		if ($para->ip && user_access('access administrator pages')) user_menu('banip','Ban this IP for 1 day',url('api/admin/ip.ban', ['ip' => $para->ip]));

		$self->theme->navigator=user_menu();
	}


	event_tricker('paper.listing.start',$self,$topics,$para);

	if ($para->ip) {
		$self->theme->title='List topic by ip '.$para->ip;
	}
	if ($para->tag && (empty($para->page) || $para->page==1)) {
		$sticky_para->sticky=_CATEGORY_STICKY;
		$sticky_para->tag=$para->tag;
		$sticky_para->field='detail,photo';
		$sticky_para->limit=cfg('sticky.category.items');
		$stickys=PaperModel::get_topic_by_condition($sticky_para);
		foreach ($topics->items as $key=>$topic) if ($topic->sticky==_CATEGORY_STICKY) unset($topics->items[$key]);
		$topics->items=array_merge($stickys->items,$topics->items);
		$topics->_num_rows=count($topics->items);
		$topics->_empty=$topics->_num_rows<=0;
	}

	if (!$para->option->no_page_top) $ret .= $topics->page->show._NL;

	//$ret .= print_o($topics,'$topics');

	switch ($para->{'list-style'}) {
		case 'table' : $ret .= R::View('paper.list.style.table', $self, $topics, $para);break;
		case 'ul' : $ret .= R::View('paper.list.style.ul', $self, $topics, $para);break;
		case 'div' : $ret .= R::View('paper.list.style.div', $self, $topics, $para);break;
		default : $ret .= R::View('paper.list.style.dl', $self, $topics, $para);break;
	}


	if (!$para->option->no_page_bottom) $ret .= $topics->page->show._NL;


	if (is_object($para->option)) $self->theme->option=(object)array_merge((array)$self->theme->option,(array)$para->option);
	//		$ret=print_o($para,'$para').print_o($self->theme,'$theme').$ret;



	event_tricker('paper.listing.complete',$self,$topics,$para);


	if (debug('method')) $ret.=print_o($para,'$para').print_o($topics,'$topics');

	return $ret;
}
?>