<?php
/**
* Paper Permission
*
* @return String
*/

$debug = true;

function module_paper_permission() {
	$perm = array();
	$types = CommonModel::get_topic_type();
	foreach ($types->items as $type) {
		if ($type->module && $type->module!='paper') continue;
		$perm[] = 'create '.$type->type.' paper';
	}
	return implode(',',$perm);
}
?>