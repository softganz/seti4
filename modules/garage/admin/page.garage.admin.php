<?php
function garage_admin($self) {
	new Toolbar($self,'Garage Administration');

	$ret = '<h2>ยินดีต้อนรับสู่ระบริหารต้นทุนอู่ซ่อมรถ</h3>';

	$self->theme->sidebar = R::View('garage.admin.menu');

	$shopInfo = R::Model('garage.get.shop');

	//$ret .= print_o($shopInfo,'$shopInfo');

	return $ret;
}
?>