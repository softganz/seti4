<?php
function view_admin_menu_site() {
	$ret='<h3><a href="'.url('admin/site').'">Site Building</a></h3>';
	$ret.='<dl class="admin-list">
<dt><a href="'.url('admin/install').'">Installation</a></dt><dd>Install new table with other table prefix.</dd>
<dt><a href="'.url('admin/site/module').'">Modules</a></dt><dd>Add / Remove / Configuration site modules.</dd>
<dt><a href="'.url('admin/site/info').'">Site information</a></dt><dd>Change basic site information, such as the site name, slogan, e-mail address, mission, front page and more.</dd>
<dt><a href="'.url('admin/site/init').'">Site initial command</a></dt><dd>Command execute before request process.</dd>
<dt><a href="'.url('admin/site/complete').'">Site completed command</a></dt><dd>Command execute before tag &lt;/body&gt; was display.</dd>
<dt><a href="'.url('admin/site/maintenance').'">Site maintenance</a></dt><dd>Take the site offline for maintenance or bring it back online.</dd>
<dt><a href="'.url('admin/site/readonly').'">Site readonly</a></dt><dd>Take the site into readonly for maintenance.</dd>
<dt><a href="'.url('admin/site/theme').'">Themes</a></dt><dd>Change which theme your site uses or allows users to set.</dd>
<dt><a href="'.url('admin/site/upgrade').'">Upgrades</a></dt><dd>Upgrade website database.</dd>
<dt><a href="'.url('admin/site/path').'">URL aliases</a></dt><dd>Change your site\'s URL paths by aliasing them..</dd>
</dl>';
	return $ret;
}
?>