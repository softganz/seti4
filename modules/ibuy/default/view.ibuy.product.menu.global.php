<?php
/**
 * class constructor
 *
 * Set global submenu
 */
function view_ibuy_product_menu_global() {
	$GLOBALS['submenu']='<li><a href="'.url('ibuy/product/name').'">List by Name</a></li>
<li><a href="'.url('ibuy/category').'">List by Category</a></li>
<li><a href="'.url('ibuy/product/search').'">Search Product</a></li>'.
(user_access('create ibuy paper')?_NL.'<li><a href="'.url('ibuy/product/post').'">Add New Product</a></li>'._NL:'');
}
?>