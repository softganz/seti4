<?php
/**
* Module Method
*
* @param 
* @return String
*/

$debug = true;

/**
 * List topic div style
 *
 * @param Object $topics
 * @param Mixed $para
 * @return String
 */
function view_paper_list_style_div($self, $topics, $para) {
	$i=$adsCount=0;
	foreach ($topics->items as $topic ) {
		++$i;
		if (isset($self)) event_tricker('paper.listing.item',$self,$topic,$para);
		if (empty($topic)) continue;
		if ( empty($topic->poster) ) $topic->poster = $topic->owner;

		$ret .= '<div class="topic-list -style-div '.($para->{'list-class'}?$para->{'list-class'}.' ':'').'topic-list-'.$i.'">'._NL;
		$ret .= '<h3 class="title title-status-'.sg_status_text($topic->status).($topic->sticky==_CATEGORY_STICKY?' sticky':'').'" ><a href="'.url('paper/'.$topic->tpid).'">'.$topic->title.'</a>'.(in_array($topic->status,array(_PUBLISH,_LOCK))?'':' <em>('.sg_status_text($topic->status).')</em>').'</h3>'._NL;

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

			if ($topic->photo->_exists) {
				$ret .= '<a href="'.url('paper/'.$topic->tpid).'"><img class="image photo-'.($topic->photo->_size->width>$topic->photo->_size->height?'wide':'tall').'"'.' src="'.$topic->photo->_src.'" alt="'.htmlspecialchars($topic->photo->title).'" /></a>';
			}
			$ret.=preg_match('/<p>/',$topic->summary)?$topic->summary:'<p>'.$topic->summary.'</p>';
			$ret.='</div>'._NL;
		}

		if (!$para->option->no_footer) {
			$ret .= '<div class="footer'.($topic->sticky==_CATEGORY_STICKY?' sticky':'').'">';
			$ret .= $topic->view.' reads | ';
			$ret .= ($topic->reply ? '<a href="'.url('paper/'.$topic->tpid).'#comment">'.$topic->reply.' comments</a>':'<a href="'.url('paper/'.$topic->tpid).'#comment">'.tr('add new comment').'</a>').' | ';
			$ret .= '<a href="'.url('paper/'.$topic->tpid).'">'.tr('read more').' &raquo;</a>';
			$ret .= '</div>'._NL;
		}
		if (isset($GLOBALS['ad']->topic_list) && ++$adsCount<=3) $ret.='<div id="ad-topic_list" class="ads">'.$GLOBALS['ad']->topic_list.'</div>';
		$ret .= '</div><!--topic-list-->'._NL;
		$ret .= '<div class="items-end topic-list-'.$i.'-end"></div>'._NL._NL;

	}
	return $ret;
}
?>