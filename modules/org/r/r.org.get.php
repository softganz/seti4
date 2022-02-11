<?php
/**
* Organization Get
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

import('model:org.php');

// @deprecated
function r_org_get($conditions = NULL, $options = '{}') {
	return OrgModel::get($conditions, $options);
}
?>