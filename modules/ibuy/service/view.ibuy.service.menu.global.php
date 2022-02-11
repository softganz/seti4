<?php
function view_ibuy_service_menu_global() {
	$GLOBALS['submenu']='<li><a href="'.url('ibuy/service/claim').'">Claim</a></li>
<li><a href="'.url('ibuy/service/product').'">Product and Stock</a></li>
<li><a href="'.url('ibuy/report').'">Report</a></li>';
}
?>