<?php
// view user password
function admin_user_password_view($self) {
	$uid = post('uid');

	/* right for root only */
	if (i()->uid != 1) return message('error','access denied','admin');

	$ret = '<header class="header"><h3>View password</h3></header>';

	$ret .= '<form method="post" action="'.url('admin/user/password/view').'">Enter user id or user name : <input class="form-text" type="text" name="uid" value="'.htmlspecialchars($uid).'" placeholder="UserId or Username" > <button class="btn -primary" type="submit"><i class="icon -material">search</i><span>View Password</span></button></form>';

	if (empty($uid) || $uid == 1 || in_array($uid,array('root','softganz'))) return $ret;

	if (is_numeric($uid)) mydb::where('`uid` = :uid', ':uid', $uid);
	else if (is_string($uid)) mydb::where('`username` = :uid', ':uid', $uid);

	$stmt = 'SELECT * FROM %users% %WHERE% LIMIT 1';
	$rs = mydb::select($stmt);

	if ($rs->_empty) return $ret.message('error','Data not found');

	$de_password=sg_decrypt($rs->password,cfg('encrypt_key'));

	$ret .= '<p>Name : <b>'.$rs->name.'</b><br />'
		. 'Username : <b>'.$rs->username.'</b><br />'
		. 'E-Mail : <b>'.$rs->email.'</b><br />'
		. 'Password : <b>'.$de_password.'</b><br />'
		. '</p>';
	return $ret;
}
?>