<?php
/**
* Model Name
* Created 2019-06-01
* Modify  2019-06-01
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function on_ibuy_paper_post_permission() {
	return user_access('administer ibuys,create ibuy paper');
}
?>