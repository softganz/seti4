<?php
/**
 * Home page
 *
 * @return String
 */
function saveup($self) {
	R::View('saveup.toolbar',$self,'ระบบงานกลุ่มออมทรัพย์ '.cfg('saveup.version'));
	$ret.=R::View('saveup.menu.main');
	return $ret;
}
?>