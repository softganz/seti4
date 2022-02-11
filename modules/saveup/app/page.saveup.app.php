<?php
/**
 * Project Application
 *
 * @param Object $topic
 */
function saveup_app($self) {
	saveup_model::init_app_mainpage();
	unset($self->theme->title);

	$ret.=R::Page('saveup.app.main');
	cfg('navigator','<nav class="nav -main"><ul class="ui-nav"><li class="ui-item"><a class="sg-action" data-rel="#primary" href="'.url('saveup/app/main').'">ข้อมูลสมาชิก</a></li><li class="ui-item"><a class="sg-action" data-rel="#primary" href="'.url('saveup/app/transfer').'">แจ้งโอนเงิน</a></li><li class="ui-item"><a class="sg-action" data-rel="#primary" href="'.url('saveup/app/welfare').'">สวัสดิการ</a></li></ul></nav>');
	page_class('-appmainpage');
	return $ret;
}
?>