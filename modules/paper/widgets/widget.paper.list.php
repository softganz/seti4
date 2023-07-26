<?php
/**
* Widget  :: Paper List Widget
* Created :: 2023-07-25
* Modify  :: 2023-07-25
* Version :: 1
*
* @param Array $args
* @return Widget
*
* @usage import('widget:paper.list.php')
* @usage new PaperListWidget([])
*/

namespace Paper\Widget;

import('model:paper.php');

class PaperListWidget extends \Widget {
	var $widgetName = 'PaperList';
	var $tagName = 'div';
	var $listStyle = 'div';
	var $url;

	function _renderChildren($childrens = [], $args = []) {
		switch ($this->listStyle) {
			case 'table': return $this->listStyleTable(); break;
			case 'dl': return $this->listStyleDl(); break;
			case 'ul': return $this->listStyleUl(); break;
			default: return $this->listStyleDiv(); break;
		}
	}

	function listStyleDiv() {
		$i=$adsCount=0;
		foreach ($this->children as $topic ) {
			++$i;
			if (isset($self)) event_tricker('paper.listing.item',$self,$topic,$para);
			if (empty($topic)) continue;
			if ( empty($topic->poster) ) $topic->poster = $topic->owner;

			$ret .= '<div class="topic-list -style-div '.($para->{'list-class'}?$para->{'list-class'}.' ':'').'topic-list-'.$i.'">'._NL;
			$ret .= '<h3 class="title title-status-'.sg_status_text($topic->status).($topic->sticky==_CATEGORY_STICKY?' sticky':'').'" ><a href="'.url('paper/'.$topic->nodeId).'">'.$topic->title.'</a>'.(in_array($topic->status,array(_PUBLISH,_LOCK))?'':' <em>('.sg_status_text($topic->status).')</em>').'</h3>'._NL;

			if (!$para->option->no_owner) {
				$ret .= '<div class="timestamp'.($topic->sticky==_CATEGORY_STICKY?' sticky':'').'">'.tr('Submitted by').' '.$topic->poster.' on '.sg_date($topic->created,cfg('dateformat'));
				if ($topic->tag) $ret .= ' Tags: '.$topic->tag;
				$ret .= '</div>'._NL;
			}

			// show detail
			if (!$para->option->no_detail) {
				$ret .= '<div class="summary'.($topic->sticky==_CATEGORY_STICKY?' sticky':'').'">'._NL;

				// topic vote
				if (module_install('voteit')) $ret.=do_class_method('voteit','node',$topic,$para);

				if ($topic->photo->exists) {
					$ret .= '<a href="'.url('paper/'.$topic->nodeId).'"><img class="image photo-'.($topic->photo->size->width > $topic->photo->size->height ? 'wide' : 'tall').'"'.' src="'.$topic->photo->url.'" alt="'.htmlspecialchars($topic->photo->title).'" /></a>';
				}
				$ret.=preg_match('/<p>/',$topic->summary)?$topic->summary:'<p>'.$topic->summary.'</p>';
				$ret.='</div>'._NL;
			}

			if (!$para->option->no_footer) {
				$ret .= '<div class="footer'.($topic->sticky==_CATEGORY_STICKY?' sticky':'').'">';
				$ret .= $topic->view.' reads | ';
				$ret .= ($topic->reply ? '<a href="'.url('paper/'.$topic->nodeId).'#comment">'.$topic->reply.' comments</a>':'<a href="'.url('paper/'.$topic->nodeId).'#comment">'.tr('add new comment').'</a>').' | ';
				$ret .= '<a href="'.url('paper/'.$topic->nodeId).'">'.tr('read more').' &raquo;</a>';
				$ret .= '</div>'._NL;
			}
			if (isset($GLOBALS['ad']->topic_list) && ++$adsCount<=3) $ret.='<div id="ad-topic_list" class="ads">'.$GLOBALS['ad']->topic_list.'</div>';
			$ret .= '</div><!--topic-list-->'._NL;

		}
		return $ret;
	}

