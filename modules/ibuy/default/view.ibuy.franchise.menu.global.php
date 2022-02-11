<?php
function view_ibuy_franchise_menu_global() {
	$GLOBALS['submenu']='<li><a href="'.url('ibuy/franchise/list/name').'">List by Name</a></li>
<li><a href="'.url('ibuy/franchise/list/province').'">List by Province</a></li>
<li><a href="'.url('ibuy/franchise/search').'">Franchise Search</a></li>'.
(user_access('create own shop') ? _NL.'<li><a href="'.url('ibuy/franchise/myshop').'">My Shop</a></li>'._NL:'');
}
?>