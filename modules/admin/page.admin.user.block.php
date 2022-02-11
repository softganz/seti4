<?php
/**
* Admin : Block/UnBlock User
* Created 2019-05-06
* Modify  2020-09-16
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage admin/user/block/{$Id}
*/

$debug = true;

function admin_user_block($self, $uid) {
	$userInfo = R::Model('user.get', $uid);

	if (!$userInfo->uid || !SG\confirm()) return;

	$status = $userInfo->status == 'block' ? 'enable' : 'block';

	// Delete cache when block or roles change
	mydb::query('UPDATE %users% SET `status` = :status WHERE `uid` = :uid LIMIT 1',':uid',$userInfo->uid, ':status',$status);

	mydb::query('DELETE FROM %cache% WHERE `headers` = :username',':username',$userInfo->username);

	$ret .= notify('User '.$username.' was '.($status == 'block' ? 'blocked' : 'active').'.');

	R::model('watchdog.log','Admin','User '.($status == 'block' ? 'Block' : 'Active'),'User '.$uid.' ('.$userInfo->username.') was '.($status == 'block' ? 'blocked' : 'active').'.', i()->uid, $uid);
	return $ret;
}
?>