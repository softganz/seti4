<?php
/**
* Lock/Unlock paper comment status
*
* @param Int $commentId
* @return Object $options
*/

$debug = true;

function r_paper_comment_status_toggle($commentId) {
	mydb::value('$PUBLISH', _PUBLISH);
	mydb::value('$BLOCK', _BLOCK);
	mydb::where('`cid` = :cid',  ':cid', intval($commentId));
	$stmt = 'UPDATE %topic_comments%
					SET `status` = IF(`status` = $PUBLISH , $BLOCK , $PUBLISH )
					%WHERE%
					LIMIT 1';

	mydb::query($stmt);
	//debugMsg(mydb()->_query);

	$result = mydb::select('SELECT `status` FROM %topic_comments% WHERE `cid` = :cid LIMIT 1', ':cid', intval($commentId))->status;
	return $result;
}
?>