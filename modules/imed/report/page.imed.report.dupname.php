<?php

/**
 * iMed report รายชื่อคนพิการเพิ่มใหม่
 *
 */
function imed_report_dupname($self) {
	$isAdmin = user_access('administer imeds');

	if (!$isAdmin) return message('error','access denied');

	$ret .= R::Page('org.admin.merge',NULL);

	return $ret;
}
?>