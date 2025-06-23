<?php
/**
* Paper   :: View Paper Hide Comment
* Modify  :: 2025-06-23
* Version :: 3
*
* @param Object $topicInfo
* @param Int $commentId
* @return String
*/

use Paper\Model\PaperModel;

function view_paper_edit_hidecomment($topicInfo, $commentId) {
	//$ret .= '$commentId = '.$commentId.'<br />';
	$result = R::Model('paper.comment.status.toggle', $commentId);

	$rs = NodeModel::getCommentById($commentId);

	$ret .= R::View('paper.comment.render',$rs);

	LogModel::save([
		'module' => 'paper',
		'keyword' => 'Paper comment '.$rs->status,
		'message' => 'Comment '.$rs->cid.' of <a href="'.url('paper/'.$rs->tpid.'#comment-'.$rs->cid).'">paper/'.$rs->tpid.'</a> was '.sg_status_text($rs->status)
	]);

	//$ret .= print_o($rs,'$rs');
	// $ret .= 'Status = '.$result;
	return $ret;
}
?>