	function listStyleTable() {
		$single_rows_per_item = false;
		$allcols = $single_rows_per_item ? 6 : 4;
		$hot = 15;
		$veryhot = 25;

		$getOrder = $this->order;

		$arrow = $sort == 'desc' ? '&dArr;' : '&uArr;';
		if (!preg_match('/order\//',$request)) $request.='/order/'.$getOrder;
		if (!preg_match('/sort\//',$request)) $request.='/sort/'.$sort;

		$ret .= '<table class="topic-list -style-table'.($single_rows_per_item?' topic-list-single':'').'" cellspacing="1" cellpadding="0" border="0">
			<thead>
			<tr>
			'
			. ($single_rows_per_item ? '<th class="postdate"><a href="'.url($this->url).'">Post Date'.($getOrder == 'tpid' ? $arrow : '').'</th>':'').'
			<th class="title"><a href="'.url($this->url, $this->headerSortParameter + ['order' => 'title']).'">'.tr('Title').($getOrder == 'title' ? $arrow : '').'</th>
			'
			.($single_rows_per_item?'<th>Post By</th>':'').'
			<th class="view"><a href="'.url($this->url,  $this->headerSortParameter + ['order' => 'read']).'">{tr:Views}'.($getOrder == 'read' ? $arrow : '').'</a></th>
			<th class="reply"><a href="'.url($this->url,  $this->headerSortParameter + ['order' => 'reply']).'">{tr:Replies}'.($getOrder == 'reply' ? $arrow : '').'</a></th>
			<th class="lastreply"><a href="'.url($this->url,  $this->headerSortParameter + ['order' => 'lastComment']).'">{tr:Last reply date}'.($getOrder == 'lastComment' ? $arrow : '').'</a></th>
			</tr>
			</thead>
			<tfoot>
			<tr>
			<td colspan="'.($allcols).'">
				<ul class="title-status">
				<li class="title-status-normal">Normal Topic</li>
				<li class="title-status-hot">Hot Topic (More than 15 replies)</li>
				<li class="title-status-veryhot">Very Hot Topic (More than 25 replies)</li>
				</ul>
			</td>
			</tr>
			</tfoot>'._NL;
		$ret .= '<tbody>'._NL;
		$no=0;
		foreach ($this->children as $topic ) {
			if (isset($self)) event_tricker('paper.listing.item',$self,$topic,$para);
			if (empty($topic)) continue;
			if ( empty($topic->poster) ) $topic->poster = $topic->owner;
			$item_class=($no%2?'odd':'even').($topic->sticky==_CATEGORY_STICKY?' sticky':'');
			$ret .= '<tr class="'.$item_class.'">'._NL;
			if ($single_rows_per_item) $ret.='<td class="timestamp">'.sg_date($topic->created,cfg('dateformat')).'</td>';
			$ret .= '<td '.($single_rows_per_item?'':'colspan="'.($allcols).'" ').'class="title title-status-'.sg_status_text($topic->status).' title-status-'.($topic->reply>$veryhot?'veryhot':($topic->reply>$hot?'hot':'normal')).'" >';
			$ret .= '<a href="'.url('paper/'.$topic->nodeId).'">'.$topic->title.'</a>'.(in_array($topic->status,array(_PUBLISH,_LOCK))?'':' <em>('.sg_status_text($topic->status).')</em>').'</td>';
			if (!$single_rows_per_item) {
				$ret.='</tr>'._NL;
			// if ($topic->pagenv) $ret .= '<tr class="'.$item_class.'"><td colspan="'.($allcols-1).'">'.$topic->pagenv.'</td></tr>';
				$ret .= '<tr class="'.$item_class.'">'._NL;
			}

			$poster='';
			if ($topic->uid && user_access('access user profiles')) $poster.='<a href="'.url('profile/'.$topic->uid).'">';
			$poster.='<span class="poster'.(i()->ok && $topic->uid==i()->uid ? ' owner' : '').'">'.($single_rows_per_item?'':'by ').$topic->poster.'</span>';
			if ($topic->uid && user_access('access user profiles')) $poster.='</a>';
			if (!$single_rows_per_item) $poster.='<span class="timestamp"> @'.sg_date($topic->created,cfg('dateformat')).'</span>';

			$ret .= '<td class="poster">'.$poster.'</td>'._NL;

			$ret .= '<td class="stat stat-view">'.$topic->view.'</td>';
			$ret .= '<td class="stat stat-reply">'.($topic->reply?$topic->reply:'-').'</td>';
			$ret .= '<td class="timestamp">'.($topic->reply?sg_date($topic->lastReply,cfg('dateformat')):'').'</td>'._NL;
			$ret .= '</tr>'._NL;

			$comment_page_items = \SG\getFirst(cfg('comment.items'),20);
			if ($topic->comments>1 && ($page_count=ceil($topic->comments/$comment_page_items))>1) {
				$page_str = '<span>Page</span>';
				for ($i=1;$i<=$page_count;$i++) {
					$page_str .= '<a href="'.url('paper/'.$topic->nodeId.'/page/'.$i).'">'.$i.' </a>';
				}
				$page_str .= '<a href="'.url('paper/'.$topic->nodeId.'/page/'.$page_count).'">last &raquo;</a>';
				$page_str = trim($page_str);
				$ret.='<tr class="'.$item_class.' comment_page">';
				$ret.=($single_rows_per_item?'<td></td><td colspan="2">':'<td>').$page_str.'</td>';
				$ret.='<td colspan="'.($allcols-($single_rows_per_item?3:1)).'"></td></tr>'._NL;
			}
			$ret.=_NL;
			$no++;
		}
		$ret .= '</tbody>'._NL;
		$ret .= '</table>'._NL;
		return $ret;
	}

