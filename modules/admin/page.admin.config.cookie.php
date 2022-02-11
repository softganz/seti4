<?php
function admin_config_cookie($self) {
	$self->theme->title='View cookies value';
	$ret.=print_o($_COOKIE,'$_COOKIE');
	return $ret;
}
?>