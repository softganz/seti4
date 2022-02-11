<?php
function imed_app_signout($self) {
	R::View('imed.toolbar',$self,'Sign Out','none');

	if (post('action')=='signout') {
		R::Model('signout');
		location('imed/app');
	} else if (i()->ok) {
		$ret.='<div align="center" style="padding: 32px 0;"><a class="btn -primary" href="'.url('imed/app/signout',array('action'=>'signout')).'"><i class="icon -material -white">lock_open</i><span>{tr:SIGN OUT}</a></div>';
	}
	return $ret;
}
?>