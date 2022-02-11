<?php
function flood_app_event($self) {
	$ret.=R::View('flood.app.head',false);
	$ret.='<div id="flood-event" class="app" data-load="flood/event/init"></div>';
	$ret.=R::View('flood.app.foot');
	echo $ret;
	die;
	return $ret;
}
?>