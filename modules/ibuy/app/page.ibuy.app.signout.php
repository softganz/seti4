<?php
function ibuy_app_signout($self) {
	R::View('ibuy.toolbar',$self,'Sign Out','app.hatyaigogreen');

	if (post('action')=='signout') {
		R::Model('signout');
		location('ibuy/app/hatyaigogreen');
	} else if (i()->ok) {
		$ret.='<p align="center"><a class="btn -primary" href="'.url('ibuy/app/signout',array('action'=>'signout')).'"><i class="icon -back -white"></i><span>Sign out</a></p>';
	}
	return $ret;
}
?>