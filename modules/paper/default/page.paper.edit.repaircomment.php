<?php
/**
* Repair Comment
* Created 2019-06-02
* Modify  2019-06-02
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

$debug = true;

function paper_edit_repaircomment($self, $topicInfo) {
	if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');

	if (!user_access('upload document')) return message('error','Access denied');

	$topicInfo = is_object($tpid) ? $tpid : R::Model('paper.get',$tpid);
	$tpid = $topicInfo->tpid;

	$ret = '';

	$ret = '<header class="header -box"><nav class="nav -back"><a class="" href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material">arrow_back</i></a></nav><h3>{tr:Repair Comment}</h3></header>';

	$stmt = 'UPDATE %topic% t SET
			reply = (SELECT COUNT(*) FROM %topic_comments% c WHERE c.`tpid` = t.`tpid`),
			last_reply = (SELECT MAX(`timestamp`) FROM %topic_comments% lr WHERE lr.`tpid` = t.`tpid`)
		WHERE tpid = :tpid LIMIT 1';
	mydb::query($stmt, ':tpid', $tpid);

	$ret .= 'Repair comment completed.';

	return $ret;
}
?>