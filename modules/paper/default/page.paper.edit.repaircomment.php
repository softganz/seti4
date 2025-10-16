<?php
/**
* Paper   :: Repair Comment
* Created :: 2019-06-02
* Modify  :: 2024-03-20
* Version :: 2
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

use Paper\Model\PaperModel;

function paper_edit_repaircomment($self, $topicInfo) {
	if (!$topicInfo->nodeId) return message('error', 'PARAMETER ERROR');
	if (!$topicInfo->right->edit) return message('error', 'Access Denied');

	if (!user_access('upload document')) return message('error','Access denied');

	$topicInfo = is_object($topicInfo) ? $topicInfo : PaperModel::get($topicInfo);
	$nodeId = $topicInfo->nodeId;

	$ret = '';

	$ret = '<header class="header -box"><nav class="nav -back"><a class="" href="'.url('paper/'.$nodeId.'/edit').'"><i class="icon -material">arrow_back</i></a></nav><h3>{tr:Repair Comment}</h3></header>';

	mydb::query(
		'UPDATE %topic% t SET
			reply = (SELECT COUNT(*) FROM %topic_comments% c WHERE c.`tpid` = t.`tpid`),
			last_reply = (SELECT MAX(`timestamp`) FROM %topic_comments% lr WHERE lr.`tpid` = t.`tpid`)
		WHERE tpid = :nodeId LIMIT 1',
		[':nodeId' => $nodeId]
	);

	$ret .= 'Repair comment completed.';

	return $ret;
}
?>