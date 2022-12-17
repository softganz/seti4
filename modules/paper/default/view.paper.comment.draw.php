<?php
/**
* Module Method
*
* @param
* @return String
*/

$debug = true;

/**
 * Draw comment list
 *
 * @param Integer $tpid
 * @param Object $para
 * @param Boolean $archive
 * @return String $ret
 */
function view_paper_comment_draw($topic=NULL,$para=NULL,$archive=false,$thread=NULL,$header=NULL) {
	$tpid=$topic->tpid;
	if ($topic->comments) {
		$result =$topic->comments;
	} else {
		$page = SG\getFirst($para->page,1);
		$page_items=SG\getFirst(cfg('comment.items'),20);
		$comment_count = mydb::select('SELECT COUNT(*) `amt` FROM %'.($archive?'archive_topic_comments':'topic_comments').'% WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid)->amt;
		$page_count=ceil($comment_count/$page_items);
		if (!isset($para->page) && cfg('comment.page')=='first') {
			$page=1;
		} else if (!isset($para->page) && cfg('comment.page')=='last') {
			$page=$page_count;
		} else if ($page=='last') {
			$page=$page_count;
		} else if ($page=='first') {
			$page=1;
		} else if (intval($page)<0) {
			location('paper/'.$topic->tpid);
		} else if (intval($page)>$page_count) {
			location('paper/'.$topic->tpid.'/page/'.$page_count);
		} else if (is_numeric($page)) {
			// do nothing
		} else {
			// page no was error
			location('paper/'.$topic->tpid);
		}
		$page_start_item=$page_items*($page-1);

		$stmt = 'SELECT c.* , u.`name` as ownername , u.`username`,
													GROUP_CONCAT(DISTINCT p.`file`) photos,
													GROUP_CONCAT(DISTINCT d.`file`) docs
									FROM %'.($archive?'archive_topic_comments':'topic_comments').'% c
										LEFT JOIN %users% as u ON c.uid=u.uid
										LEFT JOIN %'.($archive?'archive_topic_files':'topic_files').'% p ON p.tpid=c.tpid AND p.cid=c.cid AND p.`type`="photo"
										LEFT JOIN %'.($archive?'archive_topic_files':'topic_files').'% d ON d.tpid=c.tpid AND d.cid=c.cid AND d.`type`="doc"
									WHERE c.tpid='.$tpid.($thread?' AND c.`thread`="'.addslashes($thread).'"':'').'
									GROUP BY cid
									ORDER BY c.cid '.cfg('comment.order').'
									LIMIT '.$page_start_item.','.$page_items;
		$result=mydb::select($stmt);

		//$ret.=print_o($result,'$result');
	}

	if ($result->_empty) return;
	$src_url=url('paper/'.$topic->tpid);//url(q());

	if ($header) $ret.=$header;
	$ret .= '<div class="comment">'._NL;
	if ($page_count>1) {
		$page_str = '<div class="comment_page"><span>Page</span>';
		$page_str .= '<a href="'.sg_add_page_url($src_url,1).'">&laquo; first </a>';
		for ($i=1;$i<=$page_count;$i++) {
			$page_str .= $page==$i ? '<strong>'.$i.' </strong>' : '<a href="'.sg_add_page_url($src_url,$i).'">'.$i.' </a>';
		}
		$page_str .= $page>1 ? '<a href="'.sg_add_page_url($src_url,$page-1).'">&lsaquo; back </a>' : '';
		$page_str .= $page<$page_count ? '<a href="'.sg_add_page_url($src_url,$page+1).'">next &rsaquo; </a>' : '';
		$page_str .= $page<$page_count ? '<a href="'.sg_add_page_url($src_url,$page_count).'">last &raquo;</a>' : '';
		$page_str = trim($page_str);
		$page_str .= '</div>'._NL;
		$ret .= $page_str;
	}
	if ($topic->property->option->ads && isset($GLOBALS['ad']->comment_before)) $ret.='<div id="ad-comment_before" class="ads">'.$GLOBALS['ad']->comment_before.'</div>';

	$no=cfg('comment.order')=='ASC'?0:$result->_num_rows+1;

	foreach ($result->items as $rs) {
		if (cfg('comment.order')=='ASC') $no++; else $no--;
		if (in_array($rs->status,array(_DRAFT,_WAITING)) && !(user_access('administer contents,administer comments,administer papers') || ($rs->uid && $rs->uid==i()->uid))) continue;
		if ($rs->status==_BLOCK && !user_access('administer contents,administer comments,administer papers')) continue;
		if ($rs->photos && !$topic->property->option->commentwithphoto) continue;

		$rs->ip = user_access('administer contents,administer comments,administer papers','edit own comment',$rs->uid) ? long2ip($rs->ip) : sg_sub_ip($rs->ip);


		$subclass = $no%2?'odd':'even';
		if (empty($rs->name)) $rs->name=$rs->ownername;

		$ret .= '<!--comment '.$rs->cid.'-->'._NL.'<a name="comment-'.$rs->cid.'"></a>'._NL;
		if ((cfg('comment.order')=='ASC' && $no==$result->_num_rows) || (cfg('comment.order') &&$no==0)) $ret .= '<a name="lastcomment"></a>'._NL;

		$commentHeader = (new ListTile([
			'class' => '-sg-paddingmore',
			'crossAxisAlignment' => 'center',
			'title' => 'Comment #'.($page_start_item+$no).'</span><span>'.$rs->subject,
			'trailing' => user_access('administer contents,administer comments,administer papers','edit own comment',$rs->uid) ? new Row([
				'children' => [
					'<a class="sg-action btn -link" href="'.url('paper/'.$rs->tpid.'/edit.comment/'.$rs->cid).'" data-rel="#message-id-'.$rs->cid.' .message-body" title="Edit comment"><i class="icon -material -grey">edit</i></a>',
					'<a class="sg-action btn -link" href="'.url('paper/info/api/'.$rs->tpid.'/comment.delete/'.$rs->cid).'" data-rel="none" data-done="remove:#comment-id-'.$rs->cid.'" data-title="Delete this comment" data-confirm="Delete this comment. Are you sure?" title="Delete comment"><i class="icon -material">delete</i></a>',
					user_access('administer contents,administer comments,administer papers') ? '<a class="sg-action btn -link" href="'.url('paper/info/api/'.$rs->tpid.'/comment.hide/'.$rs->cid).'" data-rel="refresh" title="Hide this comment"><i class="icon -material -gray">visibility</i></a>' : NULL,
				],
			]) : NULL,
		]))->build();

		$ret .= '<div id="comment-id-'.$rs->cid.'" class="item '.$subclass.'">'._NL;
		$ret .= $commentHeader;

		$member_photo=BasicModel::user_photo($rs->username);

		$ret .= '<div class="owner">';
		$ret .= '<span class="owner-photo"><img class="owner-photo" src="'.$member_photo.'" alt="" /></span>';
		$ret .= '<span class="owner-name">';
		if ($rs->username) $ret .= '<a href="'.url('profile/'.$rs->uid).'">';
		$ret .= $rs->name. ($rs->username ? '' : ' (Not Member)');
		if ($rs->username) $ret .= '</a>';
		$ret .= '</span>';
		$ret .= '</div>'._NL;
		$ret .= '<div class="timestamp">Posted @<span>'.($rs->timestamp?sg_date($rs->timestamp,cfg('dateformat')):'ไม่ระบุวันที่').'</span> <span>ip : '.$rs->ip.'</span></div>'._NL;

		$ret .= '<div class="message" id="message-id-'.$rs->cid.'">'._NL;
		$ret .= R::View('paper.comment.render', $rs);
		$ret .= '</div><!--message-->'._NL;
		$ret .= '<div class="footer">';
		$ret .= '<ul>';
		if ($topic->comment==2 && !$archive && user_access('post comments')) {
			$ret.= '<li><a class="reply" href="javascript:void(0)" onclick="window.location=\''.url('paper/'.$rs->tpid.'/comment/'.$rs->cid,NULL,'form').'\';return false;" title="Reply comment">Reply</a></li>'._NL;
			$ret .= '<li><a class="quote" href="javascript:void(0)" onclick="window.location=\''.url('paper/'.$rs->tpid,'quote='.$rs->cid,'form').'\';return false;" title="Quote comment">Quote</a></li>'._NL;
		}
		if (cfg('email.delete_message')) $ret .= '<li><a class="sg-action" href="'.url('paper/comment/senddelete/'.$rs->cid.'/').' data-rel="box"">แจ้งลบความคิดเห็น</a></li>';
		$ret.='</ul>';
		$ret .= '</div>';

		$ret .= '</div>'._NL.'<!--comment '.$rs->cid.'-->'._NL._NL;
	}

	if ($page_count>1) $ret .= $page_str;
	$ret .= '</div><!--comment-->'._NL;
	return $ret;
}
?>