	function listStyleDl() {
		$ret .= '<dl class="topic-list -style-dl">'._NL;

		foreach ($this->children as $topic ) {
			if (isset($self)) event_tricker('paper.listing.item',$self,$topic,$para);
			if (empty($topic)) continue;
			if ( empty($topic->poster) ) $topic->poster = $topic->owner;

			$ret .= '<dt class="title title-status-'.sg_status_text($topic->status).($topic->sticky==_CATEGORY_STICKY?' sticky':'').'" ><a href="'.url('paper/'.$topic->nodeId).'">'.$topic->title.'</a>'.(in_array($topic->status,array(_PUBLISH,_LOCK))?'':' <em>('.sg_status_text($topic->status).')</em>').'</dt>'._NL;

			if (!$para->option->no_owner) {
				$ret .= '<dd class="timestamp'.($topic->sticky==_CATEGORY_STICKY?' sticky':'').'">'.tr('Submitted by').' '.$topic->poster.' on '.sg_date($topic->created,cfg('dateformat'));
				if ($topic->tag) $ret .= ' Tags: '.$topic->tag;
				$ret .= '</dd>'._NL;
			}

			// show detail
			if (!$para->option->no_detail) {
				$ret .= '<dd class="summary'.($topic->sticky==_CATEGORY_STICKY?' sticky':'').'">'._NL;

				// topic vote
				if (module_install('voteit')) $ret.=do_class_method('voteit','node',$topic,$para);

				if ($topic->photo->exists) {
					$ret .= '<a href="'.url('paper/'.$topic->nodeId).'"><img class="image photo-'.($topic->photo->size->width > $topic->photo->size->height ? 'wide' : 'tall').'"'.' src="'.$topic->photo->url.'" alt="'.htmlspecialchars($topic->photo->title).'" /></a>';
				}
				$topic->summary=str_replace('<!--read more-->',' <a href="'.url('paper/'.$topic->nodeId).'">('.tr('read more').'...)</a>',$topic->summary);
				$ret.=$topic->summary.'</dd>'._NL;
			}

			if (!$para->option->no_footer) {
				$ret .= '<dd class="footer'.($topic->sticky==_CATEGORY_STICKY?' sticky':'').'">';
				$ret .= $topic->view.' reads | ';
				$ret .= ($topic->reply ? '<a href="'.url('paper/'.$topic->nodeId).'#comment">'.$topic->reply.' comments</a>':'<a href="'.url('paper/'.$topic->nodeId).'#comment">'.tr('add new comment').'</a>').' | ';
				$ret .= '<a href="'.url('paper/'.$topic->nodeId).'">'.tr('read more').' &raquo;</a>';
				$ret .= '</dd>'._NL._NL;
			}
		}
		$ret .= '</dl>'._NL;
		return $ret;
	}

	/**
	 * List topic ul style
	 *
	 */
	function listStyleUl() {
		$ret = '<ul '.($para->id?'id="'.$para->id.'" ':'').'class="topic-list -style-ul">'._NL;
		foreach ($this->children as $topic) {
			$photo_str=$para->photo && $topic->photo?'<img class="'.$para->photo.'" src="'.$topic->photo->url.'" alt="" />':'';
			$ret .= '<li><a class="title" href="'.url('paper/'.$topic->nodeId).'">'.($para->photo && $photo_str ? $photo_str:'').$topic->title.'</a>';
			$ret .= '<span class="poster"> by '.\SG\getFirst($topic->poster,$topic->owner).'</span>';
			$ret .= '<span class="time_stamp">@'.sg_date($topic->created,cfg('dateformat')).'</span>';
			$ret .= '<span class="stat"> | '.$topic->view.' reads'.($topic->reply?' | <strong>'.$topic->reply.'</strong> comment(s)':'').'</span>';
			if ($para->option->detail) $ret .= _NL.'<p class="summary">'.$topic->summary.'</p>'._NL;
			$ret .= '</li>'._NL;
		}
		$ret .= '</ul>'._NL;
		return $ret;
	}
}
?>