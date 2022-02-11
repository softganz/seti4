<?php
/**
* Module Method
*
* @param 
* @return String
*/

$debug = true;

/**
 * List topic dl style
 *
 * @param Object $topics
 * @param Mixed $para
 * @return String
 */
function view_paper_list_style_dl($self, $topics,$para) {
	$ret .= '<dl class="topic-list -style-dl">'._NL;

	foreach ($topics->items as $topic ) {
		if (isset($self)) event_tricker('paper.listing.item',$self,$topic,$para);
		if (empty($topic)) continue;
		if ( empty($topic->poster) ) $topic->poster = $topic->owner;

		$ret .= '<dt class="title title-status-'.sg_status_text($topic->status).($topic->sticky==_CATEGORY_STICKY?' sticky':'').'" ><a href="'.url('paper/'.$topic->tpid).'">'.$topic->title.'</a>'.(in_array($topic->status,array(_PUBLISH,_LOCK))?'':' <em>('.sg_status_text($topic->status).')</em>').'</dt>'._NL;

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

			if ($topic->photo->_exists) {
				$ret .= '<img class="image photo-'.($topic->photo->_size->width>$topic->photo->_size->height?'wide':'tall').'"'.' src="'.$topic->photo->_src.'" alt="'.htmlspecialchars($topic->photo->title).'" />';
			}
			$topic->summary=str_replace('<!--read more-->',' <a href="'.url('paper/'.$topic->tpid).'">('.tr('read more').'...)</a>',$topic->summary);
			$ret.=$topic->summary.'</dd>'._NL;
		}

		if (!$para->option->no_footer) {
			$ret .= '<dd class="footer'.($topic->sticky==_CATEGORY_STICKY?' sticky':'').'">';
			$ret .= $topic->view.' reads | ';
			$ret .= ($topic->reply ? '<a href="'.url('paper/'.$topic->tpid).'#comment">'.$topic->reply.' comments</a>':'<a href="'.url('paper/'.$topic->tpid).'#comment">'.tr('add new comment').'</a>').' | ';
			$ret .= '<a href="'.url('paper/'.$topic->tpid).'">'.tr('read more').' &raquo;</a>';
			$ret .= '</dd>'._NL._NL;
		}
	}
	$ret .= '</dl>'._NL;
	return $ret;
}
?>