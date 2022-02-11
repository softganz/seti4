<?php
function admin_config_online($self) {
	$self->theme->title='Clear user online';

	$ret .= R::Page('stats.online', NULL);
	return $ret;
}
?>