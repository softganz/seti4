<?php
function admin_user_blockanddelete($self, $uid) {
	if (!SG\confirm()) return;

	$userInfo = R::Model('user.get', $uid);

	if (!$userInfo->uid) return;
	
	mydb::query(
		'UPDATE %users% SET `status` = "block" WHERE `uid` = :uid LIMIT 1',
		[':uid' => $uid]
	);
	//debugMsg(mydb()->_query);

	mydb::query(
		'DELETE FROM %cache% WHERE `headers` = :username',
		[':username' => $userInfo->username]
	);
	// debugMsg(mydb()->_query);

	$dbs = mydb::select(
		'SELECT `tpid`, `type`, `title`, `created`, `view`, `reply`, `last_reply`
		 FROM %topic%
		 WHERE `uid` = :uid
		 ORDER BY `created` DESC',
		 [':uid' => $uid]
	);
	// debugMsg($dbs,'$dbs');

	foreach ($dbs->items as $rs) {
		// Delete node
		if (in_array($rs->type, ['story', 'page', 'forum'])) {
			$nodeDeleteResult = R::Model('node.delete', $rs->tpid);
			if ($nodeDeleteResult->complete) {
				$ret .= 'Topic '.$rs->tpid.' DELETED<br />';
			}
		}
	}

	R::model('watchdog.log','Admin','User Block','User '.$uid.' was blocked and delete topics.', i()->uid, $uid);
	return $ret;
}
?>