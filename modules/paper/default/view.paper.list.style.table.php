<?php
/**
* Module Method
*
* @param 
* @return String
*/

$debug = true;

/**
 * List topic table style
 *
 * @param Object $topics
 * @param Mixed $para
 * @return String
 */
function view_paper_list_style_table($self, $topics, $para) {
	$single_rows_per_item = false;
	$allcols = $single_rows_per_item ? 6 : 4;
	$hot = 15;
	$veryhot = 25;

	$getOrder = \SG\getFirst(post('ord'),'tpid');
	$sort = in_array(strtoupper($para->sort),array('ASC','DESC')) ? $para->sort : NULL;

	$urlRequest = q();
	$arrow = $sort == 'desc' ? '&dArr;' : '&uArr;';
	if (!preg_match('/order\//',$request)) $request.='/order/'.$getOrder;
	if (!preg_match('/sort\//',$request)) $request.='/sort/'.$sort;

	$reg='/order\/[\w]*\//i';
	$hurl[1]=preg_replace($reg,'order/tpid/',$request);
	$hurl[2]=preg_replace($reg,'order/reply/',$request);
	$hurl[3]=preg_replace($reg,'order/view/',$request);
	$hurl[4]=preg_replace($reg,'order/last_reply/',$request);
	$current_url=preg_replace('/sort\/(asc|desc)/i','sort/'.($sort=='desc'?'asc':'desc'),$request);

	//$ret .= print_o($para, '$paraTable');

	$ret .= '<table class="topic-list -style-table'.($single_rows_per_item?' topic-list-single':'').'" cellspacing="1" cellpadding="0" border="0">
<thead>
<tr>
'
. ($single_rows_per_item ? '<th class="postdate"><a href="'.url($urlRequest,array()).'">Post Date'.($getOrder == 'tpid' ? $arrow : '').'</th>':'').'
<th class="title"><a href="'.url($urlRequest,array('sort' => $para->sort, 'items' => $para->items)).'">'.tr('Title').($getOrder == 'tpid' ? $arrow : '').'</th>
'
.($single_rows_per_item?'<th>Post By</th>':'').'
<th class="view"><a href="'.url($urlRequest,array('ord' => 'view', 'sort' => $para->sort, 'items' => $para->items)).'">{tr:Views}'.($getOrder == 'view' ? $arrow : '').'</a></th>
<th class="reply"><a href="'.url($urlRequest,array('ord' => 'reply', 'sort' => $para->sort, 'items' => $para->items)).'">{tr:Replies}'.($getOrder == 'reply' ? $arrow : '').'</a></th>
<th class="lastreply"><a href="'.url($urlRequest,array('ord' => 'last', 'sort' => $para->sort, 'items' => $para->items)).'">{tr:Last reply date}'.($getOrder == 'last' ? $arrow : '').'</a></th>
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
	foreach ($topics->items as $topic ) {
		if (isset($self)) event_tricker('paper.listing.item',$self,$topic,$para);
		if (empty($topic)) continue;
		if ( empty($topic->poster) ) $topic->poster = $topic->owner;
		$item_class=($no%2?'odd':'even').($topic->sticky==_CATEGORY_STICKY?' sticky':'');
		$ret .= '<tr class="'.$item_class.'">'._NL;
		if ($single_rows_per_item) $ret.='<td class="timestamp">'.sg_date($topic->created,cfg('dateformat')).'</td>';
		$ret .= '<td '.($single_rows_per_item?'':'colspan="'.($allcols).'" ').'class="title title-status-'.sg_status_text($topic->status).' title-status-'.($topic->reply>$veryhot?'veryhot':($topic->reply>$hot?'hot':'normal')).'" >';
		$ret .= '<a href="'.url('paper/'.$topic->tpid).'">'.$topic->title.'</a>'.(in_array($topic->status,array(_PUBLISH,_LOCK))?'':' <em>('.sg_status_text($topic->status).')</em>').'</td>';
		if (!$single_rows_per_item) {
			$ret.='</tr>'._NL;
//				if ($topic->pagenv) $ret .= '<tr class="'.$item_class.'"><td colspan="'.($allcols-1).'">'.$topic->pagenv.'</td></tr>';
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
		$ret .= '<td class="timestamp">'.($topic->reply?sg_date($topic->last_reply,cfg('dateformat')):'').'</td>'._NL;
		$ret .= '</tr>'._NL;

		$comment_page_items = SG\getFirst(cfg('comment.items'),20);
		if ($topic->comments>1 && ($page_count=ceil($topic->comments/$comment_page_items))>1) {
			$page_str = '<span>Page</span>';
			for ($i=1;$i<=$page_count;$i++) {
				$page_str .= '<a href="'.url('paper/'.$topic->tpid.'/page/'.$i).'">'.$i.' </a>';
			}
			$page_str .= '<a href="'.url('paper/'.$topic->tpid.'/page/'.$page_count).'">last &raquo;</a>';
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
?>