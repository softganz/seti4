<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function paper_upcomming($self) {
	$self->para=$para=para(func_get_args(),'field='.cfg('paper.listing.field'),'list-style=dl','type=story','condition=promote<>1',1);
	event_tricker('paper.listing.init',$self,$topics,$para);
	$topics=PaperModel::get_topic_by_condition($para);

	$self->theme->class='content-paper';
	if ($para->tag) $self->theme->class.=' paper-tag-'.$para->tag;
	if ($para->category||$topics->forum->cid) $self->theme->class.=' paper-category-'.\SG\getFirst($para->category,$topics->forum->cid);
	if (!$para->option->no_header) {
		$self->theme->header->text = SG\getFirst($topics->forum->category,$topics->forum->forum,'Up Comming');
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

		$self->theme->navigator=user_menu();
	}

	event_tricker('paper.listing.start',$self,$topics,$para);

	if (!$para->option->no_page_top) $ret .= $topics->page->show._NL;
	$ret .= R::View('paper.list.style.dl', $self, $topics, $para);

	if (!$para->option->no_page_bottom) $ret .= $topics->page->show._NL;

	$self->theme->option=$para->option;

	event_tricker('paper.listing.complete',$self,$topics,$para);
	if (debug('method')) $ret.=print_o($para,'$para').print_o($topics,'$topics');
	return $ret;
}
?>