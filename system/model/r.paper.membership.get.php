<?php
function r_paper_membership_get($tpid, $uid = NULL) {
	$members = NULL;
	if ($tpid && $uid) {
		// Get owner or membership for user of tpid
		$stmt = 'SELECT "OWNER" `membership`
					FROM %topic% WHERE `tpid` = :tpid AND `uid` = :uid
					UNION
					SELECT `membership`
					FROM %topic_user%
					WHERE `tpid` = :tpid AND `uid` = :uid
					LIMIT 1';
		$topicMemberShip = mydb::select($stmt,':tpid',$tpid, ':uid',$uid)->membership;
		$members = empty($topicMemberShip) ? false : strtoupper($topicMemberShip);
	} else if ($uid && empty($tpid)) {
		// Get all member ship of user
		$stmt='SELECT `tpid`,UPPER(`membership`) `membership`
					FROM %topic_user%
					WHERE `uid`=:uid
					';
		$dbs=mydb::select($stmt, ':uid',$uid);
		if ($dbs->_num_rows) {
			$members->count=$dbs->_num_rows;
			foreach ($dbs->items as $rs) {
			 	$members->items[$rs->tpid]=$rs->membership;
			 }
		}
	} else if ($tpid) {
		// Get all membership of tpid
		$stmt='SELECT
						tu.`tpid`
						, tu.`uid`
						, UPPER(tu.`membership`) `membership`
						, u.`username`
						, u.`name`
						, u.`datein`
						FROM %topic_user% tu
							LEFT JOIN %users% u USING(`uid`)
							WHERE tu.`tpid`=:tpid;
						';
		$dbs=mydb::select($stmt,':tpid',$tpid);
		if ($dbs->_num_rows) {
			$members->count=$dbs->_num_rows;
			$members->items=$dbs->items;
		}
	}
	return $members;
}
?>