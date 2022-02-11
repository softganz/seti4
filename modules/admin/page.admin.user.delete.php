<?php
// delete user information
function admin_user_delete($self,$uid) {
	if (SG\confirm()) {
		$rs = R::Model('user.get', $uid);

		$uid = $rs->uid;

		if (!$uid) return message('error','User <em>'.$uid.'</em> not exists.');

		if ($uid == 1) $error[] = 'User was lock';
		if ($rs->status == 'enable') $error[] = 'User was active';
		if (count($rs->roles) > 1) $error[] = 'User was in group';

		if ($error) $ret .= message('error','This user was not delete by reason : <ul><li>'.implode('</li><li>',$error).'</li></ul>');
		else {
			mydb::query(
				'DELETE FROM %users% WHERE `uid` = :uid LIMIT 1',
				[':uid' => $uid]
			);

			mydb::query(
				'DELETE FROM %topic_user% WHERE `uid` = :uid',
				[':uid' => $uid]
			);

			if (mydb::table_exists('org_officer')) {
				mydb::query(
					'DELETE FROM %org_officer% WHERE `uid` = :uid',
					[':uid' => $uid]
				);
			}


			location('admin/user/list');
		}
	}
	return $ret;
}
?>