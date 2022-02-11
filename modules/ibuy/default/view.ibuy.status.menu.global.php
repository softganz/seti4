<?php
/**
 * class constructor
 *
 * Set global submenu
 */
function view_ibuy_status_menu_global($self) {
	$GLOBALS['submenu']='<li><a href="'.url('ibuy/status/order').'">Order Status</a></li>
<li><a href="'.url('ibuy/status/claim').'">Claim Status</a></li>
<li><a href="'.url('ibuy/status/cart').'">Cart Status</a></li>
'.(user_access('administer ibuys')?'<li><a href="'.url('ibuy/status/monitor').'">Order and Claim Monitor</a></li>':'');
}
?>