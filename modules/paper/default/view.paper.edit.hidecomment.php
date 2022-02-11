<?php
/**
* View Paper Hide Comment
*
* @param Object $topicInfo
* @param Int $commentId
* @return String
*/

$debug = true;

function view_paper_edit_hidecomment($topicInfo, $commentId) {
	//$ret .= '$commentId = '.$commentId.'<br />';
	$result = R::Model('paper.comment.status.toggle', $commentId);

	$rs = paper_model::get_comment_by_id($commentId);

	$ret .= R::View('paper.comment.render',$rs);

	model::watch_log('paper','Paper comment '.$rs->status,'Comment '.$rs->cid.' of <a href="'.url('paper/'.$rs->tpid.'#comment-'.$rs->cid).'">paper/'.$rs->tpid.'</a> was '.sg_status_text($rs->status));

	//$ret .= print_o($rs,'$rs');
	// $ret .= 'Status = '.$result;
	return $ret;
}
?>