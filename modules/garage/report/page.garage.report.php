<?php
function garage_report($self) {
	$shopInfo=R::Model('garage.get.shop');

	R::Model('garage.verify',$self, $shopInfo,'REPORT');

	new Toolbar($self,'วิเคราะห์');


	$self->theme->sidebar = R::View('garage.report.menu');

	$ret .= R::Page('garage.report.home', $self);
	return $ret;
}
?>