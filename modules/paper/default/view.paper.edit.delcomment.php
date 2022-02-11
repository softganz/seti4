<?php
/**
* Delete comment
*
* @param Integer /delete/comment id
* @param String /confirm/yes|no
* @return String
*/

$debug = true;


function view_paper_edit_delcomment($topicInfo, $commentId) {
	$result = R::Model('paper.comment.delete',$commentId);
	$ret .= $commentId.print_o($result, '$result');
	return $ret;

	return $ret;
}
?>