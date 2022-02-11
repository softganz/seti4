<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

import('model:user.php');

// @deprecated :: use UserModel::get()
function r_user_get($conditions, $options = '{}') {
	return UserModel::get($conditions, $options);
}
?>