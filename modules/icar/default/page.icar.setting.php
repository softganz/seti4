<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

/**
 * Setting
 *
 * @return String
 */
function icar_setting($self) {
	R::View('icar.toolbar', $self, 'Setting');

	$ret.='<h3>รายงาน</h3>';
	$ret.=R::Page('icar.report',NULL);

	if (user_access('administor icar')) {
		$ret.='<h3>Administrator</h3><ul><li><a href="'.url('icar/admin').'">Administrator Dashboard</a></li></ul>';
	}

	return $ret;
}
?>