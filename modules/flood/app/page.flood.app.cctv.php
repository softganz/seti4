<?php
function flood_app_cctv($self) {
	$ret.=R::View('flood.app.head',false);
	$ret.='<div id="flood-event" class="app">';
	$ret.=R::Page('flood.app.basin');
	$ret.='</div>';
	$ret.=R::View('flood.app.foot');
	echo $ret;
	die;
	return $ret;
}
?>