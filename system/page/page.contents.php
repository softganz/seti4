<?php
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

function contents($self, $contentNameList = NULL) {
		$self->para=$para=para(func_get_args(),'field='.cfg('paper.listing.field'),'list-style=div');
		$para->type=$contentNameList;
		$types=model::get_topic_type($contentNameList);

		event_tricker('paper.listing.init',$self,$topics,$para);

		//$topics = R::Model('paper.get.topics',$para);

		$options = (Object) [
			'debug' => false,
			'field' => 'detail,photo',
			'page' => post('page'),
		];

		$topics = R::Model('paper.get.topics',$para, $options);

		//debugMsg($topics,'$topics');
		//content('type',$contentNameList);

		$self->theme->class='content-paper';
		$self->theme->class.=' paper-content-'.SG\getFirst($contentNameList);
		$self->theme->header->text=SG\getFirst($types->name);
		if ($types->description) {
			ob_start();
			eval ('?>'.$types->description);
			$self->theme->header->description=ob_get_clean();
		}

		user_menu('home','Home',url());
		user_menu('type',$types->name,url('contents/'.$types->type));
		model::member_menu();
		if ($topics->forum->cid && ($topics->forum->public==1 ||
			($topics->forum->public==2 && i()->ok) ||
			user_access('administer contents,administer papers,create '.$topics->forum->fid.' paper'))) {
			user_menu('new','Create new topic',url('paper/post/category/'.$topics->forum->cid));
		} else if ($topics->forum->fid && user_access('administer contents,administer papers,create '.$topics->forum->fid.' paper')) {
			user_menu('new','Create new topic',url('paper/post/forum/'.$topics->forum->fid));
		}

		$self->theme->navigator=user_menu();
		head('<meta name="robots" content="noindex,nofollow">');

		event_tricker('paper.listing.start',$self,$topics,$para);

		if ($para->category && (empty($para->page) || $para->page==1)) {
			$sticky_para->sticky=_CATEGORY_STICKY;
			$sticky_para->category=$para->category;
			$sticky_para->limit=cfg('sticky.category.items');
			$stickys=paper_model::get_topic_by_condition($sticky_para);
			foreach ($topics->items as $key=>$topic) if ($topic->sticky==_CATEGORY_STICKY) unset($topics->items[$key]);
			$topics->items=array_merge($stickys->items,$topics->items);
			$topics->_num_rows=count($topics->items);
			$topics->_empty=$topics->_num_rows<=0;
		}

		$ret .= $topics->page->show._NL;
		switch ($para->{'list-style'}) {
			case 'table' : $ret .= view::list_style_table($topics,$para);break;
			case 'ul' : $ret .= view::list_style_ul($topics,$para);break;
			case 'dl' : $ret .= view::list_style_dl($topics,$para);break;
			default : $ret .= view::list_style_div($topics,$para);break;
		}

		if (!$para->option->no_page && $topics->page->show) $ret .= $topics->page->show._NL;


		event_tricker('paper.listing.complete',$self,$topics,$para);
		if (debug('method')) $ret.=print_o($para,'$para').print_o($topics,'$topics');
	return $ret;
}
?>