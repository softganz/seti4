<?php
/**
 * Remove owner from project
 *
 * @param Integer $tpid
 * @param Integer $uid
 * @return String
 */

import('model:org.php');

function project_edit_removeowner($self, $tpid, $uid) {
	$topicMember = $tpid && i()->ok ? R::Model('paper.membership.get',$tpid,i()->uid) : NULL;
	$orgId = mydb::select('SELECT `orgid` FROM %topic% WHERE `tpid` = :tpid LIMIT 1',':tpid',$tpid)->orgid;
	$orgMember = $orgId && i()->ok ? OrgModel::officerType($orgId,i()->uid) : NULL;

	$isEditable = user_access('administer projects')
		|| in_array($orgMember, array('MANAGER','ADMIN','OWNER','TRAINER'))
		|| in_array($topicMember, array('MANAGER','ADMIN','OWNER','TRAINER'));


	if (!($isEditable)) return 'Access denied';

	if ($tpid && $uid && SG\confirm()) {
		mydb::query('DELETE FROM %topic_user% WHERE `tpid`=:tpid AND `uid`=:uid LIMIT 1',':tpid',$tpid, ':uid',$uid);
		$ret='ลบสมาชิกออกจากโครงการเรียบร้อย';
		model::watch_log('project','remove owner','User id '.$uid.' was removed from project '.$tpid.' by '.i()->name.'('.i()->uid.')');
	}
	return $ret;
}
?>