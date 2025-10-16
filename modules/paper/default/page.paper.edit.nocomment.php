<?php
/**
* Paper   :: Enable/Disable Comment
* Created :: 2019-06-02
* Modify  :: 2024-03-20
* Version :: 2
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

$debug = true;

function paper_edit_nocomment($self, $topicInfo) {
	if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');
	if (!$topicInfo->right->edit) return message('error', 'Access Denied');

	$tpid = $topicInfo->tpid;

	$ret = '<header class="header -box"><nav class="nav -back"><a class="" href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material">arrow_back</i></a></nav><h3>Enable/Disable Comment</h3></header>';


	$stmt = 'UPDATE %topic% SET comment = IF(comment = 0,2,0) WHERE tpid = :tpid LIMIT 1';

	mydb::query($stmt, ':tpid', $tpid);

	location('paper/'.$tpid);

	return $ret;
}
?>