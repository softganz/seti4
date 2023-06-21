<?php
/**
* Module Method
*
* @param 
* @return String
*/

$debug = true;

/**
 * List topic ul style
 *
 * @param Object $topics
 * @param Mixed $para
 * @return String
 */
function view_paper_list_style_ul($self, $topics, $para) {
	$ret = '<ul '.($para->id?'id="'.$para->id.'" ':'').'class="topic-list -style-ul">'._NL;
	foreach ($topics->items as $topic) {
		$photo_str=$para->photo && $topic->photo?'<img class="'.$para->photo.'" src="'.$topic->photo->_url.'" alt="" />':'';
		$ret .= '<li><a class="title" href="'.url('paper/'.$topic->tpid).'">'.($para->photo&&$photo_str?$photo_str:'').$topic->title.'</a>';
		$ret .= '<span class="poster"> by '.\SG\getFirst($topic->poster,$topic->owner).'</span>';
		$ret .= '<span class="time_stamp">@'.sg_date($topic->created,cfg('dateformat')).'</span>';
		$ret .= '<span class="stat"> | '.$topic->view.' reads'.($topic->reply?' | <strong>'.$topic->reply.'</strong> comment(s)':'').'</span>';
		if ($para->option->detail) $ret .= _NL.'<p class="summary">'.$topic->summary.'</p>'._NL;
		$ret .= '</li>'._NL;
	}
	$ret .= '</ul>'._NL;
	return $ret;
}
?>