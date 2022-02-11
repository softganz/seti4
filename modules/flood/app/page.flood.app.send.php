<?php
function flood_app_send($self) {
	$ret.=R::View('flood.app.head',false);
	$ret.='<div id="flood-event" class="app" data-load="flood/event/send"></div>';
	if (!i()->ok) $ret.='<div class="flood__event--register"><a class="sg-action" href="'.url('user/register',array('rel'=>'flood-event')).'" data-rel="#flood-event">สมัครสมาชิก</a></p>';
	$ret.=R::View('flood.app.foot');
	echo $ret;
	die;
	return $ret;
}
?>