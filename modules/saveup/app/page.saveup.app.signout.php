<?php
/**
 * Project Application
 *
 * @param Object $topic
 */
function saveup_app_signout($self) {
	saveup_model::init_app_mainpage();
	$ret.='<h2>Sign out</h2>';
	$ret.='<a class="sg-action button" href="'.url('signout').'" data-rel="#main" data-ret="'.url('saveup/app/main').'">ออกจากระบบ</a>';
	return $ret;
}
?>