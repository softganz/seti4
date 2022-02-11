<?php
function garage_user($self) {
	$ret.='<h2>ยินดีต้อนรับสู่ระบริหารต้นทุนอู่ซ่อมรถ</h3>';
	new Toolbar($self, i()->name);

	$shopInfo=R::Model('garage.get.shop');

	$tables = new Table();
	foreach ($shopInfo as $key => $value) {
		if (is_string($value)) $tables->rows[]=array($key,$value);
	}
	//$ret.=$tables->build();
	return $ret;
}
?>