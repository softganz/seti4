<?php
/**
 * Project Application
 *
 * @param Object $topic
 */
function project_app_signout($self) {
	project_model::init_app_mainpage();
	$ret.='<h2>Sign out</h2>';
	$ret.='<a class="sg-action button" href="'.url('signout').'" data-rel="#main" data-ret="'.url('project/app/activity').'">ออกจากระบบ</a>';
	return $ret;
}
?>