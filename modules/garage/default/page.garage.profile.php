<?php
/**
* Garage User Profile
* Created 2019-12-02
* Modify  2019-12-02
*
* @param Object $self
* @param Int $userId
* @return String
*/

$debug = true;

function garage_profile($self, $userId = NULL) {
	$userInfo = R::Model('user.get',$userId);

	if ($userInfo->_empty) return message('error','User <em>'.$userId.'</em> not exists.');

	if (!user_access('administer users,access user profiles','change own profile',$userInfo->uid)) return message('error','Access denied');

	$ret .= '<header class="header -box -hidden">'._HEADER_BACK.'<h3>'.$userInfo->name.'</h3></header>';
	// Increase profile view
	mydb::query('UPDATE %users% SET `views`=`views`+1 WHERE `uid`=:uid LIMIT 1',':uid',$userInfo->uid);

	$userInfo->views++;

	$ret .= '<div class="" style="margin:0 0 16px; padding: 16px 0; position:relative; background-color: #fff; text-align: center;">';
	$ret .= '<div style="width: 196px; height: 196px; margin: 0px auto 32px; display: block; border-radius: 50%; overflow: hidden; border: 2px #eee solid;"><img src="'.model::user_photo($userInfo->username).'" width="100%" height="100%" /></a></div>';

	$ret .='<b>'. $userInfo->name.'</b>';

	$ret .= '</div>';

	if (is_admin()) {
		$ret .= '<nav class="nav -page -sg-text-center"><a class="btn -link" href="'.url('admin/user/logas/name/'.$userInfo->username).'"><i class="icon -material">how_to_reg</i><span>LOG AS '.$userInfo->name.'</span></a></nav>';
	}


	return $ret;
}
?>