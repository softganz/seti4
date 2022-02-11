<?php
function ibuy_green_signout($self) {
	R::View('toolbar',$self,'Sign Out','none');

	if (post('action')=='signout') {
		R::Model('signout');
		location('ibuy/green/app');
	} else if (i()->ok) {
		//$ret.='<div align="center" style="padding: 32px 0;"><a class="btn -primary" href="'.url('imed/app/signout',array('action'=>'signout')).'"><i class="icon -material -white">lock_open</i><span>{tr:SIGN OUT}</a></div>';

		$ret.='<div align="center" style="padding: 32px 0;"><a class="btn -primary" href="'.url('ibuy/green/signout',array('action'=>'signout')).'"><i class="icon -material -white">lock_open</i><span>{tr:SIGN OUT}</a></div>';
	} else {
		location('ibuy/green/app/my');
	}
	return $ret;
}
?>