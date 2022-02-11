<?php
function admin_log_cache_clear($self) {
	mydb::query('TRUNCATE TABLE %cache%');
	$ret.=message('status','All cache was clear');
	location('user');
	return $ret;
}
?>