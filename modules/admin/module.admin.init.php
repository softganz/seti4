<?php
/**
* Admin :: Module Init
* Created 2021-06-19
* Modify  2021-06-19
*
* @param Object $self
*
* @usage call by controller when page /admin request
*/

$debug = true;

function module_admin_init($self) {
	R::View('admin.toolbar',$self,'Web Site Administrator');
}
?>