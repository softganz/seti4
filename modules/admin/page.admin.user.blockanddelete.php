<?php
function admin_user_blockanddelete($self, $uid) {
	$userInfo = R::Model('user.get', $uid);

	if (!$userInfo->uid) return;
	
	$stmt = 'UPDATE %users% SET `status` = "block" WHERE `uid` = :uid LIMIT 1';
	mydb::query($stmt,':uid',$uid);
	//debugMsg(mydb()->_query);

	$stmt = 'DELETE FROM %cache% WHERE `headers` = :username';
	mydb::query($stmt,':username',$userInfo->username);
	//debugMsg(mydb()->_query);

	$dbs = mydb::select('SELECT `tpid`, `title`, `created`, `view`, `reply`, `last_reply`	 FROM %topic% WHERE `uid` = :uid ORDER BY `created` DESC',':uid',$uid);
	//$ret.=print_o($dbs,'$dbs');

	foreach ($dbs->items as $rs) {
		$ret.=R::Model('topic.delete',$rs->tpid);
	}

	R::model('watchdog.log','Admin','User Block','User '.$uid.' was blocked and delete topics.', i()->uid, $uid);
	return $ret;
}
?>