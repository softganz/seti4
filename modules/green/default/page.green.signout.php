<?php
function green_signout($self) {
	new Toolbar($self,'Sign Out','none');

	if (post('action')=='signout') {
		R::Model('signout');
		location('green/app');
	} else if (i()->ok) {
		//$ret.='<div align="center" style="padding: 32px 0;"><a class="btn -primary" href="'.url('imed/app/signout',array('action'=>'signout')).'"><i class="icon -material -white">lock_open</i><span>{tr:SIGN OUT}</a></div>';

		$ret.='<div align="center" style="padding: 32px 0;"><a class="btn -primary" href="'.url('green/signout',array('action'=>'signout')).'"><i class="icon -material -white">lock_open</i><span>{tr:SIGN OUT}</a></div>';
	} else {
		location('green/app/my');
	}
	return $ret;
}
?>