<?php
function view_admin_menu_log() {
	$ret.='<h3><a href="'.url('admin/log').'">Logs</a></h3>';

	$ret.='<dl class="admin-list">
<dt><a href="'.url('admin/log/ban').'">Ban List</a></dt><dd>View IP ban list.</dd>
<dt><a href="'.url('admin/log/cache').'">Cache viewer</a></dt><dd>View cache log.</dd>
<dt><a href="'.url('admin/log/clear').'">Clear log</a></dt><dd>Clear old log.</dd>
<dt><a href="'.url('watchdog').'">Recent log entries</a></dt><dd>View recent log entries.</dd>
<dt><a href="'.url('watchdog/list/keyword/access denied').'">Last "access denied" errors</a></dt><dd>Last of "Access denied" url address</dd>
<dt><a href="'.url('watchdog/list/keyword/page not found').'">Last "page not found" errors</a></dt><dd>Last of error "Page not found" url address</dd>

<dt><a href="'.url('watchdog/list/keyword/access denied').'">Top "access denied" errors</a></dt><dd>Top of Access denied url address</dd>
<dt><a href="'.url('watchdog/list/keyword/page not found').'">Top "page not found" errors</a></dt><dd></dd>
<dt><a href="'.url('admin/log').'">Status report</a></dt><dd></dd>
<dt><a href="'.url('watchdog/analysis').'">Log analysis</a></dt><dd></dd>
<dt><a href="'.url('stats').'">Web statistics</a></dt><dd>Show web statistics</dd>
</dl>';
	return $ret;
}
?>